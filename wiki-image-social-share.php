<?php

/*
Plugin Name: Wiki Image Social Share
Description: Enhanced social media sharing plugin for WordPress images with rich preview support across all major platforms including WhatsApp, Facebook, Twitter, LinkedIn, Pinterest, Instagram, Telegram, Discord, and Reddit.
Version: 1.2.0
Author: Arnel Go
Author URI: https://arnelbg.com/
Plugin URI: https://github.com/wikiwyrhead/wiki-image-social-share
Text Domain: wiki-image-social-share
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Network: false
*/


if (! defined('ABSPATH')) {
    exit;
}

define('WISS_VER', '1.0.0');

// Define plugin constants
define('WISS_DIR', dirname(__FILE__));
define('WISS_URL', plugins_url('', __FILE__));

// Legacy constants for backward compatibility with existing modules
define('STI_VER', WISS_VER);
define('STI_URL', WISS_URL);
define('STI_DIR', WISS_DIR);


if (! class_exists('WISS_Main')) :

    /**
     * Main plugin class
     *
     * @class WISS_Main
     */
    final class WISS_Main
    {

        /**
         * @var WISS_Main The single instance of the class
         */
        protected static $_instance = null;

        /**
         * @var WISS_Main Array of all plugin data $data
         */
        private $data = array();

        /**
         * Main WISS_Main Instance
         *
         * Ensures only one instance of WISS_Main is loaded or can be loaded.
         *
         * @static
         * @return WISS_Main - Main instance
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Constructor
         */
        public function __construct()
        {

            $this->data['settings'] = get_option('wiss_settings');

            // Ensure settings is always an array to prevent fatal errors
            if (!$this->data['settings']) {
                $this->data['settings'] = array();
            }

            $this->includes();

            add_filter('plugin_action_links', array($this, 'add_settings_link'), 10, 2);

            add_filter('plugin_row_meta', array($this, 'extra_meta_links'), 10, 2);

            add_action('admin_head', array($this, 'add_meta_styles'));

            load_plugin_textdomain('wiki-image-social-share', false, dirname(plugin_basename(__FILE__)) . '/languages/');

            add_action('init', array($this, 'init'), 1);
        }

        /**
         * Include required core files used in admin and on the frontend
         */
        private function includes()
        {

            // Load error handler first for debugging
            include_once WISS_DIR . '/includes/class-wiss-error-handler.php';

            include_once WISS_DIR . '/includes/class-sti-helpers.php';
            include_once WISS_DIR . '/includes/class-sti-functions.php';
            include_once WISS_DIR . '/includes/class-sti-conditions.php';
            include_once WISS_DIR . '/includes/class-sti-integrations.php';
            include_once WISS_DIR . '/includes/class-sti-shortcodes.php';
            include_once WISS_DIR . '/includes/class-sti-shortlink.php';
            include_once WISS_DIR . '/includes/class-sti-versions.php';
            include_once WISS_DIR . '/includes/class-wiss-whatsapp-optimizer.php';

            if (is_admin()) {
                // Load dependencies first
                include_once WISS_DIR . '/includes/admin/class-sti-admin-options.php';
                include_once WISS_DIR . '/includes/admin/class-sti-admin-helpers.php';
                include_once WISS_DIR . '/includes/admin/class-sti-admin-display-rules.php';

                // Load main admin classes
                include_once WISS_DIR . '/includes/admin/class-sti-admin.php';
                include_once WISS_DIR . '/includes/admin/class-sti-admin-fields.php';
                include_once WISS_DIR . '/includes/admin/class-sti-admin-ajax.php';
                include_once WISS_DIR . '/includes/admin/class-sti-admin-meta-boxes.php';
                include_once WISS_DIR . '/includes/admin/class-sti-admin-notices.php';
            }
        }

        /*
         * Add settings link to plugins page
         */
        public function add_settings_link($links, $file)
        {
            $plugin_base = plugin_basename(__FILE__);

            if ($file == $plugin_base) {
                $setting_link = '<a href="admin.php?page=sti-options">' . __('Settings', 'wiki-image-social-share') . '</a>';
                array_unshift($links, $setting_link);
            }

            return $links;
        }

        /*
         * Adds extra links to the plugin activation page
         */
        public function extra_meta_links($meta, $file)
        {
            $plugin_base = plugin_basename(__FILE__);

            if ($file == $plugin_base) {
                $meta[] = '<a href="https://github.com/wikiwyrhead/wiki-image-social-share" target="_blank" title="' . __('GitHub Repository', 'wiki-image-social-share') . '">' . __('GitHub', 'wiki-image-social-share') . '</a>';
                $meta[] = '<a href="https://github.com/wikiwyrhead/wiki-image-social-share/issues" target="_blank" title="' . __('Report Issues', 'wiki-image-social-share') . '">' . __('Support', 'wiki-image-social-share') . '</a>';
            }

            return $meta;
        }

        /*
         * Add styles for plugins page
         */
        public function add_meta_styles()
        {
            global $pagenow;

            if ($pagenow === 'plugins.php') {

                echo "<style>";
                echo ".wiss-github-link {";
                echo "color: #0073aa;";
                echo "text-decoration: none;";
                echo "}";
                echo ".wiss-github-link:hover {";
                echo "color: #005177;";
                echo "}";
                echo "</style>";
            }
        }

        /*
         * Init plugin classes
         */
        public function init()
        {

            STI_Integrations::instance();

            STI_Shortlink::instance();

            // Initialize admin classes if in admin area
            if (is_admin()) {
                STI_Admin::instance();
                STI_Admin_Notices::instance();
                new STI_Admin_Ajax();
            }
        }

        /*
         * Get plugin settings
         */
        public function get_settings($name = false)
        {
            $plugin_options = $this->data['settings'];
            $return_value = ! $name ? $plugin_options : (isset($plugin_options[$name]) ? $plugin_options[$name] : false);
            return $return_value;
        }
    }

endif;


/**
 * Returns the main instance of WISS_Main
 *
 * @return WISS_Main
 */
function WISS()
{
    return WISS_Main::instance();
}

/*
 * Activation hook
 */
register_activation_hook(__FILE__, 'wiss_activation_check');
function wiss_activation_check()
{
    // Ensure admin options class is loaded
    if (!class_exists('STI_Admin_Options')) {
        include_once WISS_DIR . '/includes/admin/class-sti-admin-options.php';
    }

    // Create default settings if they don't exist
    if (!get_option('wiss_settings')) {
        $default_settings = STI_Admin_Options::get_default_settings();
        update_option('wiss_settings', $default_settings, false);
    }

    // Set default options on activation
    $hide_notice = get_option('wiss_hide_welcome_notice');
    if (! $hide_notice) {
        update_option('wiss_hide_welcome_notice', 'false', false);
    }

    // Set plugin version
    update_option('wiss_plugin_ver', WISS_VER, false);
}

// Initialize the plugin
WISS();

/*
 * Deactivation hook - Clean up plugin data
 */
register_deactivation_hook(__FILE__, 'wiss_deactivation_cleanup');
function wiss_deactivation_cleanup()
{
    // Clean up options if needed
    // delete_option('wiss_settings'); // Uncomment if you want to remove settings on deactivation
}

/*
 * Uninstall hook - Remove all plugin data
 */
register_uninstall_hook(__FILE__, 'wiss_uninstall_cleanup');
function wiss_uninstall_cleanup()
{
    // Remove all plugin options
    delete_option('wiss_settings');
    delete_option('wiss_hide_welcome_notice');
    delete_option('wiss_plugin_ver');

    // Remove shortlink database table if exists
    global $wpdb;
    $table_name = $wpdb->prefix . 'sti_links';
    $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
}
