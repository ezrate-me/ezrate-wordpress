<?php
/**
 * Saved widgets (EZRate › Widgets): each one has its own type, assets, language, display and style options.
 *
 * @package EZRate_Price_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget presets stored in one option.
 */
final class EZRPW_Widgets {

	const OPTION   = 'ezrpw_widgets';
	const SETTINGS = 'ezrpw_settings';

	const TYPES     = array( 'ticker', 'bar', 'cards', 'table', 'chart' );
	const RANGES    = array( '1d', '7d', '1m', 'all' );
	const POSITIONS = array( 'inline', 'top', 'bottom', 'before', 'after' );
	const SHOW_ON   = array( 'all', 'front', 'posts', 'pages' );
	const COLORS    = array( 'bg', 'fg', 'muted', 'border', 'accent', 'up', 'down', 'chart' );

	/**
	 * Default options of a new widget.
	 *
	 * @return array
	 */
	public static function defaults() {
		$s = self::settings();
		return array(
			'id'          => 0,
			'title'       => '',
			'type'        => 'ticker',
			'assets'      => array( 'usd', 'eur', 'aed', 'g18', 'emami', 'usdt' ),
			'chart_asset' => 'usd',
			'range'       => '1d',
			'lang'        => $s['default_lang'],
			'theme'       => 'light',
			'colors'      => array_fill_keys( self::COLORS, '' ),
			'radius'      => 12,
			'size'        => 'm',
			'font'        => 'inherit',
			'show_buy'    => false,
			'show_change' => true,
			'show_icons'  => true,
			'show_footer' => true,
			'columns'     => 0,
			'speed'       => 40,
			'position'    => 'inline',
			'show_on'     => 'all',
			'hide_mobile' => false,
		);
	}

	/**
	 * Global settings.
	 *
	 * @return array
	 */
	public static function settings() {
		$s = get_option( self::SETTINGS, array() );
		return wp_parse_args(
			is_array( $s ) ? $s : array(),
			array(
				'default_lang' => 0 === strpos( get_locale(), 'fa' ) ? 'fa' : 'en',
				'link_rates'   => false,
				'credit'       => false,
				'cleanup'      => true,
			)
		);
	}

	/**
	 * All saved widgets, keyed by id.
	 *
	 * @return array
	 */
	public static function all() {
		$o     = get_option( self::OPTION, array() );
		$items = isset( $o['items'] ) && is_array( $o['items'] ) ? $o['items'] : array();
		$out   = array();
		foreach ( $items as $id => $w ) {
			$out[ (int) $id ] = self::sanitize( (array) $w, (int) $id );
		}
		return $out;
	}

	/**
	 * One widget.
	 *
	 * @param int $id Widget id.
	 * @return array|null
	 */
	public static function get( $id ) {
		$all = self::all();
		return isset( $all[ (int) $id ] ) ? $all[ (int) $id ] : null;
	}

	/**
	 * Create or update a widget.
	 *
	 * @param array $w Raw options.
	 * @return int Widget id.
	 */
	public static function save( $w ) {
		$o  = get_option( self::OPTION, array() );
		$o  = wp_parse_args(
			is_array( $o ) ? $o : array(),
			array(
				'next'  => 1,
				'items' => array(),
			)
		);
		$id = isset( $w['id'] ) ? (int) $w['id'] : 0;
		if ( $id <= 0 || ! isset( $o['items'][ $id ] ) ) {
			$id = max( 1, (int) $o['next'] );
			++$o['next'];
		}
		$o['items'][ $id ] = self::sanitize( $w, $id );
		update_option( self::OPTION, $o );
		EZRPW_Data::invalidate_if_charts_missing();
		return $id;
	}

	/**
	 * Delete a widget.
	 *
	 * @param int $id Widget id.
	 */
	public static function delete( $id ) {
		$o = get_option( self::OPTION, array() );
		if ( isset( $o['items'][ (int) $id ] ) ) {
			unset( $o['items'][ (int) $id ] );
			update_option( self::OPTION, $o );
		}
	}

	/**
	 * First install: a ticker the site owner can place right away.
	 */
	public static function ensure_default() {
		if ( ! self::all() ) {
			$w          = self::defaults();
			$w['title'] = __( 'Price ticker', 'ezrate-price-widgets' );
			self::save( $w );
		}
	}

	/**
	 * Validate every option (also used for the live preview).
	 *
	 * @param array $w  Raw options.
	 * @param int   $id Widget id.
	 * @return array
	 */
	public static function sanitize( $w, $id = 0 ) {
		$d   = self::defaults();
		$w   = is_array( $w ) ? $w : array();
		$out = array( 'id' => (int) $id );

		$out['title'] = isset( $w['title'] ) ? sanitize_text_field( $w['title'] ) : '';
		$out['type']  = isset( $w['type'] ) && in_array( $w['type'], self::TYPES, true ) ? $w['type'] : $d['type'];

		$assets = isset( $w['assets'] ) ? $w['assets'] : $d['assets'];
		if ( is_string( $assets ) ) {
			$assets = explode( ',', $assets );
		}
		$assets        = array_values( array_unique( array_filter( array_map( 'sanitize_key', (array) $assets ) ) ) );
		$out['assets'] = array_slice( $assets ? $assets : $d['assets'], 0, 20 );

		$out['chart_asset'] = isset( $w['chart_asset'] ) && '' !== sanitize_key( $w['chart_asset'] ) ? sanitize_key( $w['chart_asset'] ) : $out['assets'][0];
		$out['range']       = isset( $w['range'] ) && in_array( $w['range'], self::RANGES, true ) ? $w['range'] : $d['range'];
		$out['lang']        = isset( $w['lang'] ) && in_array( $w['lang'], array( 'fa', 'en' ), true ) ? $w['lang'] : $d['lang'];
		$out['theme']       = isset( $w['theme'] ) && in_array( $w['theme'], array( 'light', 'dark' ), true ) ? $w['theme'] : $d['theme'];

		$colors = array();
		foreach ( self::COLORS as $k ) {
			$v            = isset( $w['colors'][ $k ] ) ? sanitize_hex_color( $w['colors'][ $k ] ) : '';
			$colors[ $k ] = $v ? $v : '';
		}
		$out['colors'] = $colors;

		$out['radius'] = isset( $w['radius'] ) ? max( 0, min( 28, (int) $w['radius'] ) ) : $d['radius'];
		$out['size']   = isset( $w['size'] ) && in_array( $w['size'], array( 's', 'm', 'l' ), true ) ? $w['size'] : $d['size'];
		$out['font']   = isset( $w['font'] ) && in_array( $w['font'], array( 'inherit', 'system' ), true ) ? $w['font'] : $d['font'];

		foreach ( array( 'show_buy', 'show_change', 'show_icons', 'show_footer', 'hide_mobile' ) as $k ) {
			$out[ $k ] = isset( $w[ $k ] ) ? (bool) $w[ $k ] && 'false' !== $w[ $k ] && '0' !== $w[ $k ] : ( isset( $w['_form'] ) ? false : $d[ $k ] );
		}
		$out['columns']  = isset( $w['columns'] ) ? max( 0, min( 6, (int) $w['columns'] ) ) : $d['columns'];
		$out['speed']    = isset( $w['speed'] ) ? max( 10, min( 180, (int) $w['speed'] ) ) : $d['speed'];
		$out['position'] = isset( $w['position'] ) && in_array( $w['position'], self::POSITIONS, true ) ? $w['position'] : $d['position'];
		// a fixed bar at the top / bottom of the screen only for one-line types; above / below the content for every type
		if ( in_array( $out['position'], array( 'top', 'bottom' ), true ) && ! in_array( $out['type'], array( 'ticker', 'bar' ), true ) ) {
			$out['position'] = 'before';
		}
		$out['show_on'] = isset( $w['show_on'] ) && in_array( $w['show_on'], self::SHOW_ON, true ) ? $w['show_on'] : $d['show_on'];
		return $out;
	}

	/**
	 * Does a widget placed automatically show on the current page?
	 *
	 * @param array $w Widget.
	 * @return bool
	 */
	public static function shows_here( $w ) {
		switch ( $w['show_on'] ) {
			case 'front':
				return is_front_page();
			case 'posts':
				return is_singular( 'post' );
			case 'pages':
				return is_page();
			default:
				return true;
		}
	}

	/**
	 * Where a widget is placed, in words.
	 *
	 * @param array $w Widget.
	 * @return string
	 */
	public static function place_label( $w ) {
		$labels = array(
			'top'    => __( 'Fixed at the top of the site', 'ezrate-price-widgets' ),
			'bottom' => __( 'Fixed at the bottom of the site', 'ezrate-price-widgets' ),
			'before' => __( 'Above the text of posts / pages', 'ezrate-price-widgets' ),
			'after'  => __( 'Below the text of posts / pages', 'ezrate-price-widgets' ),
		);
		return isset( $labels[ $w['position'] ] ) ? $labels[ $w['position'] ] : __( 'Shortcode / block / widget', 'ezrate-price-widgets' );
	}

	/**
	 * Label of a widget type.
	 *
	 * @param string $type Type.
	 * @return string
	 */
	public static function type_label( $type ) {
		$l = array(
			'ticker' => __( 'Moving ticker', 'ezrate-price-widgets' ),
			'bar'    => __( 'Price bar', 'ezrate-price-widgets' ),
			'cards'  => __( 'Price cards', 'ezrate-price-widgets' ),
			'table'  => __( 'Price table', 'ezrate-price-widgets' ),
			'chart'  => __( 'Chart', 'ezrate-price-widgets' ),
		);
		return isset( $l[ $type ] ) ? $l[ $type ] : $type;
	}
}
