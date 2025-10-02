<?php
if (! defined('ABSPATH')) exit;

/**
 * Order cloning.
 *
 * Uses WooCommerce CRUD (WC_Order / WC_Order_Item_*) so it works with both CPT orders and HPOS.
 */

class BOC_Cloner
{

    /**
     * Clone an existing order ID and return the new order ID on success, or WP_Error on failure.
     *
     * @param int $order_id
     * @return int|WP_Error
     */
    public static function clone_order($order_id)
    {
        if (! function_exists('wc_get_order')) {
            return new WP_Error('woocommerce_missing', __('WooCommerce is not active', 'better-order-clone'));
        }

        $source = wc_get_order($order_id);
        if (! $source) {
            return new WP_Error('invalid_order', __('Source order not found', 'better-order-clone'));
        }

        // Create new order
        $new_order = wc_create_order(array(
            'status'      => 'pending',
            'customer_id' => $source->get_user_id(),
        ));

        if (is_wp_error($new_order)) {
            return $new_order;
        }

        // Copy addresses
        $new_order->set_address($source->get_address('billing'), 'billing');
        $new_order->set_address($source->get_address('shipping'), 'shipping');

        // Copy properties
        $new_order->set_currency($source->get_currency());
        $new_order->set_payment_method($source->get_payment_method());
        $new_order->set_payment_method_title($source->get_payment_method_title());
        $new_order->set_customer_note($source->get_customer_note());
        $new_order->set_created_via('clone');

        // Copy line items, including product name, taxes
        foreach ($source->get_items('line_item') as $item) {
            $new_item = new WC_Order_Item_Product();
            $new_item->set_name($item->get_name());
            $new_item->set_product_id($item->get_product_id());
            $new_item->set_variation_id($item->get_variation_id());
            $new_item->set_quantity($item->get_quantity());
            $new_item->set_subtotal($item->get_subtotal());
            $new_item->set_total($item->get_total());
            $new_item->set_taxes($item->get_taxes());
            foreach ($item->get_meta_data() as $meta) {
                $new_item->add_meta_data($meta->key, $meta->value, false);
            }
            $new_order->add_item($new_item);
        }

        // Copy shipping methods and totals
        foreach ($source->get_items('shipping') as $item) {
            $ship = new WC_Order_Item_Shipping();
            $ship->set_method_id($item->get_method_id());
            $ship->set_method_title($item->get_method_title());
            $ship->set_total($item->get_total());
            $ship->set_taxes($item->get_taxes());
            foreach ($item->get_meta_data() as $meta) {
                $ship->add_meta_data($meta->key, $meta->value, false);
            }
            $new_order->add_item($ship);
        }

        // Copy tax line items 
        foreach ($source->get_items('tax') as $item) {
            $tax_item = new WC_Order_Item_Tax();
            $tax_item->set_rate_id($item->get_rate_id());
            $tax_item->set_label($item->get_label());
            $tax_item->set_compound($item->get_compound());
            $tax_item->set_tax_total($item->get_tax_total());
            $tax_item->set_shipping_tax_total($item->get_shipping_tax_total());

            foreach ($item->get_meta_data() as $meta) {
                $tax_item->add_meta_data($meta->key, $meta->value, false);
            }

            $new_order->add_item($tax_item);
        }

        // Copy fees
        foreach ($source->get_items('fee') as $item) {
            $fee = new WC_Order_Item_Fee();
            $fee->set_name($item->get_name());
            $fee->set_total($item->get_total());
            $fee->set_taxes($item->get_taxes());
            foreach ($item->get_meta_data() as $meta) {
                $fee->add_meta_data($meta->key, $meta->value, false);
            }
            $new_order->add_item($fee);
        }

        // Copy coupons
        foreach ($source->get_items('coupon') as $item) {
            $coupon = new WC_Order_Item_Coupon();
            $coupon->set_code($item->get_code());
            $coupon->set_discount($item->get_discount());
            $coupon->set_discount_tax($item->get_discount_tax());
            $new_order->add_item($coupon);
        }

        // Explicitly set order totals from source
        $new_order->set_cart_tax($source->get_cart_tax());
        $new_order->set_shipping_tax($source->get_shipping_tax());
        $new_order->set_shipping_total($source->get_shipping_total());
        $new_order->set_discount_total($source->get_discount_total());
        $new_order->set_total($source->get_total());

        $exclude = array(
            '_order_key',
            '_paid_date',
            '_transaction_id',
            '_order_currency',
            '_order_total',
            '_order_number',
            '_order_number_formatted',
            '_edit_lock',
            '_edit_last',
            '_cart_discount',
            '_cart_discount_tax'
        );
        foreach ($source->get_meta_data() as $meta) {
            if (in_array($meta->key, $exclude, true)) {
                continue;
            }
            $new_order->add_meta_data($meta->key, $meta->value, false);
        }

        // Copy total-related meta for compatibility 
        $total_meta_keys = array(
            '_order_shipping',
            '_order_shipping_tax',
            '_order_tax',
            '_cart_discount',
            '_cart_discount_tax',
        );
        foreach ($total_meta_keys as $key) {
            if ($source->meta_exists($key)) {
                $new_order->add_meta_data($key, $source->get_meta($key), false);
            }
        }

        // Save 
        $new_order->save();

        // Trigger action for extensibility
        do_action('boc_order_cloned', $new_order->get_id(), $order_id);
        return $new_order->get_id();
    }
}
