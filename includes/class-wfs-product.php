<?php
/**
 * Subscription product integration.
 *
 * @package Webifya_Subscriptions
 */

defined( 'ABSPATH' ) || exit;

class WFS_Product {
	/**
	 * Set up hooks.
	 */
	public static function init() {
		add_filter( 'product_type_selector', array( __CLASS__, 'add_product_type' ) );
		add_filter( 'woocommerce_product_class', array( __CLASS__, 'product_class' ), 10, 2 );
		add_action( 'woocommerce_product_options_general_product_data', array( __CLASS__, 'product_fields' ) );
		add_action( 'woocommerce_admin_process_product_object', array( __CLASS__, 'save_product_fields' ) );
		add_filter( 'woocommerce_get_price_html', array( __CLASS__, 'price_html' ), 10, 2 );
		add_filter( 'woocommerce_add_cart_item_data', array( __CLASS__, 'cart_item_data' ), 10, 2 );
		add_action( 'woocommerce_before_calculate_totals', array( __CLASS__, 'initial_cart_price' ) );
		add_action( 'woocommerce_checkout_create_order_line_item', array( __CLASS__, 'order_item_data' ), 10, 4 );
		add_action( 'admin_footer', array( __CLASS__, 'admin_script' ) );
	}

	/**
	 * Add product type.
	 *
	 * @param array $types Product types.
	 * @return array
	 */
	public static function add_product_type( $types ) {
		$types['wfs_subscription'] = __( 'Webifya subscription', 'webifya-subscriptions' );
		return $types;
	}

	/**
	 * Map product type to class.
	 *
	 * @param string $classname Class name.
	 * @param string $type Product type.
	 * @return string
	 */
	public static function product_class( $classname, $type ) {
		return 'wfs_subscription' === $type ? 'WFS_Product_Subscription' : $classname;
	}

	/**
	 * Render interval settings.
	 */
	public static function product_fields() {
		echo '<div class="options_group show_if_wfs_subscription">';

		woocommerce_wp_text_input(
			array(
				'id'                => '_wfs_interval',
				'label'             => __( 'Billing interval', 'webifya-subscriptions' ),
				'type'              => 'number',
				'value'             => get_post_meta( get_the_ID(), '_wfs_interval', true ) ?: 1,
				'custom_attributes' => array( 'min' => 1, 'step' => 1 ),
			)
		);

		woocommerce_wp_select(
			array(
				'id'      => '_wfs_period',
				'label'   => __( 'Billing period', 'webifya-subscriptions' ),
				'options' => array(
					'day'   => __( 'Day(s)', 'webifya-subscriptions' ),
					'week'  => __( 'Week(s)', 'webifya-subscriptions' ),
					'month' => __( 'Month(s)', 'webifya-subscriptions' ),
					'year'  => __( 'Year(s)', 'webifya-subscriptions' ),
				),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => '_wfs_trial_days',
				'label'             => __( 'Free trial', 'webifya-subscriptions' ),
				'description'       => __( 'Number of free-trial days before the first recurring payment.', 'webifya-subscriptions' ),
				'desc_tip'          => true,
				'type'              => 'number',
				'value'             => get_post_meta( get_the_ID(), '_wfs_trial_days', true ) ?: 0,
				'custom_attributes' => array( 'min' => 0, 'step' => 1 ),
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'          => '_wfs_signup_fee',
				'label'       => __( 'Sign-up fee', 'webifya-subscriptions' ) . ' (' . get_woocommerce_currency_symbol() . ')',
				'description' => __( 'One-time fee charged at checkout, including when a free trial is used.', 'webifya-subscriptions' ),
				'desc_tip'    => true,
				'data_type'   => 'price',
				'value'       => get_post_meta( get_the_ID(), '_wfs_signup_fee', true ) ?: '',
			)
		);

		woocommerce_wp_text_input(
			array(
				'id'                => '_wfs_renewal_limit',
				'label'             => __( 'Renewal payment limit', 'webifya-subscriptions' ),
				'description'       => __( 'Maximum successful renewal payments. Use 0 for an ongoing subscription.', 'webifya-subscriptions' ),
				'desc_tip'          => true,
				'type'              => 'number',
				'value'             => get_post_meta( get_the_ID(), '_wfs_renewal_limit', true ) ?: 0,
				'custom_attributes' => array( 'min' => 0, 'step' => 1 ),
			)
		);

		echo '</div>';
	}

	/**
	 * Save interval settings.
	 *
	 * @param WC_Product $product Product object.
	 */
	public static function save_product_fields( $product ) {
		if ( 'wfs_subscription' !== $product->get_type() ) {
			return;
		}

		$interval = isset( $_POST['_wfs_interval'] ) ? absint( wp_unslash( $_POST['_wfs_interval'] ) ) : 1;
		$period   = isset( $_POST['_wfs_period'] ) ? sanitize_key( wp_unslash( $_POST['_wfs_period'] ) ) : 'month';
		$period   = in_array( $period, array( 'day', 'week', 'month', 'year' ), true ) ? $period : 'month';

		$product->update_meta_data( '_wfs_interval', max( 1, $interval ) );
		$product->update_meta_data( '_wfs_period', $period );
		$product->update_meta_data( '_wfs_trial_days', isset( $_POST['_wfs_trial_days'] ) ? absint( wp_unslash( $_POST['_wfs_trial_days'] ) ) : 0 );
		$product->update_meta_data( '_wfs_signup_fee', isset( $_POST['_wfs_signup_fee'] ) ? wc_format_decimal( wp_unslash( $_POST['_wfs_signup_fee'] ) ) : '' );
		$product->update_meta_data( '_wfs_renewal_limit', isset( $_POST['_wfs_renewal_limit'] ) ? absint( wp_unslash( $_POST['_wfs_renewal_limit'] ) ) : 0 );
	}

	/**
	 * Add billing cadence to price.
	 *
	 * @param string     $html Price HTML.
	 * @param WC_Product $product Product.
	 * @return string
	 */
	public static function price_html( $html, $product ) {
		if ( ! $product || 'wfs_subscription' !== $product->get_type() ) {
			return $html;
		}

		$interval = max( 1, absint( $product->get_meta( '_wfs_interval' ) ) );
		$period   = sanitize_key( $product->get_meta( '_wfs_period' ) ?: 'month' );
		$label    = 1 === $interval ? $period : $interval . ' ' . $period . 's';

		$details    = array( sprintf( __( 'every %s', 'webifya-subscriptions' ), $label ) );
		$trial_days = absint( $product->get_meta( '_wfs_trial_days' ) );
		$signup_fee = (float) $product->get_meta( '_wfs_signup_fee' );

		if ( $trial_days ) {
			$details[] = sprintf(
				/* translators: %d: number of trial days. */
				_n( '%d-day free trial', '%d-day free trial', $trial_days, 'webifya-subscriptions' ),
				$trial_days
			);
		}
		if ( $signup_fee > 0 ) {
			$details[] = sprintf( __( '%s sign-up fee', 'webifya-subscriptions' ), html_entity_decode( wp_strip_all_tags( wc_price( $signup_fee ) ), ENT_QUOTES, get_bloginfo( 'charset' ) ) );
		}

		return $html . ' <span class="wfs-period">' . esc_html( implode( ' · ', $details ) ) . '</span>';
	}

	/**
	 * Capture prices before the cart price is changed for the initial payment.
	 *
	 * @param array $data Cart item data.
	 * @param int   $product_id Product ID.
	 * @return array
	 */
	public static function cart_item_data( $data, $product_id ) {
		$product = wc_get_product( $product_id );
		if ( ! $product || 'wfs_subscription' !== $product->get_type() ) {
			return $data;
		}

		$recurring = (float) $product->get_price();
		$trial     = absint( $product->get_meta( '_wfs_trial_days' ) );
		$signup    = (float) $product->get_meta( '_wfs_signup_fee' );

		$data['_wfs_recurring_price'] = $recurring;
		$data['_wfs_initial_price']   = ( $trial ? 0 : $recurring ) + $signup;
		return $data;
	}

	/**
	 * Set the checkout price to recurring price plus sign-up fee, or fee only for trials.
	 *
	 * @param WC_Cart $cart Cart.
	 */
	public static function initial_cart_price( $cart ) {
		if ( is_admin() && ! wp_doing_ajax() ) {
			return;
		}

		foreach ( $cart->get_cart() as $item ) {
			if ( isset( $item['_wfs_initial_price'], $item['data'] ) && $item['data'] instanceof WC_Product ) {
				$item['data']->set_price( (float) $item['_wfs_initial_price'] );
			}
		}
	}

	/**
	 * Persist recurring terms on the initial order item.
	 *
	 * @param WC_Order_Item_Product $item Order item.
	 * @param string                $cart_item_key Cart item key.
	 * @param array                 $values Cart values.
	 * @param WC_Order              $order Order.
	 */
	public static function order_item_data( $item, $cart_item_key, $values, $order ) {
		if ( isset( $values['_wfs_recurring_price'] ) ) {
			$item->add_meta_data( '_wfs_recurring_price', wc_format_decimal( $values['_wfs_recurring_price'] ), true );
		}
	}

	/**
	 * Make standard pricing/inventory panels visible for the custom type.
	 */
	public static function admin_script() {
		$screen = get_current_screen();
		if ( ! $screen || 'product' !== $screen->post_type ) {
			return;
		}
		?>
		<script>
		jQuery(function($) {
			$('.options_group.pricing, .inventory_options').addClass('show_if_wfs_subscription');
			$(document.body).trigger('woocommerce-product-type-change');
		});
		</script>
		<?php
	}
}

/**
 * Subscription product.
 */
class WFS_Product_Subscription extends WC_Product_Simple {
	/**
	 * Product type.
	 *
	 * @return string
	 */
	public function get_type() {
		return 'wfs_subscription';
	}
}
