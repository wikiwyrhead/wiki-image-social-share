<?php

if (! defined('ABSPATH')) {
    exit;
}


if (! class_exists('STI_Helpers')) :

    /**
     * Class for plugin help methods
     */
    class STI_Helpers
    {

        /**
         * Generate images css selector
         * @param array $condition Display conditions
         * @return string Css selector
         */
        static public function generate_css_selector($condition)
        {

            /**
             * Filter default image selector
             * @since 1.73
             * @param string Css selector for images to share
             */
            $selector = apply_filters('sti_default_selector', 'img');

            $selectors_arr = array();

            if (is_array($condition) && ! empty($condition)) {
                foreach ($condition as $condition_group) {
                    if ($condition_group && !empty($condition_group)) {

                        $group_selector = '';
                        $selector_in = array();
                        $selector_not = array();

                        $image_id_in = array();
                        $image_id_not = array();

                        $image_format_in = array();
                        $image_format_not = array();

                        $image_url_equal = array();
                        $image_url_not_equal = array();
                        $image_url_contains = array();
                        $image_url_not_contains = array();

                        foreach ($condition_group as $condition_rule) {

                            $rule_type = $condition_rule['param'];
                            $rule_operator = $condition_rule['operator'];
                            $rule_value = $condition_rule['value'];

                            switch ($rule_type) {

                                case 'selector':
                                    if ($rule_operator === 'equal') {
                                        $selector_in[] = $rule_value;
                                    } else {
                                        $selector_not[] = $rule_value;
                                    }
                                    break;

                                case 'image':
                                    if ($rule_value !== 'sti_any') {
                                        if ($rule_operator === 'equal') {
                                            $image_id_in[] = $rule_value;
                                        } else {
                                            $image_id_not[] = $rule_value;
                                        }
                                    }
                                    break;

                                case 'image_format':
                                    if ($rule_value !== 'sti_any') {
                                        if ($rule_operator === 'equal') {
                                            $image_format_in[] = $rule_value;
                                        } else {
                                            $image_format_not[] = $rule_value;
                                        }
                                    }
                                    break;

                                case 'image_url':
                                    if ($rule_operator === 'equal') {
                                        $image_url_equal[] = $rule_value;
                                    } elseif ($rule_operator === 'not_equal') {
                                        $image_url_not_equal[] = $rule_value;
                                    } elseif ($rule_operator === 'contains') {
                                        $image_url_contains[] = $rule_value;
                                    } else {
                                        $image_url_not_contains[] = $rule_value;
                                    }
                                    break;
                            }
                        }

                        if (! empty($selector_in)) {
                            $group_selector .= implode('', $selector_in);
                        } else {
                            $group_selector .= 'img';
                        }

                        if (! empty($image_id_in)) {
                            foreach ($image_id_in as $image_id_in_item) {
                                $group_selector .= '.wp-image-' . $image_id_in_item;
                            }
                        }

                        if (! empty($image_format_in)) {
                            foreach ($image_format_in as $image_format_in_item) {
                                $group_selector .= "[src$='." . $image_format_in_item . "']";
                            }
                        }

                        if (! empty($image_url_equal)) {
                            foreach ($image_url_equal as $image_url_equal_item) {
                                $group_selector .= "[src='" . $image_url_equal_item . "']";
                            }
                        }

                        if (! empty($image_url_contains)) {
                            foreach ($image_url_contains as $image_url_contains_item) {
                                $group_selector .= "[src*='" . $image_url_contains_item . "']";
                            }
                        }

                        if (! empty($image_url_not_equal)) {
                            foreach ($image_url_not_equal as $image_url_not_equal_item) {
                                $group_selector .= ":not([src='" . $image_url_not_equal_item . "'])";
                            }
                        }

                        if (! empty($image_url_not_contains)) {
                            foreach ($image_url_not_contains as $image_url_not_contains_item) {
                                $group_selector .= ":not([src*='" . $image_url_not_contains_item . "'])";
                            }
                        }

                        if (! empty($selector_not)) {
                            foreach ($selector_not as $selector_not_item) {
                                $group_selector .= ':not(' . $selector_not_item . ')';
                            }
                        }

                        if (! empty($image_id_not)) {
                            foreach ($image_id_not as $image_id_not_item) {
                                $group_selector .= ':not(.wp-image-' . $image_id_not_item . ')';
                            }
                        }

                        if (! empty($image_format_not)) {
                            foreach ($image_format_not as $image_format_not_item) {
                                $group_selector .= ":not([src$='." . $image_format_not_item . "'])";
                            }
                        }

                        /**
                         * Filter generated group selector string
                         * @since 1.86
                         * @param string $group_selector Group selector
                         * @param array $condition_group Array of group conditionsrules
                         */
                        $group_selector = apply_filters('sti_generated_group_selector', $group_selector, $condition_group);

                        if ($group_selector) {
                            $selectors_arr[] = $group_selector;
                        }
                    }
                }
            }

            /**
             * Filter generated selectors array
             * @since 1.73
             * @param array Css selectors
             */
            $selectors_arr = apply_filters('sti_generated_selectors', $selectors_arr, $condition);

            if (! empty($selectors_arr)) {
                $selector = implode(', ', $selectors_arr);
            }

            return $selector;
        }

        /**
         * Check if a string ends with a given substring
         * @param string $haystack String to search in
         * @param string $needle String to search for
         * @return bool
         */
        static public function str_ends_with($haystack, $needle)
        {
            $needle_len = strlen($needle);
            return ($needle_len === 0 || 0 === substr_compare($haystack, $needle, -$needle_len));
        }

        /**
         * Validate image for social media sharing requirements
         * @param string $image_url Image URL to validate
         * @param string $platform Target platform (whatsapp, facebook, twitter, etc.)
         * @return array Validation result with status and messages
         */
        static public function validate_social_image($image_url, $platform = 'general')
        {
            $result = array(
                'valid' => true,
                'messages' => array(),
                'warnings' => array()
            );

            if (empty($image_url)) {
                $result['valid'] = false;
                $result['messages'][] = 'Image URL is required';
                return $result;
            }

            // Get image dimensions and file info
            $image_info = @getimagesize($image_url);
            if (!$image_info) {
                $result['valid'] = false;
                $result['messages'][] = 'Unable to retrieve image information';
                return $result;
            }

            list($width, $height, $type) = $image_info;
            $aspect_ratio = $width / $height;

            // Platform-specific validation
            switch ($platform) {
                case 'whatsapp':
                    // WhatsApp requirements
                    if ($width < 200 || $height < 200) {
                        $result['valid'] = false;
                        $result['messages'][] = 'WhatsApp requires minimum 200x200px images';
                    }

                    $valid_ratios = [1.0, 1.78, 1.91]; // 1:1, 16:9, 1.91:1
                    $ratio_tolerance = 0.1;
                    $ratio_valid = false;
                    foreach ($valid_ratios as $valid_ratio) {
                        if (abs($aspect_ratio - $valid_ratio) <= $ratio_tolerance) {
                            $ratio_valid = true;
                            break;
                        }
                    }
                    if (!$ratio_valid) {
                        $result['warnings'][] = 'WhatsApp prefers aspect ratios of 1:1, 16:9, or 1.91:1';
                    }
                    break;

                case 'facebook':
                    if ($width < 600 || $height < 315) {
                        $result['warnings'][] = 'Facebook recommends minimum 600x315px for large image format';
                    }
                    if ($width < 1200 || $height < 630) {
                        $result['warnings'][] = 'Facebook recommends 1200x630px for optimal display';
                    }
                    break;

                case 'twitter':
                    if ($width < 300 || $height < 157) {
                        $result['valid'] = false;
                        $result['messages'][] = 'Twitter requires minimum 300x157px images';
                    }
                    if ($width > 4096 || $height > 4096) {
                        $result['valid'] = false;
                        $result['messages'][] = 'Twitter maximum image size is 4096x4096px';
                    }
                    break;
            }

            // General validation
            $supported_types = [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP];
            if (!in_array($type, $supported_types)) {
                $result['valid'] = false;
                $result['messages'][] = 'Unsupported image format. Use JPG, PNG, or WEBP';
            }

            // File size check (approximate)
            $headers = @get_headers($image_url, 1);
            if (isset($headers['Content-Length'])) {
                $file_size = (int) $headers['Content-Length'];

                if ($platform === 'whatsapp' && $file_size > 300000) { // 300KB
                    $result['warnings'][] = 'WhatsApp recommends images under 300KB for better performance';
                } elseif ($platform === 'facebook' && $file_size > 8000000) { // 8MB
                    $result['valid'] = false;
                    $result['messages'][] = 'Facebook maximum file size is 8MB';
                } elseif ($platform === 'twitter' && $file_size > 5000000) { // 5MB
                    $result['valid'] = false;
                    $result['messages'][] = 'Twitter maximum file size is 5MB';
                }
            }

            return $result;
        }

        /**
         * Generate optimized meta tags for social sharing
         * @param array $data Sharing data (image, title, description, etc.)
         * @param string $platform Target platform
         * @return string Generated meta tags HTML
         */
        static public function generate_social_meta_tags($data, $platform = 'general')
        {
            $meta_tags = '';

            // Required data validation
            if (empty($data['image']) || empty($data['title'])) {
                return $meta_tags;
            }

            // Sanitize data
            $image = esc_url($data['image']);
            $title = esc_attr($data['title']);
            $description = esc_attr($data['description'] ?? '');
            $url = esc_url($data['url'] ?? '');
            $site_name = esc_attr($data['site_name'] ?? '');

            // Basic Open Graph tags
            $meta_tags .= '<meta property="og:type" content="article" />' . "\n";
            $meta_tags .= '<meta property="og:title" content="' . $title . '" />' . "\n";
            $meta_tags .= '<meta property="og:image" itemprop="image" content="' . $image . '" />' . "\n";

            if (strpos($image, 'https://') === 0) {
                $meta_tags .= '<meta property="og:image:secure_url" content="' . $image . '" />' . "\n";
            }

            if ($description) {
                $meta_tags .= '<meta property="og:description" content="' . $description . '" />' . "\n";
            }

            if ($url) {
                $meta_tags .= '<meta property="og:url" content="' . $url . '" />' . "\n";
            }

            if ($site_name) {
                $meta_tags .= '<meta property="og:site_name" content="' . $site_name . '" />' . "\n";
            }

            $meta_tags .= '<meta property="og:updated_time" content="' . time() . '" />' . "\n";

            // Platform-specific tags
            if ($platform === 'twitter' || $platform === 'general') {
                $meta_tags .= '<meta name="twitter:card" content="summary_large_image" />' . "\n";
                $meta_tags .= '<meta name="twitter:title" content="' . $title . '" />' . "\n";
                $meta_tags .= '<meta name="twitter:image" content="' . $image . '" />' . "\n";

                if ($description) {
                    $meta_tags .= '<meta name="twitter:description" content="' . $description . '" />' . "\n";
                    $meta_tags .= '<meta name="twitter:image:alt" content="' . $description . '" />' . "\n";
                }

                if ($url) {
                    $meta_tags .= '<meta name="twitter:url" content="' . $url . '" />' . "\n";
                }
            }

            // Add image dimensions if available
            $image_info = @getimagesize($image);
            if ($image_info) {
                list($width, $height) = $image_info;
                $meta_tags .= '<meta property="og:image:width" content="' . $width . '" />' . "\n";
                $meta_tags .= '<meta property="og:image:height" content="' . $height . '" />' . "\n";

                if ($platform === 'twitter' || $platform === 'general') {
                    $meta_tags .= '<meta name="twitter:image:width" content="' . $width . '" />' . "\n";
                    $meta_tags .= '<meta name="twitter:image:height" content="' . $height . '" />' . "\n";
                }
            }

            return $meta_tags;
        }

        /**
         * Detect social platform crawler from user agent
         * @param string $user_agent User agent string
         * @return string|false Platform name or false if not detected
         */
        static public function detect_social_crawler($user_agent = '')
        {
            if (empty($user_agent)) {
                $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            }

            $crawlers = array(
                'facebookexternalhit' => 'facebook',
                '.facebook.com' => 'facebook',
                'Twitterbot' => 'twitter',
                'LinkedInBot' => 'linkedin',
                'WhatsApp' => 'whatsapp',
                'TelegramBot' => 'telegram',
                'Pinterest' => 'pinterest',
                'SkypeUriPreview' => 'skype',
                'Slackbot' => 'slack',
                'Applebot' => 'apple',
                'bingbot' => 'bing',
                'Googlebot' => 'google'
            );

            foreach ($crawlers as $crawler => $platform) {
                if (strpos($user_agent, $crawler) !== false) {
                    return $platform;
                }
            }

            return false;
        }

        /**
         * Get the array of available svg icons
         * @return array
         */
        static public function get_svg_icons()
        {

            $icon_arr = array(
                'facebook' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M18.77 7.46H14.5v-1.9c0-.9.6-1.1 1-1.1h3V.5h-4.33C10.24.5 9.5 3.44 9.5 5.32v2.15h-3v4h3v12h5v-12h3.85l.42-4z"/></svg>',
                'messenger' => '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"<g><path d="M12,0A11.77,11.77,0,0,0,0,11.5,11.28,11.28,0,0,0,3.93,20L3,23.37A.5.5,0,0,0,3.5,24a.5.5,0,0,0,.21,0l3.8-1.78A12.39,12.39,0,0,0,12,23,11.77,11.77,0,0,0,24,11.5,11.77,11.77,0,0,0,12,0Zm7.85,8.85-6,6a.5.5,0,0,1-.68,0L9.94,12.1l-5.2,2.83a.5.5,0,0,1-.59-.79l6-6a.5.5,0,0,1,.68,0l3.24,2.78,5.2-2.83a.5.5,0,0,1,.59.79Z"/></g></svg>',
                'twitter' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M23.44 4.83c-.8.37-1.5.38-2.22.02.93-.56.98-.96 1.32-2.02-.88.52-1.86.9-2.9 1.1-.82-.88-2-1.43-3.3-1.43-2.5 0-4.55 2.04-4.55 4.54 0 .36.03.7.1 1.04-3.77-.2-7.12-2-9.36-4.75-.4.67-.6 1.45-.6 2.3 0 1.56.8 2.95 2 3.77-.74-.03-1.44-.23-2.05-.57v.06c0 2.2 1.56 4.03 3.64 4.44-.67.2-1.37.2-2.06.08.58 1.8 2.26 3.12 4.25 3.16C5.78 18.1 3.37 18.74 1 18.46c2 1.3 4.4 2.04 6.97 2.04 8.35 0 12.92-6.92 12.92-12.93 0-.2 0-.4-.02-.6.9-.63 1.96-1.22 2.56-2.14z"/></svg>',
                'linkedin' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M6.5 21.5h-5v-13h5v13zM4 6.5C2.5 6.5 1.5 5.3 1.5 4s1-2.4 2.5-2.4c1.6 0 2.5 1 2.6 2.5 0 1.4-1 2.5-2.6 2.5zm11.5 6c-1 0-2 1-2 2v7h-5v-13h5V10s1.6-1.5 4-1.5c3 0 5 2.2 5 6.3v6.7h-5v-7c0-1-1-2-2-2z"/></svg>',
                'reddit' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M24 11.5c0-1.65-1.35-3-3-3-.96 0-1.86.48-2.42 1.24-1.64-1-3.75-1.64-6.07-1.72.08-1.1.4-3.05 1.52-3.7.72-.4 1.73-.24 3 .5C17.2 6.3 18.46 7.5 20 7.5c1.65 0 3-1.35 3-3s-1.35-3-3-3c-1.38 0-2.54.94-2.88 2.22-1.43-.72-2.64-.8-3.6-.25-1.64.94-1.95 3.47-2 4.55-2.33.08-4.45.7-6.1 1.72C4.86 8.98 3.96 8.5 3 8.5c-1.65 0-3 1.35-3 3 0 1.32.84 2.44 2.05 2.84-.03.22-.05.44-.05.66 0 3.86 4.5 7 10 7s10-3.14 10-7c0-.22-.02-.44-.05-.66 1.2-.4 2.05-1.54 2.05-2.84zM2.3 13.37C1.5 13.07 1 12.35 1 11.5c0-1.1.9-2 2-2 .64 0 1.22.32 1.6.82-1.1.85-1.92 1.9-2.3 3.05zm3.7.13c0-1.1.9-2 2-2s2 .9 2 2-.9 2-2 2-2-.9-2-2zm9.8 4.8c-1.08.63-2.42.96-3.8.96-1.4 0-2.74-.34-3.8-.95-.24-.13-.32-.44-.2-.68.15-.24.46-.32.7-.18 1.83 1.06 4.76 1.06 6.6 0 .23-.13.53-.05.67.2.14.23.06.54-.18.67zm.2-2.8c-1.1 0-2-.9-2-2s.9-2 2-2 2 .9 2 2-.9 2-2 2zm5.7-2.13c-.38-1.16-1.2-2.2-2.3-3.05.38-.5.97-.82 1.6-.82 1.1 0 2 .9 2 2 0 .84-.53 1.57-1.3 1.87z"/></svg>',
                'pinterest' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12.14.5C5.86.5 2.7 5 2.7 8.75c0 2.27.86 4.3 2.7 5.05.3.12.57 0 .66-.33l.27-1.06c.1-.32.06-.44-.2-.73-.52-.62-.86-1.44-.86-2.6 0-3.33 2.5-6.32 6.5-6.32 3.55 0 5.5 2.17 5.5 5.07 0 3.8-1.7 7.02-4.2 7.02-1.37 0-2.4-1.14-2.07-2.54.4-1.68 1.16-3.48 1.16-4.7 0-1.07-.58-1.98-1.78-1.98-1.4 0-2.55 1.47-2.55 3.42 0 1.25.43 2.1.43 2.1l-1.7 7.2c-.5 2.13-.08 4.75-.04 5 .02.17.22.2.3.1.14-.18 1.82-2.26 2.4-4.33.16-.58.93-3.63.93-3.63.45.88 1.8 1.65 3.22 1.65 4.25 0 7.13-3.87 7.13-9.05C20.5 4.15 17.18.5 12.14.5z"/></svg>',
                'whatsapp' => '<svg enable-background="new 0 0 100 100" version="1.1" viewBox="0 0 100 100" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><g><defs><rect height="100" id="SVGID_1_" width="100"/></defs><path d="M95,49.247c0,24.213-19.779,43.841-44.182,43.841c-7.747,0-15.025-1.98-21.357-5.455L5,95.406   l7.975-23.522c-4.023-6.606-6.34-14.354-6.34-22.637c0-24.213,19.781-43.841,44.184-43.841C75.223,5.406,95,25.034,95,49.247    M50.818,12.388c-20.484,0-37.146,16.535-37.146,36.859c0,8.066,2.629,15.535,7.076,21.611l-4.641,13.688l14.275-4.537   c5.865,3.851,12.891,6.097,20.437,6.097c20.481,0,37.146-16.533,37.146-36.858C87.964,28.924,71.301,12.388,50.818,12.388    M73.129,59.344c-0.273-0.447-0.994-0.717-2.076-1.254c-1.084-0.537-6.41-3.138-7.4-3.494c-0.993-0.359-1.717-0.539-2.438,0.536   c-0.721,1.076-2.797,3.495-3.43,4.212c-0.632,0.719-1.263,0.809-2.347,0.271c-1.082-0.537-4.571-1.673-8.708-5.334   c-3.219-2.847-5.393-6.364-6.025-7.44c-0.631-1.075-0.066-1.656,0.475-2.191c0.488-0.482,1.084-1.255,1.625-1.882   c0.543-0.628,0.723-1.075,1.082-1.793c0.363-0.717,0.182-1.344-0.09-1.883c-0.27-0.537-2.438-5.825-3.34-7.976   c-0.902-2.151-1.803-1.793-2.436-1.793c-0.631,0-1.354-0.09-2.076-0.09s-1.896,0.269-2.889,1.344   c-0.992,1.076-3.789,3.676-3.789,8.963c0,5.288,3.879,10.397,4.422,11.114c0.541,0.716,7.49,11.92,18.5,16.223   C63.2,71.177,63.2,69.742,65.186,69.562c1.984-0.179,6.406-2.599,7.312-5.107C73.398,61.943,73.398,59.792,73.129,59.344"/></g></svg>',
                'viber' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M444 49.9C431.3 38.2 379.9 .9 265.3 .4c0 0-135.1-8.1-200.9 52.3C27.8 89.3 14.9 143 13.5 209.5c-1.4 66.5-3.1 191.1 117 224.9h.1l-.1 51.6s-.8 20.9 13 25.1c16.6 5.2 26.4-10.7 42.3-27.8 8.7-9.4 20.7-23.2 29.8-33.7 82.2 6.9 145.3-8.9 152.5-11.2 16.6-5.4 110.5-17.4 125.7-142 15.8-128.6-7.6-209.8-49.8-246.5zM457.9 287c-12.9 104-89 110.6-103 115.1-6 1.9-61.5 15.7-131.2 11.2 0 0-52 62.7-68.2 79-5.3 5.3-11.1 4.8-11-5.7 0-6.9 .4-85.7 .4-85.7-.1 0-.1 0 0 0-101.8-28.2-95.8-134.3-94.7-189.8 1.1-55.5 11.6-101 42.6-131.6 55.7-50.5 170.4-43 170.4-43 96.9 .4 143.3 29.6 154.1 39.4 35.7 30.6 53.9 103.8 40.6 211.1zm-139-80.8c.4 8.6-12.5 9.2-12.9 .6-1.1-22-11.4-32.7-32.6-33.9-8.6-.5-7.8-13.4 .7-12.9 27.9 1.5 43.4 17.5 44.8 46.2zm20.3 11.3c1-42.4-25.5-75.6-75.8-79.3-8.5-.6-7.6-13.5 .9-12.9 58 4.2 88.9 44.1 87.8 92.5-.1 8.6-13.1 8.2-12.9-.3zm47 13.4c.1 8.6-12.9 8.7-12.9 .1-.6-81.5-54.9-125.9-120.8-126.4-8.5-.1-8.5-12.9 0-12.9 73.7 .5 133 51.4 133.7 139.2zM374.9 329v.2c-10.8 19-31 40-51.8 33.3l-.2-.3c-21.1-5.9-70.8-31.5-102.2-56.5-16.2-12.8-31-27.9-42.4-42.4-10.3-12.9-20.7-28.2-30.8-46.6-21.3-38.5-26-55.7-26-55.7-6.7-20.8 14.2-41 33.3-51.8h.2c9.2-4.8 18-3.2 23.9 3.9 0 0 12.4 14.8 17.7 22.1 5 6.8 11.7 17.7 15.2 23.8 6.1 10.9 2.3 22-3.7 26.6l-12 9.6c-6.1 4.9-5.3 14-5.3 14s17.8 67.3 84.3 84.3c0 0 9.1 .8 14-5.3l9.6-12c4.6-6 15.7-9.8 26.6-3.7 14.7 8.3 33.4 21.2 45.8 32.9 7 5.7 8.6 14.4 3.8 23.6z"/></svg>',
                'telegram' => '<svg focusable="false" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><path d="M446.7 98.6l-67.6 318.8c-5.1 22.5-18.4 28.1-37.3 17.5l-103-75.9-49.7 47.8c-5.5 5.5-10.1 10.1-20.7 10.1l7.4-104.9 190.9-172.5c8.3-7.4-1.8-11.5-12.9-4.1L117.8 284 16.2 252.2c-22.1-6.9-22.5-22.1 4.6-32.7L418.2 66.4c18.4-6.9 34.5 4.1 28.5 32.2z"></path></svg>',
                'vkontakte' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21.547 7h-3.29a.743.743 0 0 0-.655.392s-1.312 2.416-1.734 3.23C14.734 12.813 14 12.126 14 11.11V7.603A1.104 1.104 0 0 0 12.896 6.5h-2.474a1.982 1.982 0 0 0-1.75.813s1.255-.204 1.255 1.49c0 .42.022 1.626.04 2.64a.73.73 0 0 1-1.272.503 21.54 21.54 0 0 1-2.498-4.543.693.693 0 0 0-.63-.403h-2.99a.508.508 0 0 0-.48.685C3.005 10.175 6.918 18 11.38 18h1.878a.742.742 0 0 0 .742-.742v-1.135a.73.73 0 0 1 1.23-.53l2.247 2.112a1.09 1.09 0 0 0 .746.295h2.953c1.424 0 1.424-.988.647-1.753-.546-.538-2.518-2.617-2.518-2.617a1.02 1.02 0 0 1-.078-1.323c.637-.84 1.68-2.212 2.122-2.8.603-.804 1.697-2.507.197-2.507z"/></svg>',
                'tumblr' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M13.5.5v5h5v4h-5V15c0 5 3.5 4.4 6 2.8v4.4c-6.7 3.2-12 0-12-4.2V9.5h-3V6.7c1-.3 2.2-.7 3-1.3.5-.5 1-1.2 1.4-2 .3-.7.6-1.7.7-3h3.8z"/></svg>',
                'odnoklassniki' => '<svg enable-background="new 0 0 30 30" version="1.1" viewBox="0 0 30 30" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M22,15c-1,0-3,2-7,2s-6-2-7-2c-1.104,0-2,0.896-2,2c0,1,0.568,1.481,1,1.734C8.185,19.427,12,21,12,21l-4.25,5.438  c0,0-0.75,0.935-0.75,1.562c0,1.104,0.896,2,2,2c1.021,0,1.484-0.656,1.484-0.656S14.993,23.993,15,24  c0.007-0.007,4.516,5.344,4.516,5.344S19.979,30,21,30c1.104,0,2-0.896,2-2c0-0.627-0.75-1.562-0.75-1.562L18,21  c0,0,3.815-1.573,5-2.266C23.432,18.481,24,18,24,17C24,15.896,23.104,15,22,15z" id="K"/><path d="M15,0c-3.866,0-7,3.134-7,7s3.134,7,7,7c3.865,0,7-3.134,7-7S18.865,0,15,0z M15,10.5c-1.933,0-3.5-1.566-3.5-3.5  c0-1.933,1.567-3.5,3.5-3.5c1.932,0,3.5,1.567,3.5,3.5C18.5,8.934,16.932,10.5,15,10.5z" id="O"/></svg>',
                'x' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z"/></svg>',
                'mobile' => '<svg enable-background="new 0 0 64 64" version="1.1" viewBox="0 0 64 64" xml:space="preserve" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink"><path d="M48,39.26c-2.377,0-4.515,1-6.033,2.596L24.23,33.172c0.061-0.408,0.103-0.821,0.103-1.246c0-0.414-0.04-0.818-0.098-1.215  l17.711-8.589c1.519,1.609,3.667,2.619,6.054,2.619c4.602,0,8.333-3.731,8.333-8.333c0-4.603-3.731-8.333-8.333-8.333  s-8.333,3.73-8.333,8.333c0,0.414,0.04,0.817,0.098,1.215l-17.711,8.589c-1.519-1.609-3.666-2.619-6.054-2.619  c-4.603,0-8.333,3.731-8.333,8.333c0,4.603,3.73,8.333,8.333,8.333c2.377,0,4.515-1,6.033-2.596l17.737,8.684  c-0.061,0.407-0.103,0.821-0.103,1.246c0,4.603,3.731,8.333,8.333,8.333s8.333-3.73,8.333-8.333C56.333,42.99,52.602,39.26,48,39.26  z"/></svg>',
                'download' => '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 8V6h2v4h3l-4 4-4-4h3zm6 7H7v-2h10v2z"/></svg>',
            );

            /**
             * Filter the array of social icons
             * @since 1.31
             * @param array $icon_arr Array of icons
             */
            $icon_arr = apply_filters('sti_svg_icons', $icon_arr);

            return $icon_arr;
        }

        /**
         * Get svg icon code
         * @param string $icon_name Icon name
         * @return string
         */
        static public function get_svg($icon_name)
        {

            $icon = '';

            $icon_arr = STI_Helpers::get_svg_icons();

            if ($icon_name && isset($icon_arr[$icon_name])) {
                $icon = $icon_arr[$icon_name];
            }

            return $icon;
        }
    }

endif;
