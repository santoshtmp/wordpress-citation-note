<?php


// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('YIPL_CITATION_EDITOR')) {

    /**
     * YIPL_CITATION_EDITOR
     */
    class YIPL_CITATION_EDITOR {


        /**
         * construction
         */
        function __construct() {

            // 
            add_action('enqueue_block_editor_assets', [$this, 'yipl_enqueue_citation_format']);
            add_action('enqueue_block_editor_assets', [$this, 'yipl_enqueue_citation_styles']);
            add_filter('wp_kses_allowed_html', [$this, 'yipl_allow_custom_tags_gutenberg'], 10, 2);
        }

        public function yipl_enqueue_citation_format() {
            wp_enqueue_script(
                'yipl-citation-format',
                YIPL_CITATION_URL . 'assets/js/yipl-citation-editor.js', // Adjust path if needed
                array('wp-rich-text', 'wp-editor', 'wp-element', 'wp-components'),
                null,
                true
            );
        }
        public function yipl_enqueue_citation_styles() {
            wp_enqueue_style(
                'yipl-citation-editor-style',
                YIPL_CITATION_URL . 'assets/css/yipl-citation-editor.css',
                array('wp-edit-blocks'),
                null
            );
        }


        public function yipl_allow_custom_tags_gutenberg($allowed, $context) {
            if (is_array($allowed)) {
                $allowed['yipl_citation_slug'] = array(); // No attributes allowed
            }
            return $allowed;
        }
    }
}
