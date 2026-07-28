<?php
/**
 * Subscription settings.
 *
 * @package Webifya_Subscriptions
 */

defined( 'ABSPATH' ) || exit;

class WFS_Settings {
	const OPTION = 'wfs_dunning_settings';

	/**
	 * Set up hooks.
	 */
	public static function init() {
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register the settings page.
	 */
	public static function menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Subscription settings', 'webifya-subscriptions' ),
			__( 'Subscription settings', 'webifya-subscriptions' ),
			'manage_woocommerce',
			'wfs-settings',
			array( __CLASS__, 'page' )
		);
	}

	/**
	 * Register settings and fields.
	 */
	public static function register() {
		register_setting(
			'wfs_settings',
			self::OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( __CLASS__, 'sanitize' ),
				'default'           => self::defaults(),
			)
		);

		add_settings_section(
			'wfs_dunning',
			__( 'Failed-payment recovery', 'webifya-subscriptions' ),
			array( __CLASS__, 'section' ),
			'wfs-settings'
		);

		add_settings_field(
			'retry_days',
			__( 'Days between reminders', 'webifya-subscriptions' ),
			array( __CLASS__, 'number_field' ),
			'wfs-settings',
			'wfs_dunning',
			array( 'key' => 'retry_days', 'min' => 1, 'max' => 30 )
		);
		add_settings_field(
			'max_retries',
			__( 'Maximum reminders', 'webifya-subscriptions' ),
			array( __CLASS__, 'number_field' ),
			'wfs-settings',
			'wfs_dunning',
			array( 'key' => 'max_retries', 'min' => 1, 'max' => 10 )
		);
	}

	/**
	 * Defaults.
	 *
	 * @return array
	 */
	public static function defaults() {
		return array(
			'retry_days' => 3,
			'max_retries' => 3,
		);
	}

	/**
	 * Get one setting.
	 *
	 * @param string $key Setting key.
	 * @return int
	 */
	public static function get( $key ) {
		$values = wp_parse_args( get_option( self::OPTION, array() ), self::defaults() );
		return isset( $values[ $key ] ) ? absint( $values[ $key ] ) : 0;
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $values Submitted values.
	 * @return array
	 */
	public static function sanitize( $values ) {
		$values = is_array( $values ) ? $values : array();
		return array(
			'retry_days'  => min( 30, max( 1, absint( $values['retry_days'] ?? 3 ) ) ),
			'max_retries' => min( 10, max( 1, absint( $values['max_retries'] ?? 3 ) ) ),
		);
	}

	/**
	 * Settings introduction.
	 */
	public static function section() {
		echo '<p>' . esc_html__( 'Unpaid renewal orders are reminded automatically. After the final attempt, the subscription is placed on hold until the order is paid.', 'webifya-subscriptions' ) . '</p>';
	}

	/**
	 * Render a numeric field.
	 *
	 * @param array $args Field arguments.
	 */
	public static function number_field( $args ) {
		printf(
			'<input type="number" class="small-text" name="%1$s[%2$s]" value="%3$d" min="%4$d" max="%5$d" step="1" />',
			esc_attr( self::OPTION ),
			esc_attr( $args['key'] ),
			esc_attr( self::get( $args['key'] ) ),
			esc_attr( $args['min'] ),
			esc_attr( $args['max'] )
		);
	}

	/**
	 * Render settings page.
	 */
	public static function page() {
		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Subscriptions for WooCommerce settings', 'webifya-subscriptions' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'wfs_settings' );
				do_settings_sections( 'wfs-settings' );
				submit_button();
				?>
			</form>
		</div>
		<?php
	}
}
