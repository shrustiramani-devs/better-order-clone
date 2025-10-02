<?php
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Main plugin bootstrap class
 */
class Better_Order_Clone
{

    /**
     * Singleton instance
     * @var Better_Order_Clone|null
     */
    protected static $instance = null;

    public static function instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    public static function activate() {}

    public static function deactivate() {}

    public function init()
    {
        $this->load_dependencies();
        
        add_action('plugins_loaded', array($this, 'maybe_init_admin'));
    }

    protected function load_dependencies()
    {
        require_once BOC_PLUGIN_DIR . 'includes/class-boc-cloner.php';
        require_once BOC_PLUGIN_DIR . 'includes/admin/class-boc-admin.php';
    }


    public function maybe_init_admin()
    {
        if (is_admin()) {
            BOC_Admin::instance();
        }
    }
}
