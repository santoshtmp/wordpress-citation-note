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
            add_action('wp_ajax_update_citation_fields', [$this, 'ajax_update_citation_fields']);
            add_action('save_post', [$this, 'yipl_citation_save_metabox_fields_data']);
        }

        /**
         * 
         */
        public function enqueue_yipl_citation_editor_assets() {
            $allow_citation = false;
            if (in_array(get_post_type(), YIPL_CITATION_DATA::$allow_post_type)) {
                $allow_citation = true;
                wp_enqueue_script(
                    'yipl-citation-editor-script',
                    YIPL_CITATION_URL . 'assets/js/yipl-citation-editor.js',
                    ['wp-rich-text', 'wp-editor', 'wp-block-editor', 'wp-element', 'wp-components', 'jquery', 'jquery-ui-sortable'],
                    filemtime(YIPL_CITATION_PATH . 'assets/js/yipl-citation-editor.js'),
                    true
                );
                wp_localize_script('yipl-citation-editor-script', 'ajax_object', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'action_yipl_citation_fields' => 'update_citation_fields',
                    'nonce'    => wp_create_nonce('citation_fields_row'),
                    'allow_citation' => $allow_citation,
                ]);
                wp_enqueue_style(
                    'yipl-citation-editor-style',
                    YIPL_CITATION_URL . 'assets/css/yipl-citation-editor.css',
                    array('wp-edit-blocks'),
                    filemtime(YIPL_CITATION_PATH . 'assets/css/yipl-citation-editor.css')
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
         * Add Custom meata box in the post type
         * https://developer.wordpress.org/reference/hooks/add_meta_boxes/
         */
        public function yipl_citation_metabox_fields() {

            add_meta_box(
                'post_yipl_citation_content',
                esc_html__('Citation Footnotes', 'yipl-citation'),
                [$this, 'add_yipl_citation_meta_box'],
                YIPL_CITATION_DATA::$allow_post_type,
                'normal',
            );
        }

        /**
         * add_yipl_citation_meta_box
         */
        function add_yipl_citation_meta_box($post) {
            $fields_data = get_post_meta($post->ID, 'yipl_citation_list', true);
            wp_nonce_field('save_yipl_citation_list', 'yipl_citation_list_nonce');
?>
            <div class="yipl-citation--info">
                <?php
                if (is_array($fields_data) && $fields_data) { ?>
                    <p style="float: right; margin-right: 1rem;">
                        <button type="button" class="button" id="yipl-collapse-all">Collapse All</button>
                        <button type="button" class="button" id="yipl-expand-all">Expand All</button>
                    </p>
                <?php } ?>
            </div>
            <table id="yipl-citation-repeater-table" class="widefat striped" style="table-layout: auto;">
                <thead>
                    <tr>
                        <th>SN</th>
                        <th>Description</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if (is_array($fields_data)) {

                        uasort($fields_data, function ($a, $b) {
                            return $a['row_number'] <=> $b['row_number'];
                        });
                        foreach ($fields_data as $field) {
                            echo $this->get_field_row($field);
                        }
                    }
                    ?>
                </tbody>
            </table>
            <p>Place this id in the above content and mark as citation.</p>

            <p>
                <button type="button" class="button button-primary" id="yipl-citation-add-repeater-group">
                    Add Citation Footnotes
                </button>
            </p>
            <p>
                <label for="yipl_citation_published_citation_list">
                    <?php
                    $yipl_citation_published_list = get_post_meta($post->ID, 'yipl_citation_published_list', true);
                    $yipl_citation_published_list = ($yipl_citation_published_list === '') ? '1' : $yipl_citation_published_list;
                    ?>
                    <input type="checkbox" name="yipl_citation_published_list" id="yipl_citation_published_citation_list" value="1" <?php checked($yipl_citation_published_list, '1'); ?>>
                    Publish Citation List
                </label>

            </p>
            <p> Use shortcode '[yipl_citation_footnotes]' to display the citation footnotes. </p>
        <?php
        }

        /**
         * Example ajax 
         */
        function ajax_update_citation_fields() {

            // Verify _nonce
            if (!isset($_POST['_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_nonce'])), 'citation_fields_row')) {
                echo "Session timeout";
                wp_die();
            }


            // Use timestamp
            echo $this->get_field_row([]);

            // Always die in functions echoing AJAX content
            wp_die();
        }

        //
        public function get_field_row($field) {
            $index = (isset($field['index'])) ? $field['index'] : time();
            $index = ($index) ? $index : time();
            $row_number = (isset($field['row_number'])) ? $field['row_number'] : '';
            $pre_name = "yipl_citation_list[" . esc_attr($index) . "]";
            ob_start();
        ?>
            <tr class="repeater-group" data-index="<?php echo esc_attr($index); ?>">

                <td class="yipl-citation-row-number-field" style="max-width: 6rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span class="row-drag-handler" title="Drag to reorder" style="cursor: move; margin-right: 0.5rem;">
                            <i class="fas fa-arrows-alt"></i>
                        </span>

                        <div class="info-row-number">
                            <p class="citation-expandable">
                                <input type="text" inputmode="numeric" pattern="^[0-9]$" name="<?php echo esc_attr($pre_name); ?>[row_number]" value="<?php echo esc_attr($row_number); ?>" data-index="<?php echo esc_attr($index); ?>" class="input-row_number" style=" max-width: 100%;">
                            </p>
                            <p class="yi_citation_<?php echo esc_attr($index); ?> " style="margin: auto;">
                                <?php echo 'citation_' . esc_html($row_number); ?>
                            </p>

                        </div>
                    </div>
                    <div style="display: none;">
                        <input type="hidden" name="<?php echo esc_attr($pre_name); ?>[index]" value="<?php echo esc_attr($index); ?>">
                    </div>
                </td>
                <td class="yipl-citation-description-field">
                    <div class="citation-expandable">
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
                    </div>
                    <div class="citation-collapseable" style="display: none; margin: auto;">
                        <p style="max-width: 500px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap; display: block;">
                            <?php
                            echo isset($field['description']) && trim($field['description']) !== ''
                                ? esc_html(wp_strip_all_tags($field['description']))
                                : '<em>.....</em>'; ?>
                        </p>

                    </div>
                </td>
                <td class="yipl-citation-action-field" style="max-width: 6rem;">
                    <button type="button" class="button yipl-citation-remove-group">Remove</button>
                    <button type="button" class="toggle-yi-citation-row button small">Collapse</button>
                </td>
            </tr>
<?php
            $content = ob_get_contents(); //ob_get_clean()
            ob_end_clean();
            return $content;
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
            // 
            if (isset($_POST['yipl_citation_list_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['yipl_citation_list_nonce'])), 'save_yipl_citation_list')) {
                if (isset($_POST['yipl_citation_list']) && is_array($_POST['yipl_citation_list'])) {
                    $raw_data = wp_unslash($_POST['yipl_citation_list']);
                    $cleaned = array_map(
                        function ($field) {
                            // Skip if the description is empty
                            if (empty($field['description']) || trim($field['description']) === '') {
                                return null; // Return null to skip this entry
                            }
                            // 
                            return [
                                'index' => isset($field['index']) ? intval(preg_replace('/\D/', '', $field['index'])) : 0,
                                'row_number' => isset($field['row_number']) ? intval($field['row_number']) : 0,
                                'description' => isset($field['description']) ? wp_kses_post($field['description']) : '',
                            ];
                        },
                        $raw_data
                    );
                    $cleaned = array_filter($cleaned);  // Remove nulls
                    update_post_meta($post_id, 'yipl_citation_list', $cleaned);
                } else {
                    delete_post_meta($post_id, 'yipl_citation_list');
                }

                $published = isset($_POST['yipl_citation_published_list']) ? '1' : '0';
                update_post_meta($post_id, 'yipl_citation_published_list', $published);
            }
        }

        /**
         * ==== END ====
         */
    }
}
