<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('YIPL_CITATION_POST_TYPE')) {

    /**
     * YIPL_CITATION_POST_TYPE
     */
    class YIPL_CITATION_POST_TYPE {

        /**
         * 
         */
        public static $post_slug = "yipl_citation";

        /**
         * construction
         */
        function __construct() {

            add_action('init', [$this, 'yipl_citation_post_type']);
            add_filter('manage_yipl_citation_posts_columns', [$this, 'manage_yipl_citation_column']);
            add_action('manage_yipl_citation_posts_custom_column', [$this, 'manage_yipl_citation_column_data'], 10, 2);
            add_filter('wp_insert_post_data', [$this, 'yipl_citation_unique_post_title'], 10, 2);
            add_action('admin_notices', [$this, 'yipl_citation_duplicate_title_notice']);
        }

        /**
         * Define YIPL Citations Post Type
         * https://developer.wordpress.org/reference/functions/register_post_type/
         * 
         */
        public function yipl_citation_post_type() {

            $labels = [
                "name" => esc_html__("Citations"),
                "singular_name" => esc_html__("Citation"),
                "all_items" => esc_html__("All Citations"),
                "add_new" => esc_html__("Add Citation"),
                "add_new_item" => esc_html__("Add Citation"),
                "edit_item" => esc_html__("Edit Citation"),
                "new_item" => esc_html__("New Citation"),
                "view_item" => esc_html__("View Citation"),
                "view_items" => esc_html__("View Citations"),
                "search_items" => esc_html__("Search Citations"),
            ];

            $args = [
                "label" => esc_html__("Citations"),
                "labels" => $labels,
                "description" => "",
                "public" => true,
                "publicly_queryable" => true,
                "show_ui" => true,
                "show_in_rest" => true,
                "rest_base" => "",
                "rest_controller_class" => "WP_REST_Posts_Controller",
                "rest_namespace" => "wp/v2",
                "has_archive" => true,
                "show_in_menu" => true,
                "show_in_nav_menus" => true,
                "delete_with_user" => false,
                "exclude_from_search" => false,
                "capability_type" => "post",
                "map_meta_cap" => true,
                "hierarchical" => false,
                "can_export" => false,
                "rewrite" => ["slug" => self::$post_slug, "with_front" => true],
                "query_var" => true,
                "menu_icon" => "dashicons-open-folder",
                "supports" => ["title"],
                "show_in_graphql" => false,
            ];

            register_post_type(self::$post_slug, $args);
        }

        /**
         * https://developer.wordpress.org/reference/hooks/manage_posts_columns/
         */
        public function manage_yipl_citation_column($columns) {
            $new_columns = array();
            $before = 'date'; // move before this
            foreach ($columns as $key => $value) {
                if ($key == $before) {
                    $new_columns['description'] = __('Description');
                    $new_columns['placeholder_ids'] = __('Placeholder Ids');
                }
                $new_columns[$key] = $value;
            }
            return $new_columns;
        }

        function manage_yipl_citation_column_data($column_name, $post_id) {
            if ('description' === $column_name) {
                echo get_post_meta($post_id, '_yipl_citation_description', true);
            }
            if ('placeholder_ids' === $column_name) {
                echo 'yi_citation_post_' . $post_id;
            }
        }


        /**
         * https://developer.wordpress.org/reference/hooks/wp_insert_post_data/
         */
        public function yipl_citation_unique_post_title($data, $postarr) {
            // Skip autosaves, revisions, and deletions
            if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return $data;
            if (wp_is_post_revision($postarr['ID'])) return $data;
            if ($data['post_status'] == 'trash' || $data['post_status'] == 'draft') {
                return $data;
            }

            // Only apply to your custom post type
            if ($data['post_type'] !== self::$post_slug) {
                return $data;
            }

            $title = $data['post_title'];

            // Query for existing post with the same title
            $args = [
                'post_type'      => self::$post_slug,
                'title'          => $title,
                'posts_per_page' => 1,
                'post_status'    => 'any',
                'fields'         => 'ids',
            ];
            if (isset($postarr['ID']) && $postarr['ID']) {
                $args['post__not_in'] = [$postarr['ID']];
            }
            $existing_query = new WP_Query($args);

            if (!empty($existing_query->posts)) {
                // Set admin notice
                set_transient('duplicate_title_notice_' . get_current_user_id(), 'YIPL Citation with the same title already exists. Please use a unique title.', 30);

                // Empty title to prevent saving
                $data['post_title'] = '';
            }
            return $data;
        }

        /**
         * admin_notices_area
         * https://developer.wordpress.org/reference/hooks/admin_notices/
         */
        public function yipl_citation_duplicate_title_notice() {
            $notice = get_transient('duplicate_title_notice_' . get_current_user_id());

            if ($notice) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($notice) . '</p></div>';
                delete_transient('duplicate_title_notice_' . get_current_user_id());
            }
        }


        /**
         * ============= END =============
         */
    }
}
