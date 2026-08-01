<?php
/**
 * Free-to-PRO upgrade promotion.
 *
 * @package Subscribely_Recurring_Billing
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
			__( 'Upgrade to Subscribely PRO', 'subscribely-recurring-billing' ),
			__( 'Upgrade to PRO', 'subscribely-recurring-billing' ),
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
				'utm_campaign' => 'subscribely-pro',
			),
			self::CHECKOUT_URL
		);
		array_unshift(
			$links,
			'<a href="' . esc_url( $url ) . '" target="_blank" rel="noopener sponsored" style="color:#7c3aed;font-weight:700">' .
			esc_html__( 'Upgrade to PRO — $49.99/year', 'subscribely-recurring-billing' ) .
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
				'utm_campaign' => 'subscribely-pro',
			),
			self::CHECKOUT_URL
		);
		?>
		<div class="wrap wfs-upgrade-wrap">
			<style>
				.wfs-upgrade-wrap{max-width:960px}.wfs-upgrade-hero{margin-top:24px;padding:40px;border-radius:16px;background:linear-gradient(135deg,#312e81,#7c3aed);color:#fff}.wfs-upgrade-hero h1{color:#fff;font-size:34px;margin:0 0 12px}.wfs-upgrade-hero p{font-size:17px;max-width:720px}.wfs-upgrade-price{font-size:30px;font-weight:700;margin:24px 0}.wfs-upgrade-price small{font-size:15px;font-weight:400}.wfs-upgrade-button{display:inline-block;padding:13px 24px;border-radius:7px;background:#fff;color:#4c1d95!important;font-size:16px;font-weight:700;text-decoration:none}.wfs-upgrade-features{display:grid;grid-template-columns:repeat(auto-fit,minmax(260px,1fr));gap:16px;margin-top:22px}.wfs-upgrade-feature{background:#fff;border:1px solid #dcdcde;border-radius:10px;padding:20px}.wfs-upgrade-feature h2{margin-top:0;font-size:17px}.wfs-upgrade-note{margin-top:20px;color:#50575e}
			</style>
			<section class="wfs-upgrade-hero">
				<h1><?php esc_html_e( 'Grow recurring revenue with Subscribely PRO', 'subscribely-recurring-billing' ); ?></h1>
				<p><?php esc_html_e( 'Automate renewals and give customers flexible subscription controls while keeping the reliable invoicing and recovery tools from the free edition.', 'subscribely-recurring-billing' ); ?></p>
				<div class="wfs-upgrade-price">
					<?php esc_html_e( '$49.99 per year', 'subscribely-recurring-billing' ); ?>
					<small><?php esc_html_e( 'for one website', 'subscribely-recurring-billing' ); ?></small>
				</div>
				<a class="wfs-upgrade-button" href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener sponsored">
					<?php esc_html_e( 'Get Subscribely PRO', 'subscribely-recurring-billing' ); ?>
				</a>
			</section>

			<div class="wfs-upgrade-features">
				<section class="wfs-upgrade-feature"><h2><?php esc_html_e( 'Automatic renewal payments', 'subscribely-recurring-billing' ); ?></h2><p><?php esc_html_e( 'Automatically charge saved methods through supported Stripe, PayPal, and Square gateways.', 'subscribely-recurring-billing' ); ?></p></section>
				<section class="wfs-upgrade-feature"><h2><?php esc_html_e( 'Smart payment recovery', 'subscribely-recurring-billing' ); ?></h2><p><?php esc_html_e( 'Retry failed automatic payments and fall back to a customer payment invoice when needed.', 'subscribely-recurring-billing' ); ?></p></section>
				<section class="wfs-upgrade-feature"><h2><?php esc_html_e( 'Pause and resume', 'subscribely-recurring-billing' ); ?></h2><p><?php esc_html_e( 'Let customers pause and resume eligible subscriptions with merchant-controlled limits.', 'subscribely-recurring-billing' ); ?></p></section>
				<section class="wfs-upgrade-feature"><h2><?php esc_html_e( 'Early renewals', 'subscribely-recurring-billing' ); ?></h2><p><?php esc_html_e( 'Allow customers to renew before the scheduled payment date.', 'subscribely-recurring-billing' ); ?></p></section>
				<section class="wfs-upgrade-feature"><h2><?php esc_html_e( 'Advanced administration', 'subscribely-recurring-billing' ); ?></h2><p><?php esc_html_e( 'Manage subscription status and next-payment dates directly from WordPress.', 'subscribely-recurring-billing' ); ?></p></section>
				<section class="wfs-upgrade-feature"><h2><?php esc_html_e( 'Premium account experience', 'subscribely-recurring-billing' ); ?></h2><p><?php esc_html_e( 'Give customers detailed subscription pages and convenient self-service controls.', 'subscribely-recurring-billing' ); ?></p></section>
			</div>
			<p class="wfs-upgrade-note"><?php esc_html_e( 'The free plugin remains required. PRO installs alongside it and unlocks the premium features.', 'subscribely-recurring-billing' ); ?></p>
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
