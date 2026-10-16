<?php
/**
 * Number, price and date formatting in Persian or English (independent of the site language: each widget has its own).
 *
 * @package EZRate_Price_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Formatting helpers.
 */
final class EZRPW_Format {

	/**
	 * Labels of the front-end widgets in both languages.
	 *
	 * @var array
	 */
	const T = array(
		'fa' => array(
			'buy'     => 'خرید',
			'sell'    => 'فروش',
			'change'  => 'تغییر',
			'name'    => 'نام',
			'price'   => 'قیمت',
			'toman'   => 'تومان',
			'updated' => 'بروزرسانی',
			'unit'    => 'واحد پولی',
			'1d'      => 'روز',
			'7d'      => 'هفته',
			'1m'      => 'ماه',
			'all'     => 'همه',
			'low'     => 'کمترین',
			'high'    => 'بیشترین',
			'credit'  => 'قیمت‌ها از',
			'nodata'  => 'قیمت‌ها به‌زودی نمایش داده می‌شود.',
		),
		'en' => array(
			'buy'     => 'Buy',
			'sell'    => 'Sell',
			'change'  => 'Change',
			'name'    => 'Name',
			'price'   => 'Price',
			'toman'   => 'Toman',
			'updated' => 'Updated',
			'unit'    => 'Currency',
			'1d'      => 'Day',
			'7d'      => 'Week',
			'1m'      => 'Month',
			'all'     => 'All',
			'low'     => 'Low',
			'high'    => 'High',
			'credit'  => 'Rates by',
			'nodata'  => 'Rates will appear shortly.',
		),
	);

	/**
	 * Label.
	 *
	 * @param string $lang Language.
	 * @param string $key  Key.
	 * @return string
	 */
	public static function t( $lang, $key ) {
		return isset( self::T[ $lang ][ $key ] ) ? self::T[ $lang ][ $key ] : $key;
	}

	/**
	 * Persian digits and separators.
	 *
	 * @param string $s Latin text.
	 * @return string
	 */
	public static function fa_digits( $s ) {
		return strtr(
			(string) $s,
			array(
				'0' => '۰',
				'1' => '۱',
				'2' => '۲',
				'3' => '۳',
				'4' => '۴',
				'5' => '۵',
				'6' => '۶',
				'7' => '۷',
				'8' => '۸',
				'9' => '۹',
				',' => '٬',
				'.' => '٫',
			)
		);
	}

	/**
	 * Number with thousands separators.
	 *
	 * @param float  $n        Number.
	 * @param string $lang     Language.
	 * @param int    $decimals Decimals.
	 * @return string
	 */
	public static function num( $n, $lang, $decimals = 0 ) {
		if ( null === $n ) {
			return '—';
		}
		$s = number_format( (float) $n, $decimals, '.', ',' );
		return 'fa' === $lang ? self::fa_digits( $s ) : $s;
	}

	/**
	 * Crypto (except USDT) and the global ounce are quoted in US dollars, the rest in Toman.
	 *
	 * @param array $r Rate.
	 * @return bool
	 */
	public static function in_usd( $r ) {
		return ( ( 'crypto' === $r['category'] && 'usdt' !== $r['symbol'] ) || 'ounce' === $r['symbol'] ) && null !== $r['usd'];
	}

	/**
	 * Price text of a rate (sell or buy).
	 *
	 * @param array  $r    Rate.
	 * @param string $lang Language.
	 * @param string $side sell|buy.
	 * @return string
	 */
	public static function price( $r, $lang, $side = 'sell' ) {
		if ( self::in_usd( $r ) ) {
			$v = (float) $r['usd'];
			return '$' . number_format( $v, $v < 10 ? 4 : 2, '.', ',' );
		}
		return self::num( 'buy' === $side ? $r['buy'] : $r['sell'], $lang );
	}

	/**
	 * Signed percentage («+0.25%», «−1.10٪»).
	 *
	 * @param float|null $v    Change.
	 * @param string     $lang Language.
	 * @return string
	 */
	public static function pct( $v, $lang ) {
		if ( null === $v ) {
			return '';
		}
		$sign = $v > 0 ? '+' : ( $v < 0 ? '−' : '' );
		return $sign . self::num( abs( $v ), $lang, 2 ) . ( 'fa' === $lang ? '٪' : '%' );
	}

	/**
	 * Date/time in Tehran time; Persian calendar for Persian widgets when the intl extension is available.
	 *
	 * @param string $iso     ISO-8601 time.
	 * @param string $lang    Language.
	 * @param string $pattern ICU pattern (HH:mm, EEE, d MMM, MMM yy).
	 * @return string
	 */
	public static function date( $iso, $lang, $pattern = 'HH:mm' ) {
		$ts = strtotime( (string) $iso );
		if ( ! $ts ) {
			return '';
		}
		if ( class_exists( 'IntlDateFormatter' ) ) {
			$locale = 'fa' === $lang ? 'fa_IR@calendar=persian' : 'en_GB';
			$cal    = 'fa' === $lang ? IntlDateFormatter::TRADITIONAL : IntlDateFormatter::GREGORIAN;
			$f      = new IntlDateFormatter( $locale, IntlDateFormatter::NONE, IntlDateFormatter::NONE, 'Asia/Tehran', $cal, $pattern );
			$out    = $f->format( $ts );
			if ( false !== $out ) {
				return $out;
			}
		}
		$map = array(
			'HH:mm'  => 'H:i',
			'EEE'    => 'D',
			'd MMM'  => 'j M',
			'MMM yy' => 'M y',
		);
		$s   = wp_date( isset( $map[ $pattern ] ) ? $map[ $pattern ] : 'H:i', $ts, new DateTimeZone( 'Asia/Tehran' ) );
		return 'fa' === $lang ? self::fa_digits( $s ) : $s;
	}
}
