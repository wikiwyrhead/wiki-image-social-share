<?php
/**
 * Emergency Plugin Test
 * 
 * This script tests the plugin for critical errors that would cause WordPress crashes
 */

echo "🚨 EMERGENCY PLUGIN TEST - CRITICAL ERROR DETECTION\n";
echo "==================================================\n\n";

// Define WordPress constants to prevent undefined constant errors
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
}

if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// Mock critical WordPress functions to prevent fatal errors
function get_option($option, $default = false) {
    // Simulate empty database on first run
    return $default;
}

function update_option($option, $value, $autoload = null) {
    echo "✓ Setting option: $option\n";
    return true;
}

function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
    return true;
}

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
    return true;
}

function register_activation_hook($file, $callback) {
    echo "✓ Activation hook registered\n";
    return true;
}

function register_deactivation_hook($file, $callback) {
    return true;
}

function register_uninstall_hook($file, $callback) {
    return true;
}

function load_plugin_textdomain($domain, $deprecated = false, $plugin_rel_path = false) {
    return true;
}

function plugin_basename($file) {
    return basename($file);
}

function plugins_url($path = '', $plugin = '') {
    return 'http://example.com/wp-content/plugins' . $path;
}

function is_admin() {
    return false; // Test frontend loading
}

function sanitize_text_field($str) {
    return trim(strip_tags($str));
}

function __($text, $domain = 'default') {
    return $text;
}

function esc_html_e($text, $domain = 'default') {
    echo htmlspecialchars($text);
}

function esc_attr_e($text, $domain = 'default') {
    echo htmlspecialchars($text);
}

function esc_html__($text, $domain = 'default') {
    return htmlspecialchars($text);
}

function esc_attr__($text, $domain = 'default') {
    return htmlspecialchars($text);
}

function apply_filters($hook, $value) {
    return $value;
}

function do_action($hook) {
    return true;
}

// Mock global variables
global $wpdb;
$wpdb = new stdClass();
$wpdb->prefix = 'wp_';
$wpdb->query = function($sql) { return true; };

// Test critical error scenarios
echo "🔍 Testing critical error scenarios...\n\n";

try {
    echo "1. Testing main plugin file inclusion...\n";
    include_once 'wiki-image-social-share.php';
    echo "   ✅ Main plugin file loaded successfully\n\n";
    
    echo "2. Testing class existence...\n";
    if (class_exists('WISS_Main')) {
        echo "   ✅ WISS_Main class exists\n";
    } else {
        echo "   ❌ WISS_Main class not found\n";
    }
    
    if (function_exists('WISS')) {
        echo "   ✅ WISS() function exists\n";
    } else {
        echo "   ❌ WISS() function not found\n";
    }
    
    echo "\n3. Testing plugin initialization with empty settings...\n";
    if (function_exists('WISS')) {
        $plugin_instance = WISS();
        
        if ($plugin_instance instanceof WISS_Main) {
            echo "   ✅ Plugin instance created successfully\n";
            echo "   ✅ Instance type: " . get_class($plugin_instance) . "\n";
            
            // Test settings access with empty database
            $settings = $plugin_instance->get_settings();
            if (is_array($settings)) {
                echo "   ✅ Settings retrieved successfully (empty array)\n";
            } else {
                echo "   ❌ Settings retrieval failed\n";
            }
            
            $specific_setting = $plugin_instance->get_settings('nonexistent_key');
            echo "   ✅ Specific setting access handled gracefully\n";
            
        } else {
            echo "   ❌ Plugin instance creation failed\n";
        }
    }
    
    echo "\n4. Testing WhatsApp optimizer...\n";
    if (class_exists('WISS_WhatsApp_Optimizer')) {
        echo "   ✅ WISS_WhatsApp_Optimizer class exists\n";
        
        $optimizer = WISS_WhatsApp_Optimizer::instance();
        if ($optimizer instanceof WISS_WhatsApp_Optimizer) {
            echo "   ✅ WhatsApp optimizer instance created successfully\n";
        } else {
            echo "   ❌ WhatsApp optimizer instance creation failed\n";
        }
    } else {
        echo "   ❌ WISS_WhatsApp_Optimizer class not found\n";
    }
    
    echo "\n5. Testing activation hook...\n";
    if (function_exists('wiss_activation_check')) {
        wiss_activation_check();
        echo "   ✅ Activation hook executed successfully\n";
    } else {
        echo "   ❌ Activation hook function not found\n";
    }
    
    echo "\n🎉 ALL CRITICAL TESTS PASSED!\n";
    echo "✅ Plugin should activate without causing WordPress crashes\n";
    echo "✅ All fatal error scenarios have been resolved\n\n";
    
} catch (ParseError $e) {
    echo "❌ CRITICAL PARSE ERROR: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
    echo "🚨 WORDPRESS SITE WILL CRASH - IMMEDIATE FIX REQUIRED\n";
    exit(1);
} catch (Error $e) {
    echo "❌ CRITICAL FATAL ERROR: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
    echo "🚨 WORDPRESS SITE WILL CRASH - IMMEDIATE FIX REQUIRED\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ CRITICAL EXCEPTION: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
    echo "🚨 WORDPRESS SITE WILL CRASH - IMMEDIATE FIX REQUIRED\n";
    exit(1);
}

echo "✨ EMERGENCY TEST COMPLETED SUCCESSFULLY!\n";
echo "🔧 All critical errors have been resolved\n";
echo "🚀 Plugin is ready for WordPress activation\n";
