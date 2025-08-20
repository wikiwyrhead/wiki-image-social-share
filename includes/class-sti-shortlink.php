<?php
/**
 * STI url shortener
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

if ( ! class_exists( 'STI_Shortlink' ) ) :

    /**
     * Class for main plugin functions
     */
    class STI_Shortlink {

        /**
         * @var STI_Shortlink The single instance of the class
         */
        protected static $_instance = null;

        /**
         * @var STI_Shortlink Link shortener method
         */
        public $method = 'no';

        /**
         * @var STI_Shortlink Database table name
         */
        public $links_table_name = '';

        /**
         * Main STI_Shortlink Instance
         *
         * Ensures only one instance of STI_Shortlink is loaded or can be loaded.
         *
         * @static
         * @return STI_Shortlink - Main instance
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

            global $wpdb;

            $settings = STI_Admin_Options::get_settings();

            if ( $settings && isset( $settings['short_url'] ) ) {
                $this->method = $settings['short_url'];
            }

            $this->links_table_name = $wpdb->prefix . 'sti_links';

            // Add link to database
            add_action( 'wp_ajax_sti_shortLinks', array( $this, 'ajax_add_link' ) );
            add_action( 'wp_ajax_nopriv_sti_shortLinks', array( $this, 'ajax_add_link' ) );

            // Redirect for shortlink
            add_action( 'template_redirect',  array( $this, 'redirects' ), 1 );

        }

        /*
         * AJAX call action callback for new shortlink
         */
        public function ajax_add_link() {

            if ( ! defined( 'DOING_AJAX' ) ) {
                define( 'DOING_AJAX', true );
            }

            $hash = sanitize_key( $_POST['hash'] );
            $link = $_POST['link'];

            $this->insert_into_links_table( $hash, $link );

            wp_send_json_success( '1' );

        }

        /*
         * Check if links table exist
         */
        private function is_links_table_not_exist() {

            global $wpdb;

            return ( $wpdb->get_var( "SHOW TABLES LIKE '{$this->links_table_name}'" ) != $this->links_table_name );

        }

        /*
         * Create table for short URLs
         */
        private function create_table() {

            global $wpdb;

            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE {$this->links_table_name} (
                      link_id bigint(20) unsigned NOT NULL auto_increment,
                      hash VARCHAR(50) NOT NULL DEFAULT '',
                      link TEXT NOT NULL default '',
                      PRIMARY KEY (link_id),
                      UNIQUE KEY hash (hash)
                ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );

            if ( $wpdb->last_error ) {

                if ( strpos( $wpdb->last_error, 'COLLATION' ) !== false ) {
                    $sql = str_replace( " COLLATE $wpdb->collate", '', $sql );
                    dbDelta( $sql );
                }

            }

        }

        /*
         * Insert data into links table
         */
        private function insert_into_links_table( $link_hash, $link_original ) {

            global $wpdb;

            if ( $this->is_links_table_not_exist() ) {
                $this->create_table();
            }

            $values = $wpdb->prepare(
                "(%s, %s)",
                sanitize_key( $link_hash ), $link_original
            );

            $query  = "INSERT IGNORE INTO {$this->links_table_name}
				       (`hash`, `link`)
                       VALUES $values
            ";

            $wpdb->query( $query );

            if ( $wpdb->last_error ) {
                error_log('STI: Failed to insert inside links table.' );
            }

        }

        /*
         * Get data from cache table
         */
        private function get_from_links_table( $hash ) {

            global $wpdb;

            $result = '';
            $where = $wpdb->prepare( " hash = %s", sanitize_text_field( $hash ) );

            $sql = "SELECT *
                FROM
                    {$this->links_table_name}
                WHERE
                    {$where}
		    ";

            $link = $wpdb->get_results( $sql, ARRAY_A );

            if ( ! $wpdb->last_error ) {
                if ( ! empty( $link ) && ! is_wp_error( $link ) && is_array( $link ) ) {
                    $result = $link[0]['link'];
                }
            } else {
                error_log( 'STI: Filed to retrive the link from links table.' );
            }

            return $result;

        }

        /*
         * Redirects for short links
         */
        public function redirects() {

            $path_segments = explode('/', parse_url ($_SERVER['REQUEST_URI'], PHP_URL_PATH ) );

            if ( $path_segments && $key = array_search( 'sti', $path_segments )  ) {
                $hask_key = $key + 1;
                $hash = isset( $path_segments[$hask_key] ) ? $path_segments[$hask_key] : false;
            } elseif ( isset( $_GET['sti'] ) ) {
                $hash = $_GET['sti'];
            } else {
                $hash = false;
            }

            if ( $hash  ) {

                $link = $this->get_from_links_table( $hash );
                $i = 0;

                do {
                    $link = $this->get_from_links_table( $hash );
                    if ( $i > 0 ) {
                        sleep(2 );
                    }
                    $i++;
                } while ( ! $link && $i < 5 );

                if ( $link ) {
                    wp_safe_redirect( $link, 301 );
                } else {
                    wp_safe_redirect( home_url( '/' ), 301 );
                }

                exit();

            }

        }

    }

endif;