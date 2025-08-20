<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'STI_Admin_Meta_Boxes' ) ) :

    /**
     * Class for plugin admin panel
     */
    class STI_Admin_Meta_Boxes {
        
        /*
         * Get content for the welcome notice
         * @return string
         */
        static public function get_welcome_notice() {

            $html = '';

            $html .= '<div id="sti-welcome-panel">';
                $html .= '<div class="sti-welcome-notice updated notice is-dismissible" style="background:#f2fbff;">';

                    $html .= '<div class="sti-welcome-panel">';
                        $html .= '<div class="sti-welcome-panel-content">';
                            $html .= '<h2>' . sprintf( __( 'Welcome to %s', 'share-this-image' ), 'Share This Image' ) . '</h2>';
                            $html .= '<p class="about-description">' . __( 'Image sharing plugin for WordPress.', 'share-this-image' ) . '</p>';
                            $html .= '<div class="sti-welcome-panel-column-container">';
                                $html .= '<div class="sti-welcome-panel-column">';
                                    $html .= '<h4>' . __( 'Get Started', 'share-this-image' ) . '</h4>';
                                    $html .= '<p style="margin-bottom:10px;">' . __( 'Here is several steps that help you to get started:', 'share-this-image' ) . '</p>';
                                    $html .= '<ul>';
                                        $html .= '<li><strong>1.</strong> <strong>' . __( 'Check display rules.', 'share-this-image' ) . '</strong> ' . __( 'Please check the \'Display rules\' option inside the plugin settings page. With this option in is possible to choose what images on what pages must be available for sharing. By default all website images will be enabled for sharing.', 'share-this-image' ) . '</li>';
                                        $html .= '<li><strong>2.</strong> <strong>' . __( 'Set sharing buttons.', 'share-this-image' ) . '</strong> ' . __( 'Enable/disable sharing buttons that must be visible on your site.', 'share-this-image' ) . '</li>';
                                        $html .= '<li><strong>3.</strong> <strong>' . __( 'Finish!', 'share-this-image' ) . '</strong> ' . __( 'Now all is set and your image sharing buttons are live.', 'share-this-image' ) . '</li>';
                                    $html .= '</ul>';
                                $html .= '</div>';
                                $html .= '<div class="sti-welcome-panel-column">';
                                    $html .= '<h4>' . __( 'Documentation', 'share-this-image' ) . '</h4>';
                                    $html .= '<ul>';
                                        $html .= '<li><a href="https://share-this-image.com/guide/buttons-display-rules/" class="sti-welcome-icon sti-welcome-edit-page" target="_blank">' . __( 'Buttons Display Rules', 'share-this-image' ) . '</a></li>';
                                        $html .= '<li><a href="https://share-this-image.com/guide/customize-content/" class="sti-welcome-icon sti-welcome-edit-page" target="_blank">' . __( 'Content Customization', 'share-this-image' ) . '</a></li>';
                                        $html .= '<li><a href="https://share-this-image.com/guide/buttons-positions/" class="sti-welcome-icon sti-welcome-edit-page" target="_blank">' . __( 'Buttons Positions', 'share-this-image' ) . '</a></li>';
                                    $html .= '</ul>';
                                $html .= '</div>';
                                $html .= '<div class="sti-welcome-panel-column sti-welcome-panel-last">';
                                    $html .= '<h4>' . __( 'Help', 'share-this-image' ) . '</h4>';
                                    $html .= '<ul>';
                                        $html .= '<li><div class="sti-welcome-icon sti-welcome-widgets-menus"><a href="https://wordpress.org/support/plugin/share-this-image/" target="_blank">' . __( 'Support Forums', 'share-this-image' ) . '</a></div></li>';
                                        $html .= '<li><div class="sti-welcome-icon sti-welcome-widgets-menus"><a href="https://share-this-image.com/contact/" target="_blank">' . __( 'Contact Form', 'share-this-image' ) . '</a></div></li>';
                                    $html .= '</ul>';
                                $html .= '</div>';
                            $html .= '</div>';
                        $html .= '</div>';
                    $html .= '</div>';

                $html .= '</div>';
            $html .= '</div>';

            return $html;

        }

    }

endif;