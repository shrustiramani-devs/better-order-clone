=== Better Order Clone ===
Contributors: Shrusti Ramani
Tags: woocommerce, orders, clone, hpos, duplicate
Requires at least: 5.6
Tested up to: 6.8
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Clone WooCommerce orders with a single click. HPOS-compatible using WooCommerce CRUD API.

== Description ==
Adds a "Clone" quick action button to the WooCommerce Orders list. Clone will copy line items, shipping lines, fees, coupons, addresses and basic order properties. The plugin uses the official WooCommerce CRUD API to remain compatible with HPOS / custom order tables.

== Installation ==
1. Upload the `better-order-clone` folder to the `/wp-content/plugins/` directory, or upload the zip via WP Admin -> Plugins -> Add New -> Upload Plugin.
2. Activate the plugin through the 'Plugins' screen in WordPress admin.
3. Go to WooCommerce -> Orders and use the Clone action in the right-most column for any order you want to duplicate.

== Frequently Asked Questions ==
= Is this plugin HPOS compatible? =
Yes — the plugin uses WooCommerce's CRUD classes (WC_Order and WC_Order_Item_*) which work with HPOS (custom order tables) and traditional CPT orders.

= What is copied? =
Billing & shipping addresses, products (quantities/prices), shipping lines, fees, coupon items and most order meta. Internal unique keys (order number / transaction) are excluded to avoid collisions.

= Does it clone payments or refunds? =
No — payment records and refunds are not cloned. The cloned order is created with a 'pending' status by default to avoid accidental payment duplication.

== Changelog ==
= 1.0.0 =
* Initial release - HPOS-friendly order cloning via WooCommerce CRUD.
