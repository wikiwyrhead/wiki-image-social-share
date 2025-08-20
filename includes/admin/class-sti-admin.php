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

            add_filter('submenu_file', array($this, 'submenu_file'), 10, 2);
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
            add_menu_page(esc_html__('Share Image Options', 'share-this-image'), esc_html__('Share Images', 'share-this-image'), 'manage_options', 'sti-options', false, 'dashicons-format-image');
            add_submenu_page('sti-options', __('Settings', 'share-this-image'), __('Settings', 'share-this-image'), 'manage_options', 'sti-options', array($this, 'display_admin_page'));
            add_submenu_page('sti-options', __('Pro Features', 'share-this-image'),  __('Pro Features', 'share-this-image'), 'manage_options', admin_url('admin.php?page=sti-options&tab=premium'));
        }

        /**
         * Generate and display options page
         */
        public function display_admin_page()
        {

            $nonce = wp_create_nonce('plugin-settings');

            $tabs = array(
                'buttons' => esc_html__('Buttons', 'share-this-image'),
                'display' => esc_html__('Display Rules', 'share-this-image'),
                'content' => esc_html__('Content', 'share-this-image'),
                'social' => esc_html__('Social Media', 'share-this-image'),
                'general' => esc_html__('General', 'share-this-image'),
            );

            $current_tab = empty($_GET['tab']) ? 'buttons' : htmlspecialchars(urldecode($_GET['tab']));

            $tabs_html = '';

            foreach ($tabs as $name => $label) {
                $tabs_html .= '<li class="sti-nav-tab"><a data-tab="' . $name . '" href="' . admin_url('admin.php?page=sti-options&tab=' . $name) . '" class="sti-nav-tab-link ' . ($current_tab == $name ? 'active' : '') . '">' . $label . '</a></li>';
            }

            $tabs_html = '<ul class="sti-nav-tab-wrapper">' . $tabs_html . '</ul>';


            if (isset($_POST["Submit"]) && current_user_can('manage_options') && isset($_POST["_wpnonce"]) && wp_verify_nonce($_POST["_wpnonce"], 'plugin-settings')) {
                STI_Admin_Options::update_settings();
            }

            $sti_options = STI_Admin_Options::get_settings(); ?>


            <div id="sti-admin-header">
                <div class="inner">
                    <div class="logo">
                        <img src="<?php echo WISS_URL . '/assets/images/logo.png'; ?>" alt="<?php esc_attr_e('logo', 'wiki-image-social-share'); ?>">
                        <span class="title">
                            <?php esc_html_e('Wiki Image Social Share', 'wiki-image-social-share'); ?>
                        </span>
                        <span class="version">
                            <?php echo 'v' . WISS_VER; ?>
                        </span>
                    </div>
                    <div class="btns">
                        <a class="button button-docs" href="https://github.com/wikiwyrhead/wiki-image-social-share/wiki" target="_blank"><?php esc_html_e('Documentation', 'wiki-image-social-share'); ?></a>
                        <a class="button button-support" href="https://github.com/wikiwyrhead/wiki-image-social-share/issues" target="_blank"><?php esc_html_e('Support', 'wiki-image-social-share'); ?></a>
                        <a class="button button-github" href="https://github.com/wikiwyrhead/wiki-image-social-share" target="_blank"><?php esc_html_e('GitHub Repository', 'wiki-image-social-share'); ?></a>
                    </div>
                </div>
            </div>


            <div class="wrap">

                <?php if ($current_tab === 'premium'): ?>

                    <h1 class="sti-title"><?php esc_html_e('Pro Features', 'share-this-image'); ?></h1>

                    <form action="" name="sti_form" id="sti_form" class="sti_form form-tab-<?php echo $current_tab; ?>" method="post">

                        <?php new STI_Admin_Page_Premium(); ?>

                    </form>

                <?php else: ?>

                    <h1 class="sti-title"><?php esc_html_e('Settings', 'share-this-image'); ?></h1>

                    <?php echo $tabs_html; ?>

                    <form action="" name="sti_form" id="sti_form" class="sti_form form-tab-<?php echo $current_tab; ?>" method="post">

                        <div class="sti-settings">

                            <div class="sti-settings-inner">

                                <?php
                                foreach ($tabs as $name => $label) {
                                    new STI_Admin_Fields($name, $sti_options);
                                }
                                ?>

                            </div>

                        </div>

                        <input type="hidden" name="_wpnonce" value="<?php echo esc_attr($nonce); ?>">

                        <p class="submit"><input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'share-this-image'); ?>" /></p>

                    </form>

                <?php endif; ?>

            </div>

<?php }

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
                wp_localize_script('sti-admin-js', 'sti_ajax_object', array(
                    'ajax_nonce' => wp_create_nonce('ajax_nonce'),
                    'ajaxurl' => admin_url('admin-ajax.php', 'relative'),
                ));
            }
        }

        /*
         * Change current class for premium tab
         */
        public function submenu_file($submenu_file, $parent_file)
        {
            if ($parent_file === 'sti-options' && isset($_GET['tab']) && $_GET['tab'] === 'premium') {
                $submenu_file = admin_url('admin.php?page=sti-options&tab=premium');
            }
            return $submenu_file;
        }
    }


endif;

add_action('init', 'STI_Admin::instance');
