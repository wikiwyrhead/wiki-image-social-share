<?php
/**
 * Plugin Load Test
 * 
 * This script tests if the plugin can be loaded without fatal errors
 * Run this from command line: php test-plugin-load.php
 */

// Simulate WordPress environment
if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/../../../');
}

// Define WordPress constants that the plugin expects
if (!defined('WPINC')) {
    define('WPINC', 'wp-includes');
}

// Mock WordPress functions that the plugin uses
if (!function_exists('add_action')) {
    function add_action($hook, $callback, $priority = 10, $accepted_args = 1) {
        echo "add_action called: $hook\n";
        return true;
    }
}

if (!function_exists('add_filter')) {
    function add_filter($hook, $callback, $priority = 10, $accepted_args = 1) {
        echo "add_filter called: $hook\n";
        return true;
    }
}

if (!function_exists('register_activation_hook')) {
    function register_activation_hook($file, $callback) {
        echo "register_activation_hook called\n";
        return true;
    }
}

if (!function_exists('register_deactivation_hook')) {
    function register_deactivation_hook($file, $callback) {
        echo "register_deactivation_hook called\n";
        return true;
    }
}

if (!function_exists('register_uninstall_hook')) {
    function register_uninstall_hook($file, $callback) {
        echo "register_uninstall_hook called\n";
        return true;
    }
}

if (!function_exists('load_plugin_textdomain')) {
    function load_plugin_textdomain($domain, $deprecated = false, $plugin_rel_path = false) {
        echo "load_plugin_textdomain called: $domain\n";
        return true;
    }
}

if (!function_exists('plugin_basename')) {
    function plugin_basename($file) {
        return basename($file);
    }
}

if (!function_exists('plugins_url')) {
    function plugins_url($path = '', $plugin = '') {
        return 'http://example.com/wp-content/plugins' . $path;
    }
}

if (!function_exists('get_option')) {
    function get_option($option, $default = false) {
        echo "get_option called: $option\n";
        return $default;
    }
}

if (!function_exists('update_option')) {
    function update_option($option, $value, $autoload = null) {
        echo "update_option called: $option\n";
        return true;
    }
}

if (!function_exists('is_admin')) {
    function is_admin() {
        return false;
    }
}

if (!function_exists('__')) {
    function __($text, $domain = 'default') {
        return $text;
    }
}

if (!function_exists('esc_html_e')) {
    function esc_html_e($text, $domain = 'default') {
        echo htmlspecialchars($text);
    }
}

if (!function_exists('esc_attr_e')) {
    function esc_attr_e($text, $domain = 'default') {
        echo htmlspecialchars($text);
    }
}

if (!function_exists('esc_html__')) {
    function esc_html__($text, $domain = 'default') {
        return htmlspecialchars($text);
    }
}

if (!function_exists('esc_attr__')) {
    function esc_attr__($text, $domain = 'default') {
        return htmlspecialchars($text);
    }
}

echo "Starting plugin load test...\n";

try {
    // Include the main plugin file
    include_once 'wiki-image-social-share.php';
    
    echo "✅ Plugin loaded successfully!\n";
    echo "✅ WISS_Main class exists: " . (class_exists('WISS_Main') ? 'Yes' : 'No') . "\n";
    echo "✅ WISS function exists: " . (function_exists('WISS') ? 'Yes' : 'No') . "\n";
    
    // Test plugin initialization
    if (function_exists('WISS')) {
        $plugin_instance = WISS();
        echo "✅ Plugin instance created successfully\n";
        echo "✅ Plugin instance type: " . get_class($plugin_instance) . "\n";
    }
    
} catch (ParseError $e) {
    echo "❌ Parse Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}

echo "\nTest completed.\n";
