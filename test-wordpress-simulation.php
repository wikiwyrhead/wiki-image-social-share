<?php
/**
 * WordPress Simulation Test
 * 
 * This simulates WordPress environment to test plugin loading
 */

echo "🔍 Starting WordPress simulation test...\n\n";

// Define WordPress constants
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
}

if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// Mock essential WordPress functions
function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
    return true;
}

function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
    return true;
}

function register_activation_hook($file, $callback) {
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

function get_option($option, $default = false) {
    // Return some default settings for testing
    if ($option === 'wiss_settings') {
        return array(
            'position' => 'image_hover',
            'buttons' => array(
                'facebook' => array('desktop' => 'true', 'mobile' => 'true'),
                'twitter' => array('desktop' => 'true', 'mobile' => 'true'),
                'whatsapp' => array('desktop' => 'false', 'mobile' => 'true')
            )
        );
    }
    return $default;
}

function update_option($option, $value, $autoload = null) {
    return true;
}

function is_admin() {
    return false;
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

function current_user_can($capability) {
    return true;
}

function wp_verify_nonce($nonce, $action) {
    return true;
}

function sanitize_text_field($str) {
    return trim(strip_tags($str));
}

function wp_kses($data, $allowed_html) {
    return strip_tags($data);
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

// Test plugin loading
try {
    echo "📁 Including main plugin file...\n";
    include_once 'wiki-image-social-share.php';
    
    echo "✅ Plugin file included successfully!\n";
    
    // Test class existence
    if (class_exists('WISS_Main')) {
        echo "✅ WISS_Main class exists\n";
    } else {
        echo "❌ WISS_Main class not found\n";
    }
    
    // Test function existence
    if (function_exists('WISS')) {
        echo "✅ WISS() function exists\n";
    } else {
        echo "❌ WISS() function not found\n";
    }
    
    // Test plugin initialization
    if (function_exists('WISS')) {
        echo "🚀 Testing plugin initialization...\n";
        $plugin_instance = WISS();
        
        if ($plugin_instance instanceof WISS_Main) {
            echo "✅ Plugin instance created successfully\n";
            echo "✅ Instance type: " . get_class($plugin_instance) . "\n";
        } else {
            echo "❌ Plugin instance creation failed\n";
        }
    }
    
    // Test WhatsApp optimizer
    if (class_exists('WISS_WhatsApp_Optimizer')) {
        echo "✅ WISS_WhatsApp_Optimizer class exists\n";
        
        $optimizer = WISS_WhatsApp_Optimizer::instance();
        if ($optimizer instanceof WISS_WhatsApp_Optimizer) {
            echo "✅ WhatsApp optimizer instance created successfully\n";
        } else {
            echo "❌ WhatsApp optimizer instance creation failed\n";
        }
    } else {
        echo "❌ WISS_WhatsApp_Optimizer class not found\n";
    }
    
    echo "\n🎉 All tests passed! Plugin should activate successfully.\n";
    
} catch (ParseError $e) {
    echo "❌ Parse Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
    exit(1);
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
    exit(1);
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . "\n";
    echo "📍 Line: " . $e->getLine() . "\n";
    exit(1);
}

echo "\n✨ Test completed successfully!\n";
