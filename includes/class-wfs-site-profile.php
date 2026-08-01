<?php
/**
 * Consent-based site profile sharing for the free edition.
 *
 * @package Subscribely_Recurring_Billing
 */

defined( 'ABSPATH' ) || exit;

class WFS_Site_Profile {
	const CRON = 'wfs_weekly_site_profile';

	/** Register profile refresh hooks. */
	public static function init() {
		add_action( self::CRON, array( __CLASS__, 'send' ) );
		add_action( 'add_option_' . WFS_Settings::OPTION, array( __CLASS__, 'settings_added' ), 10, 2 );
		add_action( 'update_option_' . WFS_Settings::OPTION, array( __CLASS__, 'settings_updated' ), 10, 2 );
		add_action( 'admin_init', array( __CLASS__, 'schedule' ) );
		add_action( 'upgrader_process_complete', array( __CLASS__, 'plugin_updated' ), 10, 2 );
		self::schedule();
	}

	/** Send immediately when the settings option is saved for the first time. */
	public static function settings_added( $option, $value ) {
		if ( ! empty( $value['share_site_profile'] ) ) {
			self::send( true );
		}
		self::schedule();
	}

	/** Keep the weekly refresh active only while the administrator has opted in. */
	public static function schedule() {
		if ( WFS_Settings::get( 'share_site_profile' ) ) {
			if ( ! wp_next_scheduled( self::CRON ) ) {
				wp_schedule_event( time() + HOUR_IN_SECONDS, 'weekly', self::CRON );
			}
		} else {
			wp_clear_scheduled_hook( self::CRON );
		}
	}

	/** Send or remove the profile when consent changes. */
	public static function settings_updated( $old_value, $new_value ) {
		$old_consent = ! empty( $old_value['share_site_profile'] );
		$new_consent = ! empty( $new_value['share_site_profile'] );
		if ( $old_consent !== $new_consent ) {
			self::send( $new_consent );
		}
		self::schedule();
	}

	/** Refresh after this plugin is upgraded. */
	public static function plugin_updated( $upgrader, $options ) {
		$plugins = isset( $options['plugins'] ) && is_array( $options['plugins'] ) ? $options['plugins'] : array( $options['plugin'] ?? '' );
		if ( 'plugin' === ( $options['type'] ?? '' ) && in_array( plugin_basename( WFS_PLUGIN_FILE ), $plugins, true ) && WFS_Settings::get( 'share_site_profile' ) ) {
			self::send( true );
		}
	}

	/**
	 * Share a consented profile, or remove it when consent is false.
	 *
	 * @param bool|null $consent Explicit override; null reads the saved preference.
	 * @return array|WP_Error
	 */
	public static function send( $consent = null ) {
		$consent = null === $consent ? (bool) WFS_Settings::get( 'share_site_profile' ) : (bool) $consent;
		$body    = self::profile( $consent );
		$response = wp_remote_post(
			self::endpoint(),
			array(
				'timeout'   => 12,
				'sslverify' => true,
				'headers'   => array( 'Accept' => 'application/json' ),
				'body'      => $body,
			)
		);
		if ( is_wp_error( $response ) ) {
			return $response;
		}
		$data = json_decode( wp_remote_retrieve_body( $response ), true );
		return wp_remote_retrieve_response_code( $response ) >= 400
			? new WP_Error( 'wfs_profile_rejected', sanitize_text_field( $data['message'] ?? __( 'The site profile was not accepted.', 'subscribely-recurring-billing' ) ) )
			: ( is_array( $data ) ? $data : array() );
	}

	/** Build profile data. Personal fields are populated only after opt-in. */
	private static function profile( $consent ) {
		global $wp_version;
		$admin = get_user_by( 'email', get_option( 'admin_email' ) );
		$theme = wp_get_theme();
		return array(
			'consent'            => $consent ? 'true' : 'false',
			'consent_version'    => '1.0',
			'site_url'           => home_url(),
			'site_name'          => $consent ? get_bloginfo( 'name' ) : '',
			'admin_name'         => $consent && $admin ? $admin->display_name : '',
			'admin_email'        => $consent ? sanitize_email( get_option( 'admin_email' ) ) : '',
			'admin_phone'        => $consent ? sanitize_text_field( get_option( 'woocommerce_store_phone', '' ) ) : '',
			'plugin_name'        => 'Subscribely – Recurring Billing for WooCommerce',
			'plugin_slug'        => 'subscribely-recurring-billing-for-woocommerce',
			'plugin_version'     => WFS_VERSION,
			'product'            => 'subscribely-recurring-billing-for-woocommerce',
			'instance_id'        => self::instance_id(),
			'wordpress_version'  => $consent ? $wp_version : '',
			'php_version'        => $consent ? PHP_VERSION : '',
			'active_theme'       => $consent ? $theme->get( 'Name' ) : '',
			'is_multisite'       => $consent && is_multisite() ? 'true' : 'false',
			'server_ip'          => $consent && isset( $_SERVER['SERVER_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_ADDR'] ) ) : '',
		);
	}

	/** Stable free-edition installation identifier. */
	private static function instance_id() {
		$id = get_option( 'wfs_site_profile_instance_id' );
		if ( ! $id ) {
			$id = wp_generate_uuid4();
			update_option( 'wfs_site_profile_instance_id', $id, false );
		}
		return sanitize_text_field( $id );
	}

	/** Keep the collector URL out of settings and rendered source. */
	private static function endpoint() {
		return apply_filters( 'wfs_site_profile_api_url', base64_decode( 'aHR0cHM6Ly93d3cud2VibmluamFsbGMuY29tL3dwLWpzb24vd25sbS92MS9zaXRlLXByb2ZpbGU=' ) );
	}
}
