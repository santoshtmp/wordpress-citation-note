<?php

namespace yiplcitation;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

if (! class_exists('YIPL_CITATION')) {
    /**
     * YIPL_CITATION main class
     * 
     */
    class YIPL_CITATION {

        /**
         * construction
         */
        function __construct() {
            new YIPLCIFO_Data();
            new YIPLCIFO_Admin_Settings();
            new YIPLCIFO_Editor_Fields();
            add_action('init', [$this, 'yiplcifo_register_scripts']);
        }

        function yiplcifo_register_scripts() {
            wp_register_style(
                "yipl-citation-style",
                YIPLCIFO_PLUGIN_URL . 'assets/css/yipl-citation-style.css',
                [],
                filemtime(YIPLCIFO_PLUGIN_DIR . 'assets/css/yipl-citation-style.css')

            );
            wp_enqueue_script(
                'yipl-citation-script',
                YIPLCIFO_PLUGIN_URL . 'assets/js/yipl-citation.js',
                ['jquery'],
                filemtime(YIPLCIFO_PLUGIN_DIR . 'assets/js/yipl-citation.js'),
                true
            );
        }
        //  ======== END =======
    }
}

// Execute YIPL_CITATION main class
new YIPL_CITATION();
