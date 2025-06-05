<?php


// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('YIPL_CITATION_EDITOR_FIELDS')) {

    /**
     * YIPL_CITATION_EDITOR_FIELDS
     */
    class YIPL_CITATION_EDITOR_FIELDS {


        /**
         * construction
         */
        function __construct() {
            // 
            add_action('enqueue_block_editor_assets', [$this, 'enqueue_yipl_citation_editor_assets']);
            add_filter('wp_kses_allowed_html', [$this, 'yipl_allow_custom_tags_gutenberg'], 10, 2);
        }

        /**
         * 
         */
        public function enqueue_yipl_citation_editor_assets() {
            $allow_citation = false;
            if (in_array(get_post_type(), YIPL_CITATION_DATA::$allow_post_type)) {
                $allow_citation = true;
                wp_enqueue_script(
                    'yipl-citation-script',
                    YIPL_CITATION_URL . 'assets/js/yipl-citation-editor.js',
                    ['wp-rich-text', 'wp-editor', 'wp-block-editor', 'wp-element', 'wp-components', 'jquery'],
                    filemtime(YIPL_CITATION_PATH . 'assets/js/yipl-citation-editor.js'),
                    true
                );
                wp_localize_script('yipl-citation-script', 'ajax_object', [
                    'allow_citation' => $allow_citation,
                ]);
                wp_enqueue_style(
                    'yipl-citation-editor-style',
                    YIPL_CITATION_URL . 'assets/css/yipl-citation-editor.css',
                    array('wp-edit-blocks'),
                    null
                );
            }
        }

        /**
         * 
         */
        public function yipl_allow_custom_tags_gutenberg($allowed, $context) {
            if (is_array($allowed)) {
                $allowed['yipl_citation_placeholder'] = array(); // No attributes allowed
            }
            return $allowed;
        }


        /**
         * ==== END ====
         */
    }
}
