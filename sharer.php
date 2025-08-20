<!DOCTYPE html>

<html itemscope itemtype="http://schema.org/Blog">

<head>

    <meta charset="UTF-8">
    <meta property="og:type" content="article" />
    <meta name="twitter:card" content="summary_large_image">

    <?php

    $http_ext = isset($_GET['ssl']) ? 'https://' : 'http://';

    $url = isset($_GET['url']) ? htmlspecialchars($_GET['url']) : '';

    if ($url) {
        $url_params = parse_url($url);
        if (! $url_params || (isset($url_params['host']) && $url_params['host'] !== $_SERVER['HTTP_HOST'])) {
            $url = $http_ext . $_SERVER["SERVER_NAME"];
        }
    }

    if (isset($_GET['img'])) {

        $page_link = $http_ext . $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
        $title = isset($_GET['title']) ? htmlspecialchars(urldecode($_GET['title'])) : '';
        $desc = isset($_GET['desc']) ? htmlspecialchars(urldecode($_GET['desc'])) : '';
        $image = $http_ext . htmlspecialchars($_GET['img']);
        $network = isset($_GET['network']) ? htmlspecialchars($_GET['network']) : '';
        $image_sizes = @getimagesize($image);

        //if ( $network !== 'facebook' ) {
        echo '<link rel="canonical" href="' . $page_link . '" />';
        echo '<meta property="og:url" content="' . $page_link . '" />';
        echo '<meta property="twitter:url" content="' . $page_link . '" />';
        //}

        // Enhanced Open Graph image tags for WhatsApp and other platforms
        echo '<meta property="og:image" itemprop="image" content="' . $image . '" />';
        if (strpos($image, 'https://') === 0) {
            echo '<meta property="og:image:secure_url" content="' . $image . '" />';
        }

        // WhatsApp-specific optimizations
        $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $is_whatsapp = stripos($user_agent, 'whatsapp') !== false ||
            stripos($user_agent, 'facebookexternalhit') !== false ||
            $network === 'whatsapp';

        if ($is_whatsapp) {
            echo '<meta property="og:image:type" content="image/jpeg" />';
            echo '<meta property="og:locale" content="en_US" />';
            echo '<meta name="robots" content="index,follow" />';
            echo '<meta property="fb:app_id" content="966242223397117" />'; // WhatsApp's app ID
        }

        echo '<meta property="twitter:image" content="' . $image . '" />';
        echo '<meta property="twitter:image:src" content="' . $image . '" />';

        if ($image_sizes) {
            list($width, $height) = $image_sizes;
            echo '<meta property="og:image:width" content="' . $width . '" />';
            echo '<meta property="og:image:height" content="' . $height . '" />';
            echo '<meta property="twitter:image:width" content="' . $width . '" />';
            echo '<meta property="twitter:image:height" content="' . $height . '" />';
        }

        if ($title) {
            echo '<title>' . $title . '</title>';
            echo '<meta property="og:title" content="' . $title . '" />';
            echo '<meta property="twitter:title" content="' . $title . '" />';

            // Extract site name from server name for better social sharing
            $site_name = ucwords(str_replace(['-', '_', '.'], ' ', $_SERVER['HTTP_HOST']));
            echo '<meta property="og:site_name" content="' . $site_name . '" />';
        }

        if ($desc) {
            echo '<meta name="description" content="' . $desc . '">';
            echo '<meta property="og:description" content="' . $desc . '" />';
            echo '<meta property="twitter:description" content="' . $desc . '" />';
        }

        // Add required meta tags for WhatsApp and social platforms
        echo '<meta property="og:updated_time" content="' . time() . '" />';

        // Add Twitter Card specific enhancements
        if ($network == 'twitter') {
            echo '<meta name="twitter:image:alt" content="' . htmlspecialchars($title ? $title : $desc) . '" />';
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
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';

    foreach ($social_crawlers as $crawler) {
        if (strpos($user_agent, $crawler) !== false) {
            $is_social_crawler = true;
            break;
        }
    }

    // Known social platform IP addresses
    $social_ips = ['108.174.2.200', '66.249.81.90', '31.13.97.116'];
    $is_social_ip = in_array($_SERVER['REMOTE_ADDR'] ?? '', $social_ips);

    if (!$is_social_crawler && !$is_social_ip && !isset($_GET['debug'])) {
        echo '<meta http-equiv="refresh" content="0;url=' . $url . '">';
    }

    ?>

    <style type="text/css">
        body {
            background: #fff;
            font-family: arial, helvetica, lucida, verdana, sans-serif;
            margin: 0;
            padding: 0;
        }

        h1 {
            background: #f5f5f5;
            border-top: 1px solid #eee;
            border-bottom: 1px solid #eee;
            margin-top: 10%;
            padding: 50px;
            font-size: 1.4em;
            font-weight: normal;
            text-align: center;
            color: #000;
        }
    </style>

</head>

<body>
    <h1>contacting ...</h1>
</body>

</html>