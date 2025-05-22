<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('YIPL_CITATION_ADMIN_SETTINGS')) {

    /**
     * YIPL_CITATION_ADMIN_SETTINGS
     */
    class YIPL_CITATION_ADMIN_SETTINGS {

        public static $fields_group = 'yipl-citation-setting-fields-group';
        public static $admin_page_slug = "yipl-citation-settings";

        /**
         * construction
         */
        function __construct() {
            add_filter('plugin_action_links_' . YIPL_CITATION_BASENAME, [$this, 'yipl_citation_settings_link']);
            add_action('admin_init', [$this, 'yipl_citaion_register_settings_fields']);
            add_action('admin_menu', [$this, 'yipl_citation_settings_submenu']);
        }


        // Hook into the plugin action links filter
        public function yipl_citation_settings_link($links) {
            // Create the settings link
            $settings_link = '<a href="edit.php?post_type=' . YIPL_CITATION_POST_TYPE::$post_slug . '&page=' . self::$admin_page_slug . '">Settings</a>';
            // Append the link to the existing links array
            array_unshift($links, $settings_link);
            return $links;
        }
        /**
         * 
         */
        function yipl_citaion_register_settings_fields() {
            register_setting('yipl_citation_settings_group', 'yipl_citation_allow_post_type');
            register_setting('yipl_citation_settings_group', 'yipl_citation_skip_tags');
            register_setting('yipl_citation_settings_group', 'default_allow_repeated_citation_word');
            register_setting('yipl_citation_settings_group', 'default_auto_detect_citation_word');
        }
        /**
         * 
         */
        function yipl_citation_settings_submenu() {
            add_submenu_page(
                'edit.php?post_type=' . YIPL_CITATION_POST_TYPE::$post_slug,
                'YIPL Citation General Settings',
                'General Settings',
                'manage_options',
                self::$admin_page_slug,
                [$this, 'render_yipl_citation_settings_page']
            );
        }


        /**
         * 
         */
        function render_yipl_citation_settings_page() {
            // Register metaboxes right before rendering (since add_meta_boxes won't fire)
            add_meta_box(
                'yipl_citation_general_settings',
                'General Settings',
                [$this, 'render_yipl_citation_general_settings_box'],
                'yipl_citation_settings_page',
                'normal',
                'default'
            );
?>
            <div class="wrap">
                <h1 class="wp-heading-inline">YIPL Citation Settings</h1>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('yipl_citation_settings_group');
                    ?>
                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">
                            <div id="post-body-content">
                                <?php
                                do_meta_boxes('yipl_citation_settings_page', 'normal', null);
                                submit_button();
                                ?>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        <?php
        }

        /**
         * 
         */
        function render_yipl_citation_general_settings_box() {
        ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="yipl_citation_allow_post_type">
                            Select Post Types Page To Allow Citation.
                        </label>
                    </th>
                    <td>
                        <?php
                        $allow_post_type = (get_option('yipl_citation_allow_post_type', [])) ?: [];
                        $post_types = get_post_types(['public' => true], 'objects');
                        unset($post_types['attachment']);
                        unset($post_types[YIPL_CITATION_POST_TYPE::$post_slug]);
                        foreach ($post_types as $key => $value) {
                        ?>
                            <label for="post-type-<?php echo esc_attr($key); ?>">
                                <input type="checkbox" name="yipl_citation_allow_post_type[]" id="post-type-<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value->name); ?>" <?php checked(in_array($value->name, $allow_post_type)); ?>>
                                <?php echo esc_attr($value->label); ?>
                            </label>
                        <?php
                        }
                        echo '<p class="description">Citation will only be apply to selected post type.</p>';
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="yipl_citation_skip_tags">
                            Select Citation Skip HTML Tags
                        </label>
                    </th>
                    <td>
                        <?php

                        $skip_tags = YIPL_CITATION_DATA::get_skip_tags(true);
                        $selected_skip_tags =  YIPL_CITATION_DATA::get_skip_tags();
                        foreach ($skip_tags as $key => $value) {
                        ?>
                            <label for="skip-tags-<?php echo esc_attr($value); ?>">
                                <input type="checkbox" name="yipl_citation_skip_tags[]" id="skip-tags-<?php echo esc_attr($value); ?>" value="<?php echo esc_attr($value); ?>" <?php checked(in_array($value, $selected_skip_tags)); ?>>
                                <?php echo esc_attr($value); ?>
                            </label>
                        <?php
                        }
                        echo '<p class="description">Citation will not perform citation in selected tags. while "a" link is default tag</p>';
                        ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row">Default Auto Detect Citation Words. </th>
                    <td>
                        <label>
                            <input type="checkbox" name="default_auto_detect_citation_word" value="1" <?php checked(get_option('default_auto_detect_citation_word'), 1); ?>>
                            Enable
                        </label>
                        <?php
                        echo '<p class="description">Apply in allowed post items.</p>';
                        ?>
                    </td>
                </tr>
            </table>
<?php
        }


        /**
         * ====== END =======
         */
    }
}
