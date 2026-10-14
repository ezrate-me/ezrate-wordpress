<?php
/**
 * Rates data: fetched server-side from the EZRate data service, cached in the database and refreshed by WP-Cron.
 *
 * The service publishes a new snapshot once per window of its free plan (30 minutes); the plugin asks once per window
 * (plus a manual «refresh now», at most every 5 minutes). Visitors never trigger requests to the service: pages render
 * from the cached copy, and the last good copy is kept when the service cannot be reached.
 *
 * @package EZRate_Price_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Data access and refresh.
 */
final class EZRPW_Data {

	const TRANSIENT = 'ezrpw_data';
	const OPTION    = 'ezrpw_last_data';
	const STATUS    = 'ezrpw_status';
	const LOCK      = 'ezrpw_fetch_lock';
	const BACKOFF   = 'ezrpw_fetch_backoff';
	const MANUAL    = 'ezrpw_manual_refresh';
	const CRON      = 'ezrpw_refresh_event';

	/**
	 * Hooks.
	 */
	public static function init() {
		load_plugin_textdomain( 'ezrate-price-widgets', false, dirname( plugin_basename( EZRPW_FILE ) ) . '/languages' );
		add_filter( 'cron_schedules', array( __CLASS__, 'schedules' ) );
		add_action( self::CRON, array( __CLASS__, 'cron' ) );
		if ( ! wp_next_scheduled( self::CRON ) ) {
			wp_schedule_event( time() + 60, 'ezrpw_half_hour', self::CRON );
		}
	}

	/**
	 * A 30-minute WP-Cron interval.
	 *
	 * @param array $schedules Schedules.
	 * @return array
	 */
	public static function schedules( $schedules ) {
		$schedules['ezrpw_half_hour'] = array(
			'interval' => 30 * MINUTE_IN_SECONDS,
			'display'  => __( 'Every 30 minutes (EZRate)', 'ezrate-price-widgets' ),
		);
		return $schedules;
	}

	/**
	 * Activation: a first widget so the shortcode works right away, and the refresh event.
	 */
	public static function activate() {
		EZRPW_Widgets::ensure_default();
		add_filter( 'cron_schedules', array( __CLASS__, 'schedules' ) );
		if ( ! wp_next_scheduled( self::CRON ) ) {
			wp_schedule_event( time() + 30, 'ezrpw_half_hour', self::CRON );
		}
	}

	/**
	 * Deactivation: stop the refresh event (data and settings stay until uninstall).
	 */
	public static function deactivate() {
		wp_clear_scheduled_hook( self::CRON );
	}

	/**
	 * WP-Cron: refresh when the cached snapshot is stale.
	 */
	public static function cron() {
		if ( false === get_transient( self::TRANSIENT ) ) {
			self::refresh();
		}
	}

	/**
	 * Current data for rendering: the cached snapshot, refreshed when stale (one request per window, with a lock), else the
	 * last good copy. Never throws.
	 *
	 * @return array|null
	 */
	public static function get() {
		$data = get_transient( self::TRANSIENT );
		if ( is_array( $data ) ) {
			return $data;
		}
		if ( ! get_transient( self::BACKOFF ) && ! get_transient( self::LOCK ) ) {
			$res = self::refresh();
			if ( true === $res ) {
				$data = get_transient( self::TRANSIENT );
				if ( is_array( $data ) ) {
					return $data;
				}
			}
		}
		$last = get_option( self::OPTION );
		return is_array( $last ) ? $last : null;
	}

	/**
	 * Assets drawn as charts by the saved widgets (their series are requested with the rates).
	 *
	 * @return string[]
	 */
	public static function chart_assets() {
		$out = array();
		foreach ( EZRPW_Widgets::all() as $w ) {
			if ( 'chart' === $w['type'] && '' !== $w['chart_asset'] ) {
				$out[] = $w['chart_asset'];
			}
		}
		$out = array_values( array_unique( $out ) );
		sort( $out );
		return array_slice( $out, 0, 4 );
	}

	/**
	 * Download a fresh snapshot.
	 *
	 * @param bool $manual True when started by the «Refresh now» button (limited to once per 5 minutes).
	 * @return true|WP_Error
	 */
	public static function refresh( $manual = false ) {
		if ( $manual ) {
			if ( get_transient( self::MANUAL ) ) {
				return new WP_Error( 'ezrpw_wait', __( 'Please wait a few minutes before refreshing again.', 'ezrate-price-widgets' ) );
			}
			set_transient( self::MANUAL, 1, 5 * MINUTE_IN_SECONDS );
		}
		set_transient( self::LOCK, 1, MINUTE_IN_SECONDS );

		$url = add_query_arg( 'charts', rawurlencode( implode( ',', self::chart_assets() ) ), trailingslashit( EZRPW_API ) . 'v1/wp/data' );
		$res = wp_remote_get(
			$url,
			array(
				'timeout' => 8,
				'headers' => array( 'Accept' => 'application/json' ),
			)
		);
		delete_transient( self::LOCK );

		$status = array(
			'attempt' => time(),
			'ok'      => (int) self::status( 'ok' ),
			'error'   => '',
		);
		$error  = null;
		if ( is_wp_error( $res ) ) {
			$error = $res;
		} elseif ( 200 !== (int) wp_remote_retrieve_response_code( $res ) ) {
			/* translators: %d: HTTP status code */
			$error = new WP_Error( 'ezrpw_http', sprintf( __( 'The data service answered with HTTP %d.', 'ezrate-price-widgets' ), (int) wp_remote_retrieve_response_code( $res ) ) );
		} else {
			$body = json_decode( wp_remote_retrieve_body( $res ), true );
			$data = isset( $body['data'] ) && is_array( $body['data'] ) ? self::clean( $body['data'] ) : null;
			if ( ! $data ) {
				$error = new WP_Error( 'ezrpw_format', __( 'The data service sent an unexpected answer.', 'ezrate-price-widgets' ) );
			}
		}

		if ( $error ) {
			$status['error'] = $error->get_error_message();
			update_option( self::STATUS, $status, false );
			// do not retry on every page view while the service is unreachable.
			set_transient( self::BACKOFF, 1, 10 * MINUTE_IN_SECONDS );
			return $error;
		}

		$next = strtotime( $data['next_update'] );
		$ttl  = $next ? max( 5 * MINUTE_IN_SECONDS, $next - time() + wp_rand( 30, 180 ) ) : 30 * MINUTE_IN_SECONDS;
		set_transient( self::TRANSIENT, $data, min( $ttl, HOUR_IN_SECONDS ) );
		update_option( self::OPTION, $data, false );
		$status['ok'] = time();
		update_option( self::STATUS, $status, false );
		delete_transient( self::BACKOFF );
		return true;
	}

	/**
	 * Keep only the expected fields, with the expected types.
	 *
	 * @param array $d Decoded answer.
	 * @return array|null
	 */
	private static function clean( $d ) {
		if ( empty( $d['rates'] ) || ! is_array( $d['rates'] ) ) {
			return null;
		}
		$rates = array();
		foreach ( $d['rates'] as $r ) {
			if ( ! is_array( $r ) || empty( $r['symbol'] ) ) {
				continue;
			}
			$sym           = sanitize_key( $r['symbol'] );
			$rates[ $sym ] = array(
				'symbol'     => $sym,
				'code'       => sanitize_text_field( isset( $r['code'] ) ? $r['code'] : strtoupper( $sym ) ),
				'category'   => sanitize_key( isset( $r['category'] ) ? $r['category'] : 'fiat' ),
				'name'       => array(
					'fa' => sanitize_text_field( isset( $r['name']['fa'] ) ? $r['name']['fa'] : $sym ),
					'en' => sanitize_text_field( isset( $r['name']['en'] ) ? $r['name']['en'] : $sym ),
				),
				'buy'        => isset( $r['buy'] ) ? (float) $r['buy'] : null,
				'sell'       => isset( $r['sell'] ) ? (float) $r['sell'] : null,
				'usd'        => isset( $r['usd'] ) && is_numeric( $r['usd'] ) ? (float) $r['usd'] : null,
				'change_pct' => isset( $r['change_pct'] ) && is_numeric( $r['change_pct'] ) ? (float) $r['change_pct'] : null,
				'updated_at' => sanitize_text_field( isset( $r['updated_at'] ) ? $r['updated_at'] : '' ),
				'icon'       => esc_url_raw( isset( $r['icon'] ) ? $r['icon'] : '' ),
				'path'       => isset( $r['path'] ) && is_string( $r['path'] ) ? '/' . ltrim( sanitize_text_field( $r['path'] ), '/' ) : '',
			);
		}
		$series = array();
		if ( ! empty( $d['series'] ) && is_array( $d['series'] ) ) {
			foreach ( $d['series'] as $sym => $ranges ) {
				foreach ( array( '1d', '7d', '1m', 'all' ) as $k ) {
					$pts = array();
					foreach ( isset( $ranges[ $k ] ) && is_array( $ranges[ $k ] ) ? $ranges[ $k ] : array() as $p ) {
						if ( is_array( $p ) && isset( $p[0], $p[1] ) && is_numeric( $p[1] ) ) {
							$pts[] = array( (string) $p[0], (float) $p[1] );
						}
					}
					$series[ sanitize_key( $sym ) ][ $k ] = $pts;
				}
			}
		}
		return array(
			'site'         => esc_url_raw( isset( $d['site'] ) ? $d['site'] : 'https://ezrate.me' ),
			'generated_at' => sanitize_text_field( isset( $d['generated_at'] ) ? $d['generated_at'] : '' ),
			'next_update'  => sanitize_text_field( isset( $d['next_update'] ) ? $d['next_update'] : '' ),
			'rates'        => $rates,
			'series'       => $series,
		);
	}

	/**
	 * Fetch status value (ok = time of the last good download, attempt, error).
	 *
	 * @param string $key Key.
	 * @return mixed
	 */
	public static function status( $key ) {
		$s = get_option( self::STATUS, array() );
		return isset( $s[ $key ] ) ? $s[ $key ] : null;
	}

	/**
	 * Forget the cached snapshot (after widgets that need other chart series are saved).
	 */
	public static function invalidate_if_charts_missing() {
		$data = get_transient( self::TRANSIENT );
		if ( ! is_array( $data ) ) {
			return;
		}
		foreach ( self::chart_assets() as $sym ) {
			if ( empty( $data['series'][ $sym ] ) ) {
				delete_transient( self::TRANSIENT );
				delete_transient( self::BACKOFF );
				return;
			}
		}
	}
}
