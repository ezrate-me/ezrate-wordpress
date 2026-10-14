<?php
/**
 * HTML of the widgets (ticker, price bar, cards, table, chart). Everything is rendered on the server from the cached data;
 * the small front-end script only switches chart ranges.
 *
 * @package EZRate_Price_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Widget renderer.
 */
final class EZRPW_Render {

	const THEMES = array(
		'light' => array(
			'bg'     => '#ffffff',
			'fg'     => '#111318',
			'muted'  => '#6b7280',
			'border' => '#e6e3db',
			'accent' => '#1f3fd1',
			'up'     => '#0b8a55',
			'down'   => '#d1343f',
			'soft'   => '#f5f4f0',
		),
		'dark'  => array(
			'bg'     => '#111318',
			'fg'     => '#f3f4f6',
			'muted'  => '#9ca3af',
			'border' => '#2a2e36',
			'accent' => '#8fa3ff',
			'up'     => '#4ade9a',
			'down'   => '#ff7a84',
			'soft'   => '#1c1f26',
		),
	);

	/**
	 * Render a widget.
	 *
	 * @param array      $w    Widget options (sanitized).
	 * @param array|null $data Data snapshot.
	 * @param bool       $preview Admin preview (fixed bars are shown inline).
	 * @return string
	 */
	public static function widget( $w, $data, $preview = false ) {
		$lang = $w['lang'];
		$dir  = 'fa' === $lang ? 'rtl' : 'ltr';
		$pos  = $preview ? 'inline' : $w['position'];

		$classes = array( 'ezrpw', 'ezrpw-t-' . $w['type'], 'ezrpw-size-' . $w['size'], 'ezrpw-theme-' . $w['theme'] );
		if ( 'inline' !== $pos ) {
			$classes[] = 'ezrpw-fixed';
			$classes[] = 'ezrpw-fixed-' . $pos;
			if ( $w['hide_mobile'] ) {
				$classes[] = 'ezrpw-hide-mobile';
			}
		}
		if ( 'cards' === $w['type'] && $w['columns'] > 0 ) {
			$classes[] = 'ezrpw-ncols';
		}
		if ( 'system' === $w['font'] ) {
			$classes[] = 'ezrpw-font-system';
		}

		if ( ! $data || empty( $data['rates'] ) ) {
			$body = '<div class="ezrpw-empty">' . esc_html( EZRPW_Format::t( $lang, 'nodata' ) ) . '</div>';
		} else {
			switch ( $w['type'] ) {
				case 'bar':
					$body = self::bar( $w, $data );
					break;
				case 'cards':
					$body = self::cards( $w, $data );
					break;
				case 'table':
					$body = self::table( $w, $data );
					break;
				case 'chart':
					$body = self::chart( $w, $data );
					break;
				default:
					$body = self::ticker( $w, $data );
			}
			if ( $w['show_footer'] && in_array( $w['type'], array( 'cards', 'table', 'chart' ), true ) ) {
				$body .= self::footer( $w, $data );
			}
		}

		return sprintf(
			'<div class="%1$s" dir="%2$s" lang="%3$s" style="%4$s" data-ezrpw="%5$d">%6$s</div>',
			esc_attr( implode( ' ', $classes ) ),
			esc_attr( $dir ),
			esc_attr( $lang ),
			esc_attr( self::style_vars( $w ) ),
			(int) $w['id'],
			$body // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- built from escaped parts below
		);
	}

	/**
	 * CSS variables of the theme with the custom colours on top.
	 *
	 * @param array $w Widget.
	 * @return string
	 */
	private static function style_vars( $w ) {
		$t    = self::THEMES[ $w['theme'] ];
		$vars = array();
		foreach ( array( 'bg', 'fg', 'muted', 'border', 'accent', 'up', 'down' ) as $k ) {
			$vars[] = '--ezrpw-' . $k . ':' . ( '' !== $w['colors'][ $k ] ? $w['colors'][ $k ] : $t[ $k ] );
		}
		$vars[] = '--ezrpw-soft:' . $t['soft'];
		$vars[] = '--ezrpw-radius:' . (int) $w['radius'] . 'px';
		$vars[] = '--ezrpw-speed:' . (int) $w['speed'] . 's';
		if ( $w['columns'] > 0 ) {
			$vars[] = '--ezrpw-cols:' . (int) $w['columns'];
		}
		return implode( ';', $vars );
	}

	/**
	 * Rates of the widget in its order.
	 *
	 * @param array $w    Widget.
	 * @param array $data Data.
	 * @return array
	 */
	private static function rates( $w, $data ) {
		$out = array();
		foreach ( $w['assets'] as $sym ) {
			if ( isset( $data['rates'][ $sym ] ) ) {
				$out[] = $data['rates'][ $sym ];
			}
		}
		return $out;
	}

	/**
	 * Link of a rate to its page (only when the site owner turned it on in the settings).
	 *
	 * @param array  $r    Rate.
	 * @param array  $data Data.
	 * @param string $lang Language.
	 * @return string
	 */
	private static function href( $r, $data, $lang ) {
		$s = EZRPW_Widgets::settings();
		if ( ! $s['link_rates'] || '' === $r['path'] ) {
			return '';
		}
		return untrailingslashit( $data['site'] ) . $r['path'] . '/' . $lang;
	}

	/**
	 * Name (with icon), optionally linked.
	 *
	 * @param array $r    Rate.
	 * @param array $w    Widget.
	 * @param array $data Data.
	 * @param int   $size Icon size.
	 * @return string
	 */
	private static function name( $r, $w, $data, $size = 18 ) {
		$lang = $w['lang'];
		$icon = $w['show_icons'] && $r['icon'] ? sprintf( '<img class="ezrpw-ic" src="%s" alt="" width="%d" height="%d" loading="lazy" decoding="async">', esc_url( $r['icon'] ), $size, $size ) : '';
		$text = '<span class="ezrpw-n">' . esc_html( $r['name'][ $lang ] ) . '</span>';
		$href = self::href( $r, $data, $lang );
		return $href ? sprintf( '<a class="ezrpw-name" href="%s" target="_blank" rel="noopener">%s%s</a>', esc_url( $href ), $icon, $text ) : '<span class="ezrpw-name">' . $icon . $text . '</span>';
	}

	/**
	 * Change badge.
	 *
	 * @param array  $r    Rate.
	 * @param string $lang Language.
	 * @return string
	 */
	private static function change( $r, $lang ) {
		if ( null === $r['change_pct'] ) {
			return '';
		}
		$cls = $r['change_pct'] > 0 ? 'ezrpw-up' : ( $r['change_pct'] < 0 ? 'ezrpw-dn' : 'ezrpw-flat' );
		return '<span class="ezrpw-c ' . $cls . '" dir="ltr">' . esc_html( EZRPW_Format::pct( $r['change_pct'], $lang ) ) . '</span>';
	}

	/**
	 * One inline item (ticker and bar).
	 *
	 * @param array $r    Rate.
	 * @param array $w    Widget.
	 * @param array $data Data.
	 * @return string
	 */
	private static function item( $r, $w, $data ) {
		$lang = $w['lang'];
		$buy  = $w['show_buy'] && ! EZRPW_Format::in_usd( $r ) ? '<span class="ezrpw-b">' . esc_html( EZRPW_Format::t( $lang, 'buy' ) . ' ' . EZRPW_Format::price( $r, $lang, 'buy' ) ) . '</span>' : '';
		return '<span class="ezrpw-it">' . self::name( $r, $w, $data ) . '<b class="ezrpw-p">' . esc_html( EZRPW_Format::price( $r, $lang ) ) . '</b>' . $buy . ( $w['show_change'] ? self::change( $r, $lang ) : '' ) . '</span>';
	}

	/**
	 * Moving ticker.
	 *
	 * @param array $w    Widget.
	 * @param array $data Data.
	 * @return string
	 */
	private static function ticker( $w, $data ) {
		$items = '';
		foreach ( self::rates( $w, $data ) as $r ) {
			$items .= self::item( $r, $w, $data );
		}
		// the list twice: the animation slides by half its width and loops without a gap.
		return '<div class="ezrpw-track-wrap"><div class="ezrpw-track">' . $items . '<span class="ezrpw-dup" aria-hidden="true">' . $items . '</span></div></div>';
	}

	/**
	 * Static price bar.
	 *
	 * @param array $w    Widget.
	 * @param array $data Data.
	 * @return string
	 */
	private static function bar( $w, $data ) {
		$items = '';
		foreach ( self::rates( $w, $data ) as $r ) {
			$items .= self::item( $r, $w, $data );
		}
		return '<div class="ezrpw-row">' . $items . '</div>';
	}

	/**
	 * Price cards.
	 *
	 * @param array $w    Widget.
	 * @param array $data Data.
	 * @return string
	 */
	private static function cards( $w, $data ) {
		$lang = $w['lang'];
		$out  = '';
		foreach ( self::rates( $w, $data ) as $r ) {
			$unit = EZRPW_Format::in_usd( $r ) ? '' : '<small>' . esc_html( EZRPW_Format::t( $lang, 'toman' ) ) . '</small>';
			$buy  = $w['show_buy'] && ! EZRPW_Format::in_usd( $r ) ? '<span class="ezrpw-b">' . esc_html( EZRPW_Format::t( $lang, 'buy' ) . ' ' . EZRPW_Format::price( $r, $lang, 'buy' ) ) . '</span>' : '';
			$out .= '<div class="ezrpw-card">' . self::name( $r, $w, $data, 22 ) . '<div class="ezrpw-cp"><b class="ezrpw-p">' . esc_html( EZRPW_Format::price( $r, $lang ) ) . '</b>' . $unit . '</div>' . $buy . ( $w['show_change'] ? self::change( $r, $lang ) : '' ) . '</div>';
		}
		return '<div class="ezrpw-cards">' . $out . '</div>';
	}

	/**
	 * Price table.
	 *
	 * @param array $w    Widget.
	 * @param array $data Data.
	 * @return string
	 */
	private static function table( $w, $data ) {
		$lang  = $w['lang'];
		$head  = '<th>' . esc_html( EZRPW_Format::t( $lang, 'name' ) ) . '</th>';
		$head .= $w['show_buy'] ? '<th>' . esc_html( EZRPW_Format::t( $lang, 'buy' ) ) . '</th><th>' . esc_html( EZRPW_Format::t( $lang, 'sell' ) ) . '</th>' : '<th>' . esc_html( EZRPW_Format::t( $lang, 'price' ) ) . '</th>';
		$head .= $w['show_change'] ? '<th>' . esc_html( EZRPW_Format::t( $lang, 'change' ) ) . '</th>' : '';
		$rows  = '';
		foreach ( self::rates( $w, $data ) as $r ) {
			$usd   = EZRPW_Format::in_usd( $r );
			$rows .= '<tr><td>' . self::name( $r, $w, $data ) . '</td>';
			if ( $w['show_buy'] ) {
				$rows .= '<td class="ezrpw-num">' . ( $usd ? '—' : esc_html( EZRPW_Format::price( $r, $lang, 'buy' ) ) ) . '</td>';
			}
			$rows .= '<td class="ezrpw-num"><b>' . esc_html( EZRPW_Format::price( $r, $lang ) ) . '</b></td>';
			$rows .= $w['show_change'] ? '<td class="ezrpw-num">' . self::change( $r, $lang ) . '</td>' : '';
			$rows .= '</tr>';
		}
		return '<div class="ezrpw-table-wrap"><table class="ezrpw-tbl"><thead><tr>' . $head . '</tr></thead><tbody>' . $rows . '</tbody></table></div>';
	}

	/**
	 * Chart of one asset: one SVG per range, the buttons switch between them.
	 *
	 * @param array $w    Widget.
	 * @param array $data Data.
	 * @return string
	 */
	private static function chart( $w, $data ) {
		$lang = $w['lang'];
		$sym  = $w['chart_asset'];
		if ( ! isset( $data['rates'][ $sym ] ) ) {
			return '<div class="ezrpw-empty">' . esc_html( EZRPW_Format::t( $lang, 'nodata' ) ) . '</div>';
		}
		$r     = $data['rates'][ $sym ];
		$head  = '<div class="ezrpw-ch-h"><div>' . self::name( $r, $w, $data, 22 ) . '<div class="ezrpw-ch-p"><b class="ezrpw-p">' . esc_html( EZRPW_Format::price( $r, $lang ) ) . '</b>';
		$head .= ( EZRPW_Format::in_usd( $r ) ? '' : '<small>' . esc_html( EZRPW_Format::t( $lang, 'toman' ) ) . '</small>' ) . ( $w['show_change'] ? self::change( $r, $lang ) : '' ) . '</div></div>';
		$btns  = '';
		foreach ( EZRPW_Widgets::RANGES as $k ) {
			$btns .= sprintf( '<button type="button" data-range="%1$s" aria-pressed="%2$s">%3$s</button>', esc_attr( $k ), $k === $w['range'] ? 'true' : 'false', esc_html( EZRPW_Format::t( $lang, $k ) ) );
		}
		$head  .= '<div class="ezrpw-rg" role="group">' . $btns . '</div></div>';
		$panes  = '';
		$series = isset( $data['series'][ $sym ] ) ? $data['series'][ $sym ] : array();
		foreach ( EZRPW_Widgets::RANGES as $k ) {
			$panes .= sprintf( '<div class="ezrpw-pane" data-range="%1$s"%2$s>%3$s</div>', esc_attr( $k ), $k === $w['range'] ? '' : ' hidden', self::svg( isset( $series[ $k ] ) ? $series[ $k ] : array(), $k, $w ) );
		}
		return '<div class="ezrpw-ch">' . $head . $panes . '</div>';
	}

	/**
	 * Area chart of one range with 6 time labels and the low / high.
	 *
	 * @param array  $pts   [[time, close], …].
	 * @param string $range Range.
	 * @param array  $w     Widget.
	 * @return string
	 */
	private static function svg( $pts, $range, $w ) {
		$lang = $w['lang'];
		$n    = count( $pts );
		if ( $n < 2 ) {
			return '<div class="ezrpw-nochart">—</div>';
		}
		$vals = array_column( $pts, 1 );
		$lo   = min( $vals );
		$hi   = max( $vals );
		$wd   = 600;
		$ht   = 160;
		$span = ( $hi - $lo ) ? ( $hi - $lo ) : 1;
		$path = '';
		foreach ( $vals as $i => $v ) {
			$x     = round( $i * $wd / ( $n - 1 ), 1 );
			$y     = round( 8 + ( $ht - 16 ) * ( 1 - ( $v - $lo ) / $span ), 1 );
			$path .= ( $i ? 'L' : 'M' ) . $x . ' ' . $y . ' ';
		}
		$area  = $path . 'L' . $wd . ' ' . $ht . ' L0 ' . $ht . ' Z';
		$color = '' !== $w['colors']['chart'] ? $w['colors']['chart'] : ( end( $vals ) >= reset( $vals ) ? 'var(--ezrpw-up)' : 'var(--ezrpw-down)' );
		$pat   = array(
			'1d'  => 'HH:mm',
			'7d'  => 'EEE',
			'1m'  => 'd MMM',
			'all' => 'MMM yy',
		);
		$axis  = '';
		for ( $i = 0; $i < 6; $i++ ) {
			$p     = $pts[ (int) round( $i * ( $n - 1 ) / 5 ) ];
			$axis .= '<span>' . esc_html( EZRPW_Format::date( $p[0], $lang, $pat[ $range ] ) ) . '</span>';
		}
		$svg = sprintf(
			'<svg class="ezrpw-svg" viewBox="0 0 %1$d %2$d" preserveAspectRatio="none" aria-hidden="true" focusable="false"><path d="%3$s" fill="%5$s" fill-opacity=".12"/><path d="%4$s" fill="none" stroke="%5$s" stroke-width="2" vector-effect="non-scaling-stroke" stroke-linejoin="round"/></svg>',
			$wd,
			$ht,
			esc_attr( $area ),
			esc_attr( trim( $path ) ),
			esc_attr( $color )
		);
		// time runs left to right in the SVG, so the labels do too (also in Persian).
		$svg .= '<div class="ezrpw-ax" dir="ltr">' . $axis . '</div>';
		$svg .= '<div class="ezrpw-lohi"><span>' . esc_html( EZRPW_Format::t( $lang, 'low' ) . ' ' . EZRPW_Format::num( $lo, $lang ) ) . '</span><span>' . esc_html( EZRPW_Format::t( $lang, 'high' ) . ' ' . EZRPW_Format::num( $hi, $lang ) ) . '</span></div>';
		return $svg;
	}

	/**
	 * «Updated 05:30 – Currency: Toman», plus the optional credit link.
	 *
	 * @param array $w    Widget.
	 * @param array $data Data.
	 * @return string
	 */
	private static function footer( $w, $data ) {
		$lang   = $w['lang'];
		$s      = EZRPW_Widgets::settings();
		$time   = EZRPW_Format::date( $data['generated_at'], $lang, 'HH:mm' );
		$text   = EZRPW_Format::t( $lang, 'updated' ) . ' ' . $time . ' - ' . EZRPW_Format::t( $lang, 'unit' ) . ': ' . EZRPW_Format::t( $lang, 'toman' );
		$credit = $s['credit'] ? '<a class="ezrpw-credit" href="' . esc_url( untrailingslashit( $data['site'] ) . '/' . $lang ) . '" target="_blank" rel="noopener">' . esc_html( EZRPW_Format::t( $lang, 'credit' ) ) . ' EZRate</a>' : '<span></span>';
		return '<div class="ezrpw-ft">' . $credit . '<span>' . esc_html( $text ) . '</span></div>';
	}
}
