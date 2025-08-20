<?php

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('WISS_WhatsApp_Optimizer')) :

    /**
     * WhatsApp-specific optimization class
     * Handles image optimization and meta tag generation for WhatsApp sharing
     */
    class WISS_WhatsApp_Optimizer
    {

        /**
         * @var WISS_WhatsApp_Optimizer The single instance of the class
         */
        protected static $_instance = null;

        /**
         * WhatsApp image requirements
         */
        const MAX_FILE_SIZE = 300000; // 300KB
        const MIN_WIDTH = 200;
        const MIN_HEIGHT = 200;
        const PREFERRED_WIDTH = 1200;
        const PREFERRED_HEIGHT = 630;
        const PREFERRED_FORMATS = ['image/jpeg', 'image/png'];

        /**
         * Main instance
         */
        public static function instance()
        {
            if (is_null(self::$_instance)) {
                self::$_instance = new self();
            }
            return self::$_instance;
        }

        /**
         * Constructor
         */
        public function __construct()
        {
            // Hook into WordPress image processing if needed
            add_filter('wiss_optimize_image_for_whatsapp', array($this, 'optimize_image'), 10, 2);
        }

        /**
         * Check if current request is from WhatsApp
         */
        public function is_whatsapp_request()
        {
            $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $network = $_GET['network'] ?? '';

            // Check for WhatsApp in network parameter
            if ($network === 'whatsapp') {
                return true;
            }

            // Check user agent for WhatsApp crawlers
            $whatsapp_agents = [
                'WhatsApp',
                'whatsapp',
                'facebookexternalhit', // WhatsApp uses Facebook's crawler
                'Facebot'
            ];

            foreach ($whatsapp_agents as $agent) {
                if (stripos($user_agent, $agent) !== false) {
                    return true;
                }
            }

            return false;
        }

        /**
         * Validate image for WhatsApp requirements
         */
        public function validate_image($image_url)
        {
            $result = array(
                'valid' => false,
                'errors' => array(),
                'warnings' => array(),
                'optimizations' => array()
            );

            if (empty($image_url) || !filter_var($image_url, FILTER_VALIDATE_URL)) {
                $result['errors'][] = 'Invalid image URL';
                return $result;
            }

            // Get image information
            $image_info = @getimagesize($image_url);
            if (!$image_info) {
                $result['errors'][] = 'Unable to retrieve image information';
                return $result;
            }

            list($width, $height, $type, $attr) = $image_info;
            $mime_type = image_type_to_mime_type($type);

            // Check dimensions
            if ($width < self::MIN_WIDTH || $height < self::MIN_HEIGHT) {
                $result['errors'][] = sprintf(
                    'Image too small: %dx%d (minimum: %dx%d)',
                    $width,
                    $height,
                    self::MIN_WIDTH,
                    self::MIN_HEIGHT
                );
            }

            // Check file size
            $file_size = $this->get_remote_file_size($image_url);
            if ($file_size !== false && $file_size > self::MAX_FILE_SIZE) {
                $result['errors'][] = sprintf(
                    'File size too large: %s (maximum: %s)',
                    $this->format_bytes($file_size),
                    $this->format_bytes(self::MAX_FILE_SIZE)
                );
            }

            // Check format
            if (!in_array($mime_type, self::PREFERRED_FORMATS)) {
                $result['warnings'][] = sprintf(
                    'Image format %s may not be optimal for WhatsApp. Preferred: JPEG, PNG',
                    $mime_type
                );
            }

            // Check aspect ratio
            $aspect_ratio = $width / $height;
            $preferred_ratios = [1.0, 1.78, 1.91]; // 1:1, 16:9, 1.91:1
            $ratio_tolerance = 0.1;
            $ratio_valid = false;

            foreach ($preferred_ratios as $preferred_ratio) {
                if (abs($aspect_ratio - $preferred_ratio) <= $ratio_tolerance) {
                    $ratio_valid = true;
                    break;
                }
            }

            if (!$ratio_valid) {
                $result['warnings'][] = sprintf(
                    'Aspect ratio %.2f:1 may not be optimal. Preferred: 1:1, 16:9, or 1.91:1',
                    $aspect_ratio
                );
            }

            // Suggest optimizations
            if ($width !== self::PREFERRED_WIDTH || $height !== self::PREFERRED_HEIGHT) {
                $result['optimizations'][] = sprintf(
                    'Consider resizing to %dx%d for optimal WhatsApp display',
                    self::PREFERRED_WIDTH,
                    self::PREFERRED_HEIGHT
                );
            }

            $result['valid'] = empty($result['errors']);
            return $result;
        }

        /**
         * Optimize image for WhatsApp
         */
        public function optimize_image($image_url, $force = false)
        {
            if (!$this->is_whatsapp_request() && !$force) {
                return $image_url;
            }

            $validation = $this->validate_image($image_url);
            
            // If image is already valid, add cache-busting parameter
            if ($validation['valid']) {
                return $this->add_cache_buster($image_url);
            }

            // For now, return original with cache buster
            // In future versions, implement server-side optimization
            return $this->add_cache_buster($image_url);
        }

        /**
         * Generate WhatsApp-specific meta tags
         */
        public function generate_meta_tags($data)
        {
            if (!$this->is_whatsapp_request()) {
                return '';
            }

            $meta_tags = '';
            
            // Essential WhatsApp meta tags
            $meta_tags .= '<meta property="og:image:type" content="image/jpeg" />' . "\n";
            $meta_tags .= '<meta property="og:locale" content="en_US" />' . "\n";
            $meta_tags .= '<meta name="robots" content="index,follow" />' . "\n";
            
            // Facebook app ID (WhatsApp uses Facebook's infrastructure)
            $meta_tags .= '<meta property="fb:app_id" content="966242223397117" />' . "\n";
            
            // Additional structured data
            if (!empty($data['title'])) {
                $meta_tags .= '<meta property="og:title" content="' . esc_attr($data['title']) . '" />' . "\n";
            }
            
            if (!empty($data['description'])) {
                $meta_tags .= '<meta property="og:description" content="' . esc_attr($data['description']) . '" />' . "\n";
            }

            return $meta_tags;
        }

        /**
         * Add cache-busting parameter to image URL
         */
        private function add_cache_buster($image_url)
        {
            $separator = strpos($image_url, '?') !== false ? '&' : '?';
            return $image_url . $separator . 'wiss_wa=' . time();
        }

        /**
         * Get remote file size
         */
        private function get_remote_file_size($url)
        {
            $headers = @get_headers($url, 1);
            if (!$headers || !isset($headers['Content-Length'])) {
                return false;
            }

            return is_array($headers['Content-Length'])
                ? (int) end($headers['Content-Length'])
                : (int) $headers['Content-Length'];
        }

        /**
         * Format bytes to human readable format
         */
        private function format_bytes($bytes, $precision = 2)
        {
            $units = array('B', 'KB', 'MB', 'GB', 'TB');

            for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                $bytes /= 1024;
            }

            return round($bytes, $precision) . ' ' . $units[$i];
        }
    }

endif;

// Initialize the WhatsApp optimizer
WISS_WhatsApp_Optimizer::instance();
