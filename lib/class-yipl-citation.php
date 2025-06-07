<?php

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
            new YIPL_CITATION_DATA();
            new YIPL_CITATION_ADMIN_SETTINGS();
            new YIPL_CITATION_EDITOR_FIELDS();
            add_action('init', [$this, 'yipl_citation_register_scripts']);
        }

        function yipl_citation_register_scripts() {
            wp_register_style(
                "yipl-citation-style",
                YIPL_CITATION_URL . 'assets/css/yipl-citation-style.css',
                [],
                null
            );
        }
        //  ======== END =======
    }
}

// Execute YIPL_CITATION main class
new YIPL_CITATION();
