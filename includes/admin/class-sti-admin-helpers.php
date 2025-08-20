<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}


if ( ! class_exists( 'STI_Admin_Helpers' ) ) :

    /**
     * Class for plugin help methods
     */
    class STI_Admin_Helpers {

        /**
         * Get array of allowed tags for wp_kses function
         * @param array $allowed_tags Tags that is allowed to display
         * @return array $tags
         */
        static public function get_kses( $allowed_tags = array() ) {

            $tags = array(
                'a' => array(
                    'href' => array(),
                    'title' => array()
                ),
                'br' => array(),
                'em' => array(),
                'strong' => array(),
                'b' => array(),
                'code' => array(),
                'blockquote' => array(
                    'cite' => array(),
                ),
                'p' => array(),
                'i' => array(),
                'h1' => array(),
                'h2' => array(),
                'h3' => array(),
                'h4' => array(),
                'h5' => array(),
                'h6' => array(),
                'img' => array(
                    'alt' => array(),
                    'src' => array()
                )
            );

            if ( is_array( $allowed_tags ) && ! empty( $allowed_tags ) ) {
                foreach ( $tags as $tag => $tag_arr ) {
                    if ( array_search( $tag, $allowed_tags ) === false ) {
                        unset( $tags[$tag] );
                    }
                }

            }

            return $tags;

        }

        /*
         * Get available post types
         * @return array
         */
        static public function get_post_types() {

            $options = array();

            $args = array(
                'public' => true,
            );
            $post_types = get_post_types( $args, 'object' );

            if ( $post_types && ! empty( $post_types ) ) {
                foreach( $post_types as $post_type_name => $post_type ) {

                    if ( in_array( $post_type->name, array( 'attachment' ) ) ) {
                        continue;
                    }

                    $options[] = array(
                        'name'  => $post_type->label,
                        'value' => $post_type->name
                    );

                }
            }

            return $options;

        }

        /*
         * Get post type items
         * @param $name string Post type name
         * @return array
         */
        static public function get_posts( $name = false ) {

            if ( ! $name ) {
                return false;
            }

            $options = array();

            $args = array(
                'post_type'   => $name,
                'numberposts' => -1,
                'post_status' => 'any'
            );

            $posts = get_posts( $args );

            if ( ! empty( $posts ) ) {
                foreach ( $posts as $post ) {
                    $title = $post->post_title ? $post->post_title :  __( "(no title)", "share-this-image" ) . ' (ID = ' . $post->ID . ')';
                    $options[$post->ID] = $title;
                }
            }

            return $options;

        }

        /*
         * Get available post taxonomies
         * @param $name string Post type name
         * @return array
         */
        static public function get_post_taxonomies( $name = false ) {

            if ( ! $name ) {
                return false;
            }

            $options = array();
            $taxonomy_objects = get_object_taxonomies( $name, 'objects' );

            foreach( $taxonomy_objects as $taxonomy_object ) {

                $options[] = array(
                    'name'  => $taxonomy_object->label,
                    'value' => $taxonomy_object->name
                );

            }

            return $options;

        }

        /*
         * Get available images
         * @return array
         */
        static public function get_images() {

            $options = array();

            $args = array(
                'post_type'      => 'attachment',
                'numberposts'    => -1,
                'post_status'    => 'any',
                'post_mime_type' => 'image',
            );

            $images = get_posts( $args );

            $options['sti_any'] = __( "Any", "share-this-image" );

            if ( ! empty( $images ) ) {
                foreach ( $images as $image ) {
                    $options[$image->ID] = $image->post_title;
                }
            }

            return $options;

        }

        /*
         * Get available image formats
         * @return array
         */
        static public function get_image_formats() {

            $options = array();

            $values = array(
                'jpeg' => 'JPEG',
                'gif'  => 'GIF',
                'png'  => 'PNG',
                'svg'  => 'SVG',
                'webp' => 'WebP',
                'tiff' => 'TIFF',
                'bmp'  => 'Bitmap',
                'eps'  => 'EPS',
                'heif' => 'HEIF',
            );

            $options['sti_any'] = __( "Any", "share-this-image" );

            foreach ( $values as $value_val => $value_name ) {
                $options[$value_val] = $value_name;
            }

            return $options;

        }

        /*
         * Get available taxonomies_terms
         * @param $name string Tax name
         * @return array
         */
        static public function get_tax_terms( $name = false ) {

            if ( ! $name ) {
                return false;
            }

            $tax = get_terms( array(
                'taxonomy'   => $name,
                'hide_empty' => false,
            ) );

            $options = array();

            if ( $name && $name === 'product_shipping_class' ) {
                $options['none'] = __( "No shipping class", "share-this-image" );
            }

            if ( ! empty( $tax ) ) {
                foreach ( $tax as $tax_item ) {
                    $options[$tax_item->term_id] = $tax_item->name;
                }
            }

            return $options;

        }

        /*
         * Get all available pages
         * @return array
         */
        static public function get_pages() {

            $pages = get_pages( array( 'parent' => 0, 'hierarchical' => 0 ) );
            $options = array();

            $options['sti_any'] = __( "Any", "share-this-image" );

            if ( $pages && ! empty( $pages ) ) {

                foreach( $pages as $page ) {

                    $title = $page->post_title ? $page->post_title :  __( "(no title)", "share-this-image" );

                    $options[$page->ID] = $title;

                    $child_pages = get_pages( array( 'child_of' => $page->ID ) );

                    if ( $child_pages && ! empty( $child_pages ) ) {

                        foreach( $child_pages as $child_page ) {

                            $page_prefix = '';
                            $parents_number = sizeof( $child_page->ancestors );

                            if ( $parents_number && is_int( $parents_number ) ) {
                                $page_prefix = str_repeat( "-", $parents_number );
                            }

                            $title = $child_page->post_title ? $child_page->post_title :  __( "(no title)", "share-this-image" );
                            $title = $page_prefix . $title;

                            $options[$child_page->ID] = $title;

                        }

                    }

                }

            }

            return $options;

        }

        /*
         * Get all available page templates
         * @return array
         */
        static public function get_page_templates() {

            $page_templates = get_page_templates();
            $options = array();

            $options['default'] = __( 'Default template', 'share-this-image' );

            if ( $page_templates && ! empty( $page_templates ) ) {
                foreach( $page_templates as $page_template_name => $page_template_file ) {
                    $options[] = array(
                        'name'  => $page_template_name,
                        'value' => $page_template_file
                    );
                }
            }

            return $options;

        }

        /*
         * Get available pages types
         * @return array
         */
        static public function get_page_type() {

            $options = array();

            $types = array(
                'front' => __( 'Front page', 'share-this-image' ),
                'posts' => __( 'Posts page', 'share-this-image' ),
                'search' => __( 'Search results page', 'share-this-image' ),
                'archive' => __( 'Archive page', 'share-this-image' ),
                'tax_page' => __( 'Custom taxonomy archive page', 'share-this-image' ),
                'page' => __( 'Simple page', 'share-this-image' ),
                'singular' => __( 'Singular page', 'share-this-image' ),
                '404' => __( '404 error page', 'share-this-image' ),
            );

            foreach( $types as $type_slug => $type_name ) {
                $options[$type_slug] = $type_name;
            }

            return $options;

        }

        /*
         * Get available archive pages
         * @return array
         */
        static public function get_page_archives() {

            $options = array();

            $args = array(
                'public' => true,
            );
            $post_types = get_post_types( $args, 'object' );

            if ( $post_types && ! empty( $post_types ) ) {
                foreach( $post_types as $post_type_name => $post_type ) {

                    if ( in_array( $post_type->name, array( 'attachment', 'page' ) ) ) {
                        continue;
                    }

                    $taxonomies = get_object_taxonomies( $post_type->name, 'objects' );

                    if ( $taxonomies && ! empty( $taxonomies) ) {
                        foreach ( $taxonomies as $tax ) {
                            if ( ( property_exists( $tax, 'has_archive' ) && $tax->has_archive ) || $tax->public || $tax->publicly_queryable ) {
                                $options[] = array(
                                    'name'  => $tax->label,
                                    'value' => $tax->name
                                );
                            }
                        }
                    }

                }
            }

            return $options;

        }

        /*
         * Get available archive pages terms
         * @return array
         */
        static public function get_page_archive_terms( $name = false ) {

            if ( ! $name ) {
                return false;
            }

            $options = array();

            switch( $name ) {

                case 'attributes':

                    if ( function_exists( 'wc_get_attribute_taxonomies' ) ) {
                        $attributes = wc_get_attribute_taxonomies();

                        if ( $attributes && ! empty( $attributes ) ) {
                            foreach( $attributes as $attribute ) {
                                if ( $attribute->attribute_public ) {

                                    $options[] = array(
                                        'name'  => $attribute->attribute_label,
                                        'value' => wc_attribute_taxonomy_name( $attribute->attribute_name )
                                    );

                                }
                            }
                        }

                    }

                    break;

                default:

                    $options = STI_Admin_Helpers::get_tax_terms( $name );

            }

            return $options;

        }

        /*
         * Check for incorrect display conditions and return them
         * @return string
         */
        static public function check_for_incorrect_display_rules( $rules ) {

            $incorrect_rules_string = '';
            $check_rules = array( 'image', 'image_url', 'post', 'page', 'page_id', 'page_url', 'page_template' );

            if ( $rules && ! empty( $rules )  ) {

                foreach ( $rules as $cond_group ) {

                    $maybe_wrong_rules = array();

                    foreach ( $cond_group as $cond_rule ) {
                        $rule_name = $cond_rule['param'];
                        if ( array_search( $rule_name, $check_rules ) !== false && $cond_rule['operator'] === 'equal' ) {
                            $maybe_wrong_rules[$rule_name][] = $cond_rule;
                        }
                        if ( isset( $maybe_wrong_rules[$rule_name] ) && count( $maybe_wrong_rules[$rule_name] ) > 1 ) {
                            foreach ( $maybe_wrong_rules[$rule_name] as $rule ) {
                                $rule_value = isset( $rule['value']  ) ? $rule['value'] : '';
                                $incorrect_rules_string .= $rule['param'] . ' -> ' . 'equal to' . ' -> ' . $rule_value .  '<br>';
                            }
                            break;
                        }
                    }

                    if ( $incorrect_rules_string ) {
                        break;
                    }

                }

            }

            return $incorrect_rules_string;

        }

    }

endif;