<?php
/**
 * Plugin Name: My Subscriptions for WooCommerce
 * Plugin URI: https://www.webninjallc.com/
 * Description: Sell subscription products with gateway-neutral, customer-paid renewal orders.
 * Version: 0.4.2
 * Author: Mahfuzar Rahman
 * Author URI: https://github.com/webifya
 * Text Domain: webifya-subscriptions
 * Requires at least: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 8.5
 * WC tested up to: 9.9
 *
 * @package Webifya_Subscriptions
 */

defined( 'ABSPATH' ) || exit;

define( 'WFS_VERSION', '0.4.2' );
define( 'WFS_PLUGIN_FILE', __FILE__ );
define( 'WFS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );

add_action(
	'before_woocommerce_init',
	static function () {
		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
		}
	}
);

require_once WFS_PLUGIN_PATH . 'includes/class-wfs-plugin.php';

register_activation_hook( __FILE__, array( 'WFS_Plugin', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'WFS_Plugin', 'deactivate' ) );

add_action( 'plugins_loaded', array( 'WFS_Plugin', 'instance' ) );
