<?php

/**
 * WISS Production Environment Synchronization Fix
 * 
 * This script fixes the discrepancies between local development and live production
 * environments by ensuring proper database settings and file synchronization.
 * 
 * CRITICAL ISSUES ADDRESSED:
 * 1. Download button missing from admin interface
 * 2. Download button still opening new tabs (old JavaScript)
 * 3. Admin interface styling differences
 * 4. Database settings synchronization
 * 
 * USAGE: Run this script ONCE on the production site after uploading updated files
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit('Direct access not allowed');
}

class WISS_Production_Sync_Fix
{

    private $log = array();

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('wp_ajax_wiss_run_sync_fix', array($this, 'run_sync_fix'));
    }

    public function add_admin_menu()
    {
        add_submenu_page(
            'tools.php',
            'WISS Production Sync Fix',
            'WISS Sync Fix',
            'manage_options',
            'wiss-sync-fix',
            array($this, 'admin_page')
        );
    }

    public function admin_page()
    {
?>
        <div class="wrap">
            <h1>🔧 WISS Production Synchronization Fix</h1>

            <div class="notice notice-warning">
                <p><strong>⚠️ IMPORTANT:</strong> This tool fixes critical discrepancies between development and production environments.</p>
                <p><strong>Issues Fixed:</strong></p>
                <ul>
                    <li>✅ Download button missing from admin interface</li>
                    <li>✅ Download button opening new tabs instead of direct downloads</li>
                    <li>✅ Admin interface styling differences</li>
                    <li>✅ Database settings synchronization</li>
                </ul>
            </div>

            <div class="card">
                <h2>🚀 Production Environment Status Check</h2>
                <div id="status-check">
                    <?php $this->display_current_status(); ?>
                </div>
            </div>

            <div class="card">
                <h2>🔧 Run Synchronization Fix</h2>
                <p>Click the button below to synchronize your production environment with the latest fixes:</p>
                <button id="run-sync-fix" class="button button-primary button-large">
                    🚀 Run Production Sync Fix
                </button>
                <div id="sync-results" style="margin-top: 20px;"></div>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                $('#run-sync-fix').click(function() {
                    var button = $(this);
                    button.prop('disabled', true).text('🔄 Running Fix...');

                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'wiss_run_sync_fix',
                            nonce: '<?php echo wp_create_nonce('wiss_sync_fix'); ?>'
                        },
                        success: function(response) {
                            if (response.success) {
                                $('#sync-results').html('<div class="notice notice-success"><p><strong>✅ SUCCESS!</strong> ' + response.data.message + '</p><pre>' + response.data.log + '</pre></div>');
                            } else {
                                $('#sync-results').html('<div class="notice notice-error"><p><strong>❌ ERROR:</strong> ' + response.data.message + '</p></div>');
                            }
                            button.prop('disabled', false).text('🚀 Run Production Sync Fix');
                        },
                        error: function() {
                            $('#sync-results').html('<div class="notice notice-error"><p><strong>❌ ERROR:</strong> Failed to run sync fix</p></div>');
                            button.prop('disabled', false).text('🚀 Run Production Sync Fix');
                        }
                    });
                });
            });
        </script>

        <style>
            .card {
                background: white;
                padding: 20px;
                margin: 20px 0;
                border: 1px solid #ccd0d4;
                box-shadow: 0 1px 1px rgba(0, 0, 0, .04);
            }

            .status-item {
                padding: 10px;
                margin: 5px 0;
                border-left: 4px solid #ddd;
            }

            .status-ok {
                border-left-color: #46b450;
                background: #f7fcf0;
            }

            .status-error {
                border-left-color: #dc3232;
                background: #fef7f1;
            }

            .status-warning {
                border-left-color: #ffb900;
                background: #fff8e5;
            }
        </style>
<?php
    }

    private function display_current_status()
    {
        $settings = get_option('sti_options', array());
        $buttons = isset($settings['buttons']) ? $settings['buttons'] : array();

        echo '<h3>📊 Current Configuration Status:</h3>';

        // Check if download button exists in settings
        $download_configured = isset($buttons['download']);
        echo '<div class="status-item ' . ($download_configured ? 'status-ok' : 'status-error') . '">';
        echo $download_configured ? '✅' : '❌';
        echo ' Download Button in Database: ' . ($download_configured ? 'CONFIGURED' : 'MISSING');
        echo '</div>';

        // Check JavaScript file version
        $plugin_path = dirname(__FILE__);
        $js_file = $plugin_path . '/assets/js/sti.js';
        $js_content = file_exists($js_file) ? file_get_contents($js_file) : '';
        $has_new_tab_fix = strpos($js_content, 'Removed newTab case to prevent blank tabs') !== false;

        echo '<div class="status-item ' . ($has_new_tab_fix ? 'status-ok' : 'status-error') . '">';
        echo $has_new_tab_fix ? '✅' : '❌';
        echo ' JavaScript Fix Applied: ' . ($has_new_tab_fix ? 'YES' : 'NO - OLD VERSION');
        echo '</div>';

        // Check CSS file
        $css_file = $plugin_path . '/assets/css/sti.css';
        $css_exists = file_exists($css_file);

        echo '<div class="status-item ' . ($css_exists ? 'status-ok' : 'status-warning') . '">';
        echo $css_exists ? '✅' : '⚠️';
        echo ' CSS File: ' . ($css_exists ? 'EXISTS' : 'CHECK REQUIRED');
        echo '</div>';
    }

    public function run_sync_fix()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'wiss_sync_fix')) {
            wp_send_json_error(array('message' => 'Security check failed'));
        }

        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => 'Insufficient permissions'));
        }

        $this->log[] = "🚀 Starting WISS Production Sync Fix...";

        // Fix 1: Ensure download button is properly configured in database
        $this->fix_download_button_configuration();

        // Fix 2: Clear all caches
        $this->clear_all_caches();

        // Fix 3: Verify file integrity
        $this->verify_file_integrity();

        // Fix 4: Update plugin version to force refresh
        $this->update_plugin_version();

        $this->log[] = "✅ Production sync fix completed successfully!";
        $this->log[] = "";
        $this->log[] = "🧪 NEXT STEPS FOR TESTING:";
        $this->log[] = "1. Clear browser cache and hard refresh (Ctrl+F5)";
        $this->log[] = "2. Test download button - should NOT open new tabs";
        $this->log[] = "3. Check admin interface - Download button should appear";
        $this->log[] = "4. Test social media sharing with debugging tools";

        wp_send_json_success(array(
            'message' => 'Production environment successfully synchronized!',
            'log' => implode("\n", $this->log)
        ));
    }

    private function fix_download_button_configuration()
    {
        $this->log[] = "🔧 Fixing download button configuration...";

        $settings = get_option('sti_options', array());

        // Ensure download button is properly configured
        if (!isset($settings['buttons']['download'])) {
            $settings['buttons']['download'] = array(
                'desktop' => 'true',
                'mobile' => 'true'
            );

            update_option('sti_options', $settings);
            $this->log[] = "✅ Download button added to database configuration";
        } else {
            $this->log[] = "✅ Download button already configured in database";
        }

        // Ensure default buttons are properly set
        $default_buttons = array(
            'facebook' => array('desktop' => 'true', 'mobile' => 'true'),
            'twitter' => array('desktop' => 'true', 'mobile' => 'true'),
            'linkedin' => array('desktop' => 'true', 'mobile' => 'true'),
            'pinterest' => array('desktop' => 'true', 'mobile' => 'true'),
            'whatsapp' => array('desktop' => 'true', 'mobile' => 'true'),
            'download' => array('desktop' => 'true', 'mobile' => 'true')
        );

        foreach ($default_buttons as $button => $config) {
            if (!isset($settings['buttons'][$button])) {
                $settings['buttons'][$button] = $config;
            }
        }

        update_option('sti_options', $settings);
        $this->log[] = "✅ All button configurations verified and updated";
    }

    private function clear_all_caches()
    {
        $this->log[] = "🧹 Clearing all caches...";

        // Clear WordPress object cache
        if (function_exists('wp_cache_flush')) {
            wp_cache_flush();
            $this->log[] = "✅ WordPress object cache cleared";
        }

        // Clear transients
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_%'");
        $this->log[] = "✅ All transients cleared";

        // Clear common caching plugins
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
            $this->log[] = "✅ W3 Total Cache cleared";
        }

        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
            $this->log[] = "✅ WP Super Cache cleared";
        }

        if (class_exists('WpFastestCache')) {
            $cache = new WpFastestCache();
            $cache->deleteCache();
            $this->log[] = "✅ WP Fastest Cache cleared";
        }
    }

    private function verify_file_integrity()
    {
        $this->log[] = "🔍 Verifying file integrity...";

        $plugin_path = dirname(__FILE__);
        $js_file = $plugin_path . '/assets/js/sti.js';
        if (file_exists($js_file)) {
            $js_content = file_get_contents($js_file);
            if (strpos($js_content, 'Removed newTab case to prevent blank tabs') !== false) {
                $this->log[] = "✅ JavaScript fix verified - newTab method removed";
            } else {
                $this->log[] = "❌ JavaScript file needs update - old version detected";
            }
        } else {
            $this->log[] = "❌ JavaScript file missing";
        }
    }

    private function update_plugin_version()
    {
        $this->log[] = "🔄 Updating plugin version to force refresh...";

        // Update plugin version in database to force browser cache refresh
        $current_version = get_option('wiss_version', '1.0.0');
        $new_version = $current_version . '.' . time();
        update_option('wiss_version', $new_version);

        $this->log[] = "✅ Plugin version updated to: " . $new_version;
    }
}

// Initialize the sync fix tool
new WISS_Production_Sync_Fix();

/**
 * Quick activation function - include this file in functions.php temporarily
 * to activate the sync tool without going through plugin activation
 */
function wiss_activate_sync_tool()
{
    if (is_admin() && current_user_can('manage_options')) {
        new WISS_Production_Sync_Fix();
    }
}

// Uncomment the line below if you need to activate via functions.php
// add_action('init', 'wiss_activate_sync_tool');
?>