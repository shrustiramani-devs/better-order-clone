<?php
if (! defined('ABSPATH')) exit;

/**
 * Admin-specific hooks and UI for Better Order Clone
 */
class BOC_Admin
{
    /**
     * Singleton instance
     * @var BOC_Admin|null
     */
    protected static $instance = null;

    /**
     * Singleton instance
     */
    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
            self::$instance->hooks();
        }
        return self::$instance;
    }

    /**
     * Initialize hooks
     */
    public function hooks()
    {
        // Add a quick action button to the Orders list (WC admin)
        add_filter('woocommerce_admin_order_actions', array($this, 'add_clone_action_button'), 100, 2);

        // CSS to show an icon for the action
        add_action('admin_head', array($this, 'admin_css'));

        // AJAX endpoint to perform clone (uses admin-ajax)
        add_action('wp_ajax_boc_clone_order', array($this, 'ajax_clone_order'));
    }

    /**
     * Adds a "Clone" icon/button to the Orders list quick actions
     *
     * @param array $actions Existing actions
     * @param WC_Order $order
     * @return array
     */
    public function add_clone_action_button($actions, $order)
    {
        if (! $order instanceof WC_Order) {
            return $actions;
        }

        if (! current_user_can('edit_shop_orders')) {
            return $actions;
        }

        $order_id = $order->get_id();

        // Create nonce for security
        $nonce = wp_create_nonce('boc_clone_order_' . $order_id);

        // Build URL with nonce
        $url = esc_url(
            wp_nonce_url(
                admin_url('admin-ajax.php?action=boc_clone_order&order_id=' . $order_id),
                'boc_clone_order_' . $order_id,
                'nonce'
            )
        );

        // Add action
        $actions['boc_clone'] = array(
            'url'    => $url,
            'name'   => __('Clone', 'better-order-clone'),
            'action' => 'boc_clone',
        );

        return $actions;
    }

    /**
     * CSS to add an icon to the action button in the Orders list
     */
    public function admin_css()
    {
        echo '<style>
            .wc-action-button-boc_clone::after {
                font-family: woocommerce !important;
                content: "\e02a" !important; 
            }
        </style>';
    }

    /**
     * AJAX handler that clones an order
     */
    public function ajax_clone_order()
    {
        // Check user capability
        if (! current_user_can('edit_shop_orders')) {
            wp_die(
                esc_html__('Insufficient permissions', 'better-order-clone'),
                '',
                array('response' => 403)
            );
        }

        // Sanitize and unslash input
        $order_id = isset($_REQUEST['order_id']) ? absint(wp_unslash($_REQUEST['order_id'])) : 0;
        $nonce    = isset($_REQUEST['nonce']) ? sanitize_text_field(wp_unslash($_REQUEST['nonce'])) : '';

        // Verify nonce
        if (! wp_verify_nonce($nonce, 'boc_clone_order_' . $order_id)) {
            wp_die(
                esc_html__('Security check failed', 'better-order-clone'),
                '',
                array('response' => 403)
            );
        }

        // Clone the order
        $result = BOC_Cloner::clone_order($order_id);

        // Handle errors
        if (is_wp_error($result)) {
            wp_die(
                esc_html($result->get_error_message()),
                '',
                array('response' => 500)
            );
        }

        // Redirect back to Orders page
        $referrer = wp_get_referer() ?: admin_url('edit.php?post_type=shop_order');
        wp_safe_redirect($referrer);
        exit;
    }
}
