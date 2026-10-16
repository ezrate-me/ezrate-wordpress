<?php
/**
 * Plugin Name:       EZRate Price Widgets
 * Plugin URI:        https://ezrate.me/services/widgets
 * Description:       Live Iranian free-market rates (US dollar, euro, gold, coins, USDT and crypto) in Toman: ticker, price bar, cards, table and chart widgets. Shortcode, block and classic widget; every setting in your dashboard.
 * Version:           2.1.0
 * Requires at least: 6.3
 * Requires PHP:      7.4
 * Author:            EZRate
 * Author URI:        https://ezrate.me
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       ezrate-price-widgets
 * Domain Path:       /languages
 *
 * @package EZRate_Price_Widgets
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'EZRPW_VERSION', '2.1.0' );
define( 'EZRPW_FILE', __FILE__ );
define( 'EZRPW_DIR', plugin_dir_path( __FILE__ ) );
define( 'EZRPW_URL', plugin_dir_url( __FILE__ ) );
if ( ! defined( 'EZRPW_API' ) ) {
	// Data service of the plugin (see readme.txt › External services). Can be overridden in wp-config.php.
	define( 'EZRPW_API', 'https://api.ezrate.me' );
}

require_once EZRPW_DIR . 'includes/class-ezrpw-data.php';
require_once EZRPW_DIR . 'includes/class-ezrpw-widgets.php';
require_once EZRPW_DIR . 'includes/class-ezrpw-format.php';
require_once EZRPW_DIR . 'includes/class-ezrpw-render.php';
require_once EZRPW_DIR . 'includes/class-ezrpw-front.php';
require_once EZRPW_DIR . 'includes/class-ezrpw-wp-widget.php';
if ( is_admin() ) {
	require_once EZRPW_DIR . 'includes/class-ezrpw-admin.php';
}

register_activation_hook( __FILE__, array( 'EZRPW_Data', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'EZRPW_Data', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'EZRPW_Data', 'init' ) );
add_action( 'plugins_loaded', array( 'EZRPW_Front', 'init' ) );
add_action(
	'plugins_loaded',
	static function () {
		if ( is_admin() ) {
			EZRPW_Admin::init();
		}
	}
);
add_action(
	'widgets_init',
	static function () {
		register_widget( 'EZRPW_WP_Widget' );
	}
);
