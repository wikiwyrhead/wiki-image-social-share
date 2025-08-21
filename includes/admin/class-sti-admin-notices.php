<?php

if (! defined('ABSPATH')) {
    exit;
}


if (! class_exists('STI_Admin_Notices')) :

    /**
     * Class for plugin admin panel
     */
    class STI_Admin_Notices
    {

        /**
         * @var STI_Admin_Notices The single instance of the class
         */
        protected static $_instance = null;

        /**
         * Main STI_Admin_Notices Instance
         *
         * Ensures only one instance of STI_Admin_Notices is loaded or can be loaded.
         *
         * @static
         * @return STI_Admin_Notices - Main instance
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

            // Plugins integration notice
            add_action('admin_notices', array($this, 'plugins_integration_notice'), 1);

            // Local server notice
            add_action('admin_notices', array($this, 'admin_notices_local'));

            // Welcome notice
            add_action('admin_notices', array($this, 'display_welcome_header'), 1);

            // Hide integration notices
            add_action('admin_init', array($this, 'hide_notices'));
        }

        /*
         * Show notices about plugin integrations (removed pro features)
         */
        public function plugins_integration_notice()
        {
            // Pro integration notices removed - this is now a free plugin
            return;
        }

        /*
         * Add admin notice
         */
        public function admin_notices_local()
        {

            if (get_option('sti-notice-dismiss-local-notice')) {
                return;
            }

            if (isset($_GET['page']) && $_GET['page'] === 'sti-options') { ?>
                <div data-sti-notice="local-notice" class="notice notice-info is-dismissible">
                    <p><?php _e('<strong>Remember:</strong> Plugin won\'t work properly if you are using it on your <strong>local server</strong> or if your site has disabled <strong>search engine indexing</strong>.', 'wiki-image-social-share'); ?></p>

                    <p><?php _e('Please test the <strong>Wiki Image Social Share</strong> plugin on a publicly available site.', 'wiki-image-social-share'); ?></p>
                </div>
<?php }
        }

        /*
         * Add welcome notice
         */
        public function display_welcome_header()
        {

            if (! isset($_GET['page']) || $_GET['page'] !== 'sti-options') {
                return;
            }

            $hide_notice = get_option('wiss_hide_welcome_notice');

            if (!! $hide_notice || $hide_notice === 'true') {
                return;
            }

            echo STI_Admin_Meta_Boxes::get_welcome_notice();
        }

        /*
         * Check plugin activation time: show notices only who use the plugin more than 7 days
         */
        public function check_activation_time()
        {

            $activation_time = get_option('sti_activation_time');
            $show_notices = false;

            if (! $activation_time) {
                update_option('sti_activation_time', time(), 'no');
            } else {
                $time_pass = time() - $activation_time;
                $days_pass = (int) round((($time_pass / 24) / 60) / 60);
                if ($days_pass && $days_pass > 7) {
                    $show_notices = true;
                }
            }

            return $show_notices;
        }

        /*
         * Hide admin integration notices
         */
        public function hide_notices()
        {

            if (isset($_GET['wiss_hide_int_notices']) && $_GET['wiss_hide_int_notices']) {
                $option = strpos($_GET['wiss_hide_int_notices'], '|') !== false ? explode('|', $_GET['wiss_hide_int_notices']) : array($_GET['wiss_hide_int_notices']);
                $option_current = get_option('wiss_hide_int_notices');
                $option = $option_current ? array_merge($option_current, $option) : $option;
                update_option('wiss_hide_int_notices', $option, false);
            }
        }
    }

endif;


add_action('init', 'STI_Admin_Notices::instance');
