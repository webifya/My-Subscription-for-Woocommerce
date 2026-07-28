<?php
/**
 * Main plugin class.
 *
 * @package Webifya_Subscriptions
 */

defined( 'ABSPATH' ) || exit;

final class WFS_Plugin {
	/**
	 * Singleton.
	 *
	 * @var WFS_Plugin|null
	 */
	private static $instance;

	/**
	 * Get the plugin instance.
	 *
	 * @return WFS_Plugin|null
	 */
	public static function instance() {
		if ( ! self::$instance ) {
			if ( ! class_exists( 'WooCommerce' ) ) {
				add_action( 'admin_notices', array( __CLASS__, 'woocommerce_notice' ) );
				return null;
			}

			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * Initialize features.
	 */
	private function __construct() {
		require_once WFS_PLUGIN_PATH . 'includes/class-wfs-product.php';
		require_once WFS_PLUGIN_PATH . 'includes/class-wfs-subscription.php';
		require_once WFS_PLUGIN_PATH . 'includes/class-wfs-renewals.php';
		require_once WFS_PLUGIN_PATH . 'includes/class-wfs-account.php';
		require_once WFS_PLUGIN_PATH . 'includes/class-wfs-settings.php';

		WFS_Product::init();
		WFS_Subscription::init();
		WFS_Renewals::init();
		WFS_Account::init();
		WFS_Settings::init();
	}

	/**
	 * Register data structures on activation.
	 */
	public static function activate() {
		require_once WFS_PLUGIN_PATH . 'includes/class-wfs-subscription.php';
		WFS_Subscription::register_post_type();
		flush_rewrite_rules();
	}

	/**
	 * Clear rewrite rules on deactivation.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Display dependency notice.
	 */
	public static function woocommerce_notice() {
		echo '<div class="notice notice-error"><p>';
		echo esc_html__( 'My Subscriptions for WooCommerce requires WooCommerce to be installed and active.', 'webifya-subscriptions' );
		echo '</p></div>';
	}
}
