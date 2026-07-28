# Webifya Subscriptions roadmap

## 0.1 — Gateway-neutral MVP

- Subscription product type and billing cadence
- Subscription records and admin list
- Action Scheduler renewals with WP-Cron fallback
- Customer-paid renewal orders through standard WooCommerce checkout
- My Account list, payment links, and cancellation
- HPOS compatibility

## 0.2 — Payment recovery

- Configurable renewal invoice reminders
- Past-due and on-hold lifecycle states
- Automatic recovery after late payment
- Stable renewal price and currency snapshots
- Retry audit notes and extension hooks

## 0.3 — Flexible subscription offers

- Free trials
- One-time sign-up fees
- Fixed renewal limits and automatic expiration
- Separate initial and recurring checkout pricing

## 0.4 — Production hardening

- Fixed calendar expiration dates and synchronized renewals
- Proration and quantity switching
- Early-renewal and pause/resume controls
- Subscription detail screen, notes, filters, exports, and reports
- Dedicated transactional email settings and templates
- REST API, webhook events, and privacy exporter/eraser integration
- End-to-end tests against current WordPress and WooCommerce releases

## 0.5 — Automatic gateway adapters

Automatic renewal charging cannot be implemented generically: the payment provider must expose a merchant-initiated/off-session API and its WooCommerce gateway must store a reusable token.

Adapters should implement a future `WFS_Automatic_Renewal_Gateway` contract and declare support explicitly. The fallback will always remain a customer-payable renewal order, so unsupported or failed automatic payments never block a merchant from collecting payment.

Initial adapter candidates:

- Stripe
- WooPayments
- PayPal

Each adapter must be built and tested against the gateway vendor's current public API and terms.
