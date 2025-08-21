<?php

if (! defined('ABSPATH')) {
    exit;
}


if (! class_exists('STI_Admin_Options')) :

    /**
     * Class for plugin admin options methods
     */
    class STI_Admin_Options
    {

        /*
         * Get default settings values
         * @param string $tab Tab name
		 * @return array
         */
        static public function get_default_settings($tab = false)
        {

            $options = self::options_array($tab);
            $default_settings = array();

            // Ensure options is an array to prevent fatal errors
            if (!is_array($options)) {
                return array();
            }

            foreach ($options as $section_name => $section) {
                if (!is_array($section)) {
                    continue;
                }

                foreach ($section as $values) {

                    // Ensure values is an array and has required keys
                    if (!is_array($values) || !isset($values['type'])) {
                        continue;
                    }

                    if ($values['type'] === 'heading' || $values['type'] === 'html') {
                        continue;
                    }

                    if ($values['type'] === 'checkbox') {
                        if (isset($values['choices']) && is_array($values['choices']) && isset($values['id']) && isset($values['value'])) {
                            foreach ($values['choices'] as $key => $val) {
                                if (isset($values['value'][$key])) {
                                    $default_settings[$values['id']][$key] = (string) sanitize_text_field($values['value'][$key]);
                                }
                            }
                        }
                        continue;
                    }

                    if ($values['type'] === 'display_rules') {
                        if (isset($values['id']) && isset($values['value'])) {
                            $default_settings[$values['id']] = $values['value'];
                        }
                        continue;
                    }

                    if ($values['type'] === 'textarea' && isset($values['allow_tags'])) {
                        $default_settings[$values['id']] = (string) addslashes(wp_kses(stripslashes($values['value']), STI_Admin_Helpers::get_kses($values['allow_tags'])));
                        continue;
                    }

                    if ($values['type'] === 'sharing_buttons') {
                        foreach ($values['choices'] as $key => $opts_arr) {
                            foreach ($opts_arr as $opt_name => $opt_val) {
                                if ($opt_name === 'name') continue;
                                $default_settings[$values['id']][$key][$opt_name] = (string) sanitize_text_field($opt_val);
                            }
                        }
                        continue;
                    }

                    if (isset($values['id']) && isset($values['value'])) {
                        $default_settings[$values['id']] = (string) sanitize_text_field($values['value']);
                    }
                }
            }

            return $default_settings;
        }

        /*
         * Update plugin settings
         */
        static public function update_settings()
        {

            $options = self::options_array(false);

            $settings = self::get_settings();

            foreach ($options as $tab_name => $tab_options) {
                foreach ($tab_options as $values) {

                    if (! isset($values['type'])) {
                        continue;
                    }

                    if ($values['type'] === 'heading' || $values['type'] === 'html') {
                        continue;
                    }

                    if ($values['type'] === 'toggler') {
                        $new_value = isset($_POST[$values['id']]) ? 'true' : 'false';
                        $settings[$values['id']] = $new_value;
                        continue;
                    }

                    if ($values['type'] === 'checkbox') {
                        foreach ($values['choices'] as $key => $val) {
                            $settings[$values['id']][$key] = (string) sanitize_text_field($_POST[$values['id']][$key]);
                        }
                        continue;
                    }

                    if ($values['type'] === 'textarea' && isset($values['allow_tags'])) {
                        $settings[$values['id']] = (string) addslashes(wp_kses(stripslashes($_POST[$values['id']]), STI_Admin_Helpers::get_kses($values['allow_tags'])));
                        continue;
                    }

                    if ($values['type'] === 'display_rules') {
                        $new_value = isset($_POST[$values['id']]) ? $_POST[$values['id']] : array();
                        $settings[$values['id']] = $new_value;
                        continue;
                    }

                    if ($values['type'] === 'sharing_buttons') {

                        $table_keys = array_map('sanitize_text_field', array_keys($_POST[$values['id']]));
                        $sorted_table = array_merge(array_flip($table_keys), $values['choices']);
                        $table_options = array();

                        foreach ($sorted_table as $key => $opts_arr) {
                            foreach ($opts_arr as $opt_name => $opt_val) {
                                if ($opt_name === 'name') continue;
                                $table_options[$key][$opt_name] = isset($_POST[$values['id']][$key][$opt_name]) ? (string) sanitize_text_field($_POST[$values['id']][$key][$opt_name]) : 'false';
                            }
                        }

                        $settings[$values['id']] = $table_options;

                        continue;
                    }

                    $new_value = isset($_POST[$values['id']]) ? $_POST[$values['id']] : '';
                    $settings[$values['id']] = (string) sanitize_text_field($new_value);
                }
            }

            update_option('wiss_settings', $settings);
        }

        /*
         * Get plugin settings
         * @return array
         */
        static public function get_settings()
        {
            $plugin_options = get_option('wiss_settings');
            return $plugin_options;
        }

        /*
         * Options array that generate settings page
         *
         * @param string $tab Tab name
         * @return array
         */
        static public function options_array($tab = false, $section = false)
        {

            $options = self::include_options();
            $options_arr = array();

            /**
             * Filter the array of plugin options
             * @since 1.31
             * @param array $options Array of options
             */
            $options = apply_filters('sti_all_options', $options);

            foreach ($options as $tab_name => $tab_options) {

                if ($tab && $tab !== $tab_name) {
                    continue;
                }

                foreach ($tab_options as $option) {

                    if ($section) {

                        if ((isset($option['section']) && $option['section'] !== $section) || (!isset($option['section']) && $section !== 'none')) {
                            continue;
                        }
                    }

                    $options_arr[$tab_name][] = $option;
                }
            }

            return $options_arr;
        }

        /*
         * Include options array
         * @return array
         */
        static public function include_options()
        {

            $options = array();

            $options['buttons'][] = array(
                "name"    => __("Buttons To Display", "wiki-image-social-share"),
                "desc"    => '',
                "type"    => "heading"
            );

            $options['buttons'][] = array(
                "name"  => __("Sharing buttons", "wiki-image-social-share"),
                "desc"  => __("Enable or disable sharing buttons for desktop and mobile. Drag & drop to change the order.", "wiki-image-social-share"),
                "id"    => "buttons",
                "value" => array(),
                "type"  => "sharing_buttons",
                'choices' => array(
                    "facebook" => array(
                        'name'    => __("Facebook", "wiki-image-social-share"),
                        'desktop' => 'true',
                        'mobile'  => 'true'
                    ),
                    "twitter" => array(
                        'name'    => __("X (Twitter)", "wiki-image-social-share"),
                        'desktop' => 'true',
                        'mobile'  => 'true'
                    ),
                    "linkedin" => array(
                        'name'    => __("LinkedIn", "wiki-image-social-share"),
                        'desktop' => 'true',
                        'mobile'  => 'true'
                    ),
                    "pinterest" => array(
                        'name'    => __("Pinterest", "share-this-image"),
                        'desktop' => 'true',
                        'mobile'  => 'true'
                    ),
                    "messenger" => array(
                        'name'    => __("Messenger", "share-this-image"),
                        'desktop' => 'false',
                        'mobile'  => 'false'
                    ),
                    "whatsapp" => array(
                        'name'    => __("WhatsApp", "wiki-image-social-share"),
                        'desktop' => 'true',
                        'mobile'  => 'true'
                    ),
                    "telegram" => array(
                        'name'    => __("Telegram", "share-this-image"),
                        'desktop' => 'false',
                        'mobile'  => 'false'
                    ),
                    "tumblr" => array(
                        'name'    => __("Tumblr", "share-this-image"),
                        'desktop' => 'false',
                        'mobile'  => 'false'
                    ),
                    "reddit" => array(
                        'name'    => __("Reddit", "share-this-image"),
                        'desktop' => 'false',
                        'mobile'  => 'false'
                    ),
                    "vkontakte" => array(
                        'name'    => __("Vkontakte", "share-this-image"),
                        'desktop' => 'false',
                        'mobile'  => 'false'
                    ),
                    "odnoklassniki" => array(
                        'name'    => __("Odnoklassniki", "share-this-image"),
                        'desktop' => 'false',
                        'mobile'  => 'false'
                    ),
                    "viber" => array(
                        'name'    => __("Viber", "share-this-image"),
                        'desktop' => 'false',
                        'mobile'  => 'false'
                    ),
                    "download" => array(
                        'name'    => __("Download", "wiki-image-social-share"),
                        'desktop' => 'true',
                        'mobile'  => 'true'
                    ),
                )
            );

            $options['display'][] = array(
                "name"    => __("Buttons Display Rules", "share-this-image"),
                "desc"    => '',
                "type"    => "heading"
            );

            $options['display'][] = array(
                "name"    => __("Display rules", "share-this-image"),
                "desc"    => __("Choose what images on what pages must be available for sharing.", "share-this-image") . '<br>' .
                    __("By default all images on all pages are available for sharing.", "share-this-image"),
                "id"      => "display_rules",
                "value"   => array(
                    "group_1" => array(
                        "rule_1" => array(
                            "param" => "image",
                            "operator" => "equal",
                            "value" => "sti_any",
                        ),
                        "rule_2" => array(
                            "param" => "page",
                            "operator" => "equal",
                            "value" => "sti_any",
                        ),
                    ),
                ),
                "type"    => "display_rules",
            );

            $options['general'][] = array(
                "name"    => __("Display Settings", "share-this-image"),
                "desc"    => '',
                "type"    => "heading"
            );

            $options['general'][] = array(
                "name"  => __("Buttons position", "share-this-image"),
                "desc"  => __("Choose sharing buttons position.", "share-this-image") . '<br>' .
                    __("NOTE: Enabling some positions can cause problems with images inside sliders, galleries, etc.", "share-this-image"),
                "id"    => "position",
                "value" => 'image_hover',
                "type"  => "radio",
                'choices' => array(
                    'image'       => __('On image ( always show )', 'share-this-image'),
                    'image_hover' => __('On image ( show on mouse enter )', 'share-this-image'),
                )
            );

            $options['general'][] = array(
                "name"  => __("Minimal width", "share-this-image"),
                "desc"  => __("Minimum width of image in pixels to use for sharing.", "share-this-image"),
                "id"    => "minWidth",
                "value" => '150',
                "type"  => "number"
            );

            $options['general'][] = array(
                "name"  => __("Minimal height", "share-this-image"),
                "desc"  => __("Minimum height of image in pixels to use for sharing.", "share-this-image"),
                "id"    => "minHeight",
                "value" => '150',
                "type"  => "number"
            );

            $options['general'][] = array(
                "name"  => __("Facebook app id", "wiki-image-social-share"),
                "desc"  => __("Required for FB Messenger sharing.", "wiki-image-social-share"),
                "id"    => "fb_app",
                "value" => '',
                "type"  => "text"
            );

            $options['general'][] = array(
                "name"  => __("X (Twitter) via", "wiki-image-social-share"),
                "desc"  => __("Set X/Twitter 'via' property.", "wiki-image-social-share"),
                "id"    => "twitter_via",
                "value" => '',
                "type"  => "text"
            );

            $options['general'][] = array(
                "name"  => __("Use X icon", "wiki-image-social-share"),
                "desc"  => __("Use the X icon instead of Twitter bird icon.", "wiki-image-social-share"),
                "id"    => "twitter_x",
                "value" => 'true',
                "type"  => "toggler",
                'choices' => array(
                    'true' => __('On', 'share-this-image'),
                    'false' => __('Off', 'share-this-image')
                )
            );

            $options['general'][] = array(
                "name"  => __("Enable on mobile?", "share-this-image"),
                "desc"  => __("Enable image sharing on mobile devices", "share-this-image"),
                "id"    => "on_mobile",
                "value" => 'true',
                "type"  => "toggler",
                'choices' => array(
                    'true' => __('On', 'share-this-image'),
                    'false' => __('Off', 'share-this-image')
                )
            );

            $options['general'][] = array(
                "name"  => __("Short links", "share-this-image"),
                "desc"  => sprintf(__('Use or not short links method. If enabled links will look like: %s', 'share-this-image'), home_url('/') . 'sti/1485507'),
                "id"    => "short_url",
                "inherit" => "true",
                "value" => 'true',
                "type"  => "toggler",
            );

            $options['general'][] = array(
                "name"  => __("Use intermediate page", "share-this-image"),
                "desc"  => __("If you have some problems with redirection from social networks to page with sharing image try to switch Off this option.", "share-this-image") . '</br>' .
                    __("But before apply it need to be tested to ensure that all work's fine.", 'share-this-image'),
                "id"    => "sharer",
                "value" => 'true',
                "type"  => "toggler",
                'choices' => array(
                    'true'  => __('On', 'share-this-image'),
                    'false' => __('Off', 'share-this-image')
                )
            );

            $options['general'][] = array(
                "name"  => __("Google Analytics", "wiki-image-social-share"),
                "desc"  => __("Use google analytics to track social buttons clicks. Google Analytics needs to be installed on your site.", "wiki-image-social-share") .
                    '<br>' . __("Will send events with category - 'STI click', action - 'social button name' and label of image URL.", "wiki-image-social-share"),
                "id"    => "use_analytics",
                "value" => 'false',
                "type"  => "toggler",
                'choices' => array(
                    'true'  => __('On', 'share-this-image'),
                    'false' => __('Off', 'share-this-image')
                )
            );

            $options['general'][] = array(
                "name"  => __("Buttons z-index", "share-this-image"),
                "desc"  => __("Change css z-index value for sharing buttons. Use if buttons overlapping main content.", "share-this-image"),
                "id"    => "zIndex",
                "value" => '9999999999999999',
                "type"  => "number"
            );

            $options['content'][] = array(
                "name"    => __("Content Settings", "wiki-image-social-share"),
                "desc"    => __('Plugin has special rules for choosing what title and description to use for sharing.', 'wiki-image-social-share') . '<br>' .
                    __('There are different sources that the plugin looks at in step-by-step searching for content according to priority of these sources.', 'wiki-image-social-share') . '<br><br>' .
                    __("For title: 'data-title attribute' -> 'image title attribute' -> 'default title option' -> 'page title'", "wiki-image-social-share") . '<br>' .
                    __("For description: 'data-summary attribute' -> 'image caption' -> 'image alt attribute' -> 'default description option'", "wiki-image-social-share"),
                "type"    => "heading"
            );

            $options['content'][] = array(
                "name"    => __("Default Content", "share-this-image"),
                "desc"    => '',
                "type"    => "heading"
            );

            $options['content'][] = array(
                "name"  => __("Default Title", "share-this-image"),
                "desc"  => __("Content for 'Default Title' source.", "share-this-image"),
                "id"    => "title",
                "value" => '',
                "type"  => "text"
            );

            $options['content'][] = array(
                "name"  => __("Default Description", "share-this-image"),
                "desc"  => __("Content for 'Default Description' source.", "share-this-image"),
                "id"    => "summary",
                "value" => '',
                "type"  => "textarea",
                'allow_tags' => array('a', 'br', 'em', 'strong', 'b', 'code', 'blockquote', 'p', 'i')
            );

            // Social Media Settings Tab
            $options['social'][] = array(
                "name"    => __("Social Media Settings", "share-this-image"),
                "desc"    => __("Configure social media handles and platform-specific settings for enhanced sharing.", "share-this-image"),
                "type"    => "heading"
            );

            $options['social'][] = array(
                "name"  => __("Twitter/X Handle", "share-this-image"),
                "desc"  => __("Your Twitter/X username (without @). Used for twitter:site and twitter:creator meta tags.", "share-this-image"),
                "id"    => "twitter_handle",
                "value" => '',
                "type"  => "text"
            );

            $options['social'][] = array(
                "name"  => __("Facebook Page ID", "share-this-image"),
                "desc"  => __("Your Facebook Page ID for enhanced Open Graph integration.", "share-this-image"),
                "id"    => "facebook_page_id",
                "value" => '',
                "type"  => "text"
            );

            $options['social'][] = array(
                "name"  => __("Default Site Name", "share-this-image"),
                "desc"  => __("Site name to use in og:site_name meta tag. Leave empty to auto-generate from domain.", "share-this-image"),
                "id"    => "site_name",
                "value" => '',
                "type"  => "text"
            );

            $options['social'][] = array(
                "name"  => __("Image Validation", "share-this-image"),
                "desc"  => __("Enable image validation for social media platform requirements.", "share-this-image"),
                "id"    => "image_validation",
                "value" => 'true',
                "type"  => "toggler",
                'choices' => array(
                    'true' => __('On', 'share-this-image'),
                    'false' => __('Off', 'share-this-image')
                )
            );

            $options['social'][] = array(
                "name"  => __("Enhanced Meta Tags", "share-this-image"),
                "desc"  => __("Enable enhanced Open Graph and Twitter Card meta tags for better social sharing.", "share-this-image"),
                "id"    => "enhanced_meta_tags",
                "value" => 'true',
                "type"  => "toggler",
                'choices' => array(
                    'true' => __('On', 'share-this-image'),
                    'false' => __('Off', 'share-this-image')
                )
            );

            $options['social'][] = array(
                "name"    => __("Platform Requirements", "share-this-image"),
                "desc"    => '',
                "type"    => "heading"
            );

            $options['social'][] = array(
                "name"  => __("Platform Requirements Info", "share-this-image"),
                "desc"  => '',
                "id"    => "platform_info",
                "type"  => "html",
                "html"  => '<div class="sti-platform-info">
                    <h4>' . __('WhatsApp Requirements:', 'share-this-image') . '</h4>
                    <ul>
                        <li>' . __('Minimum image size: 200x200px', 'share-this-image') . '</li>
                        <li>' . __('Recommended size: 1200x630px', 'share-this-image') . '</li>
                        <li>' . __('Aspect ratios: 1:1, 16:9, or 1.91:1', 'share-this-image') . '</li>
                        <li>' . __('File size: Under 300KB recommended', 'share-this-image') . '</li>
                        <li>' . __('Formats: JPG, PNG, WEBP', 'share-this-image') . '</li>
                    </ul>
                    
                    <h4>' . __('Facebook Requirements:', 'share-this-image') . '</h4>
                    <ul>
                        <li>' . __('Recommended size: 1200x630px', 'share-this-image') . '</li>
                        <li>' . __('Maximum file size: 8MB', 'share-this-image') . '</li>
                        <li>' . __('Aspect ratio: 1.91:1', 'share-this-image') . '</li>
                    </ul>
                    
                    <h4>' . __('Twitter/X Requirements:', 'share-this-image') . '</h4>
                    <ul>
                        <li>' . __('Minimum size: 300x157px', 'share-this-image') . '</li>
                        <li>' . __('Maximum size: 4096x4096px', 'share-this-image') . '</li>
                        <li>' . __('Maximum file size: 5MB', 'share-this-image') . '</li>
                        <li>' . __('Aspect ratio: 2:1 for large image cards', 'share-this-image') . '</li>
                    </ul>
                </div>'
            );

            return $options;
        }

        /*
       * Include display rules
       * @return array
       */
        static public function include_rules()
        {

            $options = array();

            $options['common'][] = array(
                "name" => __("Selector", "share-this-image"),
                "id"   => "selector",
                "type" => "text",
                'placeholder' => __("Image css selector", "share-this-image") . ' ( default = img )',
                "operators" => "equals",
            );

            $options['image'][] = array(
                "name" => __("Image", "share-this-image"),
                "id"   => "image",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'STI_Admin_Helpers::get_images',
                    'params'   => array()
                ),
            );

            $options['image'][] = array(
                "name" => __("Image URL", "share-this-image"),
                "id"   => "image_url",
                "type" => "text",
                'placeholder' => '',
                "operators" => "equals_compare",
            );

            $options['image'][] = array(
                "name" => __("Image format", "share-this-image"),
                "id"   => "image_format",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'STI_Admin_Helpers::get_image_formats',
                    'params'   => array()
                ),
            );

            $options['post'][] = array(
                "name" => __("Post", "share-this-image"),
                "id"   => "post",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'STI_Admin_Helpers::get_posts',
                    'params'   => array()
                ),
                "suboption" => array(
                    'callback' => 'STI_Admin_Helpers::get_post_types',
                    'params'   => array()
                ),
            );

            $options['post'][] = array(
                "name" => __("Post type", "share-this-image"),
                "id"   => "post_type",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'STI_Admin_Helpers::get_post_types',
                    'params'   => array()
                ),
            );

            $options['page'][] = array(
                "name" => __("Page", "share-this-image"),
                "id"   => "page",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'STI_Admin_Helpers::get_pages',
                    'params'   => array()
                ),
            );

            $options['page'][] = array(
                "name" => __("Page ID", "share-this-image"),
                "id"   => "page_id",
                "type" => "number",
                "operators" => "equals",
            );

            $options['page'][] = array(
                "name" => __("Page URL", "share-this-image"),
                "id"   => "page_url",
                "type" => "text",
                'placeholder' => sprintf(__('Full page URL, e.g: %s', 'share-this-image'), home_url('/') . 'my-page/'),
                "operators" => "equals_compare",
            );

            $options['page'][] = array(
                "name" => __("Page template", "share-this-image"),
                "id"   => "page_template",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'STI_Admin_Helpers::get_page_templates',
                    'params'   => array()
                ),
            );

            $options['page'][] = array(
                "name" => __("Page type", "share-this-image"),
                "id"   => "page_type",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'STI_Admin_Helpers::get_page_type',
                    'params'   => array()
                ),
            );

            $options['page'][] = array(
                "name" => __("Page archives", "share-this-image"),
                "id"   => "page_archives",
                "type" => "callback",
                "operators" => "equals",
                "choices" => array(
                    'callback' => 'STI_Admin_Helpers::get_page_archive_terms',
                    'params'   => array()
                ),
                "suboption" => array(
                    'callback' => 'STI_Admin_Helpers::get_page_archives',
                    'params'   => array()
                ),
            );

            /**
             * Filter display rules
             * @since 1.60
             * @param array $options Array of label rules
             */
            $options = apply_filters('sti_display_rules', $options);

            return $options;
        }

        /*
         * Rules operators
         * @param $name string Operator name
         * @return array
         */
        static public function get_rule_operators($name)
        {

            $operators = array();

            $operators['equals'] = array(
                array(
                    "name" => __("equal to", "share-this-image"),
                    "id"   => "equal",
                ),
                array(
                    "name" => __("not equal to", "share-this-image"),
                    "id"   => "not_equal",
                ),
            );

            $operators['equals_compare'] = array(
                array(
                    "name" => __("equal to", "share-this-image"),
                    "id"   => "equal",
                ),
                array(
                    "name" => __("not equal to", "share-this-image"),
                    "id"   => "not_equal",
                ),
                array(
                    "name" => __("contains", "share-this-image"),
                    "id"   => "contains",
                ),
                array(
                    "name" => __("not contains", "share-this-image"),
                    "id"   => "not_contains",
                ),
            );

            return $operators[$name];
        }

        /*
         * Include rule array by rule id
         * @return array
         */
        static public function include_rule_by_id($id, $rules)
        {

            $rule = array();

            if ($rules) {
                foreach ($rules as $rule_section => $section_rules) {
                    foreach ($section_rules as $section_rule) {
                        if ($section_rule['id'] === $id) {
                            $rule = $section_rule;
                            break;
                        }
                    }
                }
            }

            if (empty($rule)) {
                $first_arr = reset($rules);
                $rule = $first_arr[0];
            }

            return $rule;
        }

        /*
         * Get section name
         * @param $name string Section id
         * @return string
         */
        static public function get_rule_section($name)
        {

            $label = $name;

            $sections = array(
                'common'     => __("Common", "share-this-image"),
                'page'       => __("Page", "share-this-image"),
                'post'       => __("Post", "share-this-image"),
                'image'      => __("Image", "share-this-image"),
            );

            if (isset($sections[$name])) {
                $label = $sections[$name];
            }

            return $label;
        }
    }

endif;
