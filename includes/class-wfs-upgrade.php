<?php
/**
 * Free-to-PRO upgrade promotion.
 *
 * @package Webifya_Subscriptions
 */

defined( 'ABSPATH' ) || exit;

class WFS_Upgrade {
	const CHECKOUT_URL = 'https://www.webninjallc.com/product/my-subscriptions-pro-for-woocommerce/';

	/**
	 * Register upgrade entry points.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ), 99 );
		add_filter( 'plugin_action_links_' . plugin_basename( WFS_PLUGIN_FILE ), array( __CLASS__, 'plugin_action_links' ) );
	}

	/**
	 * Add the WooCommerce upgrade submenu for free-edition users.
	 */
	public static function menu() {
		if ( self::pro_is_active() ) {
			return;
		}

		add_submenu_page(
			'woocommerce',
			__( 'Upgrade to My Subscriptions PRO', 'webifya-subscriptions' ),
			__( 'Upgrade to PRO', 'webifya-subscriptions' ),
			'manage_woocommerce',
			'wfs-upgrade-pro',
			array( __CLASS__, 'page' )
		);
	}

	/**
	 * Add a prominent upgrade link on the Plugins screen.
	 *
	 * @param array $links Existing plugin links.
	 * @return array
	 */
	public static function plugin_action_links( $links ) {
		if ( self::pro_is_active() ) {
			return $links;
		}

		$url = add_query_arg(
			array(
				'utm_source'   => 'wordpress-admin',
				'utm_medium'   => 'plugin-link',
				'utm_campaign' => 'my-subscriptions-pro',
			),
			self::CHECKOUT_URL
		);
		array_unshift(
			$links,
			'<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener sponsored" style="color:#7c3aed;font-weight:700">' .
			esc_html__( 'Upgrade to PRO — $49.99/year', 'webifya-subscriptions' ) .
			'</a>'
		);
		return $links;
	}

	/**
	 * Render the upgrade page.
	 */
	public static function page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}

		$url = add_query_arg(
			array(
				'utm_source'   => 'wordpress-admin',
				'utm_medium'   => 'upgrade-page',
				'utm_campaign' => 'my-subscriptions-pro',
			),
			self::CHECKOUT_URL
		);
		?>
		<div class="wrap wfs-upgrade-wrap">
			<style>
				.wfs-upgrade-wrap{max-width:960px}.wfs-upgrade-hero{margin-top:24px;padding:40px;border-radius:16px;background:linear-gradient(135deg,#312e81,#7c3aed);color:#fff}.wfs-upgrade-hero h1{color:#fff;font-size:34px;margin:0 0 12px}.wfs-upgrade-hero p{font-size:17px;max-width:720px}.wfs-upgrade-price{font-size:30px;font-weight:700;margin:24px 0}.wfs-upgrade-price small{font-size:15px;font-weight:400}.wfs-upgrade-button{display:inline-block;padding:13px 24px;border-radius:7px;background:#fff;color:#4c1d95!important;font-size:16px;font-weight:700;text-decoration:none}.wfs-upgrade-features{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-top:22px}.wfs-upgrade-feature{background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:20px}.wfs-upgrade-feature h2{margin-top:0;font-size:17px}.wfs-upgrade-note{margin-top:20px;color:#50575e}
			</style>
			<section class="wfs-upgrade-hero">
				<h1><?php esc_html_e( 'Grow recurring revenue with My Subscriptions PRO', 'webifya-subscriptions' ); ?></h1>
				<p><?php esc_html_e( 'Automate renewals and give customers flexible subscription controls while keeping the reliable invoicing and recovery tools from the free edition.', 'webifya-subscriptions' ); ?></p>
				<div class="wfs-upgrade-price">
					<?php esc_html_e( '$49.99 per year', 'webifya-subscriptions' ); ?>
					<small><?php esc_html_e( 'for one website', 'webifya-subscriptions' ); ?></small>
				</div>
				<a class="wfs-upgrade-button" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener sponsored">
					<?php esc_html_e( 'Get My Subscriptions PRO', 'webifya-subscriptions' ); ?>
				</a>
			</section>

			<div class="wfs-upgrade-features">
				<section class="wfs-upgrade-feature"><h2><?php esc_html_e( 'Automatic renewal payments', 'webifya-subscriptions' ); ?></h2><p><?php esc_html_e( 'Automatically charge saved methods through supported Stripe, PayPal, and Square gateways.', 'webifya-subscriptions' ); ?></p></section>
				<section class="wfs-upgrade-feature"><h2><?php esc_html_e( 'Smart payment recovery', 'webifya-subscriptions' ); ?></h2><p><?php esc_html_e( 'Retry failed automatic payments and fall back to a customer payment invoice when needed.', 'webifya-subscriptions' ); ?></p></section>
				<section class="wfs-upgrade-feature"><h2><?php esc_html_e( 'Pause and resume', 'webifya-subscriptions' ); ?></h2><p><?php esc_html_e( 'Let customers pause and resume eligible subscriptions with merchant-controlled limits.', 'webifya-subscriptions' ); ?></p></section>
				<section class="wfs-upgrade-feature"><h2><?php esc_html_e( 'Early renewals', 'webifya-subscriptions' ); ?></h2><p><?php esc_html_e( 'Allow customers to renew before the scheduled payment date.', 'webifya-subscriptions' ); ?></p></section>
				<section class="wfs-upgrade-feature"><h2><?php esc_html_e( 'Advanced administration', 'webifya-subscriptions' ); ?></h2><p><?php esc_html_e( 'Manage subscription status and next-payment dates directly from WordPress.', 'webifya-subscriptions' ); ?></p></section>
				<section class="wfs-upgrade-feature"><h2><?php esc_html_e( 'Premium account experience', 'webifya-subscriptions' ); ?></h2><p><?php esc_html_e( 'Give customers detailed subscription pages and convenient self-service controls.', 'webifya-subscriptions' ); ?></p></section>
			</div>
			<p class="wfs-upgrade-note"><?php esc_html_e( 'The free plugin remains required. PRO installs alongside it and unlocks the premium features.', 'webifya-subscriptions' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Check whether the PRO add-on is active.
	 *
	 * @return bool
	 */
	private static function pro_is_active() {
		return defined( 'MSPRO_VERSION' ) || class_exists( 'MSPRO_Automatic_Payments' );
	}
}
