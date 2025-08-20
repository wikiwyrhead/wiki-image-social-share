<?php
/**
 * WhatsApp Sharing Test Page
 * 
 * This file can be used to test WhatsApp sharing functionality
 * Access via: yoursite.com/wp-content/plugins/wiki-image-social-share/test-whatsapp.php
 */

// Simulate WordPress environment for testing
if (!defined('ABSPATH')) {
    // Basic WordPress simulation for testing
    define('ABSPATH', dirname(__FILE__) . '/../../../');
}

// Include the WhatsApp optimizer
require_once dirname(__FILE__) . '/includes/class-wiss-whatsapp-optimizer.php';

// Test image URL (replace with actual test image)
$test_image = 'https://via.placeholder.com/1200x630/0066cc/ffffff?text=WhatsApp+Test+Image';
$test_title = 'WhatsApp Sharing Test - Wiki Image Social Share';
$test_description = 'Testing WhatsApp thumbnail display with enhanced Open Graph meta tags and image optimization.';

// Simulate WhatsApp request
$_GET['network'] = 'whatsapp';
$_GET['img'] = $test_image;
$_GET['title'] = $test_title;
$_GET['desc'] = $test_description;

$whatsapp_optimizer = WISS_WhatsApp_Optimizer::instance();
$validation_result = $whatsapp_optimizer->validate_image($test_image);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($test_title); ?></title>
    
    <!-- Basic Open Graph Tags -->
    <meta property="og:type" content="article" />
    <meta property="og:title" content="<?php echo htmlspecialchars($test_title); ?>" />
    <meta property="og:description" content="<?php echo htmlspecialchars($test_description); ?>" />
    <meta property="og:image" itemprop="image" content="<?php echo htmlspecialchars($test_image); ?>" />
    <meta property="og:image:secure_url" content="<?php echo htmlspecialchars($test_image); ?>" />
    <meta property="og:url" content="<?php echo htmlspecialchars('http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']); ?>" />
    
    <!-- WhatsApp-specific optimizations -->
    <?php echo $whatsapp_optimizer->generate_meta_tags([
        'title' => $test_title,
        'description' => $test_description,
        'image' => $test_image
    ]); ?>
    
    <!-- Twitter Card Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo htmlspecialchars($test_title); ?>">
    <meta name="twitter:description" content="<?php echo htmlspecialchars($test_description); ?>">
    <meta name="twitter:image" content="<?php echo htmlspecialchars($test_image); ?>">
    
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
            line-height: 1.6;
        }
        .test-section {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .success { border-left: 4px solid #28a745; }
        .warning { border-left: 4px solid #ffc107; }
        .error { border-left: 4px solid #dc3545; }
        .meta-tag {
            background: #e9ecef;
            padding: 5px 10px;
            margin: 5px 0;
            border-radius: 4px;
            font-family: monospace;
            font-size: 14px;
        }
        .share-button {
            display: inline-block;
            background: #25d366;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            margin: 10px 5px;
            font-weight: bold;
        }
        .share-button:hover {
            background: #128c7e;
            color: white;
        }
        .validation-item {
            padding: 8px;
            margin: 5px 0;
            border-radius: 4px;
        }
        .validation-success { background: #d4edda; color: #155724; }
        .validation-warning { background: #fff3cd; color: #856404; }
        .validation-error { background: #f8d7da; color: #721c24; }
    </style>
</head>
<body>
    <h1>🚀 WhatsApp Sharing Test - Wiki Image Social Share</h1>
    
    <div class="test-section">
        <h2>📱 Test WhatsApp Sharing</h2>
        <p>Click the button below to test WhatsApp sharing with the optimized meta tags:</p>
        
        <?php
        $whatsapp_url = 'https://api.whatsapp.com/send?text=' . urlencode('http' . (isset($_SERVER['HTTPS']) ? 's' : '') . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
        ?>
        
        <a href="<?php echo $whatsapp_url; ?>" class="share-button" target="_blank">
            📱 Share on WhatsApp
        </a>
        
        <a href="https://developers.facebook.com/tools/debug/" class="share-button" target="_blank" style="background: #1877f2;">
            🔍 Facebook Debugger
        </a>
    </div>

    <div class="test-section <?php echo $validation_result['valid'] ? 'success' : 'error'; ?>">
        <h2>🔍 Image Validation Results</h2>
        
        <div class="validation-item validation-<?php echo $validation_result['valid'] ? 'success' : 'error'; ?>">
            <strong>Overall Status:</strong> <?php echo $validation_result['valid'] ? '✅ Valid' : '❌ Invalid'; ?>
        </div>
        
        <?php if (!empty($validation_result['errors'])): ?>
            <h3>❌ Errors:</h3>
            <?php foreach ($validation_result['errors'] as $error): ?>
                <div class="validation-item validation-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($validation_result['warnings'])): ?>
            <h3>⚠️ Warnings:</h3>
            <?php foreach ($validation_result['warnings'] as $warning): ?>
                <div class="validation-item validation-warning"><?php echo htmlspecialchars($warning); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <?php if (!empty($validation_result['optimizations'])): ?>
            <h3>💡 Optimization Suggestions:</h3>
            <?php foreach ($validation_result['optimizations'] as $optimization): ?>
                <div class="validation-item validation-warning"><?php echo htmlspecialchars($optimization); ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <div class="test-section">
        <h2>🏷️ Generated Meta Tags</h2>
        <p>The following meta tags are being generated for WhatsApp optimization:</p>
        
        <div class="meta-tag">&lt;meta property="og:type" content="article" /&gt;</div>
        <div class="meta-tag">&lt;meta property="og:title" content="<?php echo htmlspecialchars($test_title); ?>" /&gt;</div>
        <div class="meta-tag">&lt;meta property="og:description" content="<?php echo htmlspecialchars($test_description); ?>" /&gt;</div>
        <div class="meta-tag">&lt;meta property="og:image" itemprop="image" content="<?php echo htmlspecialchars($test_image); ?>" /&gt;</div>
        <div class="meta-tag">&lt;meta property="og:image:secure_url" content="<?php echo htmlspecialchars($test_image); ?>" /&gt;</div>
        <div class="meta-tag">&lt;meta property="og:image:type" content="image/jpeg" /&gt;</div>
        <div class="meta-tag">&lt;meta property="og:locale" content="en_US" /&gt;</div>
        <div class="meta-tag">&lt;meta name="robots" content="index,follow" /&gt;</div>
        <div class="meta-tag">&lt;meta property="fb:app_id" content="966242223397117" /&gt;</div>
    </div>

    <div class="test-section">
        <h2>📋 Testing Checklist</h2>
        <p>To properly test WhatsApp sharing:</p>
        <ol>
            <li>✅ Share this URL in WhatsApp and check if thumbnail appears</li>
            <li>✅ Use Facebook's Sharing Debugger to validate Open Graph tags</li>
            <li>✅ Test with different image sizes and formats</li>
            <li>✅ Verify image file size is under 300KB</li>
            <li>✅ Check that images are accessible via HTTPS</li>
            <li>✅ Test on both mobile and desktop WhatsApp</li>
        </ol>
    </div>

    <div class="test-section">
        <h2>🛠️ Troubleshooting</h2>
        <p>If WhatsApp thumbnails are not showing:</p>
        <ul>
            <li><strong>Image Size:</strong> Ensure images are at least 200x200px and under 300KB</li>
            <li><strong>Image Format:</strong> Use JPEG or PNG format</li>
            <li><strong>HTTPS:</strong> Ensure images are served over HTTPS</li>
            <li><strong>Cache:</strong> WhatsApp caches aggressively - try adding cache-busting parameters</li>
            <li><strong>Meta Tags:</strong> Verify all required Open Graph tags are present</li>
            <li><strong>Server Response:</strong> Ensure server responds quickly to crawler requests</li>
        </ul>
    </div>

    <div class="test-section">
        <h2>🔗 Useful Links</h2>
        <ul>
            <li><a href="https://developers.facebook.com/tools/debug/" target="_blank">Facebook Sharing Debugger</a></li>
            <li><a href="https://cards-dev.twitter.com/validator" target="_blank">Twitter Card Validator</a></li>
            <li><a href="https://www.linkedin.com/post-inspector/" target="_blank">LinkedIn Post Inspector</a></li>
            <li><a href="https://github.com/wikiwyrhead/wiki-image-social-share" target="_blank">Plugin GitHub Repository</a></li>
        </ul>
    </div>

    <script>
        // Add some basic interactivity
        document.addEventListener('DOMContentLoaded', function() {
            console.log('WhatsApp Test Page Loaded');
            console.log('User Agent:', navigator.userAgent);
            console.log('Is WhatsApp:', /whatsapp/i.test(navigator.userAgent));
        });
    </script>
</body>
</html>
