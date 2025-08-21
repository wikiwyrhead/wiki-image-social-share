<!DOCTYPE html>

<html itemscope itemtype="http://schema.org/Blog">

<head>

    <meta charset="UTF-8">
    <meta property="og:type" content="article" />
    <meta name="twitter:card" content="summary_large_image">

    <?php

    // Enhanced security: Sanitize and validate all inputs
    $http_ext = isset($_GET['ssl']) && $_GET['ssl'] === '1' ? 'https://' : 'http://';

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

        // Validate image URL
        $image = '';
        if (isset($_GET['img'])) {
            $raw_img = filter_var($_GET['img'], FILTER_SANITIZE_URL);
            if (filter_var($raw_img, FILTER_VALIDATE_URL)) {
                $image = esc_url($http_ext . $raw_img);
            }
        }

        $network = isset($_GET['network']) ? sanitize_key($_GET['network']) : '';
        $image_sizes = $image ? @getimagesize($image) : false;

        //if ( $network !== 'facebook' ) {
        echo '<link rel="canonical" href="' . esc_url($page_link) . '" />';
        echo '<meta property="og:url" content="' . esc_url($page_link) . '" />';
        echo '<meta property="twitter:url" content="' . esc_url($page_link) . '" />';
        //}

        // Enhanced Open Graph image tags with Facebook-specific optimizations
        if ($image) {
            // Add cache-busting parameter for Facebook crawler
            $cache_buster = '?fb_cache=' . time() . '&network=' . urlencode($network);
            $image_with_cache = $image . $cache_buster;

            echo '<meta property="og:image" itemprop="image" content="' . esc_url($image_with_cache) . '" />';

            // Facebook-specific image requirements
            if ($network === 'facebook' || stripos($user_agent, 'facebookexternalhit') !== false) {
                echo '<meta property="og:image:width" content="1200" />';
                echo '<meta property="og:image:height" content="630" />';
                echo '<meta property="og:image:type" content="image/jpeg" />';

                // Additional Facebook-specific tags
                echo '<meta property="fb:app_id" content="966242223397117" />';
                echo '<meta property="og:type" content="article" />';
                echo '<meta property="article:author" content="' . esc_attr(get_bloginfo('name')) . '" />';
                echo '<meta property="article:published_time" content="' . esc_attr(date('c')) . '" />';
            }
        }

        if ($image && strpos($image, 'https://') === 0) {
            echo '<meta property="og:image:secure_url" content="' . esc_url($image) . '" />';
        }

        // WhatsApp-specific optimizations
        $user_agent = sanitize_text_field($_SERVER['HTTP_USER_AGENT'] ?? '');
        $is_whatsapp = stripos($user_agent, 'whatsapp') !== false ||
            stripos($user_agent, 'facebookexternalhit') !== false ||
            $network === 'whatsapp';

        if ($is_whatsapp || $network === 'whatsapp') {
            echo '<meta property="og:image:type" content="image/jpeg" />';
            echo '<meta property="og:locale" content="en_US" />';
            echo '<meta name="robots" content="index,follow" />';
            echo '<meta property="fb:app_id" content="966242223397117" />'; // WhatsApp's app ID

            // WhatsApp-specific image optimization
            if ($image) {
                $whatsapp_image = $image . '?wa_opt=1&w=1200&h=630&q=85';
                echo '<meta property="og:image" content="' . esc_url($whatsapp_image) . '" />';
                echo '<meta property="og:image:width" content="1200" />';
                echo '<meta property="og:image:height" content="630" />';
            }
        }

        // Twitter Card optimizations
        if ($image) {
            echo '<meta property="twitter:image" content="' . esc_url($image) . '" />';
            echo '<meta property="twitter:image:src" content="' . esc_url($image) . '" />';

            if ($network === 'twitter') {
                echo '<meta name="twitter:card" content="summary_large_image" />';
                echo '<meta name="twitter:image:width" content="1200" />';
                echo '<meta name="twitter:image:height" content="630" />';
            }
        }

        if ($image_sizes && is_array($image_sizes) && count($image_sizes) >= 2) {
            $width = intval($image_sizes[0]);
            $height = intval($image_sizes[1]);
            if ($width > 0 && $height > 0) {
                echo '<meta property="og:image:width" content="' . esc_attr($width) . '" />';
                echo '<meta property="og:image:height" content="' . esc_attr($height) . '" />';
                echo '<meta property="twitter:image:width" content="' . esc_attr($width) . '" />';
                echo '<meta property="twitter:image:height" content="' . esc_attr($height) . '" />';
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