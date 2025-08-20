<?php

/**
 * STI plugin gutenberg integrations init
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (!class_exists('STI_Gutenberg_Init')) :

    /**
     * Class for main plugin functions
     */
    class STI_Gutenberg_Init {

        /**
         * @var STI_Gutenberg_Init The single instance of the class
         */
        protected static $_instance = null;

        /**
         * Main STI_Gutenberg_Init Instance
         *
         * Ensures only one instance of STI_Gutenberg_Init is loaded or can be loaded.
         *
         * @static
         * @return STI_Gutenberg_Init - Main instance
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

            add_action( 'init', array( $this, 'register_block' ) );

            if ( version_compare( get_bloginfo('version'),'5.8', '>=' ) ) {
                add_filter( 'block_categories_all', array( $this, 'add_block_category' ) );
            } else {
                add_filter( 'block_categories', array( $this, 'add_block_category' ) );
            }

            add_action( 'init', array( $this, 'set_script_translations' ) );

        }

        /*
         * Register gutenberg blocks
         */
        public function register_block() {

            global $pagenow;

            $scripts = array( 'wp-blocks', 'wp-editor', 'wp-i18n' );
            if ( $pagenow && $pagenow === 'widgets.php' && version_compare( get_bloginfo('version'),'5.8', '>=' ) ) {
                $scripts = array( 'wp-blocks', 'wp-edit-widgets', 'wp-i18n' );
            }

            $options_array = STI_Admin_Options::options_array();
            $available_buttons = array();

            if ( $options_array ) {
                foreach( $options_array as $option_tab ) {
                    foreach ( $option_tab as $option_array ) {
                        if ( isset( $option_array['id'] ) && $option_array['id'] === 'buttons' && isset( $option_array['choices'] ) ) {
                            foreach ($option_array['choices'] as $choices_key => $choices_val) {
                                $available_buttons[] = $choices_key;
                            }
                        }
                    }
                }
            }

            wp_register_script(
                'sti-gutenberg-buttons',
                STI_URL . '/includes/modules/gutenberg/sti-gutenberg-buttons.js',
                $scripts,
                STI_VER
            );

            wp_register_style(
                'sti-gutenberg-styles-editor',
                STI_URL . '/assets/css/sti.css',
                array( 'wp-edit-blocks' ),
                STI_VER
            );

            register_block_type( 'share-this-image/sharing-buttons', array(
                'apiVersion' => 2,
                'editor_script' => 'sti-gutenberg-buttons',
                'editor_style' => 'sti-gutenberg-styles-editor',
                'render_callback' => array( $this, 'sharing_buttons_dynamic_render_callback' ),
                'attributes'      =>  array(
                    'available_buttons'   =>  array(
                        'type'    => 'string',
                        'default' => implode( ', ', $available_buttons )
                    ),
                    'buttons'   =>  array(
                        'type'    => 'string',
                        'default' => 'facebook, twitter, linkedin',
                    ),
                    'image'   =>  array(
                        'type'    => 'string',
                        'default' => ''
                    ),
                    'title'   =>  array(
                        'type'    => 'string',
                        'default' => ''
                    ),
                    'description'   =>  array(
                        'type'    => 'string',
                        'default' => ''
                    ),
                    'url'   =>  array(
                        'type'    => 'string',
                        'default' => ''
                    ),
                    'alignment' => array(
                        'type'    => 'string',
                        'default' => 'none',
                    ),
                ),
            ) );

        }

        /*
         * Render dynamic content
         */
        public function sharing_buttons_dynamic_render_callback( $block_attributes, $content ) {

            $shortcode = '';
            $available_params = array( 'buttons', 'image', 'title', 'description', 'url' );

            if ( $block_attributes ) {
                foreach ( $block_attributes as $block_attributes_name => $block_attributes_val ) {
                    if ( gettype( $block_attributes_val ) === 'string' && array_search( $block_attributes_name, $available_params ) !== false ) {
                        $shortcode .= $block_attributes_name . '="' . htmlspecialchars( $block_attributes_val ) . '" ';
                    }
                }
            }

            $shortcode = '[sti_buttons ' . $shortcode . ']';

            $buttons = do_shortcode( $shortcode );

            if ( isset( $block_attributes['alignment'] ) && $block_attributes['alignment'] !== 'none' ) {
                $buttons = '<div class="sti-align-' . htmlspecialchars( $block_attributes['alignment'] ) . '">' . $buttons . '</div>';
            }

            return $buttons;

        }

        /*
         * Add new blocks category
         */
        public function add_block_category( $categories ) {
            if ( is_array( $categories ) ) {
                $categories = array_merge(
                    $categories,
                    array(
                        array(
                            'slug'  => 'sti',
                            'title' => 'Share This Image',
                            'icon'  => 'search',
                        ),
                    )
                );
            }
            return $categories;
        }

        /*
         * Set translations script
         */
        public function set_script_translations() {
            wp_set_script_translations( 'sti-gutenberg-buttons', 'share-this-image' );
        }

    }


endif;

STI_Gutenberg_Init::instance();