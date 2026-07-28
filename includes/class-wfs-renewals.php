<?php
/**
 * Renewal scheduling and orders.
 *
 * @package Webifya_Subscriptions
 */

defined( 'ABSPATH' ) || exit;

class WFS_Renewals {
	const ACTION = 'wfs_create_renewal_order';
	const RETRY_ACTION = 'wfs_retry_renewal_payment';
	const GROUP  = 'webifya-subscriptions';

	/**
	 * Set up hooks.
	 */
	public static function init() {
		add_action( self::ACTION, array( __CLASS__, 'create_renewal_order' ) );
		add_action( self::RETRY_ACTION, array( __CLASS__, 'retry_payment' ) );
		add_action( 'woocommerce_order_status_processing', array( __CLASS__, 'renewal_paid' ) );
		add_action( 'woocommerce_order_status_completed', array( __CLASS__, 'renewal_paid' ) );
		add_action( 'woocommerce_order_status_failed', array( __CLASS__, 'renewal_failed' ) );
	}

	/**
	 * Schedule the next unpaid-payment check.
	 *
	 * @param int $subscription_id Subscription ID.
	 * @param int $timestamp Run time.
	 */
	public static function schedule_retry( $subscription_id, $timestamp ) {
		$args = array( absint( $subscription_id ) );
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( self::RETRY_ACTION, $args, self::GROUP );
			as_schedule_single_action( absint( $timestamp ), self::RETRY_ACTION, $args, self::GROUP, true );
		} else {
			wp_clear_scheduled_hook( self::RETRY_ACTION, $args );
			wp_schedule_single_event( absint( $timestamp ), self::RETRY_ACTION, $args );
		}
	}

	/**
	 * Schedule one renewal.
	 *
	 * @param int $subscription_id Subscription ID.
	 * @param int $timestamp Run time.
	 */
	public static function schedule( $subscription_id, $timestamp ) {
		$args = array( absint( $subscription_id ) );
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( self::ACTION, $args, self::GROUP );
			as_schedule_single_action( absint( $timestamp ), self::ACTION, $args, self::GROUP, true );
		} else {
			wp_clear_scheduled_hook( self::ACTION, $args );
			wp_schedule_single_event( absint( $timestamp ), self::ACTION, $args );
		}
	}

	/**
	 * Create an unpaid order so the customer can use any enabled gateway.
	 *
	 * @param int $subscription_id Subscription ID.
	 */
	public static function create_renewal_order( $subscription_id ) {
		$subscription_id = absint( $subscription_id );
		if ( ! in_array( get_post_meta( $subscription_id, '_wfs_status', true ), array( 'active', 'trialling' ), true ) ) {
			return;
		}

		$existing = absint( get_post_meta( $subscription_id, '_wfs_pending_order_id', true ) );
		if ( $existing ) {
			$existing_order = wc_get_order( $existing );
			if ( $existing_order && $existing_order->needs_payment() ) {
				return;
			}
		}

		$parent  = wc_get_order( absint( get_post_meta( $subscription_id, '_wfs_parent_order_id', true ) ) );
		$product = wc_get_product( absint( get_post_meta( $subscription_id, '_wfs_product_id', true ) ) );
		if ( ! $parent || ! $product ) {
			update_post_meta( $subscription_id, '_wfs_status', 'on-hold' );
			return;
		}

		$order = wc_create_order( array( 'customer_id' => $parent->get_customer_id() ) );
		if ( is_wp_error( $order ) ) {
			return;
		}

		$order->set_address( $parent->get_address( 'billing' ), 'billing' );
		$order->set_address( $parent->get_address( 'shipping' ), 'shipping' );
		$order->set_currency( get_post_meta( $subscription_id, '_wfs_currency', true ) ?: $parent->get_currency() );
		$quantity = max( 1, absint( get_post_meta( $subscription_id, '_wfs_quantity', true ) ) );
		$price    = (float) get_post_meta( $subscription_id, '_wfs_recurring_price', true );
		$total    = wc_format_decimal( $price * $quantity );
		$order->add_product(
			$product,
			$quantity,
			array(
				'subtotal' => $total,
				'total'    => $total,
			)
		);
		$order->update_meta_data( '_wfs_subscription_id', $subscription_id );
		$order->update_meta_data( '_wfs_is_renewal', 1 );
		$order->calculate_totals();
		$order->update_status( 'pending', __( 'Subscription renewal awaiting customer payment.', 'webifya-subscriptions' ) );
		$order->save();

		update_post_meta( $subscription_id, '_wfs_pending_order_id', $order->get_id() );
		update_post_meta( $subscription_id, '_wfs_retry_count', 0 );
		update_post_meta( $subscription_id, '_wfs_status', 'active' );
		self::schedule_retry( $subscription_id, time() + ( DAY_IN_SECONDS * WFS_Settings::get( 'retry_days' ) ) );

		self::send_invoice( $order );
		do_action( 'wfs_renewal_order_created', $order, $subscription_id );
	}

	/**
	 * Remind a customer about an unpaid renewal.
	 *
	 * @param int $subscription_id Subscription ID.
	 */
	public static function retry_payment( $subscription_id ) {
		$subscription_id = absint( $subscription_id );
		$status          = get_post_meta( $subscription_id, '_wfs_status', true );
		if ( in_array( $status, array( 'cancelled', 'expired' ), true ) ) {
			return;
		}

		$order = wc_get_order( absint( get_post_meta( $subscription_id, '_wfs_pending_order_id', true ) ) );
		if ( ! $order || ! $order->needs_payment() ) {
			return;
		}

		$count = absint( get_post_meta( $subscription_id, '_wfs_retry_count', true ) ) + 1;
		$max   = WFS_Settings::get( 'max_retries' );
		update_post_meta( $subscription_id, '_wfs_retry_count', $count );

		if ( $count >= $max ) {
			update_post_meta( $subscription_id, '_wfs_status', 'on-hold' );
			$order->add_order_note( __( 'Final subscription renewal reminder sent; subscription placed on hold.', 'webifya-subscriptions' ) );
		} else {
			update_post_meta( $subscription_id, '_wfs_status', 'past-due' );
			$order->add_order_note(
				sprintf(
					/* translators: 1: attempt number, 2: maximum attempts. */
					__( 'Subscription renewal reminder %1$d of %2$d sent.', 'webifya-subscriptions' ),
					$count,
					$max
				)
			);
			self::schedule_retry( $subscription_id, time() + ( DAY_IN_SECONDS * WFS_Settings::get( 'retry_days' ) ) );
		}

		self::send_invoice( $order );
		do_action( 'wfs_renewal_payment_retried', $order, $subscription_id, $count, $max );
	}

	/**
	 * Mark a failed renewal for recovery.
	 *
	 * @param int $order_id Order ID.
	 */
	public static function renewal_failed( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || ! $order->get_meta( '_wfs_is_renewal' ) ) {
			return;
		}

		$subscription_id = absint( $order->get_meta( '_wfs_subscription_id' ) );
		if ( $subscription_id && 'cancelled' !== get_post_meta( $subscription_id, '_wfs_status', true ) ) {
			update_post_meta( $subscription_id, '_wfs_status', 'past-due' );
			self::schedule_retry( $subscription_id, time() + ( DAY_IN_SECONDS * WFS_Settings::get( 'retry_days' ) ) );
		}
	}

	/**
	 * Send the standard customer invoice email.
	 *
	 * @param WC_Order $order Renewal order.
	 */
	private static function send_invoice( $order ) {
		$mailer = WC()->mailer();
		$emails = $mailer->get_emails();
		if ( isset( $emails['WC_Email_Customer_Invoice'] ) ) {
			$emails['WC_Email_Customer_Invoice']->trigger( $order->get_id(), $order );
		}
	}

	/**
	 * Advance subscription after renewal is paid.
	 *
	 * @param int $order_id Order ID.
	 */
	public static function renewal_paid( $order_id ) {
		$order = wc_get_order( $order_id );
		if ( ! $order || ! $order->get_meta( '_wfs_is_renewal' ) || $order->get_meta( '_wfs_renewal_processed' ) ) {
			return;
		}

		$subscription_id = absint( $order->get_meta( '_wfs_subscription_id' ) );
		if ( ! $subscription_id ) {
			return;
		}

		$completed = absint( get_post_meta( $subscription_id, '_wfs_completed_renewals', true ) ) + 1;
		$limit     = absint( get_post_meta( $subscription_id, '_wfs_renewal_limit', true ) );
		update_post_meta( $subscription_id, '_wfs_completed_renewals', $completed );

		delete_post_meta( $subscription_id, '_wfs_pending_order_id' );
		delete_post_meta( $subscription_id, '_wfs_retry_count' );

		$args = array( $subscription_id );
		if ( function_exists( 'as_unschedule_all_actions' ) ) {
			as_unschedule_all_actions( self::RETRY_ACTION, $args, self::GROUP );
		} else {
			wp_clear_scheduled_hook( self::RETRY_ACTION, $args );
		}

		$order->update_meta_data( '_wfs_renewal_processed', 1 );
		$order->save();

		if ( $limit && $completed >= $limit ) {
			update_post_meta( $subscription_id, '_wfs_status', 'expired' );
			delete_post_meta( $subscription_id, '_wfs_next_payment' );
			do_action( 'wfs_subscription_expired', $subscription_id, $order );
			return;
		}

		$interval = max( 1, absint( get_post_meta( $subscription_id, '_wfs_interval', true ) ) );
		$period   = sanitize_key( get_post_meta( $subscription_id, '_wfs_period', true ) );
		$next     = WFS_Subscription::next_timestamp( time(), $interval, $period );
		update_post_meta( $subscription_id, '_wfs_status', 'active' );
		update_post_meta( $subscription_id, '_wfs_next_payment', $next );
		self::schedule( $subscription_id, $next );
	}
}
