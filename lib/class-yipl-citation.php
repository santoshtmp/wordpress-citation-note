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
            new YIPL_CITATION_POST_TYPE();
            new YIPL_CITATION_DATA();
            new YIPL_CITATION_ADMIN_SETTINGS();
            new YIPL_CITATION_EDITOR_FIELDS();
            add_action('wp', [$this, 'yipl_citation_wp_hook']);
            add_action('init', [$this, 'yipl_citation_register_scripts']);
        }


        /**
         * https://developer.wordpress.org/reference/hooks/wp/
         */
        public function yipl_citation_wp_hook() {
            global $yipl_citation_matched_words;
            $yipl_citation_matched_words = [];
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
