=== Webifya Subscriptions for WooCommerce ===
Contributors: webifya
Tags: woocommerce, subscriptions, recurring payments, renewal orders
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.3.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sell subscriptions with any WooCommerce payment gateway through customer-paid renewal orders.

== Description ==

Webifya Subscriptions adds a subscription product type to WooCommerce. After the initial order is paid, it schedules renewal orders at the product's billing interval. Customers receive a normal WooCommerce renewal order and can pay it with any gateway enabled for that order.

This gateway-neutral approach does not claim that every gateway can charge a customer automatically. Automatic off-session charging requires explicit support from the payment gateway and provider. Version 0.1 uses customer-paid renewals for predictable compatibility.

Features:

* Subscription product type with daily, weekly, monthly, or yearly billing.
* Action Scheduler integration, with WP-Cron fallback.
* Renewal orders using standard WooCommerce checkout.
* Customer Subscriptions page with renewal payment and cancellation actions.
* Subscription administration under WooCommerce.
* HPOS-compatible order access.
* Extension hook after renewal creation: `wfs_renewal_order_created`.
* Configurable failed-payment reminders and retry schedule.
* Past-due and on-hold subscription states.
* Automatic recovery when a late renewal is paid.
* Renewal price and currency snapshots for billing consistency.
* Free trials with configurable duration.
* One-time sign-up fees.
* Fixed renewal limits with automatic expiration.

== Installation ==

1. Install and activate WooCommerce.
2. Upload this plugin folder to `/wp-content/plugins/`.
3. Activate Webifya Subscriptions.
4. Create a product and choose "Webifya subscription" as its product type.
5. Set its price and billing interval, then publish it.

== Changelog ==

= 0.3.0 =
* Added free-trial product settings and trialling lifecycle state.
* Added one-time sign-up fees to initial checkout pricing.
* Added optional renewal limits and automatic expiration.
* Separated initial checkout pricing from recurring renewal pricing.

= 0.2.0 =
* Added configurable dunning and failed-payment reminders.
* Added past-due and on-hold lifecycle states.
* Added automatic subscription recovery after late payment.
* Preserved the original recurring price and currency on renewal orders.
* Added retry lifecycle extension hooks and order notes.

= 0.1.0 =
* Initial MVP.
