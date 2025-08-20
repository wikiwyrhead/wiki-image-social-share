<?php
/**
 * STI integrations
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'STI_Integrations' ) ) :

    /**
     * Class for main plugin functions
    */
    class STI_Integrations {

        /**
         * @var STI_Integrations The single instance of the class
         */
        protected static $_instance = null;

        /**
         * @var STI_Integrations Current theme name
         */
        public $current_theme = '';

        /**
         * Main STI_Integrations Instance
         *
         * Ensures only one instance of STI_Integrations is loaded or can be loaded.
         *
         * @static
         * @return STI_Integrations - Main instance
         */
        public static function instance() {
            if ( is_null( self::$_instance ) ) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }
        
        /**
         * Setup actions and filters for all things settings
         */
        public function __construct() {

            $theme = function_exists( 'wp_get_theme' ) ? wp_get_theme() : false;

            if ( $theme ) {
                $this->current_theme = $theme->get( 'Name' );
                if ( $theme->parent() ) {
                    $this->current_theme = $theme->parent()->get( 'Name' );
                }
            }

            $this->includes();

            // Photo Gallery plugin
            if ( class_exists( 'BWG' ) ) {
                add_action( 'wp_enqueue_scripts', array( $this, 'bwg_wp_enqueue_scripts' ), 9999999 );
            }

            // SimpLy Gallery Block & Lightbox plugin
            if ( defined('PGC_SGB_VERSION') ) {
                add_action( 'wp_head', array( $this, 'pgc_wp_head' ) );
                add_action( 'wp_enqueue_scripts', array( $this, 'pgc_wp_enqueue_scripts' ), 9999999 );
            }

            // Simple Lightbox plugin
            if ( function_exists( 'slb_init' ) ) {
                add_action( 'wp_head', array( $this, 'slb_wp_head' ) );
            }

            // Envira gallery plugin
            if ( class_exists( 'Envira_Gallery' ) || class_exists( 'Envira_Gallery_Lite' ) ) {
                add_action( 'wp_enqueue_scripts', array( $this, 'envira_wp_enqueue_scripts' ), 9999999 );
            }

            // WooThumbs for WooCommerce by Iconic plugin
            if ( class_exists( 'Iconic_WooThumbs' ) ) {
                add_action( 'wp_enqueue_scripts', array( $this, 'iconic_woothumbs_wp_enqueue_scripts' ), 9999999 );
            }

            // NextGEN Gallery
            if ( defined('NGG_PLUGIN') ) {
                add_action( 'wp_enqueue_scripts', array( $this, 'nextgen_wp_enqueue_scripts' ), 9999999 );
            }

            // Elementor
            if ( defined( 'ELEMENTOR_VERSION' ) || defined( 'ELEMENTOR_PRO_VERSION' ) ) {
                add_filter( 'sti_generated_selectors', array( $this, 'elementor_sti_generated_selectors' ), 1 );
            }

            // Spectra
            if ( defined('UAGB_FILE') ) {
                add_filter( 'sti_generated_selectors', array( $this, 'spectra_sti_generated_selectors' ), 1 );
                add_action( 'wp_enqueue_scripts', array( $this, 'spectra_wp_enqueue_scripts' ), 9999999 );
            }

            // OceanWP theme
            if ( 'OceanWP' === $this->current_theme ) {
                add_filter( 'sti_generated_selectors', array( $this, 'oceanwp_sti_generated_selectors' ), 1 );
            }

            // Avada theme
            if ( 'Avada' === $this->current_theme ) {
                add_filter( 'sti_generated_selectors', array( $this, 'avada_sti_generated_selectors' ), 1 );
            }

            // SEOPress, on-site SEO
            add_filter('option_seopress_social_option_name', array( $this, 'option_seopress_social_option_name' ), 10, 2);

        }

        /**
         * Include files
         */
        public function includes() {

            // Gutenberg block
            if ( function_exists( 'register_block_type' ) ) {
                include_once( STI_DIR . '/includes/modules/gutenberg/class-sti-gutenberg-init.php' );
            }

            // Metaslider plugin
            if ( class_exists( 'MetaSliderPlugin' ) ) {
                include_once( STI_DIR . '/includes/modules/class-sti-metaslider.php' );
            }

        }

        /*
         * Photo Gallery plugin
         */
        public function bwg_wp_enqueue_scripts() {

            $script = "
                document.addEventListener('stiLoaded', function() {
                
                    function bwg_sti_share_container( el ) {
                      if ( el.closest('.bwg-item').length > 0 ) {
                          el = false;
                      }
                      return el;
                    }
                    StiHooks.add_filter( 'sti_share_container', bwg_sti_share_container );
                  
                    var timeoutID;
                      jQuery('body').on('DOMSubtreeModified', '#spider_popup_wrap', function() {
                        window.clearTimeout(timeoutID);
                        timeoutID = window.setTimeout( function() {
                            jQuery('.bwg_popup_image').sti( { 'position' : 'image' } );
                        }, 1000 );
                    });
                
                }, false);
            ";

            wp_add_inline_script( 'sti-script', $script);

        }

        /*
         * SimpLy Gallery plugin: custom styles
         */
        public function pgc_wp_head() {

            echo '<style>
            .pgc-rev-lb-b-view.pgc-rev-lb-b-activate {
                z-index: 2147483 !important;
            }
            </style>';

        }

        /*
         * SimpLy Gallery plugin: custom js
         */
        public function pgc_wp_enqueue_scripts() {

            $script = "
                document.addEventListener('stiLoaded', function() {
                    var timeoutID;
                    jQuery(document).on( 'click', '.action-lightbox', function() {                  
                        timeoutID = window.setTimeout( function() {
                            jQuery('.pgc-rev-lb-b-activate img').sti();
                        }, 1000 );
                    } );
                }, false);
            ";

            wp_add_inline_script( 'sti-script', $script);

        }

        /*
         * Simple Lightbox plugin: fix styles inside lightbox
         */
        public function slb_wp_head() {

            $css_file_url = STI_URL . '/assets/css/sti.css';
            $css_file_dir = STI_DIR . '/assets/css/sti.css';

            $css_styles = file_get_contents( $css_file_dir );

            $css_styles = str_replace('.sti ', '#slb_viewer_wrap .slb_theme_slb_baseline .sti ', $css_styles );
            $css_styles = str_replace('.sti.', '#slb_viewer_wrap .slb_theme_slb_baseline .sti.', $css_styles );
            $css_styles = str_replace('.sti-mobile-btn', '#slb_viewer_wrap .slb_theme_slb_baseline .sti-mobile-btn', $css_styles );

            $css_styles = '#slb_viewer_wrap .slb_theme_slb_baseline .sti { width: auto !important; height: auto  !important; }' . $css_styles;
            $css_styles = '#slb_viewer_wrap .slb_theme_slb_baseline .sti-mobile-btn { width: 36px !important; height: 36px !important; }' . $css_styles;
            $css_styles = '.sti.sti-top.sti-mobile { z-index: 999999; }' . $css_styles;

            echo '<style>' . $css_styles . '</style>';

        }

        /*
         * Envira gallery: relayout sharingbuutons on images load
         */
        public function envira_wp_enqueue_scripts() {

            $script = "
                document.addEventListener('stiLoaded', function() {
                
                     var mylazyTimeoutID;
                     jQuery( document ).on( 'envira_image_lazy_load_complete', function( event ) {
                        window.clearTimeout(mylazyTimeoutID);
                        mylazyTimeoutID = window.setTimeout( function() {
                            jQuery('.envira-gallery-wrap img').sti('relayout');
                        }, 100 );
                     });
                            
                }, false);
            ";

            wp_add_inline_script( 'sti-script', $script);

        }

        /*
         * WooThumbs for WooCommerce by Iconic plugin: fix layout for galleries
         */
        public function iconic_woothumbs_wp_enqueue_scripts() {

            $script = "
                document.addEventListener('stiLoaded', function() {
                    function iconic_woothumbs_sti_sharing_box_layout( styles, options ) {
                      if ( options.opts.position === 'image_hover' ) {
                         var thumbsGallery = options.el.closest('.iconic-woothumbs-images-wrap');
                         var zoomControl = options.el.closest('.zm-viewer');
                         var bodyBts = jQuery('body > ' + options.box);
                         if ( thumbsGallery.length > 0 || zoomControl.length > 0 ) {
                             jQuery(options.box).appendTo(thumbsGallery);
                             styles.top = 0;
                             styles.left = 0;
                         } else if( bodyBts.length <= 0 ) {
                             jQuery(options.box).appendTo('body');
                         }
                      }
                      return styles;
                    }
                    StiHooks.add_filter( 'sti_sharing_box_layout', iconic_woothumbs_sti_sharing_box_layout );
                }, false);
            ";

            wp_add_inline_script( 'sti-script', $script);

        }

        /*
         * NextGEN Gallery plugin
         */
        public function nextgen_wp_enqueue_scripts() {

            $script = "
                document.addEventListener('stiLoaded', function() {
                    function nexten_sti_media( media, e, network ) {
                        let thumbContainer = e.closest('[data-src]');
                        if ( thumbContainer.length > 0 ) {
                            media = thumbContainer.data('src');
                        }
                        return media;
                    }
                    function nexten_sti_sharing_box_layout( styles, options ) {
                        if ( options.el.closest('.sl-image, .sl-wrapper').length > 0 ) {
                            styles.zIndex = 99999;
                        }
                        return styles;
                    }
                    StiHooks.add_filter( 'sti_media', nexten_sti_media );
                    StiHooks.add_filter( 'sti_sharing_box_layout', nexten_sti_sharing_box_layout );
                }, false);
            ";

            wp_add_inline_script( 'sti-script', $script);

        }

        /*
         * New image selectors for Elemetor gallery widgets
         */
        public function elementor_sti_generated_selectors( $selectors_arr ) {

            $new_selectors = array();

            if ( ! empty( $selectors_arr ) ) {
                foreach ( $selectors_arr as $selector ) {
                    if ( STI_Helpers::str_ends_with( $selector, 'img') ) {
                        $new_selectors[] = '.elementor-widget-gallery .elementor-gallery-item';
                        $new_selectors[] = '.elementor-carousel-image';
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
        * Spectra: New image selectors for gallery widgets
        */
        public function spectra_sti_generated_selectors( $selectors_arr ) {

            $new_selectors = array();

            if ( ! empty( $selectors_arr ) ) {
                foreach ( $selectors_arr as $selector ) {
                    if ( STI_Helpers::str_ends_with( $selector, 'img') ) {
                        $new_selectors[] = '.spectra-image-gallery .spectra-image-gallery__media';
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
        * OceanWP: New image selectors for gallery widgets
        */
        public function oceanwp_sti_generated_selectors( $selectors_arr ) {

            $new_selectors = array();

            if ( ! empty( $selectors_arr ) ) {
                foreach ( $selectors_arr as $selector ) {
                    if ( STI_Helpers::str_ends_with( $selector, 'img') ) {
                        $new_selectors[] = '.ogb-banner';
                        $new_selectors[] = '.ogb-grid-media';
                        $new_selectors[] = '.ogb-list-media';
                        $new_selectors[] = '.portfolio-entry-thumbnail';
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
         * Avada: New image selectors for builder elements
         */
        public function avada_sti_generated_selectors( $selectors_arr ) {

            $new_selectors = array();

            if ( ! empty( $selectors_arr ) ) {
                foreach ( $selectors_arr as $selector ) {
                    if ( STI_Helpers::str_ends_with( $selector, 'img') ) {
                        $new_selectors[] = '.fusion-image-wrapper';
                        $new_selectors[] = 'img:not(.fusion-image-wrapper img)';
                        $new_selectors[] = '.fusion-slider-container .background-image';
                        break;
                    }
                }
            }

            unset( $selectors_arr[array_search('img', $selectors_arr)] );

            if ( ! empty( $new_selectors ) ) {
                $selectors_arr = array_merge( $selectors_arr, $new_selectors );
            }

            return $selectors_arr;

        }

        /*
         * Spectra: Update sharing buttons styles
         */
        public function spectra_wp_enqueue_scripts() {

            $script = "
                document.addEventListener('stiLoaded', function() {
                    function nexten_sti_sharing_box_layout( styles, options ) {
                        if ( options.el.closest('.swiper-wrapper').length > 0 ) {
                            styles.zIndex = 999999999;
                        }
                        return styles;
                    }
                    StiHooks.add_filter( 'sti_sharing_box_layout', nexten_sti_sharing_box_layout );
                }, false);
            ";

            wp_add_inline_script( 'sti-script', $script);

        }

        /*
         * SEOPress, on-site SEO
         */
        public function option_seopress_social_option_name( $value, $option ) {
            if ( isset( $_GET['img'] ) ) {
                $value['seopress_social_facebook_og'] = '0';
                $value['seopress_social_twitter_card'] = '0';
            }
            return $value;
        }

        /*
         * Register plugin settings
         */
        public function get_settings( $id = false ) {
            $sti_options = get_option( 'sti_settings' );
            if ( $id ) {
                return $sti_options[ $id ];
            } else {
                return $sti_options;
            }
        }

    }

endif;