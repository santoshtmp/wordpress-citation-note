<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('YIPL_CITATION_POST_FIELDS')) {

    /**
     * YIPL_CITATION_POST_FIELDS
     */
    class YIPL_CITATION_POST_FIELDS {

        /**
         * construction
         */
        function __construct() {
            add_action('add_meta_boxes', [$this, 'yipl_citation_metabox_fields']);
            add_action('save_post', [$this, 'yipl_citation_save_metabox_fields_data']);
        }



        /**
         * Add Custom meata box in the post type
         * https://developer.wordpress.org/reference/hooks/add_meta_boxes/
         */
        public function yipl_citation_metabox_fields() {
            add_meta_box(
                'yipl_citation_descriptino',
                esc_html__('Description'),
                [$this, 'render_yipl_citation_descriptino_meta_box'],
                YIPL_CITATION_POST_TYPE::$post_slug,
                'normal',
                'high'
            );

            // add_meta_box(
            //     'yipl_citation_in_repeated_word',
            //     esc_html__('Index YIPL Citation in multiple repeated word'),
            //     [$this, 'render_yipl_citation_in_repeated_word_meta_box'],
            //     YIPL_CITATION_POST_TYPE::$post_slug,
            //     'normal',
            //     'high'
            // );

            add_meta_box(
                'auto_detect_yipl_citation',
                esc_html__('Allow Auto Detect Citation'),
                [$this, 'render_auto_detect_yipl_citation_meta_box'],
                YIPL_CITATION_DATA::$allow_post_type,
                'side',
            );
        }

        /**
         * render_yipl_citation_descriptino_meta_box
         */
        public function render_yipl_citation_descriptino_meta_box($post) {
            $value = get_post_meta($post->ID, '_yipl_citation_description', true);
            wp_nonce_field('save_yipl_citation_descriptino', 'description_meta_nonce');
            // echo '<textarea style="width:100%;height:150px;" name="yipl_citation_description_field">' . esc_textarea($value) . '</textarea>';
            wp_editor(
                $value,
                'yipl_citation_description_field', // HTML name and ID
                [
                    'textarea_name' => 'yipl_citation_description_field',
                    'media_buttons' => true,
                    'textarea_rows' => 10,
                    'teeny' => false,
                ]
            );
        }

        /**
         * render_auto_detect_yipl_citation_meta_box
         */
        function render_auto_detect_yipl_citation_meta_box($post) {
            $default_auto_detect_citation_word = (get_option('default_auto_detect_citation_word', '0')) ?: '0';
            $value = get_post_meta($post->ID, '_auto_detect_yipl_citation', true) ?: $default_auto_detect_citation_word;
            wp_nonce_field('save_auto_detect_yipl_citation_meta_box', 'auto_detect_yipl_citation_meta_box_nonce');
            echo '<label><input type="checkbox" name="auto_detect_yipl_citation" value="1"' . checked($value, '1', false) . '> Mark to allow auto detect Citation in the content.<br>If this is disable you can manually set the citation by selecting citation placeholder/slug text. </label>';
        }

        /**
         * render_yipl_citation_in_repeated_word_meta_box
         */
        function render_yipl_citation_in_repeated_word_meta_box($post) {
            $default_allow_repeated_citation_word = (get_option('default_allow_repeated_citation_word', '0')) ?: '0';
            $value = get_post_meta($post->ID, '_yipl_citation_in_repeated_word', true) ?: $default_allow_repeated_citation_word;
            wp_nonce_field('save_yipl_citation_in_repeated_word_meta_box', 'yipl_citation_in_repeated_word_meta_box_nonce');
            echo '<label><input type="checkbox" name="yipl_citation_in_repeated_word" value="1"' . checked($value, '1', false) . '> Mark to allow YIPL Citation in the multiple repeated word else in first matching word.</label>';
        }



        /**
         * Save post 
         * https://developer.wordpress.org/reference/hooks/save_post/
         * 
         */
        public function yipl_citation_save_metabox_fields_data($post_id) {

            // Skip autosaves, revisions, and deletions
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }

            // auto_detect_yipl_citation
            if (
                isset($_POST['auto_detect_yipl_citation_meta_box_nonce']) &&
                wp_verify_nonce($_POST['auto_detect_yipl_citation_meta_box_nonce'], 'save_auto_detect_yipl_citation_meta_box')
            ) {
                $value = isset($_POST['auto_detect_yipl_citation']) ? $_POST['auto_detect_yipl_citation'] : '0';
                update_post_meta($post_id, '_auto_detect_yipl_citation', $value);
            }

            // yipl_citation_in_repeated_word
            if (
                isset($_POST['yipl_citation_in_repeated_word_meta_box_nonce']) &&
                wp_verify_nonce($_POST['yipl_citation_in_repeated_word_meta_box_nonce'], 'save_yipl_citation_in_repeated_word_meta_box')
            ) {
                $value = isset($_POST['yipl_citation_in_repeated_word']) ? $_POST['yipl_citation_in_repeated_word'] : '0';
                update_post_meta($post_id, '_yipl_citation_in_repeated_word', $value);
            }

            // yipl_citation_description_field
            if (
                isset($_POST['description_meta_nonce']) &&
                wp_verify_nonce($_POST['description_meta_nonce'], 'save_yipl_citation_descriptino')
            ) {
                if (isset($_POST['yipl_citation_description_field'])) {
                    // update_post_meta($post_id, '_description', sanitize_textarea_field($_POST['yipl_citation_description_field']));
                    update_post_meta($post_id, '_yipl_citation_description', wp_kses_post($_POST['yipl_citation_description_field']));
                    //    var_dump($_POST['yipl_citation_description_field']);
                }
            }
        }

        /**
         * =========== END ============
         */
    }
}
