<?php

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * WISS Error Handler Class
 * Handles error logging and debugging for the Wiki Image Social Share plugin
 */
if (!class_exists('WISS_Error_Handler')) :

    class WISS_Error_Handler
    {
        /**
         * @var WISS_Error_Handler The single instance of the class
         */
        protected static $_instance = null;

        /**
         * Main WISS_Error_Handler Instance
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
        public function __construct()
        {
            // Only enable in debug mode or for administrators
            if (defined('WP_DEBUG') && WP_DEBUG || current_user_can('manage_options')) {
                add_action('admin_notices', array($this, 'display_error_notices'));
                add_action('wp_footer', array($this, 'display_frontend_errors'));
            }
        }

        /**
         * Log plugin-specific errors
         */
        public static function log_error($message, $context = array())
        {
            $log_message = '[WISS] ' . $message;
            
            if (!empty($context)) {
                $log_message .= ' Context: ' . wp_json_encode($context);
            }
            
            error_log($log_message);
        }

        /**
         * Check for critical class dependencies
         */
        public static function check_dependencies()
        {
            $required_classes = array(
                'STI_Admin_Options',
                'STI_Admin_Helpers', 
                'STI_Admin_Display_Rules',
                'STI_Admin_Fields',
                'STI_Helpers',
                'STI_Functions'
            );

            $missing_classes = array();
            
            foreach ($required_classes as $class) {
                if (!class_exists($class)) {
                    $missing_classes[] = $class;
                }
            }

            if (!empty($missing_classes)) {
                self::log_error('Missing required classes', array('classes' => $missing_classes));
                return false;
            }

            return true;
        }

        /**
         * Validate plugin configuration
         */
        public static function validate_configuration()
        {
            $errors = array();

            // Check if required constants are defined
            if (!defined('WISS_DIR')) {
                $errors[] = 'WISS_DIR constant not defined';
            }

            if (!defined('WISS_URL')) {
                $errors[] = 'WISS_URL constant not defined';
            }

            // Check if required files exist
            $required_files = array(
                WISS_DIR . '/includes/class-sti-helpers.php',
                WISS_DIR . '/includes/class-sti-functions.php',
                WISS_DIR . '/includes/admin/class-sti-admin-options.php'
            );

            foreach ($required_files as $file) {
                if (!file_exists($file)) {
                    $errors[] = 'Required file missing: ' . basename($file);
                }
            }

            if (!empty($errors)) {
                self::log_error('Configuration validation failed', array('errors' => $errors));
                return false;
            }

            return true;
        }

        /**
         * Display admin error notices
         */
        public function display_error_notices()
        {
            // Only show on plugin pages
            if (!isset($_GET['page']) || strpos($_GET['page'], 'sti-') !== 0) {
                return;
            }

            // Check dependencies
            if (!self::check_dependencies()) {
                echo '<div class="notice notice-error"><p>';
                echo '<strong>Wiki Image Social Share:</strong> Critical error - Missing required classes. ';
                echo 'Please check the error log for details.';
                echo '</p></div>';
            }

            // Check configuration
            if (!self::validate_configuration()) {
                echo '<div class="notice notice-error"><p>';
                echo '<strong>Wiki Image Social Share:</strong> Configuration error detected. ';
                echo 'Please check the error log for details.';
                echo '</p></div>';
            }
        }

        /**
         * Display frontend errors for debugging
         */
        public function display_frontend_errors()
        {
            if (!current_user_can('manage_options')) {
                return;
            }

            $errors = array();

            // Check if JavaScript files are loaded
            if (!wp_script_is('wiss-sti-js', 'enqueued')) {
                $errors[] = 'Main JavaScript file not enqueued';
            }

            // Check if CSS files are loaded  
            if (!wp_style_is('wiss-sti-css', 'enqueued')) {
                $errors[] = 'Main CSS file not enqueued';
            }

            if (!empty($errors)) {
                echo '<!-- WISS Debug Errors: ' . implode(', ', $errors) . ' -->';
            }
        }

        /**
         * Test all critical functionality
         */
        public static function run_diagnostics()
        {
            $results = array(
                'dependencies' => self::check_dependencies(),
                'configuration' => self::validate_configuration(),
                'admin_classes' => array(),
                'frontend_classes' => array()
            );

            // Test admin classes
            if (is_admin()) {
                $admin_classes = array('STI_Admin', 'STI_Admin_Fields', 'STI_Admin_Options');
                foreach ($admin_classes as $class) {
                    $results['admin_classes'][$class] = class_exists($class);
                }
            }

            // Test frontend classes
            $frontend_classes = array('STI_Functions', 'STI_Helpers');
            foreach ($frontend_classes as $class) {
                $results['frontend_classes'][$class] = class_exists($class);
            }

            self::log_error('Diagnostic results', $results);
            
            return $results;
        }

        /**
         * Clear debug log
         */
        public static function clear_debug_log()
        {
            $debug_log = WP_CONTENT_DIR . '/debug.log';
            if (file_exists($debug_log)) {
                file_put_contents($debug_log, '// Debug log cleared by WISS Error Handler - [' . date('d-M-Y H:i:s T') . ']' . PHP_EOL);
            }
        }
    }

endif;

// Initialize error handler
add_action('init', 'WISS_Error_Handler::instance');
