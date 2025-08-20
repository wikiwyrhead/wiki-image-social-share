<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'STI_Conditions_Check' ) ) :

    /**
     * STI Conditions check class
     */
    class STI_Conditions_Check {

        protected $conditions = null;
        protected $rule = null;
        protected $matched_conditions = null;


        /*
         * Constructor
         */
        public function __construct( $conditions ) {

            $this->conditions = $conditions;

        }


        /*
         * Get conditions array that was matched
         */
        public function get_matched_conditions() {
            return $this->matched_conditions;
        }


        /*
         * Match condition
         */
        public function match() {

            if ( empty( $this->conditions ) || ! is_array( $this->conditions ) ) {
                return false;
            }

            /**
             * Filter condition functions
             * @since 1.60
             * @param array Array of custom condition functions
             */
            $custom_match_functions = apply_filters( 'sti_display_condition_rules', array() );

            $match = false;

            foreach ( $this->conditions as $condition_group ) {

                $rules_match = true;

                if ( $condition_group && ! empty( $condition_group ) ) {

                    foreach( $condition_group as $condition_rule ) {

                        $this->rule = $condition_rule;
                        $condition_name = $condition_rule['param'];

                        if ( isset( $custom_match_functions[$condition_name] ) ) {
                            $match_rule = call_user_func( $custom_match_functions[$condition_name], $condition_rule );
                        } elseif ( method_exists( $this, 'match_' . $condition_name ) ) {
                            $match_rule = call_user_func( array( $this, 'match_' . $condition_name ) );
                        } else {
                            $match_rule = true;
                        }

                        if ( ! $match_rule ) {
                            $rules_match = false;
                            break;
                        }

                    }

                }

                if ( $rules_match ) {
                    $this->matched_conditions[] = $condition_group;
                    $match = true;
                }

            }


            return $match;

        }


        /*
         * Compare values
         * @param $value
         * @return bool
         */
        private function compare_values( $compare_value ) {

            /**
             * Filter condition value before compare
             * @since 1.60
             * @param string|integer $compare_value Value to compare with
             * @param array $this->rule Condition parameters
             */
            $compare_value = apply_filters( 'sti_display_condition_compare_value', $compare_value, $this->rule );

            $match = false;
            $value = $this->rule['value'];
            $operator = $this->rule['operator'];

            if ( is_bool( $compare_value )  ) {
                $compare_value = $compare_value ? 'true' : 'false';
            }

            if ( 'equal' == $operator ) {
                $match = ($compare_value == $value);
            } elseif ( 'not_equal' == $operator ) {
                $match = ($compare_value != $value);
            } elseif ( 'contains' == $operator ) {
                $match = strpos( $compare_value, $value ) !== false;
            } elseif ( 'not_contains' == $operator ) {
                $match = strpos( $compare_value, $value ) === false;
            }

            return $match;

        }

        /*
         * Post rule
         */
        public function match_post() {

            global $wp_query;

            $post_type = $this->rule['suboption'];
            $post_id = $this->rule['value'] === 'sti_any' ? '' : $this->rule['value'];

            $current_post_id = $wp_query->get_queried_object_id();

            if ( $post_id ) {
                $value = $post_id == $current_post_id;
            } else {
                $value = is_singular( $post_type );
            }

            if ( 'equal' == $this->rule['operator'] ) {
                return $value;
            } else {
                return !$value;
            }

        }

        /*
         * Post type rule
         */
        public function match_post_type() {

            $value = is_singular() ? get_post_type() : false;

            return call_user_func_array( array( $this, 'compare_values' ), array( $value ) );

        }

        /*
         * Post status rule
         */
        public function match_post_status() {

            $value = is_singular() ? get_post_status() : false;

            return call_user_func_array( array( $this, 'compare_values' ), array( $value ) );

        }

        /*
         * Post taxonomy rule
         */
        public function match_post_taxonomy() {

            global $wp_query;

            $post_taxonomy = $this->rule['suboption'];
            $term_id = $this->rule['value'] === 'sti_any' ? '' : $this->rule['value'];
            $current_post_id = $wp_query->get_queried_object_id();

            $value = is_singular() ? has_term( $term_id, $post_taxonomy, $current_post_id ) : false;
            $operator = $this->rule['operator'];

            if ( 'equal' == $operator ) {
                return $value;
            } else {
                return !$value;
            }

        }

        /*
         * User rule
         */
        public function match_user() {

            if ( ! is_user_logged_in() ) {
                return false;
            }

            $value = get_current_user_id();

            return call_user_func_array( array( $this, 'compare_values' ), array( $value ) );

        }

        /*
         * User role rule
         */
        public function match_user_role() {

            $role = $this->rule['value'];

            if ( is_user_logged_in() ) {
                global $current_user;
                $roles = (array) $current_user->roles;
            } else {
                $roles = array( 'non-logged' );
            }

            $value = array_search( $role, $roles ) !== false;

            if ( 'equal' == $this->rule['operator'] ) {
                return $value;
            } else {
                return !$value;
            }

        }

        /*
         * User language rule
         */
        public function match_user_language() {

            $value = substr( $_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2 );

            return call_user_func_array( array( $this, 'compare_values' ), array( $value ) );

        }

        /*
         * User device rule
         */
        public function match_user_device() {

            $value = wp_is_mobile() ? 'mobile' : 'desktop';

            return call_user_func_array( array( $this, 'compare_values' ), array( $value ) );

        }

        /*
         * Page rule
         */
        public function match_page() {

            global $wp_query;

            $value = $this->rule['value'] === 'sti_any' ? 'sti_any' : $wp_query->get_queried_object_id();

            return call_user_func_array( array( $this, 'compare_values' ), array( $value ) );

        }

        /*
         * Page id rule
         */
        public function match_page_id() {

            global $wp_query;

            $value = $wp_query->get_queried_object_id();

            return call_user_func_array( array( $this, 'compare_values' ), array( $value ) );

        }

        /*
         * Page url rule
         */
        public function match_page_url() {

            $value = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

            return call_user_func_array( array( $this, 'compare_values' ), array( $value ) );

        }

        /*
         * Page template rule
         */
        public function match_page_template() {

            $value = is_page() ? get_page_template_slug( get_queried_object_id() ) : false;

            if ( $value === '' ) {
                $value = 'default';
            }

            return call_user_func_array( array( $this, 'compare_values' ), array( $value ) );

        }

        /*
         * Page type rule
         */
        public function match_page_type() {

            $queried_obj = get_queried_object();

            $page_type = array();

            if ( is_home() || is_front_page() ) {
                $page_type[] = 'front';
            }
            if ( is_home() ) {
                $page_type[] = 'posts';
            }
            if ( is_search() ) {
                $page_type[] = 'search';
            }
            if ( is_singular() ) {
                $page_type[] = 'singular';
            }
            if ( is_tax() ) {
                $page_type[] = 'tax_page';
            }
            if ( is_archive() ) {
                $page_type[] = 'archive';
            }
            if ( is_page() ) {
                $page_type[] = 'page';
            }
            if ( is_404() ) {
                $page_type[] = '404';
            }

            $value = in_array( $this->rule['value'], $page_type );

            if ( 'equal' == $this->rule['operator'] ) {
                return $value;
            } else {
                return !$value;
            }

        }

        /*
         * Page archives rule
         */
        public function match_page_archives() {

            $tax = $this->rule['suboption'];
            $term = $this->rule['value'] === 'sti_any' ? '' : $this->rule['value'];

            if ( 'attributes' === $tax ) {
                $queried_obj = get_queried_object();
                $value = $term ? is_tax( $term ) : ( is_tax() && function_exists( 'taxonomy_is_product_attribute' ) && taxonomy_is_product_attribute( $queried_obj->taxonomy ) );
            } elseif ( 'category' === $tax ) {
                $value = is_category( $term );
            } elseif ( 'post_tag' === $tax ) {
                $value = is_tag( $term );
            } else {
                $value = is_tax( $tax, $term );
            }

            if ( 'equal' == $this->rule['operator'] ) {
                return $value;
            } else {
                return !$value;
            }

        }

    }

endif;