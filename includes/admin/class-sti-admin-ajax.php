<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'STI_Admin_Ajax' ) ) :

    /**
     * Class for plugin admin ajax hooks
     */
    class STI_Admin_Ajax {

        /*
         * Constructor
         */
        public function __construct() {

            add_action( 'wp_ajax_sti-dismissNotice', array( $this, 'dismiss_notice' ) );

            add_action( 'wp_ajax_sti-hideWelcomeNotice', array( $this, 'hide_welcome_notice' ) );

            add_action( 'wp_ajax_sti-getRuleGroup', array( $this, 'get_rule_group' ) );

            add_action( 'wp_ajax_sti-getSuboptionValues', array( $this, 'get_suboption_values' ) );

        }

        /*
         * Ajax hook for form renaming
         */
        public function dismiss_notice() {

            check_ajax_referer( 'ajax_nonce' );

            $notice_name = sanitize_text_field( $_POST['notice'] );

            update_option( 'sti-notice-dismiss-' . $notice_name, '1' );

            die;

        }

        /*
         * Hide plugin welcome notice
         */
        public function hide_welcome_notice() {

            check_ajax_referer( 'ajax_nonce' );

            update_option( 'sti_hide_welcome_notice', 'true', false );

            wp_send_json_success( '1' );

        }

        /*
        * Ajax hook for rule groups
        */
        public function get_rule_group() {

            check_ajax_referer( 'ajax_nonce' );

            $name = sanitize_text_field( $_POST['name'] );
            $group_id = sanitize_text_field( $_POST['groupID'] );
            $rule_id = sanitize_text_field( $_POST['ruleID'] );

            $rules = STI_Admin_Options::include_rules();
            $html = array();

            foreach ( $rules as $rule_section => $section_rules ) {
                foreach ( $section_rules as $rule ) {
                    if ( $rule['id'] === $name ) {

                        $rule_obj = new STI_Admin_Display_Rules( $rule, $group_id, $rule_id );

                        $html['aoperators'] = $rule_obj->get_field( 'operator' );

                        if ( isset( $rule['suboption'] ) ) {
                            $html['asuboptions'] = $rule_obj->get_field( 'suboption' );
                        }

                        $html['avalues'] = $rule_obj->get_field( 'value' );

                        break;

                    }
                }
            }

            wp_send_json_success( $html );

        }

        /*
         * Ajax hook for suboption values
         */
        public function get_suboption_values() {

            check_ajax_referer( 'ajax_nonce' );

            $param = sanitize_text_field( $_POST['param'] );
            $suboption = sanitize_text_field( $_POST['suboption'] );
            $group_id = sanitize_text_field( $_POST['groupID'] );
            $rule_id = sanitize_text_field( $_POST['ruleID'] );

            $rules = STI_Admin_Options::include_rules();
            $html = array();

            foreach ( $rules as $rule_section => $section_rules ) {
                foreach ( $section_rules as $rule ) {
                    if ( $rule['id'] === $param ) {

                        $rule['choices']['params'] = array( $suboption );

                        $rule_obj = new STI_Admin_Display_Rules( $rule, $group_id, $rule_id );

                        $html = $rule_obj->get_field( 'value' );

                        break;

                    }
                }
            }

            wp_send_json_success( $html );

        }

    }

endif;


new STI_Admin_Ajax();