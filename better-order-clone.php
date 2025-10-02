<?php
/**
 * Plugin Name:       Better Order Clone
 * Description:       Clone WooCommerce orders (line items, shipping, fees, coupons, addresses) using WooCommerce CRUD so it's HPOS compatible.
 * Version:           1.0.0
 * Author:            Shrusti Ramani
 * Text Domain:       better-order-clone
 * Requires Plugins:  woocommerce
 * Requires at least: 5.6
 * Requires PHP:      7.4
 * License:           GPL-2.0-or-later
 */

// Exit if accessed directly to prevent security issues.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'BOC_PLUGIN_FILE', __FILE__ );
define( 'BOC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'BOC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'BOC_VERSION', '1.0.0' );

require_once BOC_PLUGIN_DIR . 'includes/class-boc-cloner.php';
require_once BOC_PLUGIN_DIR . 'includes/class-better-order-clone.php';

// Register activation and deactivation hooks.
register_activation_hook( __FILE__, array( 'Better_Order_Clone', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Better_Order_Clone', 'deactivate' ) );

Better_Order_Clone::instance();
