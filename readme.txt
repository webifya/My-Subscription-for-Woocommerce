=== Subscribely – Recurring Billing for WooCommerce ===
Contributors: webifya
Tags: woocommerce, subscriptions, recurring payments, subscription products, renewal orders
Requires at least: 6.4
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 0.5.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Create flexible WooCommerce subscriptions with scheduled renewals, failed-payment recovery, trials, sign-up fees, and gateway-neutral renewal invoices.

== Description ==

Subscribely – Recurring Billing for WooCommerce turns ordinary products into daily, weekly, monthly, or yearly subscriptions while keeping orders and checkout inside WooCommerce.

After the initial order is paid, Subscribely schedules each renewal, creates a normal WooCommerce renewal order, and gives the customer a clear subscription record in My Account. Renewal invoices work with any payment gateway enabled for that order. The plugin also handles failed-payment reminders, recovery, trials, sign-up fees, renewal limits, and renewal price snapshots.

Developed by Mahfuzar Rahman. Company website: https://www.webninjallc.com/

The Free edition uses customer-paid renewal invoices for broad gateway compatibility. Automatic off-session charging requires explicit saved-method support and is available in Subscribely PRO for compatible official Stripe, PayPal Payments, and Square gateways.

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

= Upgrade to Subscribely PRO =

Subscribely PRO adds automatic supported-gateway renewals, retry handling with invoice fallback, customer pause and resume, early renewal, advanced administration, and protected subscriber downloads with limits and expiry.

Subscribely PRO is currently $69.99 per year. Learn more at https://www.webninjallc.com/plugins/subscribely/ or purchase at https://www.webninjallc.com/product/subscribely-pro-recurring-billing-for-woocommerce/

== Installation ==

1. Install and activate WooCommerce.
2. Upload this plugin folder to `/wp-content/plugins/`.
3. Activate Subscribely – Recurring Billing for WooCommerce.
4. Create a product and choose "Subscription" as its product type.
5. Set its price and billing interval, then publish it.

== Frequently Asked Questions ==

= Can customers use any WooCommerce payment gateway? =

Yes. Customer-paid renewal invoices can use any gateway enabled for the renewal order. Automatic renewals require a compatible saved payment method and supported PRO gateway integration.

= Does the Free edition automatically charge customers? =

The Free edition creates scheduled renewal orders and customer-paid invoices. Subscribely PRO can automatically charge compatible saved methods through supported official Stripe, PayPal Payments, and Square gateways.

= What happens when a renewal payment fails? =

Subscribely records the failed renewal, schedules configured reminders, and updates the subscription lifecycle. A late paid renewal can recover the subscription automatically.

= Can I offer free trials or charge a sign-up fee? =

Yes. Subscription products can include a free trial, one-time sign-up fee, and optional renewal limit.

= Can customers manage subscriptions from My Account? =

Yes. Customers can view subscriptions and pay renewal orders. PRO adds merchant-controlled pause, resume, early renewal, and premium detail screens.

= Can I sell downloadable subscription products? =

Yes, with Subscribely PRO. PRO protects WooCommerce downloads, applies limits and expiry, resets eligible access after renewal, and revokes access when entitlement ends.

= Does Subscribely support HPOS? =

Yes. The plugin declares compatibility with WooCommerce High-Performance Order Storage and uses WooCommerce order APIs.

= What information is shared with Web Ninja LLC? =

The Free edition shares a site profile only after an administrator explicitly opts in. It never sends orders, customers, payment details, or subscription records. Disabling permission requests deletion of the stored Free profile.

== Changelog ==

= 0.5.1 =
* Added an explicit opt-in setting for consent-based site profile sharing with Web Ninja LLC License Manager.
* Added weekly profile refreshes and automatic profile removal when consent is withdrawn.
* Site profiles never include orders, customers, payments, or subscription records.
* Added Settings and Go PRO links on the Plugins screen.
* Updated the annual PRO offer to $69.99 and expanded the upgrade presentation.
* Improved the description, FAQ, documentation, and details metadata.

= 0.5.0 =
* Rebranded the public plugin as Subscribely – Recurring Billing for WooCommerce.
* Updated plugin metadata, administration copy, documentation, and translation domain.
* Preserved all existing `wfs_` subscription records, hooks, schedules, and product types for seamless upgrades.

= 0.4.4 =
* Added an Upgrade to PRO menu and feature overview for free-edition users.
* Added a $49.99/year single-site upgrade link to the Plugins screen.
* Automatically hides upgrade promotion when PRO is active.

= 0.4.3 =
* Simplified cart and checkout billing terms to formats such as "$55 per year".
* Added a live fallback for blank credit-card radio labels in classic and block checkout.

= 0.4.2 =
* Updated plugin authorship to Mahfuzar Rahman and added official profile and company URLs.

= 0.4.1 =
* Prevented enabled checkout gateways from displaying a blank payment-method title.
* Added classic Checkout and Checkout Blocks label-visibility compatibility.
* Added light hosted-field styling for Stripe and Square card inputs.

= 0.4.0 =
* Added full subscription terms to cart and checkout.
* Added stable subscription status and renewal-paid integration events.
* Added secure payment-token and filtered gateway metadata transfer to renewal orders.
* Added automatic-payment interception before manual renewal invoices are sent.

= 0.3.2 =
* Renamed the plugin to My Subscriptions for WooCommerce.

= 0.3.1 =
* Renamed the plugin to Subscriptions for WooCommerce.
* Restored the pricing and inventory fields for subscription products.
* Restored the add-to-cart form on subscription product pages.

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
