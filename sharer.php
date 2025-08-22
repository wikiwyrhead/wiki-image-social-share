<?php
// Attempt to bootstrap WordPress if this file is accessed directly
if (!function_exists('esc_url')) {
    $wp_load = realpath(__DIR__ . '/../../..') . '/wp-load.php';
    if (file_exists($wp_load)) {
        require_once $wp_load;
    }
}

// Ensure correct HTTP response and basic headers for social bots and browsers
if (!headers_sent()) {
    http_response_code(200);
    header('Content-Type: text/html; charset=UTF-8');
    header('Cache-Control: public, max-age=60');
}

// Define minimal shims if WordPress functions are unavailable
if (!function_exists('esc_attr')) {
    function esc_attr($text)
    {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('esc_html')) {
    function esc_html($text)
    {
        return htmlspecialchars((string)$text, ENT_QUOTES, 'UTF-8');
    }
}
if (!function_exists('esc_url')) {
    function esc_url($url)
    {
        // Basic URL sanitizer
        $url = filter_var((string)$url, FILTER_SANITIZE_URL);
        return $url;
    }
}
if (!function_exists('get_bloginfo')) {
    function get_bloginfo($show = 'name')
    {
        if ($show === 'name') {
            return $_SERVER['HTTP_HOST'] ?? 'Website';
        }
        return '';
    }
}

// Helper: normalize absolute HTTPS image URL
$normalize_image = function ($img) use ($use_https) {
    $img = trim((string)$img);
    $img = html_entity_decode($img, ENT_QUOTES, 'UTF-8');
    $img = filter_var($img, FILTER_SANITIZE_URL);
    if (!$img) return '';
    if (strpos($img, '//') === 0) {
        $img = ($use_https ? 'https:' : 'http:') . $img;
    } elseif (strpos($img, 'http://') === 0 && $use_https) {
        $img = 'https://' . substr($img, 7);
    }
    return $img;
};

// Helper: build/cache an optimized OG image for faster crawler fetching
if (!function_exists('wiss_build_optimized_og_image')) {
    function wiss_build_optimized_og_image($src_url)
    {
        if (empty($src_url)) return null;
        if (!function_exists('wp_upload_dir')) return null;

        $uploads = wp_upload_dir();
        if (!empty($uploads['error'])) return null;

        $cache_dir = trailingslashit($uploads['basedir']) . 'wiss-og-cache/';
        $cache_url = trailingslashit($uploads['baseurl']) . 'wiss-og-cache/';

        if (!file_exists($cache_dir)) {
            if (!wp_mkdir_p($cache_dir)) return null;
        }

        $hash = sha1($src_url);
        $dest_path = $cache_dir . $hash . '.jpg';
        $dest_url  = $cache_url . $hash . '.jpg';

        // Serve cached if exists
        if (file_exists($dest_path) && filesize($dest_path) > 0) {
            return array('url' => $dest_url, 'width' => 1200, 'height' => 630);
        }

        if (!function_exists('download_url')) return null;
        $tmp = download_url($src_url, 15);
        if (is_wp_error($tmp)) return null;

        $editor = wp_get_image_editor($tmp);
        if (is_wp_error($editor)) {
            @unlink($tmp);
            return null;
        }
        // Create 1200x630 JPEG, crop to fill
        $editor->resize(1200, 630, true);
        if (method_exists($editor, 'set_quality')) {
            $editor->set_quality(75);
        }
        $saved = $editor->save($dest_path, 'image/jpeg');
        @unlink($tmp);
        if (is_wp_error($saved) || !file_exists($dest_path)) return null;

        return array('url' => $dest_url, 'width' => 1200, 'height' => 630);
    }
}

// Ensure commonly used vars exist
$user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
?>
<!DOCTYPE html>

<html itemscope itemtype="http://schema.org/Blog">

<head>

    <meta charset="UTF-8">
    <meta property="og:type" content="website" />
    <meta name="twitter:card" content="summary_large_image">

    <?php

    // Enhanced security: Sanitize and validate all inputs
    // Robust HTTPS detection: accept ssl=1,true,yes,on; honor is_ssl(); respect provided URL scheme
    $ssl_param = strtolower((string)($_GET['ssl'] ?? ''));
    $use_https = (function_exists('is_ssl') && is_ssl()) || in_array($ssl_param, ['1', 'true', 'yes', 'on'], true);
    if (!$use_https && isset($_GET['url'])) {
        $probe_url = filter_var($_GET['url'], FILTER_SANITIZE_URL);
        if ($probe_url && filter_var($probe_url, FILTER_VALIDATE_URL)) {
            $scheme = parse_url($probe_url, PHP_URL_SCHEME);
            if (strtolower((string)$scheme) === 'https') {
                $use_https = true;
            }
        }
    }
    $http_ext = $use_https ? 'https://' : 'http://';

    // Validate and sanitize URL
    $url = '';
    if (isset($_GET['url'])) {
        $raw_url = filter_var($_GET['url'], FILTER_SANITIZE_URL);
        if (filter_var($raw_url, FILTER_VALIDATE_URL)) {
            $url_params = parse_url($raw_url);
            // Security: Only allow URLs from the same host
            if ($url_params && isset($url_params['host']) && $url_params['host'] === $_SERVER['HTTP_HOST']) {
                $url = esc_url($raw_url);
            } else {
                $url = $http_ext . sanitize_text_field($_SERVER["SERVER_NAME"]);
            }
        } else {
            $url = $http_ext . sanitize_text_field($_SERVER["SERVER_NAME"]);
        }
    }

    if (isset($_GET['img'])) {

        $page_link = $http_ext . sanitize_text_field($_SERVER["SERVER_NAME"]) . esc_url($_SERVER["REQUEST_URI"]);

        // Enhanced sanitization for title and description
        $title = isset($_GET['title']) ? sanitize_text_field(urldecode($_GET['title'])) : '';
        $desc = isset($_GET['desc']) ? sanitize_textarea_field(urldecode($_GET['desc'])) : '';

        // Validate and normalize image URL (accepts full URL or scheme-less host/path)
        $image = '';
        if (isset($_GET['img'])) {
            $raw_img = trim(urldecode($_GET['img']));

            // Build absolute URL candidate based on provided value
            if (preg_match('#^https?://#i', $raw_img)) {
                // Already an absolute URL
                $candidate = $raw_img;
            } elseif (strpos($raw_img, '//') === 0) {
                // Protocol-relative URL
                $candidate = $http_ext . ltrim($raw_img, '/');
            } elseif (strpos($raw_img, '/') === 0) {
                // Absolute path on current host
                $candidate = $http_ext . sanitize_text_field($_SERVER['HTTP_HOST']) . $raw_img;
            } else {
                // Likely host/path without scheme (e.g., example.com/path/to.jpg)
                $candidate = $http_ext . $raw_img;
            }

            if (filter_var($candidate, FILTER_VALIDATE_URL)) {
                $image = esc_url($candidate);
            }
        }

        $network = isset($_GET['network']) ? sanitize_key($_GET['network']) : '';
        $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');
        $image_sizes = $image ? @getimagesize($image) : false;

        // Decide whether to use an optimized OG image (WhatsApp/Facebook crawlers or explicit network=whatsapp)
        $is_fb_bot = (stripos($user_agent, 'facebookexternalhit') !== false) || (stripos($user_agent, 'facebot') !== false);
        $is_wa_bot = (stripos($user_agent, 'whatsapp') !== false);
        $prefer_optimized = $is_fb_bot || $is_wa_bot || ($network === 'whatsapp');
        $optimized = null;
        if ($prefer_optimized && $image) {
            $optimized = wiss_build_optimized_og_image($image);
        }

        // Canonical and URL tags
        echo '<link rel="canonical" href="' . esc_url($page_link) . '" />';
        echo '<meta property="og:url" content="' . esc_url($page_link) . '" />';
        echo '<meta property="twitter:url" content="' . esc_url($page_link) . '" />';

        // Always add site name for consistency across platforms
        $site_name_meta = ucwords(str_replace(['-', '_', '.'], ' ', sanitize_text_field($_SERVER['HTTP_HOST'] ?? '')));
        if ($site_name_meta) {
            echo '<meta property="og:site_name" content="' . esc_attr($site_name_meta) . '" />';
        }

        // Enhanced Open Graph image tags with optimized caching for WA/FB
        if ($image) {
            // Use optimized URL when available
            $og_image_src = ($optimized && !empty($optimized['url'])) ? $optimized['url'] : $image;
            // For WhatsApp/Facebook, avoid query params on og:image URL
            $use_clean_image_url = ($network === 'whatsapp') || (stripos($user_agent, 'facebookexternalhit') !== false) || (stripos($user_agent, 'whatsapp') !== false) || (stripos($user_agent, 'facebot') !== false);
            if ($use_clean_image_url) {
                $image_with_cache = $og_image_src; // no query params
            } else {
                $sep = (strpos($og_image_src, '?') !== false) ? '&' : '?';
                $image_with_cache = $og_image_src . $sep . 'fb_cache=' . time() . '&network=' . urlencode($network);
            }

            echo '<meta property="og:image" itemprop="image" content="' . esc_url($image_with_cache) . '" />';
            // Provide alt text for image previews
            $og_alt = $title ? $title : $desc;
            if (!empty($og_alt)) {
                echo '<meta property="og:image:alt" content="' . esc_attr($og_alt) . '" />';
            }

            // Facebook-specific image requirements
            if ($network === 'facebook' || stripos($user_agent, 'facebookexternalhit') !== false) {
                echo '<meta property="og:image:width" content="' . esc_attr($optimized['width'] ?? 1200) . '" />';
                echo '<meta property="og:image:height" content="' . esc_attr($optimized['height'] ?? 630) . '" />';
                echo '<meta property="og:image:type" content="image/jpeg" />';

                // Additional Facebook-specific tags
                echo '<meta property="fb:app_id" content="966242223397117" />';
                echo '<meta property="article:author" content="' . esc_attr(get_bloginfo('name')) . '" />';
                echo '<meta property="article:published_time" content="' . esc_attr(date('c')) . '" />';
            }
        }

        if ($image) {
            $og_image_src = ($optimized && !empty($optimized['url'])) ? $optimized['url'] : $image;
            if (strpos($og_image_src, 'https://') === 0) {
                $use_clean_image_url = ($network === 'whatsapp') || (stripos($user_agent, 'facebookexternalhit') !== false) || (stripos($user_agent, 'whatsapp') !== false) || (stripos($user_agent, 'facebot') !== false);
                if ($use_clean_image_url) {
                    $image_with_cache = $og_image_src; // no query params
                } else {
                    $sep = (strpos($og_image_src, '?') !== false) ? '&' : '?';
                    $image_with_cache = $og_image_src . $sep . 'fb_cache=' . time() . '&network=' . urlencode($network);
                }
                echo '<meta property="og:image:secure_url" content="' . esc_url($image_with_cache) . '" />';
            }
        }

        // WhatsApp-specific optimizations
        $is_whatsapp = stripos($user_agent, 'whatsapp') !== false ||
            stripos($user_agent, 'facebookexternalhit') !== false ||
            $network === 'whatsapp';

        if ($is_whatsapp || $network === 'whatsapp') {
            echo '<meta property="og:image:type" content="image/jpeg" />';
            echo '<meta property="og:locale" content="en_US" />';
            echo '<meta name="robots" content="index,follow" />';
            echo '<meta property="fb:app_id" content="966242223397117" />';
            // Do not emit a second og:image to avoid conflicts.
        }

        // Twitter Card optimizations
        if ($image) {
            $sep_tw = (strpos($image, '?') !== false) ? '&' : '?';
            $image_tw = $image . $sep_tw . 'tw_cache=' . time();
            echo '<meta property="twitter:image" content="' . esc_url($image_tw) . '" />';
            echo '<meta property="twitter:image:src" content="' . esc_url($image_tw) . '" />';

            if ($network === 'twitter') {
                echo '<meta name="twitter:card" content="summary_large_image" />';
                echo '<meta name="twitter:image:width" content="1200" />';
                echo '<meta name="twitter:image:height" content="630" />';
            }
        }

        if ($image) {
            if ($optimized) {
                echo '<meta property="og:image:width" content="' . esc_attr($optimized['width']) . '" />';
                echo '<meta property="og:image:height" content="' . esc_attr($optimized['height']) . '" />';
                echo '<meta property="twitter:image:width" content="' . esc_attr($optimized['width']) . '" />';
                echo '<meta property="twitter:image:height" content="' . esc_attr($optimized['height']) . '" />';
            } elseif ($image_sizes && is_array($image_sizes) && count($image_sizes) >= 2) {
                $width = intval($image_sizes[0]);
                $height = intval($image_sizes[1]);
                if ($width > 0 && $height > 0) {
                    echo '<meta property="og:image:width" content="' . esc_attr($width) . '" />';
                    echo '<meta property="og:image:height" content="' . esc_attr($height) . '" />';
                    echo '<meta property="twitter:image:width" content="' . esc_attr($width) . '" />';
                    echo '<meta property="twitter:image:height" content="' . esc_attr($height) . '" />';
                }
            } else {
                // Fallback dimensions to help WhatsApp render a preview
                echo '<meta property="og:image:width" content="1200" />';
                echo '<meta property="og:image:height" content="630" />';
                echo '<meta property="twitter:image:width" content="1200" />';
                echo '<meta property="twitter:image:height" content="630" />';
            }
        }

        if ($title) {
            echo '<title>' . esc_html($title) . '</title>';
            echo '<meta property="og:title" content="' . esc_attr($title) . '" />';
            echo '<meta property="twitter:title" content="' . esc_attr($title) . '" />';

            // Extract site name from server name for better social sharing
            $site_name = ucwords(str_replace(['-', '_', '.'], ' ', sanitize_text_field($_SERVER['HTTP_HOST'])));
            echo '<meta property="og:site_name" content="' . esc_attr($site_name) . '" />';
        }

        if ($desc) {
            echo '<meta name="description" content="' . esc_attr($desc) . '">';
            echo '<meta property="og:description" content="' . esc_attr($desc) . '" />';
            echo '<meta property="twitter:description" content="' . esc_attr($desc) . '" />';
        }

        // Add required meta tags for WhatsApp and social platforms
        echo '<meta property="og:updated_time" content="' . esc_attr(time()) . '" />';

        // LinkedIn-specific optimizations
        if ($network === 'linkedin' || stripos($user_agent, 'LinkedInBot') !== false) {
            if ($image) {
                // LinkedIn prefers specific image dimensions
                echo '<meta property="og:image:width" content="1200" />';
                echo '<meta property="og:image:height" content="627" />';
                echo '<meta property="og:image:type" content="image/jpeg" />';

                // LinkedIn-specific tags
                echo '<meta property="og:type" content="article" />';
                echo '<meta name="author" content="' . esc_attr(get_bloginfo('name')) . '" />';
            }
        }

        // Add Twitter Card specific enhancements
        if ($network === 'twitter') {
            $alt_text = $title ? $title : $desc;
            if ($alt_text) {
                echo '<meta name="twitter:image:alt" content="' . esc_attr($alt_text) . '" />';
            }
        }
    }

    // Enhanced user agent detection for social platform crawlers
    $social_crawlers = [
        'linkedin',
        'LinkedInBot',
        'search.google.com',
        'developers.google.com',
        'Google-AMPHTML',
        '.facebook.com',
        'facebookexternalhit',
        'Facebot',
        'Twitterbot',
        'WhatsApp',
        'whatsapp',
        'TelegramBot',
        'Pinterest',
        'SkypeUriPreview',
        'Slackbot',
        'Applebot',
        'bingbot',
        'DuckDuckBot',
        'DiscordBot',
        'Discordbot',
        'InstagramBot'
    ];

    $is_social_crawler = false;
    $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');

    foreach ($social_crawlers as $crawler) {
        if (strpos($user_agent, $crawler) !== false) {
            $is_social_crawler = true;
            break;
        }
    }

    // Known social platform IP addresses
    $social_ips = ['108.174.2.200', '66.249.81.90', '31.13.97.116'];
    $remote_addr = sanitize_text_field($_SERVER['REMOTE_ADDR'] ?? '');
    $is_social_ip = in_array($remote_addr, $social_ips);

    $debug_mode = isset($_GET['debug']) && $_GET['debug'] === '1';

    // Enhanced redirect logic with lightbox support
    $show_lightbox = !$is_social_crawler && !$is_social_ip && !$debug_mode;
    $image_id = isset($_GET['image_id']) ? sanitize_text_field($_GET['image_id']) : '';

    if ($show_lightbox && $url) {
        // Instead of immediate redirect, show lightbox with image
        $lightbox_mode = true;
    } else {
        $lightbox_mode = false;
    }

    // Handle unique image URL redirects from main site
    if (!isset($_GET['img']) && isset($_GET['wiss_image'])) {
        // This is a request from the main site with a unique image identifier
        // We need to redirect to the gallery page since we don't have the image data
        if ($url) {
            wp_safe_redirect($url, 302);
            exit;
        }
    }

    ?>

    <style type="text/css">
        body {
            background: #000;
            font-family: arial, helvetica, lucida, verdana, sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }

        .wiss-lightbox {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        }

        .wiss-lightbox-content {
            max-width: 90%;
            max-height: 90%;
            position: relative;
        }

        .wiss-lightbox-image {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        .wiss-lightbox-close {
            position: absolute;
            top: -40px;
            right: 0;
            color: white;
            font-size: 30px;
            cursor: pointer;
            background: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
        }

        .wiss-lightbox-close:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .wiss-lightbox-info {
            position: absolute;
            bottom: -60px;
            left: 0;
            right: 0;
            color: white;
            text-align: center;
            padding: 10px;
        }

        .wiss-lightbox-title {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }

        .wiss-lightbox-desc {
            font-size: 14px;
            opacity: 0.8;
        }

        .wiss-loading {
            color: white;
            text-align: center;
            font-size: 18px;
            margin-top: 20%;
        }

        @media (max-width: 768px) {
            .wiss-lightbox-content {
                max-width: 95%;
                max-height: 95%;
            }

            .wiss-lightbox-close {
                top: -35px;
                font-size: 24px;
                width: 35px;
                height: 35px;
            }

            .wiss-lightbox-info {
                bottom: -50px;
                font-size: 14px;
            }
        }
    </style>

</head>

<body>
    <?php if ($lightbox_mode && $image): ?>
        <div class="wiss-lightbox" id="wissLightbox">
            <div class="wiss-lightbox-content">
                <a href="<?php echo esc_url($url); ?>" class="wiss-lightbox-close" title="<?php echo esc_attr__('Close and go to gallery', 'wiki-image-social-share'); ?>">&times;</a>
                <img src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($title); ?>" class="wiss-lightbox-image" id="wissLightboxImage">
                <?php if ($title || $desc): ?>
                    <div class="wiss-lightbox-info">
                        <?php if ($title): ?>
                            <div class="wiss-lightbox-title"><?php echo esc_html($title); ?></div>
                        <?php endif; ?>
                        <?php if ($desc): ?>
                            <div class="wiss-lightbox-desc"><?php echo esc_html($desc); ?></div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <script>
            // Enhanced lightbox functionality
            document.addEventListener('DOMContentLoaded', function() {
                var lightbox = document.getElementById('wissLightbox');
                var image = document.getElementById('wissLightboxImage');

                // Close lightbox on background click
                lightbox.addEventListener('click', function(e) {
                    if (e.target === lightbox) {
                        window.location.href = '<?php echo esc_js($url); ?>';
                    }
                });

                // Close lightbox on Escape key
                document.addEventListener('keydown', function(e) {
                    if (e.key === 'Escape') {
                        window.location.href = '<?php echo esc_js($url); ?>';
                    }
                });

                // Preload image and show when ready
                image.addEventListener('load', function() {
                    lightbox.style.opacity = '1';
                });

                // Handle image load errors
                image.addEventListener('error', function() {
                    window.location.href = '<?php echo esc_js($url); ?>';
                });
            });
        </script>
    <?php else: ?>
        <div class="wiss-loading">
            <h1><?php echo $is_social_crawler ? 'Loading image preview...' : 'Redirecting...'; ?></h1>
        </div>
        <?php if (!$is_social_crawler && !$is_social_ip && !$debug_mode && $url): ?>
            <script>
                // Fallback redirect for non-lightbox scenarios
                setTimeout(function() {
                    window.location.href = '<?php echo esc_js($url); ?>';
                }, 1000);
            </script>
        <?php endif; ?>
    <?php endif; ?>
</body>

</html>