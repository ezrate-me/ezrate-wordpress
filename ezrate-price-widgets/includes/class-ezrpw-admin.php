<?php
/**
 * Dashboard pages: EZRate › Widgets (list), Add / edit widget (with live preview), Settings, Help.
 *
 * @package EZRate_Price_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin screens.
 */
final class EZRPW_Admin {

	const CAP = 'manage_options';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'assets' ) );
		add_action( 'admin_post_ezrpw_save', array( __CLASS__, 'save' ) );
		add_action( 'admin_post_ezrpw_delete', array( __CLASS__, 'delete' ) );
		add_action( 'admin_post_ezrpw_duplicate', array( __CLASS__, 'duplicate' ) );
		add_action( 'admin_post_ezrpw_refresh', array( __CLASS__, 'refresh' ) );
		add_action( 'admin_post_ezrpw_settings', array( __CLASS__, 'save_settings' ) );
		add_action( 'wp_ajax_ezrpw_preview', array( __CLASS__, 'preview' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( EZRPW_FILE ), array( __CLASS__, 'action_links' ) );
	}

	/**
	 * Menu.
	 */
	public static function menu() {
		add_menu_page( __( 'EZRate price widgets', 'ezrate-price-widgets' ), 'EZRate', self::CAP, 'ezrpw-widgets', array( __CLASS__, 'page_list' ), self::menu_icon(), 58 );
		add_submenu_page( 'ezrpw-widgets', __( 'Widgets', 'ezrate-price-widgets' ), __( 'Widgets', 'ezrate-price-widgets' ), self::CAP, 'ezrpw-widgets', array( __CLASS__, 'page_list' ) );
		add_submenu_page( 'ezrpw-widgets', __( 'Add widget', 'ezrate-price-widgets' ), __( 'Add widget', 'ezrate-price-widgets' ), self::CAP, 'ezrpw-edit', array( __CLASS__, 'page_edit' ) );
		add_submenu_page( 'ezrpw-widgets', __( 'Settings', 'ezrate-price-widgets' ), __( 'Settings', 'ezrate-price-widgets' ), self::CAP, 'ezrpw-settings', array( __CLASS__, 'page_settings' ) );
		add_submenu_page( 'ezrpw-widgets', __( 'Help', 'ezrate-price-widgets' ), __( 'Help', 'ezrate-price-widgets' ), self::CAP, 'ezrpw-help', array( __CLASS__, 'page_help' ) );
	}

	/**
	 * EZRate icon before a page title.
	 */
	private static function logo() {
		echo '<img class="ezrpwa-logo" src="' . esc_url( EZRPW_URL . 'assets/img/icon-128.png' ) . '" alt="" width="30" height="30">';
	}

	/**
	 * The EZRate mark as the menu icon (inline SVG, no request).
	 *
	 * @return string
	 */
	private static function menu_icon() {
		return 'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHZpZXdCb3g9IjAgMCAyMCAyMCI+PHBhdGggZmlsbD0ibm9uZSIgc3Ryb2tlPSIjYTdhYWFkIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIgZD0iTTIuNSA4SDZsMS42LTMuOCAyIDcuMiAxLjgtMy40aDMiLz48Y2lyY2xlIGN4PSIxNi40IiBjeT0iOCIgcj0iMS45IiBmaWxsPSIjRkY1QTFGIi8+PHBhdGggZmlsbD0ibm9uZSIgc3Ryb2tlPSIjYTdhYWFkIiBzdHJva2Utd2lkdGg9IjIuNiIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBkPSJNNC4yIDE1aDExLjYiLz48L3N2Zz4=';
	}

	/**
	 * Links on the Plugins screen.
	 *
	 * @param array $links Links.
	 * @return array
	 */
	public static function action_links( $links ) {
		array_unshift(
			$links,
			'<a href="' . esc_url( admin_url( 'admin.php?page=ezrpw-widgets' ) ) . '">' . esc_html__( 'Widgets', 'ezrate-price-widgets' ) . '</a>',
			'<a href="' . esc_url( admin_url( 'admin.php?page=ezrpw-help' ) ) . '">' . esc_html__( 'Help', 'ezrate-price-widgets' ) . '</a>'
		);
		return $links;
	}

	/**
	 * Admin assets on the plugin's own pages only.
	 *
	 * @param string $hook Screen hook.
	 */
	public static function assets( $hook ) {
		if ( false === strpos( (string) $hook, 'ezrpw' ) ) {
			return;
		}
		wp_enqueue_style( 'ezrpw', EZRPW_URL . 'assets/css/ezrpw.css', array(), EZRPW_VERSION );
		wp_enqueue_style( 'ezrpw-admin', EZRPW_URL . 'assets/css/admin.css', array( 'wp-color-picker' ), EZRPW_VERSION );
		wp_enqueue_script( 'ezrpw', EZRPW_URL . 'assets/js/ezrpw.js', array(), EZRPW_VERSION, true );
		wp_enqueue_script( 'ezrpw-admin', EZRPW_URL . 'assets/js/admin.js', array( 'wp-color-picker' ), EZRPW_VERSION, true );
		wp_localize_script(
			'ezrpw-admin',
			'ezrpwAdmin',
			array(
				'ajax'    => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'ezrpw_preview' ),
				'copied'  => __( 'Copied', 'ezrate-price-widgets' ),
				'confirm' => __( 'Delete this widget? It disappears from every page that shows it.', 'ezrate-price-widgets' ),
				'remove'  => __( 'Remove', 'ezrate-price-widgets' ),
				'up'      => __( 'Move up', 'ezrate-price-widgets' ),
				'down'    => __( 'Move down', 'ezrate-price-widgets' ),
			)
		);
	}

	/**
	 * Assets the site owner can choose, grouped by category, named in the dashboard language.
	 *
	 * @return array [category => [symbol => name]]
	 */
	private static function choices() {
		$data = EZRPW_Data::get();
		$lang = 0 === strpos( get_user_locale(), 'fa' ) ? 'fa' : 'en';
		$out  = array();
		if ( $data ) {
			foreach ( $data['rates'] as $r ) {
				$out[ $r['category'] ][ $r['symbol'] ] = array(
					'name' => $r['name'][ $lang ],
					'code' => $r['code'],
					'icon' => $r['icon'],
					'q'    => strtolower( $r['name']['fa'] . ' ' . $r['name']['en'] . ' ' . $r['code'] . ' ' . $r['symbol'] ),
				);
			}
		}
		return $out;
	}

	/**
	 * Category labels.
	 *
	 * @param string $cat Category.
	 * @return string
	 */
	private static function cat_label( $cat ) {
		$l = array(
			'fiat'   => __( 'Currencies', 'ezrate-price-widgets' ),
			'gold'   => __( 'Gold', 'ezrate-price-widgets' ),
			'coin'   => __( 'Coins', 'ezrate-price-widgets' ),
			'metal'  => __( 'Metals', 'ezrate-price-widgets' ),
			'crypto' => __( 'Crypto', 'ezrate-price-widgets' ),
		);
		return isset( $l[ $cat ] ) ? $l[ $cat ] : $cat;
	}

	/**
	 * Data status line (last update, errors).
	 */
	private static function status_box() {
		$ok    = (int) EZRPW_Data::status( 'ok' );
		$error = (string) EZRPW_Data::status( 'error' );
		$data  = get_option( EZRPW_Data::OPTION );
		echo '<div class="ezrpwa-status ' . ( $error && ! $ok ? 'is-error' : '' ) . '">';
		if ( $ok ) {
			/* translators: %s: time since the last update, e.g. "5 mins" */
			echo '<span class="dashicons dashicons-yes-alt"></span> ' . esc_html( sprintf( __( 'Prices updated %s ago.', 'ezrate-price-widgets' ), human_time_diff( $ok ) ) );
			if ( is_array( $data ) && ! empty( $data['next_update'] ) && strtotime( $data['next_update'] ) > time() ) {
				/* translators: %s: time until the next update */
				echo ' ' . esc_html( sprintf( __( 'Next update in about %s (every 30 minutes on the free data plan).', 'ezrate-price-widgets' ), human_time_diff( strtotime( $data['next_update'] ) ) ) );
			}
		} else {
			echo '<span class="dashicons dashicons-clock"></span> ' . esc_html__( 'Prices have not been downloaded yet.', 'ezrate-price-widgets' );
		}
		if ( $error ) {
			echo ' <span class="ezrpwa-err">' . esc_html( $error ) . '</span>';
		}
		echo '</div>';
	}

	/**
	 * Notice after an action.
	 */
	private static function notice() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- display only
		$msg = isset( $_GET['ezrpw_msg'] ) ? sanitize_key( wp_unslash( $_GET['ezrpw_msg'] ) ) : '';
		$map = array(
			'saved'     => __( 'Widget saved.', 'ezrate-price-widgets' ),
			'deleted'   => __( 'Widget deleted.', 'ezrate-price-widgets' ),
			'copied'    => __( 'Widget duplicated.', 'ezrate-price-widgets' ),
			'refreshed' => __( 'Prices refreshed.', 'ezrate-price-widgets' ),
			'settings'  => __( 'Settings saved.', 'ezrate-price-widgets' ),
			'wait'      => __( 'Please wait a few minutes before refreshing again.', 'ezrate-price-widgets' ),
			'failed'    => __( 'The prices could not be downloaded; the last saved prices are shown.', 'ezrate-price-widgets' ),
		);
		if ( isset( $map[ $msg ] ) ) {
			$type = in_array( $msg, array( 'wait', 'failed' ), true ) ? 'warning' : 'success';
			echo '<div class="notice notice-' . esc_attr( $type ) . ' is-dismissible"><p>' . esc_html( $map[ $msg ] ) . '</p></div>';
		}
	}

	/**
	 * EZRate › Widgets.
	 */
	public static function page_list() {
		$all = EZRPW_Widgets::all();
		?>
		<div class="wrap ezrpwa-admin">
			<h1 class="wp-heading-inline"><?php self::logo(); ?><?php esc_html_e( 'EZRate price widgets', 'ezrate-price-widgets' ); ?></h1>
			<a class="page-title-action" href="<?php echo esc_url( admin_url( 'admin.php?page=ezrpw-edit' ) ); ?>"><?php esc_html_e( 'Add widget', 'ezrate-price-widgets' ); ?></a>
			<hr class="wp-header-end">
			<?php self::notice(); ?>
			<?php self::status_box(); ?>
			<p class="ezrpwa-lead"><?php esc_html_e( 'Each widget has its own type, prices, language and colours. Place it with its shortcode, the «EZRate price widget» block, the classic widget — or show a ticker or bar fixed at the top or bottom of every page.', 'ezrate-price-widgets' ); ?></p>
			<table class="wp-list-table widefat fixed striped ezrpwa-list">
				<thead><tr>
					<th style="width:46px">ID</th>
					<th><?php esc_html_e( 'Widget', 'ezrate-price-widgets' ); ?></th>
					<th><?php esc_html_e( 'Type', 'ezrate-price-widgets' ); ?></th>
					<th><?php esc_html_e( 'Where', 'ezrate-price-widgets' ); ?></th>
					<th><?php esc_html_e( 'Shortcode', 'ezrate-price-widgets' ); ?></th>
				</tr></thead>
				<tbody>
				<?php if ( ! $all ) : ?>
					<tr><td colspan="5"><?php esc_html_e( 'No widgets yet.', 'ezrate-price-widgets' ); ?></td></tr>
				<?php endif; ?>
				<?php
				foreach ( $all as $w ) :
					$edit = admin_url( 'admin.php?page=ezrpw-edit&id=' . $w['id'] );
					$sc   = '[ezrate_widget id="' . $w['id'] . '"]';
					?>
					<tr>
						<td><?php echo (int) $w['id']; ?></td>
						<td>
							<strong><a class="row-title" href="<?php echo esc_url( $edit ); ?>"><?php echo esc_html( '' !== $w['title'] ? $w['title'] : EZRPW_Widgets::type_label( $w['type'] ) ); ?></a></strong>
							<div class="row-actions">
								<span class="edit"><a href="<?php echo esc_url( $edit ); ?>"><?php esc_html_e( 'Edit', 'ezrate-price-widgets' ); ?></a> | </span>
								<span><a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ezrpw_duplicate&id=' . $w['id'] ), 'ezrpw_duplicate_' . $w['id'] ) ); ?>"><?php esc_html_e( 'Duplicate', 'ezrate-price-widgets' ); ?></a> | </span>
								<span class="trash"><a class="ezrpwa-del" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=ezrpw_delete&id=' . $w['id'] ), 'ezrpw_delete_' . $w['id'] ) ); ?>"><?php esc_html_e( 'Delete', 'ezrate-price-widgets' ); ?></a></span>
							</div>
						</td>
						<td><?php echo esc_html( EZRPW_Widgets::type_label( $w['type'] ) ); ?> · <?php echo esc_html( strtoupper( $w['lang'] ) ); ?></td>
						<td>
							<?php
							if ( 'top' === $w['position'] ) {
								esc_html_e( 'Fixed at the top of the site', 'ezrate-price-widgets' );
							} elseif ( 'bottom' === $w['position'] ) {
								esc_html_e( 'Fixed at the bottom of the site', 'ezrate-price-widgets' );
							} else {
								esc_html_e( 'Shortcode / block / widget', 'ezrate-price-widgets' );
							}
							?>
						</td>
						<td><code class="ezrpwa-copy" title="<?php esc_attr_e( 'Click to copy', 'ezrate-price-widgets' ); ?>"><?php echo esc_html( $sc ); ?></code></td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</div>
		<?php
	}

	/**
	 * Add / edit a widget.
	 */
	public static function page_edit() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only id of the widget to edit
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0;
		$w  = $id ? EZRPW_Widgets::get( $id ) : null;
		if ( ! $w ) {
			$w = EZRPW_Widgets::defaults();
		}
		$choices = self::choices();
		$names   = array();
		foreach ( $choices as $list ) {
			$names += $list;
		}
		$f = static function ( $key ) {
			return 'ezrpw[' . $key . ']';
		};
		?>
		<div class="wrap ezrpwa-admin">
			<h1><?php self::logo(); ?><?php echo $w['id'] ? esc_html__( 'Edit widget', 'ezrate-price-widgets' ) : esc_html__( 'Add widget', 'ezrate-price-widgets' ); ?></h1>
			<?php self::notice(); ?>
			<?php if ( ! $choices ) : ?>
				<div class="notice notice-warning"><p><?php esc_html_e( 'The list of prices is not available yet (the data service could not be reached). Try again in a few minutes.', 'ezrate-price-widgets' ); ?></p></div>
			<?php endif; ?>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" id="ezrpwa-form" class="ezrpw-edit">
				<?php wp_nonce_field( 'ezrpw_save' ); ?>
				<input type="hidden" name="action" value="ezrpw_save">
				<input type="hidden" name="<?php echo esc_attr( $f( 'id' ) ); ?>" value="<?php echo (int) $w['id']; ?>">
				<input type="hidden" name="<?php echo esc_attr( $f( '_form' ) ); ?>" value="1">

				<div class="ezrpwa-cols">
					<div class="ezrpwa-main">
						<div class="ezrpwa-box">
							<h2><?php esc_html_e( 'Widget', 'ezrate-price-widgets' ); ?></h2>
							<p><label class="ezrpwa-l" for="ezrpwa-title"><?php esc_html_e( 'Name (only shown in the dashboard)', 'ezrate-price-widgets' ); ?></label>
								<input type="text" class="regular-text" id="ezrpwa-title" name="<?php echo esc_attr( $f( 'title' ) ); ?>" value="<?php echo esc_attr( $w['title'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. Header ticker', 'ezrate-price-widgets' ); ?>"></p>
							<div class="ezrpwa-l"><?php esc_html_e( 'Type', 'ezrate-price-widgets' ); ?></div>
							<div class="ezrpwa-types">
								<?php
								$icons = array(
									'ticker' => 'dashicons-controls-forward',
									'bar'    => 'dashicons-minus',
									'cards'  => 'dashicons-screenoptions',
									'table'  => 'dashicons-editor-table',
									'chart'  => 'dashicons-chart-area',
								);
								$descs = array(
									'ticker' => __( 'Prices scroll like a news ticker', 'ezrate-price-widgets' ),
									'bar'    => __( 'One row of prices', 'ezrate-price-widgets' ),
									'cards'  => __( 'Tidy cards, for a sidebar or home page', 'ezrate-price-widgets' ),
									'table'  => __( 'Buy, sell and change in a table', 'ezrate-price-widgets' ),
									'chart'  => __( 'One asset: day, week, month, all', 'ezrate-price-widgets' ),
								);
								foreach ( EZRPW_Widgets::TYPES as $t ) :
									?>
									<label class="ezrpwa-type">
										<input type="radio" name="<?php echo esc_attr( $f( 'type' ) ); ?>" value="<?php echo esc_attr( $t ); ?>" <?php checked( $w['type'], $t ); ?>>
										<span><i class="dashicons <?php echo esc_attr( $icons[ $t ] ); ?>"></i><b><?php echo esc_html( EZRPW_Widgets::type_label( $t ) ); ?></b><small><?php echo esc_html( $descs[ $t ] ); ?></small></span>
									</label>
								<?php endforeach; ?>
							</div>
							<div class="ezrpwa-grid2">
								<p><label class="ezrpwa-l"><?php esc_html_e( 'Widget language', 'ezrate-price-widgets' ); ?></label>
									<select name="<?php echo esc_attr( $f( 'lang' ) ); ?>">
										<option value="fa" <?php selected( $w['lang'], 'fa' ); ?>>فارسی (Persian, RTL)</option>
										<option value="en" <?php selected( $w['lang'], 'en' ); ?>>English</option>
									</select></p>
								<p><label class="ezrpwa-l"><?php esc_html_e( 'Theme', 'ezrate-price-widgets' ); ?></label>
									<select name="<?php echo esc_attr( $f( 'theme' ) ); ?>">
										<option value="light" <?php selected( $w['theme'], 'light' ); ?>><?php esc_html_e( 'Light', 'ezrate-price-widgets' ); ?></option>
										<option value="dark" <?php selected( $w['theme'], 'dark' ); ?>><?php esc_html_e( 'Dark', 'ezrate-price-widgets' ); ?></option>
									</select></p>
							</div>
						</div>

						<div class="ezrpwa-box" data-show="ticker bar cards table">
							<h2><?php esc_html_e( 'Prices', 'ezrate-price-widgets' ); ?></h2>
							<p class="description"><?php esc_html_e( 'Click a price to add or remove it; change the order with the arrows. Up to 20.', 'ezrate-price-widgets' ); ?></p>
							<input type="hidden" id="ezrpwa-assets" name="<?php echo esc_attr( $f( 'assets' ) ); ?>" value="<?php echo esc_attr( implode( ',', $w['assets'] ) ); ?>">
							<ol class="ezrpwa-sel" id="ezrpwa-sel">
								<?php foreach ( $w['assets'] as $sym ) : ?>
									<li data-s="<?php echo esc_attr( $sym ); ?>">
									<?php
									if ( isset( $names[ $sym ] ) ) :
										?>
										<img src="<?php echo esc_url( $names[ $sym ]['icon'] ); ?>" alt="" width="18" height="18"><?php endif; ?><span><?php echo esc_html( isset( $names[ $sym ] ) ? $names[ $sym ]['name'] : $sym ); ?></span><button type="button" class="ezrpwa-up" aria-label="<?php esc_attr_e( 'Move up', 'ezrate-price-widgets' ); ?>">↑</button><button type="button" class="ezrpwa-down" aria-label="<?php esc_attr_e( 'Move down', 'ezrate-price-widgets' ); ?>">↓</button><button type="button" class="ezrpwa-x" aria-label="<?php esc_attr_e( 'Remove', 'ezrate-price-widgets' ); ?>">×</button></li>
								<?php endforeach; ?>
							</ol>
							<input type="search" class="ezrpwa-q" id="ezrpwa-q" placeholder="<?php esc_attr_e( 'Search currency, gold, coin…', 'ezrate-price-widgets' ); ?>">
							<div class="ezrpwa-pool" id="ezrpwa-pool">
								<?php foreach ( $choices as $cat => $list ) : ?>
									<div class="ezrpwa-cat"><b><?php echo esc_html( self::cat_label( $cat ) ); ?></b>
										<?php foreach ( $list as $sym => $c ) : ?>
											<button type="button" class="ezrpwa-chip" data-s="<?php echo esc_attr( $sym ); ?>" data-icon="<?php echo esc_url( $c['icon'] ); ?>" data-q="<?php echo esc_attr( $c['q'] ); ?>"><img src="<?php echo esc_url( $c['icon'] ); ?>" alt="" width="16" height="16" loading="lazy"><?php echo esc_html( $c['name'] ); ?></button>
										<?php endforeach; ?>
									</div>
								<?php endforeach; ?>
							</div>
						</div>

						<div class="ezrpwa-box" data-show="chart">
							<h2><?php esc_html_e( 'Chart', 'ezrate-price-widgets' ); ?></h2>
							<div class="ezrpwa-grid2">
								<p><label class="ezrpwa-l"><?php esc_html_e( 'Asset', 'ezrate-price-widgets' ); ?></label>
									<select name="<?php echo esc_attr( $f( 'chart_asset' ) ); ?>">
										<?php foreach ( $choices as $cat => $list ) : ?>
											<optgroup label="<?php echo esc_attr( self::cat_label( $cat ) ); ?>">
												<?php foreach ( $list as $sym => $c ) : ?>
													<option value="<?php echo esc_attr( $sym ); ?>" <?php selected( $w['chart_asset'], $sym ); ?>><?php echo esc_html( $c['name'] ); ?></option>
												<?php endforeach; ?>
											</optgroup>
										<?php endforeach; ?>
									</select></p>
								<p><label class="ezrpwa-l"><?php esc_html_e( 'Default range', 'ezrate-price-widgets' ); ?></label>
									<select name="<?php echo esc_attr( $f( 'range' ) ); ?>">
										<option value="1d" <?php selected( $w['range'], '1d' ); ?>><?php esc_html_e( 'Day', 'ezrate-price-widgets' ); ?></option>
										<option value="7d" <?php selected( $w['range'], '7d' ); ?>><?php esc_html_e( 'Week', 'ezrate-price-widgets' ); ?></option>
										<option value="1m" <?php selected( $w['range'], '1m' ); ?>><?php esc_html_e( 'Month', 'ezrate-price-widgets' ); ?></option>
										<option value="all" <?php selected( $w['range'], 'all' ); ?>><?php esc_html_e( 'All', 'ezrate-price-widgets' ); ?></option>
									</select></p>
							</div>
							<p class="description"><?php esc_html_e( 'Visitors can switch between day, week, month and all.', 'ezrate-price-widgets' ); ?></p>
						</div>

						<div class="ezrpwa-box">
							<h2><?php esc_html_e( 'Display', 'ezrate-price-widgets' ); ?></h2>
							<div class="ezrpwa-checks">
								<label><input type="checkbox" name="<?php echo esc_attr( $f( 'show_change' ) ); ?>" value="1" <?php checked( $w['show_change'] ); ?>> <?php esc_html_e( 'Show 24-hour change (%)', 'ezrate-price-widgets' ); ?></label>
								<label data-show="ticker bar cards table"><input type="checkbox" name="<?php echo esc_attr( $f( 'show_buy' ) ); ?>" value="1" <?php checked( $w['show_buy'] ); ?>> <?php esc_html_e( 'Show the buy price too', 'ezrate-price-widgets' ); ?></label>
								<label><input type="checkbox" name="<?php echo esc_attr( $f( 'show_icons' ) ); ?>" value="1" <?php checked( $w['show_icons'] ); ?>> <?php esc_html_e( 'Show flags and icons', 'ezrate-price-widgets' ); ?></label>
								<label data-show="cards table chart"><input type="checkbox" name="<?php echo esc_attr( $f( 'show_footer' ) ); ?>" value="1" <?php checked( $w['show_footer'] ); ?>> <?php esc_html_e( 'Show update time and currency', 'ezrate-price-widgets' ); ?></label>
							</div>
							<div class="ezrpwa-grid2">
								<p data-show="ticker"><label class="ezrpwa-l"><?php esc_html_e( 'Ticker speed (seconds per loop)', 'ezrate-price-widgets' ); ?></label>
									<input type="number" min="10" max="180" name="<?php echo esc_attr( $f( 'speed' ) ); ?>" value="<?php echo (int) $w['speed']; ?>" class="small-text"></p>
								<p data-show="cards"><label class="ezrpwa-l"><?php esc_html_e( 'Columns', 'ezrate-price-widgets' ); ?></label>
									<select name="<?php echo esc_attr( $f( 'columns' ) ); ?>">
										<option value="0" <?php selected( $w['columns'], 0 ); ?>><?php esc_html_e( 'Automatic', 'ezrate-price-widgets' ); ?></option>
										<?php for ( $i = 1; $i <= 6; $i++ ) : ?>
											<option value="<?php echo (int) $i; ?>" <?php selected( $w['columns'], $i ); ?>><?php echo (int) $i; ?></option>
										<?php endfor; ?>
									</select></p>
							</div>
							<div data-show="ticker bar">
								<div class="ezrpwa-l"><?php esc_html_e( 'Where', 'ezrate-price-widgets' ); ?></div>
								<div class="ezrpwa-radios">
									<label><input type="radio" name="<?php echo esc_attr( $f( 'position' ) ); ?>" value="inline" <?php checked( $w['position'], 'inline' ); ?>> <?php esc_html_e( 'Where I put the shortcode / block', 'ezrate-price-widgets' ); ?></label>
									<label><input type="radio" name="<?php echo esc_attr( $f( 'position' ) ); ?>" value="top" <?php checked( $w['position'], 'top' ); ?>> <?php esc_html_e( 'Fixed at the top of every page', 'ezrate-price-widgets' ); ?></label>
									<label><input type="radio" name="<?php echo esc_attr( $f( 'position' ) ); ?>" value="bottom" <?php checked( $w['position'], 'bottom' ); ?>> <?php esc_html_e( 'Fixed at the bottom of every page', 'ezrate-price-widgets' ); ?></label>
								</div>
								<div class="ezrpwa-grid2 ezrpwa-fixed-opts">
									<p><label class="ezrpwa-l"><?php esc_html_e( 'Show on', 'ezrate-price-widgets' ); ?></label>
										<select name="<?php echo esc_attr( $f( 'show_on' ) ); ?>">
											<option value="all" <?php selected( $w['show_on'], 'all' ); ?>><?php esc_html_e( 'All pages', 'ezrate-price-widgets' ); ?></option>
											<option value="front" <?php selected( $w['show_on'], 'front' ); ?>><?php esc_html_e( 'Home page only', 'ezrate-price-widgets' ); ?></option>
										</select></p>
									<p><label><input type="checkbox" name="<?php echo esc_attr( $f( 'hide_mobile' ) ); ?>" value="1" <?php checked( $w['hide_mobile'] ); ?>> <?php esc_html_e( 'Hide on phones', 'ezrate-price-widgets' ); ?></label></p>
								</div>
							</div>
						</div>

						<div class="ezrpwa-box">
							<h2><?php esc_html_e( 'Style', 'ezrate-price-widgets' ); ?></h2>
							<p class="description"><?php esc_html_e( 'Leave a colour empty to use the theme colour.', 'ezrate-price-widgets' ); ?></p>
							<div class="ezrpwa-colors">
								<?php
								$labels = array(
									'bg'     => __( 'Background', 'ezrate-price-widgets' ),
									'fg'     => __( 'Text', 'ezrate-price-widgets' ),
									'muted'  => __( 'Secondary text', 'ezrate-price-widgets' ),
									'border' => __( 'Border', 'ezrate-price-widgets' ),
									'accent' => __( 'Accent (buttons)', 'ezrate-price-widgets' ),
									'up'     => __( 'Price up', 'ezrate-price-widgets' ),
									'down'   => __( 'Price down', 'ezrate-price-widgets' ),
									'chart'  => __( 'Chart line (empty = green/red by trend)', 'ezrate-price-widgets' ),
								);
								foreach ( $labels as $k => $label ) :
									?>
									<p data-show="<?php echo 'chart' === $k ? 'chart' : 'ticker bar cards table chart'; ?>"><label class="ezrpwa-l"><?php echo esc_html( $label ); ?></label>
										<input type="text" class="ezrpwa-color" name="<?php echo esc_attr( $f( 'colors' ) . '[' . $k . ']' ); ?>" value="<?php echo esc_attr( $w['colors'][ $k ] ); ?>" data-default-color=""></p>
								<?php endforeach; ?>
							</div>
							<div class="ezrpwa-grid2">
								<p><label class="ezrpwa-l"><?php esc_html_e( 'Corner radius', 'ezrate-price-widgets' ); ?> <output><?php echo (int) $w['radius']; ?></output>px</label>
									<input type="range" min="0" max="28" name="<?php echo esc_attr( $f( 'radius' ) ); ?>" value="<?php echo (int) $w['radius']; ?>"></p>
								<p><label class="ezrpwa-l"><?php esc_html_e( 'Text size', 'ezrate-price-widgets' ); ?></label>
									<select name="<?php echo esc_attr( $f( 'size' ) ); ?>">
										<option value="s" <?php selected( $w['size'], 's' ); ?>><?php esc_html_e( 'Small', 'ezrate-price-widgets' ); ?></option>
										<option value="m" <?php selected( $w['size'], 'm' ); ?>><?php esc_html_e( 'Medium', 'ezrate-price-widgets' ); ?></option>
										<option value="l" <?php selected( $w['size'], 'l' ); ?>><?php esc_html_e( 'Large', 'ezrate-price-widgets' ); ?></option>
									</select></p>
								<p><label class="ezrpwa-l"><?php esc_html_e( 'Font', 'ezrate-price-widgets' ); ?></label>
									<select name="<?php echo esc_attr( $f( 'font' ) ); ?>">
										<option value="inherit" <?php selected( $w['font'], 'inherit' ); ?>><?php esc_html_e( 'Same as my theme', 'ezrate-price-widgets' ); ?></option>
										<option value="system" <?php selected( $w['font'], 'system' ); ?>><?php esc_html_e( 'System font', 'ezrate-price-widgets' ); ?></option>
									</select></p>
							</div>
						</div>
						<?php submit_button( $w['id'] ? __( 'Save widget', 'ezrate-price-widgets' ) : __( 'Create widget', 'ezrate-price-widgets' ) ); ?>
					</div>

					<div class="ezrpwa-side">
						<div class="ezrpwa-box ezrpwa-sticky">
							<h2><?php esc_html_e( 'Live preview', 'ezrate-price-widgets' ); ?></h2>
							<div class="ezrpwa-preview" id="ezrpwa-preview"><?php echo EZRPW_Front::render( $w, true ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in EZRPW_Render ?></div>
							<?php if ( $w['id'] ) : ?>
								<h3><?php esc_html_e( 'Place it', 'ezrate-price-widgets' ); ?></h3>
								<p class="ezrpwa-how"><b><?php esc_html_e( 'Shortcode', 'ezrate-price-widgets' ); ?></b><code class="ezrpwa-copy"><?php echo esc_html( '[ezrate_widget id="' . $w['id'] . '"]' ); ?></code></p>
								<p class="ezrpwa-how"><b><?php esc_html_e( 'Block editor', 'ezrate-price-widgets' ); ?></b><?php esc_html_e( 'Add the «EZRate price widget» block and pick this widget.', 'ezrate-price-widgets' ); ?></p>
								<p class="ezrpwa-how"><b><?php esc_html_e( 'Theme file', 'ezrate-price-widgets' ); ?></b><code class="ezrpwa-copy"><?php echo esc_html( '<?php echo do_shortcode( \'[ezrate_widget id="' . $w['id'] . '"]\' ); ?>' ); ?></code></p>
							<?php else : ?>
								<p class="description"><?php esc_html_e( 'Create the widget to get its shortcode.', 'ezrate-price-widgets' ); ?></p>
							<?php endif; ?>
						</div>
					</div>
				</div>
			</form>
		</div>
		<?php
	}

	/**
	 * Settings page.
	 */
	public static function page_settings() {
		$s = EZRPW_Widgets::settings();
		?>
		<div class="wrap ezrpwa-admin">
			<h1><?php self::logo(); ?><?php esc_html_e( 'EZRate settings', 'ezrate-price-widgets' ); ?></h1>
			<?php self::notice(); ?>
			<div class="ezrpwa-box">
				<h2><?php esc_html_e( 'Prices', 'ezrate-price-widgets' ); ?></h2>
				<?php self::status_box(); ?>
				<p class="description"><?php esc_html_e( 'Prices come from the EZRate data service and are stored on your site. They are refreshed every 30 minutes in the background (free data plan); your visitors never wait for the service.', 'ezrate-price-widgets' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'ezrpw_refresh' ); ?>
					<input type="hidden" name="action" value="ezrpw_refresh">
					<?php submit_button( __( 'Refresh prices now', 'ezrate-price-widgets' ), 'secondary', 'submit', false ); ?>
				</form>
			</div>
			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="ezrpwa-box">
				<?php wp_nonce_field( 'ezrpw_settings' ); ?>
				<input type="hidden" name="action" value="ezrpw_settings">
				<h2><?php esc_html_e( 'General', 'ezrate-price-widgets' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr><th scope="row"><?php esc_html_e( 'Language of new widgets', 'ezrate-price-widgets' ); ?></th>
						<td><select name="ezrpw_settings[default_lang]"><option value="fa" <?php selected( $s['default_lang'], 'fa' ); ?>>فارسی</option><option value="en" <?php selected( $s['default_lang'], 'en' ); ?>>English</option></select></td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Links', 'ezrate-price-widgets' ); ?></th>
						<td>
							<label><input type="checkbox" name="ezrpw_settings[link_rates]" value="1" <?php checked( $s['link_rates'] ); ?>> <?php esc_html_e( 'Link each price to its page on ezrate.me (chart and history)', 'ezrate-price-widgets' ); ?></label><br>
							<label><input type="checkbox" name="ezrpw_settings[credit]" value="1" <?php checked( $s['credit'] ); ?>> <?php esc_html_e( 'Show a small «Rates by EZRate» link under cards, tables and charts — thank you, it keeps this plugin free', 'ezrate-price-widgets' ); ?></label>
							<p class="description"><?php esc_html_e( 'Both are off by default: the plugin adds no links to your site unless you allow it.', 'ezrate-price-widgets' ); ?></p>
						</td></tr>
					<tr><th scope="row"><?php esc_html_e( 'Uninstall', 'ezrate-price-widgets' ); ?></th>
						<td><label><input type="checkbox" name="ezrpw_settings[cleanup]" value="1" <?php checked( $s['cleanup'] ); ?>> <?php esc_html_e( 'Delete the widgets and settings when the plugin is deleted', 'ezrate-price-widgets' ); ?></label></td></tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Help (the same guide as docs/guide.html, also readable outside WordPress).
	 */
	public static function page_help() {
		$lang = 0 === strpos( get_user_locale(), 'fa' ) ? 'fa' : 'en';
		?>
		<div class="wrap ezrpwa-admin">
			<h1><?php self::logo(); ?><?php esc_html_e( 'EZRate price widgets — guide', 'ezrate-price-widgets' ); ?></h1>
			<iframe class="ezrpwa-guide" title="<?php esc_attr_e( 'Guide', 'ezrate-price-widgets' ); ?>" src="<?php echo esc_url( add_query_arg( 'lang', $lang, EZRPW_URL . 'docs/guide.html' ) ); ?>"></iframe>
		</div>
		<?php
	}

	/**
	 * Stop unless allowed and the nonce is valid.
	 *
	 * @param string $action Nonce action.
	 */
	private static function guard( $action ) {
		if ( ! current_user_can( self::CAP ) ) {
			wp_die( esc_html__( 'You are not allowed to do this.', 'ezrate-price-widgets' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( $action );
	}

	/**
	 * Back to a page with a message.
	 *
	 * @param string $page Page slug.
	 * @param string $msg  Message key.
	 * @param array  $args Extra query args.
	 */
	private static function back( $page, $msg, $args = array() ) {
		wp_safe_redirect(
			add_query_arg(
				array_merge(
					array(
						'page'      => $page,
						'ezrpw_msg' => $msg,
					),
					$args
				),
				admin_url( 'admin.php' )
			)
		);
		exit;
	}

	/**
	 * Raw form values of a widget.
	 *
	 * @return array
	 */
	private static function posted() {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- checked by the callers; sanitized field by field in EZRPW_Widgets::sanitize()
		$raw = isset( $_POST['ezrpw'] ) && is_array( $_POST['ezrpw'] ) ? map_deep( wp_unslash( $_POST['ezrpw'] ), 'sanitize_text_field' ) : array();
		return $raw;
	}

	/**
	 * Save a widget.
	 */
	public static function save() {
		self::guard( 'ezrpw_save' );
		$id = EZRPW_Widgets::save( self::posted() );
		self::back( 'ezrpw-edit', 'saved', array( 'id' => $id ) );
	}

	/**
	 * Delete a widget.
	 */
	public static function delete() {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- the nonce of this id is checked on the next line.
		self::guard( 'ezrpw_delete_' . $id );
		EZRPW_Widgets::delete( $id );
		self::back( 'ezrpw-widgets', 'deleted' );
	}

	/**
	 * Duplicate a widget.
	 */
	public static function duplicate() {
		$id = isset( $_GET['id'] ) ? absint( $_GET['id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- the nonce of this id is checked on the next line.
		self::guard( 'ezrpw_duplicate_' . $id );
		$w = EZRPW_Widgets::get( $id );
		if ( $w ) {
			$w['id']       = 0;
			$w['position'] = 'inline';
			/* translators: %s: name of the copied widget */
			$w['title'] = sprintf( __( '%s (copy)', 'ezrate-price-widgets' ), '' !== $w['title'] ? $w['title'] : EZRPW_Widgets::type_label( $w['type'] ) );
			EZRPW_Widgets::save( $w );
		}
		self::back( 'ezrpw-widgets', 'copied' );
	}

	/**
	 * «Refresh prices now».
	 */
	public static function refresh() {
		self::guard( 'ezrpw_refresh' );
		$res = EZRPW_Data::refresh( true );
		$msg = true === $res ? 'refreshed' : ( 'ezrpw_wait' === $res->get_error_code() ? 'wait' : 'failed' );
		self::back( 'ezrpw-settings', $msg );
	}

	/**
	 * Save the settings.
	 */
	public static function save_settings() {
		self::guard( 'ezrpw_settings' );
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- checked by guard() above.
		$raw = isset( $_POST['ezrpw_settings'] ) && is_array( $_POST['ezrpw_settings'] ) ? map_deep( wp_unslash( $_POST['ezrpw_settings'] ), 'sanitize_text_field' ) : array();
		update_option(
			EZRPW_Widgets::SETTINGS,
			array(
				'default_lang' => isset( $raw['default_lang'] ) && 'en' === $raw['default_lang'] ? 'en' : 'fa',
				'link_rates'   => ! empty( $raw['link_rates'] ),
				'credit'       => ! empty( $raw['credit'] ),
				'cleanup'      => ! empty( $raw['cleanup'] ),
			)
		);
		self::back( 'ezrpw-settings', 'settings' );
	}

	/**
	 * Live preview of the unsaved form (from the cached prices; never contacts the data service more than the cache does).
	 */
	public static function preview() {
		check_ajax_referer( 'ezrpw_preview', 'nonce' );
		if ( ! current_user_can( self::CAP ) ) {
			wp_send_json_error( null, 403 );
		}
		$w = EZRPW_Widgets::sanitize( self::posted() );
		wp_send_json_success( array( 'html' => EZRPW_Render::widget( $w, EZRPW_Data::get(), true ) ) );
	}
}
