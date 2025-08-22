<?php

/**
 * STI admin functions
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (! class_exists('STI_Admin')) :

    /**
     * Class for plugin search
     */
    class STI_Admin
    {

        /**
         * @var STI_Admin The single instance of the class
         */
        protected static $_instance = null;

        /**
         * Main STI_Admin Instance
         *
         * Ensures only one instance of STI_Admin is loaded or can be loaded.
         *
         * @static
         * @return STI_Admin - Main instance
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /*
         * Constructor
         */
        public function __construct()
        {

            add_action('admin_init', array(&$this, 'register_settings'));
            add_action('admin_menu', array(&$this, 'add_admin_page'));
            add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));

            if (! STI_Admin_Options::get_settings()) {
                $default_settings = STI_Admin_Options::get_default_settings();
                update_option('wiss_settings', $default_settings);
            }
        }

        /*
        * Register plugin settings
        */
        public function register_settings()
        {
            register_setting('wiss_settings', 'wiss_settings');
        }

        /*
         * Get plugin settings
         */
        public function get_settings()
        {
            $plugin_options = get_option('wiss_settings');
            return $plugin_options;
        }

        /**
         * Add options page
         */
        public function add_admin_page()
        {
            add_menu_page(esc_html__('Wiki Image Social Share', 'wiki-image-social-share'), esc_html__('Image Sharing', 'wiki-image-social-share'), 'manage_options', 'sti-options', false, 'dashicons-format-image');
            add_submenu_page('sti-options', __('Settings', 'wiki-image-social-share'), __('Settings', 'wiki-image-social-share'), 'manage_options', 'sti-options', array($this, 'display_admin_page'));
        }

        /**
         * Generate and display options page
         */
        public function display_admin_page() {
            $nonce = wp_create_nonce('plugin-settings');

            $tabs = array(
                'buttons' => esc_html__('Buttons', 'wiki-image-social-share'),
                'display' => esc_html__('Display Rules', 'wiki-image-social-share'),
                'content' => esc_html__('Content', 'wiki-image-social-share'),
                'social' => esc_html__('Social Media', 'wiki-image-social-share'),
                'general' => esc_html__('General', 'wiki-image-social-share'),
            );

            $current_tab = isset($_GET['tab']) && array_key_exists($_GET['tab'], $tabs) 
                ? sanitize_text_field($_GET['tab']) 
                : 'buttons';

            // Handle form submission
            if (isset($_POST['Submit']) && current_user_can('manage_options') && 
                isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'plugin-settings')) {
                STI_Admin_Options::update_settings();
                add_settings_error(
                    'wiss_settings',
                    'settings_updated',
                    __('Settings saved successfully.', 'wiki-image-social-share'),
                    'updated'
                );
            }

            $sti_options = STI_Admin_Options::get_settings();
            ?>

            <div class="wrap wiss-admin-wrap">
                <div id="sti-admin-header" class="wiss-header">
                    <div class="wiss-header-inner">
                        <div class="wiss-logo">
                            <img src="<?php echo esc_url(WISS_URL . '/assets/images/logo.png'); ?>" alt="<?php esc_attr_e('Wiki Image Social Share', 'wiki-image-social-share'); ?>">
                            <div class="wiss-title-wrap">
                                <h1><?php esc_html_e('Wiki Image Social Share', 'wiki-image-social-share'); ?></h1>
                                <span class="wiss-version"><?php echo 'v' . esc_html(WISS_VER); ?></span>
                            </div>
                        </div>
                        <div class="wiss-header-actions">
                            <a href="https://github.com/wikiwyrhead/wiki-image-social-share/wiki" target="_blank" class="button button-secondary">
                                <span class="dashicons dashicons-book"></span> 
                                <?php esc_html_e('Documentation', 'wiki-image-social-share'); ?>
                            </a>
                            <a href="https://github.com/wikiwyrhead/wiki-image-social-share/issues" target="_blank" class="button button-secondary">
                                <span class="dashicons dashicons-sos"></span> 
                                <?php esc_html_e('Support', 'wiki-image-social-share'); ?>
                            </a>
                            <a href="https://github.com/wikiwyrhead/wiki-image-social-share" target="_blank" class="button button-primary">
                                <span class="dashicons dashicons-randomize"></span> 
                                <?php esc_html_e('GitHub', 'wiki-image-social-share'); ?>
                            </a>
                        </div>
                    </div>
                </div>

                <?php settings_errors('wiss_settings'); ?>

                <nav class="nav-tab-wrapper wiss-nav-tab-wrapper">
                    <?php foreach ($tabs as $name => $label) : ?>
                        <a href="<?php echo esc_url(admin_url('admin.php?page=sti-options&tab=' . $name)); ?>" 
                           class="nav-tab <?php echo $current_tab === $name ? 'nav-tab-active' : ''; ?>">
                            <?php echo esc_html($label); ?>
                        </a>
                    <?php endforeach; ?>
                </nav>

                <div class="wiss-content-wrap">
                    <form action="" method="post" name="sti_form" id="sti_form" class="wiss-settings-form">
                        <div class="wiss-settings-content">
                            <?php
                            // Display the current tab's fields
                            new STI_Admin_Fields($current_tab, $sti_options);
                            ?>
                        </div>

                        <?php wp_nonce_field('plugin-settings', '_wpnonce', false); ?>
                        
                        <div class="wiss-settings-footer">
                            <?php submit_button(__('Save Changes', 'wiki-image-social-share'), 'primary', 'Submit', false); ?>
                        </div>
                    </form>
                </div>
            </div>
            <?php
        }

        /**
         * Enqueue admin scripts and styles
         */
        public function admin_enqueue_scripts()
        {
            if (isset($_GET['page']) && $_GET['page'] == 'sti-options') {

                $suffix = defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ? '' : '.min';

                wp_enqueue_style('wiss-select2', WISS_URL . '/assets/css/select2.min.css');
                wp_enqueue_style('wiss-admin-style', WISS_URL . '/assets/css/sti-admin' . $suffix . '.css', array(), WISS_VER);

                wp_register_script('wiss-select2', WISS_URL . '/assets/js/select2.full.min.js', array('jquery'), WISS_VER);

                wp_enqueue_script('jquery');
                wp_enqueue_script('wiss-select2');
                wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_media();
                wp_enqueue_script('wiss-admin-js', WISS_URL . '/assets/js/admin' . $suffix . '.js', array('jquery', 'wiss-select2', 'jquery-ui-sortable'), WISS_VER);
                wp_localize_script('wiss-admin-js', 'sti_ajax_object', array(
                    'ajax_nonce' => wp_create_nonce('ajax_nonce'),
                    'ajaxurl' => admin_url('admin-ajax.php', 'relative'),
                ));
            }
        }
    }


endif;

add_action('init', 'STI_Admin::instance');
