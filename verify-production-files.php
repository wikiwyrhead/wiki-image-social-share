<?php
/**
 * WISS Production File Verification Script
 * 
 * This script verifies that all critical fixes are present in the production files
 * and provides detailed information about what needs to be updated.
 * 
 * USAGE: Access via browser: yoursite.com/wp-content/plugins/wiki-image-social-share/verify-production-files.php
 */

// Basic security check
if (!isset($_GET['verify']) || $_GET['verify'] !== 'wiss-production') {
    die('Access denied. Use: ?verify=wiss-production');
}

?>
<!DOCTYPE html>
<html>
<head>
    <title>WISS Production File Verification</title>
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; margin: 40px; background: #f1f1f1; }
        .container { max-width: 1000px; margin: 0 auto; background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; border-radius: 8px; text-align: center; margin-bottom: 30px; }
        .check-item { padding: 15px; margin: 10px 0; border-radius: 6px; border-left: 5px solid #ddd; }
        .check-ok { background: #f0f9ff; border-left-color: #10b981; }
        .check-error { background: #fef2f2; border-left-color: #ef4444; }
        .check-warning { background: #fffbeb; border-left-color: #f59e0b; }
        .code-block { background: #1f2937; color: #f9fafb; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 14px; overflow-x: auto; margin: 10px 0; }
        .file-content { max-height: 200px; overflow-y: auto; background: #f8f9fa; padding: 10px; border-radius: 4px; font-family: monospace; font-size: 12px; }
        .action-required { background: #fee2e2; border: 1px solid #fecaca; padding: 15px; border-radius: 6px; margin: 20px 0; }
        .success-message { background: #dcfce7; border: 1px solid #bbf7d0; padding: 15px; border-radius: 6px; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔍 WISS Production File Verification</h1>
            <p>Checking critical fixes in production environment</p>
        </div>

        <?php
        $plugin_path = dirname(__FILE__);
        $checks_passed = 0;
        $total_checks = 0;
        $critical_issues = array();
        
        // Check 1: JavaScript file with download fix
        $total_checks++;
        echo '<h2>📄 JavaScript File Verification</h2>';
        
        $js_file = $plugin_path . '/assets/js/sti.js';
        if (file_exists($js_file)) {
            $js_content = file_get_contents($js_file);
            
            // Check for newTab removal
            $has_newtab_removal = strpos($js_content, 'Removed newTab case to prevent blank tabs') !== false;
            $has_old_newtab = strpos($js_content, 'window.open(imageUrl, "_blank");') !== false;
            
            if ($has_newtab_removal && !$has_old_newtab) {
                $checks_passed++;
                echo '<div class="check-item check-ok">✅ <strong>JavaScript Fix Applied:</strong> Download button fix is present (newTab method removed)</div>';
            } else {
                $critical_issues[] = 'JavaScript file contains old download code that opens new tabs';
                echo '<div class="check-item check-error">❌ <strong>JavaScript Fix Missing:</strong> Old download code still present - will open new tabs</div>';
                echo '<div class="action-required"><strong>ACTION REQUIRED:</strong> Upload the updated sti.js file from your local development environment</div>';
            }
            
            // Check for metadata storage system
            $has_metadata_storage = strpos($js_content, 'storeImageMetadata') !== false;
            if ($has_metadata_storage) {
                echo '<div class="check-item check-ok">✅ <strong>Metadata Storage System:</strong> Social media sharing fix is present</div>';
            } else {
                echo '<div class="check-item check-warning">⚠️ <strong>Metadata Storage System:</strong> Social media sharing enhancement missing</div>';
            }
            
        } else {
            $critical_issues[] = 'JavaScript file is missing completely';
            echo '<div class="check-item check-error">❌ <strong>JavaScript File Missing:</strong> sti.js file not found</div>';
        }
        
        // Check 2: PHP functions file
        $total_checks++;
        echo '<h2>🔧 PHP Functions Verification</h2>';
        
        $php_file = $plugin_path . '/includes/class-sti-functions.php';
        if (file_exists($php_file)) {
            $php_content = file_get_contents($php_file);
            
            // Check for metadata storage methods
            $has_store_metadata = strpos($php_content, 'store_image_metadata') !== false;
            $has_handle_unique_urls = strpos($php_content, 'handle_unique_image_urls') !== false;
            
            if ($has_store_metadata && $has_handle_unique_urls) {
                $checks_passed++;
                echo '<div class="check-item check-ok">✅ <strong>PHP Functions Updated:</strong> Social media sharing backend is present</div>';
            } else {
                $critical_issues[] = 'PHP functions file missing social media sharing enhancements';
                echo '<div class="check-item check-error">❌ <strong>PHP Functions Missing:</strong> Social media sharing backend not updated</div>';
            }
            
        } else {
            $critical_issues[] = 'PHP functions file is missing';
            echo '<div class="check-item check-error">❌ <strong>PHP Functions File Missing:</strong> class-sti-functions.php not found</div>';
        }
        
        // Check 3: Admin options file
        $total_checks++;
        echo '<h2>⚙️ Admin Configuration Verification</h2>';
        
        $admin_file = $plugin_path . '/includes/admin/class-sti-admin-options.php';
        if (file_exists($admin_file)) {
            $admin_content = file_get_contents($admin_file);
            
            // Check for download button in admin options
            $has_download_button = strpos($admin_content, '"download" => array(') !== false;
            
            if ($has_download_button) {
                $checks_passed++;
                echo '<div class="check-item check-ok">✅ <strong>Admin Options Updated:</strong> Download button configuration is present</div>';
            } else {
                $critical_issues[] = 'Admin options missing download button configuration';
                echo '<div class="check-item check-error">❌ <strong>Admin Options Missing:</strong> Download button not configured in admin</div>';
            }
            
        } else {
            $critical_issues[] = 'Admin options file is missing';
            echo '<div class="check-item check-error">❌ <strong>Admin Options File Missing:</strong> class-sti-admin-options.php not found</div>';
        }
        
        // Check 4: Sharer.php file
        $total_checks++;
        echo '<h2>🔗 Sharer File Verification</h2>';
        
        $sharer_file = $plugin_path . '/sharer.php';
        if (file_exists($sharer_file)) {
            $sharer_content = file_get_contents($sharer_file);
            
            // Check for enhanced meta tag generation
            $has_enhanced_meta = strpos($sharer_content, 'Enhanced Open Graph image tags') !== false;
            
            if ($has_enhanced_meta) {
                $checks_passed++;
                echo '<div class="check-item check-ok">✅ <strong>Sharer File Updated:</strong> Enhanced social media meta tags present</div>';
            } else {
                echo '<div class="check-item check-warning">⚠️ <strong>Sharer File:</strong> May need enhanced meta tag updates</div>';
            }
            
        } else {
            echo '<div class="check-item check-error">❌ <strong>Sharer File Missing:</strong> sharer.php not found</div>';
        }
        
        // Summary
        echo '<h2>📊 Verification Summary</h2>';
        
        if (count($critical_issues) === 0 && $checks_passed >= 3) {
            echo '<div class="success-message">';
            echo '<h3>🎉 SUCCESS: All Critical Fixes Verified!</h3>';
            echo '<p><strong>Checks Passed:</strong> ' . $checks_passed . '/' . $total_checks . '</p>';
            echo '<p>Your production environment has all the necessary fixes:</p>';
            echo '<ul>';
            echo '<li>✅ Download button will NOT open new tabs</li>';
            echo '<li>✅ Social media sharing with proper metadata</li>';
            echo '<li>✅ Admin interface should show Download button</li>';
            echo '</ul>';
            echo '<p><strong>Next Steps:</strong></p>';
            echo '<ol>';
            echo '<li>Clear all caches (WordPress, CDN, browser)</li>';
            echo '<li>Test download functionality</li>';
            echo '<li>Test social media sharing</li>';
            echo '<li>Verify admin interface shows Download button</li>';
            echo '</ol>';
            echo '</div>';
        } else {
            echo '<div class="action-required">';
            echo '<h3>⚠️ CRITICAL ISSUES FOUND</h3>';
            echo '<p><strong>Checks Passed:</strong> ' . $checks_passed . '/' . $total_checks . '</p>';
            echo '<p><strong>Issues that need immediate attention:</strong></p>';
            echo '<ul>';
            foreach ($critical_issues as $issue) {
                echo '<li>❌ ' . $issue . '</li>';
            }
            echo '</ul>';
            echo '<p><strong>SOLUTION:</strong> Upload the updated plugin files from your local development environment to production.</p>';
            echo '</div>';
        }
        
        // File upload instructions
        echo '<h2>📁 File Upload Instructions</h2>';
        echo '<div class="check-item check-warning">';
        echo '<h3>🚀 How to Fix Production Environment:</h3>';
        echo '<ol>';
        echo '<li><strong>From Local Development:</strong> Zip the entire wiki-image-social-share folder</li>';
        echo '<li><strong>Upload to Production:</strong> Replace the plugin folder via FTP/cPanel</li>';
        echo '<li><strong>Alternative:</strong> Upload individual files that failed verification above</li>';
        echo '<li><strong>Clear Caches:</strong> Clear all WordPress, CDN, and browser caches</li>';
        echo '<li><strong>Test:</strong> Verify download button and social sharing work correctly</li>';
        echo '</ol>';
        echo '</div>';
        
        echo '<div class="code-block">';
        echo 'Key Files to Upload if Missing Fixes:' . "\n";
        echo '- /assets/js/sti.js (download button fix)' . "\n";
        echo '- /includes/class-sti-functions.php (social media backend)' . "\n";
        echo '- /includes/admin/class-sti-admin-options.php (admin interface)' . "\n";
        echo '- /sharer.php (enhanced meta tags)' . "\n";
        echo '</div>';
        ?>
        
        <div style="text-align: center; margin-top: 30px; padding: 20px; background: #f8f9fa; border-radius: 6px;">
            <p><strong>🔄 After uploading files, run this verification again to confirm all fixes are applied.</strong></p>
            <p><a href="?verify=wiss-production&refresh=<?php echo time(); ?>" style="background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 4px;">🔄 Re-run Verification</a></p>
        </div>
    </div>
</body>
</html>
