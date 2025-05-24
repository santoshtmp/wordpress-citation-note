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
            add_action('add_meta_boxes', [$this, 'yipl_citation_metabox_fields']);
            add_action('wp_ajax_update_citation_fields', [$this, 'update_citation_fields']);
            add_action('save_post', [$this, 'yipl_citation_save_metabox_fields_data']);
        }

        public function enqueue_yipl_citation_editor_assets() {
            wp_enqueue_script(
                'yipl-citation-script',
                YIPL_CITATION_URL . 'assets/js/yipl-citation-editor.js', // Adjust path if needed
                array('wp-rich-text', 'wp-editor', 'wp-element', 'wp-components'),
                null,
                true
            );
            wp_localize_script('yipl-citation-script', 'ajax_object', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('citation_fields_row') // optional
            ]);

            wp_enqueue_style(
                'yipl-citation-editor-style',
                YIPL_CITATION_URL . 'assets/css/yipl-citation-editor.css',
                array('wp-edit-blocks'),
                null
            );
        }

        public function yipl_allow_custom_tags_gutenberg($allowed, $context) {
            if (is_array($allowed)) {
                $allowed['yipl_citation_placeholder'] = array(); // No attributes allowed
            }
            return $allowed;
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

            add_meta_box(
                'post_yipl_citation_content',
                esc_html__('Citation Footnotes'),
                [$this, 'add_yipl_citation_meta_box'],
                YIPL_CITATION_DATA::$allow_post_type,
                'normal',
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
         * add_yipl_citation_meta_box
         */
        function add_yipl_citation_meta_box($post) {
            $fields_data = get_post_meta($post->ID, 'yipl_citation_list', true);
            wp_nonce_field('save_yipl_citation_list', 'yipl_citation_list_nonce');
?>
            <table id="repeater-table" class="widefat striped" style="table-layout: auto;">
                <thead>
                    <tr>
                        <th>Placeholder Id</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (!empty($fields_data)) {
                        foreach ($fields_data as $field) {
                            echo $this->get_field_row($field);
                        }
                    }
                    ?>
                </tbody>
            </table>
            <p>Place this id in the above content and mark as citation.</p>

            <p>
                <button type="button" class="button button-primary" id="add-repeater-group">
                    Add Citation Footnotes
                </button>
            </p>
        <?php
        }

        public function get_field_row($field) {
            $index = (isset($field['index'])) ? $field['index'] : time();
            $ids = 'yi_citation_' . $index;
            $pre_name = "yipl_citation_list[" . $index . "]";
            ob_start();
        ?>
            <tr class="repeater-group" data-index="<?php echo $index; ?>">

                <td style="max-width: 6rem;">
                    <p><?php echo $ids; ?></p>
                    <div style="display: none;">
                        <input type="hidden" name="<?php echo $pre_name; ?>[ids]" value="<?php echo $ids; ?>">
                        <input type="hidden" name="<?php echo $pre_name; ?>[index]" value="<?php echo $index; ?>">
                    </div>
                </td>
                <td>
                    <?php
                    $editor_id = 'yipl_citation_list_' . $index . '_description';
                    wp_editor(
                        isset($field['description']) ? $field['description'] : '',
                        $editor_id,
                        [
                            'textarea_name' => $pre_name . "[description]",
                            'textarea_rows' => 3,
                            'media_buttons' => false,
                            'teeny' => false,
                            'quicktags' => true,
                        ]
                    );
                    ?>
                </td>
                <td>
                    <button type="button" class="button remove-group">Remove</button>
                </td>
            </tr>
<?php
            $content = ob_get_contents(); //ob_get_clean()
            ob_end_clean();
            return $content;
        }

        /**
         * Example ajax 
         */
        function update_citation_fields() {

            // Verify _nonce
            if (!isset($_POST['_nonce']) || !wp_verify_nonce($_POST['_nonce'], 'citation_fields_row')) {
                echo "Session timeout";
                wp_die();
            }

            // Use timestamp
            echo $this->get_field_row([]);

            // Always die in functions echoing AJAX content
            wp_die();
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

            // yipl_citation_description_field
            if (
                isset($_POST['description_meta_nonce']) &&
                wp_verify_nonce($_POST['description_meta_nonce'], 'save_yipl_citation_descriptino')
            ) {
                if (isset($_POST['yipl_citation_description_field'])) {
                    // update_post_meta($post_id, '_description', sanitize_textarea_field($_POST['yipl_citation_description_field']));
                    update_post_meta($post_id, '_yipl_citation_description', wp_kses_post($_POST['yipl_citation_description_field']));
                }
            }

            if (isset($_POST['yipl_citation_list_nonce']) && wp_verify_nonce($_POST['yipl_citation_list_nonce'], 'save_yipl_citation_list')) {

                if (isset($_POST['yipl_citation_list']) && is_array($_POST['yipl_citation_list'])) {
                    $cleaned = array_map(
                        function ($field) {
                            return [
                                'index' => isset($field['index']) ? intval(preg_replace('/\D/', '', $field['index'])) : 0,
                                'ids' => isset($field['ids']) ? sanitize_text_field($field['ids']) : '',
                                'description' => isset($field['description']) ? wp_kses_post($field['description']) : '',
                            ];
                        },
                        $_POST['yipl_citation_list']
                    );

                    update_post_meta($post_id, 'yipl_citation_list', $cleaned);
                } else {
                    delete_post_meta($post_id, 'yipl_citation_list');
                }
            }
        }

        /**
         * ==== END ====
         */
    }
}
