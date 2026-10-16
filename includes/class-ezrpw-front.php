<?php
/**
 * Front end: [ezrate_widget] shortcode, the «EZRate price widget» block, site-wide fixed bars and the assets.
 *
 * @package EZRate_Price_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Front-end hooks.
 */
final class EZRPW_Front {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'wp_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'register_assets' ) );
		add_shortcode( 'ezrate_widget', array( __CLASS__, 'shortcode' ) );
		add_action( 'init', array( __CLASS__, 'register_block' ) );
		add_action( 'wp_footer', array( __CLASS__, 'fixed_bars' ) );
		add_filter( 'body_class', array( __CLASS__, 'body_class' ) );
		add_filter( 'the_content', array( __CLASS__, 'auto_content' ), 20 );
	}

	/**
	 * Widgets placed «above / below the text of posts and pages»: added to the main content of a single post or page
	 * only (not to excerpts, lists, feeds or other queries), so the theme layout is never touched.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function auto_content( $content ) {
		static $done = array();
		if ( is_admin() || is_feed() || ! is_singular() || ! in_the_loop() || ! is_main_query() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
			return $content;
		}
		$post = get_the_ID();
		if ( isset( $done[ $post ] ) ) {
			return $content;
		}
		$done[ $post ] = true;
		$before        = '';
		$after         = '';
		foreach ( EZRPW_Widgets::all() as $w ) {
			if ( ! in_array( $w['position'], array( 'before', 'after' ), true ) || ! EZRPW_Widgets::shows_here( $w ) ) {
				continue;
			}
			$html = '<div class="ezrpw-auto ezrpw-auto-' . esc_attr( $w['position'] ) . '">' . self::render( $w ) . '</div>';
			if ( 'before' === $w['position'] ) {
				$before .= $html;
			} else {
				$after .= $html;
			}
		}
		return $before . $content . $after;
	}

	/**
	 * Styles and the tiny script (chart ranges); enqueued only where a widget is shown.
	 */
	public static function register_assets() {
		wp_register_style( 'ezrpw', EZRPW_URL . 'assets/css/ezrpw.css', array(), EZRPW_VERSION );
		wp_register_script( 'ezrpw', EZRPW_URL . 'assets/js/ezrpw.js', array(), EZRPW_VERSION, true );
	}

	/**
	 * Render a saved widget with its assets.
	 *
	 * @param array $w       Widget.
	 * @param bool  $preview Admin preview.
	 * @return string
	 */
	public static function render( $w, $preview = false ) {
		if ( ! wp_style_is( 'ezrpw', 'registered' ) ) {
			self::register_assets();
		}
		wp_enqueue_style( 'ezrpw' );
		wp_enqueue_script( 'ezrpw' );
		return EZRPW_Render::widget( $w, EZRPW_Data::get(), $preview );
	}

	/**
	 * [ezrate_widget id="2"] — or a quick widget without saving one:
	 * [ezrate_widget type="cards" assets="usd,eur,g18" lang="fa" theme="dark"]
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function shortcode( $atts ) {
		$atts = is_array( $atts ) ? $atts : array();
		$base = null;
		if ( ! empty( $atts['id'] ) ) {
			$base = EZRPW_Widgets::get( (int) $atts['id'] );
		}
		if ( ! $base ) {
			$all  = EZRPW_Widgets::all();
			$base = empty( $atts['id'] ) && empty( $atts['type'] ) && $all ? reset( $all ) : EZRPW_Widgets::defaults();
		}
		$over = array_intersect_key( $atts, array_flip( array( 'type', 'assets', 'chart_asset', 'range', 'lang', 'theme', 'speed', 'columns' ) ) );
		$w    = EZRPW_Widgets::sanitize( array_merge( $base, $over ), (int) $base['id'] );
		if ( 'inline' !== $w['position'] ) {
			// a fixed bar is printed once in the footer (see fixed_bars); inside content it stays inline.
			$w['position'] = 'inline';
		}
		return self::render( $w );
	}

	/**
	 * Block (server-side rendered; the editor shows the same HTML).
	 */
	public static function register_block() {
		// the block's style (block.json › style: "ezrpw") must exist before the block: on the site and inside the
		// iframed editor (Block API 3) WordPress loads it only where the block is shown
		self::register_assets();
		register_block_type(
			EZRPW_DIR . 'blocks/widget',
			array(
				'render_callback' => static function ( $attrs ) {
					$id = isset( $attrs['widgetId'] ) ? (int) $attrs['widgetId'] : 0;
					return self::shortcode( $id ? array( 'id' => $id ) : array() );
				},
			)
		);
		add_action(
			'enqueue_block_editor_assets',
			static function () {
				$list = array();
				foreach ( EZRPW_Widgets::all() as $w ) {
					$list[] = array(
						'value' => $w['id'],
						'label' => '#' . $w['id'] . ' — ' . ( '' !== $w['title'] ? $w['title'] : EZRPW_Widgets::type_label( $w['type'] ) ),
					);
				}
				$handle = generate_block_asset_handle( 'ezrate-price-widgets/widget', 'editorScript' );
				wp_add_inline_script( $handle, 'window.ezrpwWidgets = ' . wp_json_encode( $list ) . '; window.ezrpwAdminUrl = ' . wp_json_encode( admin_url( 'admin.php?page=ezrpw-widgets' ) ) . ';', 'before' );
			}
		);
	}

	/**
	 * Widgets whose place is «fixed at the top / bottom of every page».
	 *
	 * @return array
	 */
	private static function fixed() {
		$out = array();
		foreach ( EZRPW_Widgets::all() as $w ) {
			if ( ! in_array( $w['position'], array( 'top', 'bottom' ), true ) || ! EZRPW_Widgets::shows_here( $w ) ) {
				continue;
			}
			$out[ $w['position'] ] = $w; // One bar per edge.
		}
		return $out;
	}

	/**
	 * Print the fixed bars.
	 */
	public static function fixed_bars() {
		if ( is_admin() ) {
			return;
		}
		foreach ( self::fixed() as $w ) {
			echo self::render( $w ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in EZRPW_Render
		}
	}

	/**
	 * Body padding classes so the fixed bars do not cover the site.
	 *
	 * @param array $classes Classes.
	 * @return array
	 */
	public static function body_class( $classes ) {
		foreach ( self::fixed() as $pos => $w ) {
			$classes[] = 'ezrpw-pad-' . $pos;
			if ( $w['hide_mobile'] ) {
				$classes[] = 'ezrpw-pad-desktop-only';
			}
		}
		return $classes;
	}
}
