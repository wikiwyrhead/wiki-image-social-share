<?php

/**
 * STI integration with Metaslider plugin
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('STI_Metaslider')) :

    /**
     * Class for main plugin functions
     */
    class STI_Metaslider {

        /**
         * @var STI_Metaslider The single instance of the class
         */
        protected static $_instance = null;

        protected $data = array();

        /**
         * Main STI_Metaslider Instance
         *
         * Ensures only one instance of STI_Metaslider is loaded or can be loaded.
         *
         * @static
         * @return STI_Metaslider - Main instance
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
        public function __construct() {

            // check diff buttins positions

            // get slider content to share. How to customize it ? ( PRO )

            add_filter( 'sti_display_rules', array( $this, 'sti_display_rules' ), 1 );

            add_filter( 'metaslider_flex_slider_parameters', array( $this, 'flex_slider_parameters' ) );

            add_filter( 'sti_generated_selectors', array( $this, 'sti_generated_selectors' ), 1 );

            add_filter( 'sti_generated_group_selector', array( $this, 'sti_generated_group_selector' ), 1, 2 );

        }

        public function sti_display_rules( $options ) {

            $options['Special'][] = array(
                "name" => __( "Metaslider: Is slider", "share-this-image" ),
                "id"   => "metaslider_is_slider",
                "type" => "bool",
                "operators" => "equals",
            );

            return $options;

        }

        /*
         * Metaslider flex slider integration
         */
        public function flex_slider_parameters( $options ) {
            $options['after'] = 'function(slider){ $("'. esc_html( stripslashes('img' ) ) .'").sti(); }';
            return $options;
        }

        /*
         * New image selectors for slider
         */
        public function sti_generated_selectors( $selectors_arr ) {

            $new_selectors = array();

            if ( ! empty( $selectors_arr ) ) {
                foreach ( $selectors_arr as $selector ) {
                    if ( STI_Helpers::str_ends_with( $selector, 'img') ) {
                        $new_selectors[] = '.metaslider .coin-slider';
                        break;
                    }
                }
            }

            if ( ! empty( $new_selectors ) ) {
                $selectors_arr = array_merge( $selectors_arr, $new_selectors );
            }

            return $selectors_arr;

        }

        /*
         * Update generated selector for group conditions
         */
        public function sti_generated_group_selector( $group_selector, $condition_group ) {

            if ( $condition_group ) {
                foreach ( $condition_group as $condition_rule ) {
                    if ( $condition_rule['param'] === 'metaslider_is_slider' ) {

                        $show_slider = true;

                        if ( $condition_rule['operator'] === 'not_equal' ) {
                            $show_slider = !($condition_rule['value'] === 'true');
                        }

                        if ( $condition_rule['operator'] === 'equal' ) {
                            $show_slider = $condition_rule['value'] === 'true';
                        }

                        if ( ! $show_slider ) {
                            $group_selector .= ':not(.metaslider img)';
                        }

                    }
                }
            }

            return $group_selector;

        }

    }

endif;

STI_Metaslider::instance();