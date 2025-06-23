<?php

namespace citenote;

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('CITENOTE_Editor_Fields')) {

    /**
     * CITENOTE_Editor_Fields
     */
    class CITENOTE_Editor_Fields {


        /**
         * construction
         */
        function __construct() {
            // 
            add_action('enqueue_block_editor_assets', [$this, 'citenote_enqueue_block_editor_assets']);
            add_filter('wp_kses_allowed_html', [$this, 'citenote_wp_kses_allowed_html'], 10, 2);
            add_action('add_meta_boxes', [$this, 'citenote_add_meta_boxes']);
            add_action('wp_ajax_updateCitationEditField', [$this, 'citenote_updateCitationEditField']);
            add_action('save_post', [$this, 'citenote_save_post']);
        }

        /**
         * 
         */
        public function citenote_enqueue_block_editor_assets() {
            $allow_citation = false;
            if (in_array(get_post_type(), CITENOTE_Data::$citenote_allow_post_type)) {
                $allow_citation = true;
                wp_enqueue_script(
                    'citation-note-editor-script',
                    CITENOTE_PLUGIN_URL . 'assets/js/citation-note-editor.js',
                    ['wp-rich-text', 'wp-editor', 'wp-block-editor', 'wp-element', 'wp-components', 'jquery', 'jquery-ui-sortable'],
                    filemtime(CITENOTE_PLUGIN_DIR . 'assets/js/citation-note-editor.js'),
                    array(
                        'in_footer' => true,
                        'strategy' => 'defer',
                    )
                );
                wp_localize_script('citation-note-editor-script', 'citenoteAjax', [
                    'ajax_url' => admin_url('admin-ajax.php'),
                    'action_name' => 'updateCitationEditField',
                    'nonce'    => wp_create_nonce('citation_fields_row'),
                    'allow_citation' => $allow_citation,
                ]);
                wp_enqueue_style(
                    'citation-note-editor-style',
                    CITENOTE_PLUGIN_URL . 'assets/css/citation-note-editor.css',
                    array('wp-edit-blocks'),
                    filemtime(CITENOTE_PLUGIN_DIR . 'assets/css/citation-note-editor.css'),
                    'all'
                );
            }
        }

        /**
         * 
         */
        public function citenote_wp_kses_allowed_html($allowed, $context) {
            if (is_array($allowed)) {
                $allowed['citenote_placeholder'] = array(); // No attributes allowed
            }
            return $allowed;
        }

        /**
         * Add Custom meata box in the post type
         * https://developer.wordpress.org/reference/hooks/add_meta_boxes/
         */
        public function citenote_add_meta_boxes() {
            if (CITENOTE_Data::$citenote_allow_post_type) {
                add_meta_box(
                    'post_citenote_content',
                    esc_html__('Citation Footnotes', 'citation-note'),
                    [$this, 'citenote_add_citenote_meta_box'],
                    CITENOTE_Data::$citenote_allow_post_type,
                    'normal',
                );
            }
        }

        /**
         * add_citenote_meta_box
         */
        function citenote_add_citenote_meta_box($post) {
            $fields_data = get_post_meta($post->ID, 'citenote_list', true);
            wp_nonce_field('save_citenote_list', 'citenote_list_nonce'); ?>
            <div class="citation-note--info">
                <?php
                if (is_array($fields_data) && $fields_data) { ?>
                    <p style="float: right; margin-right: 1rem;">
                        <button type="button" class="button" id="citenote-collapse-all">Collapse All</button>
                        <button type="button" class="button" id="citenote-expand-all">Expand All</button>
                    </p>
                <?php } ?>
            </div>
            <table id="citation-note-repeater-table" class="widefat striped" style="table-layout: auto;">
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
                            $this->citenote_get_field_row($field);
                        }
                    }
                    ?>
                </tbody>
            </table>
            <p>Place this id in the above content and mark as citation.</p>

            <p>
                <button type="button" class="button button-primary" id="citation-note-add-repeater-group">
                    Add Citation Footnotes
                </button>
            </p>
            <p> Use shortcode '[citenote_display_list]' to display the citation footnotes. </p>
        <?php
        }

        /**
         * Example ajax 
         */
        function citenote_updateCitationEditField() {

            // Verify _nonce
            if (!isset($_POST['_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['_nonce'])), 'citation_fields_row')) {
                echo "Session timeout";
                wp_die();
            }

            // Use timestamp
            $this->citenote_get_field_row([]);

            // Always die in functions echoing AJAX content
            wp_die();
        }

        //
        public function citenote_get_field_row($field) {
            $index = (isset($field['index'])) ? $field['index'] : time();
            $index = ($index) ? $index : time();
            $row_number = (isset($field['row_number'])) ? $field['row_number'] : '';
            $pre_name = "citenote_list[" . esc_attr($index) . "]";
        ?>
            <tr class="repeater-group" data-index="<?php echo esc_attr($index); ?>">

                <td class="citation-note-row-number-field" style="max-width: 6rem;">
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
                <td class="citation-note-description-field">
                    <div class="citation-expandable">
                        <?php
                        $editor_id = 'citenote_list_' . $index . '_description';
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
                <td class="citation-note-action-field" style="max-width: 6rem;">
                    <button type="button" class="button citation-note-remove-group">Remove</button>
                    <button type="button" class="toggle-yi-citation-row button small">Collapse</button>
                </td>
            </tr>
<?php
        }


        /**
         * Save post 
         * https://developer.wordpress.org/reference/hooks/save_post/
         * 
         */
        public function citenote_save_post($post_id) {

            // Skip autosaves, revisions, and deletions
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
                return;
            }
            // 
            if (isset($_POST['citenote_list_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['citenote_list_nonce'])), 'save_citenote_list')) {
                if (isset($_POST['citenote_list']) && is_array($_POST['citenote_list'])) {
                    $raw_citation_list = (array)wp_unslash($_POST['citenote_list']);
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
                        $raw_citation_list
                    );
                    $cleaned = array_filter($cleaned);  // Remove nulls
                    update_post_meta($post_id, 'citenote_list', $cleaned);
                } else {
                    delete_post_meta($post_id, 'citenote_list');
                }
            }
        }

        /**
         * ==== END ====
         */
    }
}
