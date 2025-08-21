<?php

/**
 * Versions capability
 */

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

if (! class_exists('STI_Versions')) :

    /**
     * Class for plugin search
     */
    class STI_Versions
    {

        /**
         * Return a singleton instance of the current class
         *
         * @return object
         */
        public static function factory()
        {
            static $instance = false;

            if (! $instance) {
                $instance = new self();
                $instance->setup();
            }

            return $instance;
        }

        /**
         * Placeholder
         */
        public function __construct() {}

        /**
         * Setup actions and filters for all things settings
         */
        public function setup()
        {
            // For rebranded plugin, start fresh with version 1.0.0
            // No migration needed from old plugin versions

            update_option('wiss_plugin_ver', WISS_VER);
        }
    }


endif;

add_action('admin_init', 'STI_Versions::factory');
