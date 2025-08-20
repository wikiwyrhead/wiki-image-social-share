<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! class_exists( 'STI_Admin_Notices' ) ) :

    /**
     * Class for plugin admin panel
     */
    class STI_Admin_Notices {

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
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /*
         * Constructor
         */
        public function __construct() {

            // Plugins integration notice
            add_action( 'admin_notices', array( $this, 'plugins_integration_notice' ), 1 );

            // Local server notice
            add_action( 'admin_notices', array( $this, 'admin_notices_local' ) );

            // Welcome notice
            add_action( 'admin_notices', array( $this, 'display_welcome_header' ), 1 );

            // Hide integration notices
            add_action( 'admin_init', array( $this, 'hide_notices' ) );

        }

        /*
         * Show notices about PRO plugin integrations
         */
        public function plugins_integration_notice() {

            if ( ! current_user_can( 'manage_options' ) ) {
                return;
            }

            if ( ! class_exists('ACF') &&
                 ! class_exists( 'MetaSliderPlugin' ) &&
                 ! defined('NGG_PLUGIN') &&
                 ! ( class_exists( 'Envira_Gallery' ) || class_exists( 'Envira_Gallery_Lite' ) ) ) {
                return;
            }

            $hide_option = get_option( 'sti_hide_int_notices' );
            $notice_top_message = sprintf( __( 'Hi! Looks like you are using some plugins that have the advanced integration with %s. Please find more details below.', 'share-this-image' ), '<b>Share This Image PRO</b>' );
            $notice_message = '';
            $notice_id = '';

            if ( class_exists('ACF') && ( ! $hide_option || array_search( 'acf', $hide_option ) === false ) ) {
                $notice_message .= '<li>' . __( 'Advanced Custom Fields ( ACF ) plugin.', 'share-this-image' ) . ' <a target="_blank" href="https://share-this-image.com/guide/advanced-custom-fields-acf/?utm_source=wp-plugin&utm_medium=integration_notice&utm_campaign=acf">' . __( 'Learn more', 'share-this-image' ) . '</a></li>';
                $notice_id .= 'acf|';
            }

            if ( class_exists('MetaSliderPlugin') && ( ! $hide_option || array_search( 'metaslider', $hide_option ) === false ) ) {
                $notice_message .= '<li>' . __( 'Metaslider plugin.', 'share-this-image' ) . ' <a target="_blank" href="https://share-this-image.com/guide/metaslider/?utm_source=wp-plugin&utm_medium=integration_notice&utm_campaign=metaslider">' . __( 'Learn more', 'share-this-image' ) . '</a></li>';
                $notice_id .= 'metaslider|';
            }

            if ( defined('NGG_PLUGIN') && ( ! $hide_option || array_search( 'nextgen', $hide_option ) === false ) ) {
                $notice_message .= '<li>' . __( 'NextGen Gallery plugin.', 'share-this-image' ) . ' <a target="_blank" href="https://share-this-image.com/guide/nextgen-gallery/?utm_source=wp-plugin&utm_medium=integration_notice&utm_campaign=nextgen">' . __( 'Learn more', 'share-this-image' ) . '</a></li>';
                $notice_id .= 'nextgen|';
            }

            if ( ( class_exists( 'Envira_Gallery' ) || class_exists( 'Envira_Gallery_Lite' ) ) && ( ! $hide_option || array_search( 'envira', $hide_option ) === false ) ) {
                $notice_message .= '<li>' . __( 'Envira Gallery plugin.', 'share-this-image' ) . ' <a target="_blank" href="https://share-this-image.com/guide/envira-gallery/?utm_source=wp-plugin&utm_medium=integration_notice&utm_campaign=envira">' . __( 'Learn more', 'share-this-image' ) . '</a></li>';
                $notice_id .= 'envira|';
            }

            $notice_id = 'sti_hide_int_notices=' . urlencode( trim( $notice_id, '|' ) );

            if ( $notice_message ) {

                $check_timing = $this->check_activation_time();
                if ( ! $check_timing ) {
                    return;
                }

                $current_page_url = function_exists('wc_get_current_admin_url') ? wc_get_current_admin_url() : esc_url( admin_url('admin.php?page=sti-options'));
                $dismiss_link = strpos( $current_page_url, '?' ) === false ? $current_page_url . '?' : $current_page_url . '&';

                $html = '';

                $html .= '<div class="sti-integration-notice notice notice-success" style="position:relative;display:flex;">';
                    $html .= '<div style="margin: 20px 20px 0 0;" class="sti-integration-notice--logo">';
                        $html .= '<img style="max-width:70px;border-radius:3px;" src="' . STI_URL . '/assets/images/logo.jpg' . '">';
                    $html .= '</div>';
                    $html .= '<div class="sti-integration-notice--content">';
                        $html .= '<h2>Share This Image: ' . __( 'Integrations for your plugins', 'share-this-image' ) . '</h2>';
                        $html .= '<p>' . $notice_top_message. '</p>';
                        $html .= '<ul style="list-style:disc;padding-left:20px;margin:15px 0 18px;">' . $notice_message. '</ul>';
                        $html .= '<a href="https://share-this-image.com/pro/?utm_source=wp-plugin&utm_medium=integration_notice&utm_campaign=all_pro" target="_blank" class="button button-primary">' . __( 'All PRO Features', 'share-this-image' ) . '</a>&nbsp;&nbsp;<a href="https://share-this-image.com/pricing/?utm_source=wp-plugin&utm_medium=integration_notice&utm_campaign=pricing" target="_blank" class="button button-primary">' . __( 'View Pricing', 'share-this-image' ) . '</a>';
                        $html .= '<div style="margin-bottom:15px;"></div>';
                        $html .= '<a href="' . $dismiss_link . $notice_id . '" title="' . __( 'Dismiss', 'share-this-image'  ) . '" style="color:#787c82;text-decoration:none;font-size:16px;position:absolute;top:0;right:1px;border:none;margin:0;padding:9px;background:0 0;cursor:pointer;"><span style="font-size:16px;" class="dashicons dashicons-dismiss"></span></a>';
                    $html .= '</div>';
                $html .= '</div>';

                echo $html;

            }

        }

        /*
         * Add admin notice
         */
        public function admin_notices_local() {

            if ( get_option( 'sti-notice-dismiss-local-notice' ) ) {
                return;
            }

            if ( isset( $_GET['page'] ) && $_GET['page'] === 'sti-options' ) { ?>
                <div data-sti-notice="local-notice" class="notice notice-info is-dismissible">
                    <p><?php _e('<strong>Remember:</strong> Plugin won\'t scrap any data if you are using it on your <strong>local server</strong> or if your site has disabled <strong>search engine indexing</strong>.', 'share-this-image'); ?></p>

                    <p><?php _e('Please test the <strong>Share This Image</strong> plugin on a publicly available site.', 'share-this-image'); ?></p>
                </div>
            <?php }

        }

        /*
         * Add welcome notice
         */
        public function display_welcome_header() {

            if ( ! isset( $_GET['page'] ) || $_GET['page'] !== 'sti-options' ) {
                return;
            }

            $hide_notice = get_option( 'sti_hide_welcome_notice' );

            if ( !! $hide_notice || $hide_notice === 'true' ) {
                return;
            }

            echo STI_Admin_Meta_Boxes::get_welcome_notice();

        }

        /*
         * Check plugin activation time: show notices only who use the plugin more than 7 days
         */
        public function check_activation_time() {

            $activation_time = get_option( 'sti_activation_time' );
            $show_notices = false;

            if ( ! $activation_time ) {
                update_option( 'sti_activation_time', time(), 'no' );
            } else {
                $time_pass = time() - $activation_time;
                $days_pass = (int) round((($time_pass/24)/60)/60);
                if ( $days_pass && $days_pass > 7 ) {
                    $show_notices = true;
                }
            }

            return $show_notices;

        }

        /*
         * Hide admin integration notices
         */
        public function hide_notices() {

            if ( isset( $_GET['sti_hide_int_notices'] ) && $_GET['sti_hide_int_notices'] ) {
                $option = strpos( $_GET['sti_hide_int_notices'], '|' ) !== false ? explode('|', $_GET['sti_hide_int_notices'] ) : array( $_GET['sti_hide_int_notices'] );
                $option_current = get_option( 'sti_hide_int_notices' );
                $option = $option_current ? array_merge( $option_current, $option ) : $option;
                update_option( 'sti_hide_int_notices', $option, false );
            }

        }

    }

endif;


add_action( 'init', 'STI_Admin_Notices::instance' );