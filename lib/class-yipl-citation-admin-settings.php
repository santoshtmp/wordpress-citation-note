<?php

namespace yiplcifo;

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('YIPLCIFO_Admin_Settings')) {

    /**
     * YIPLCIFO_Admin_Settings
     */
    class YIPLCIFO_Admin_Settings {

        public static $yiplcifo_fields_group = 'yiplcifo_settings';
        public static $yiplcifo_admin_page_slug = "yipl-citation-settings";

        /**
         * construction
         */
        function __construct() {
            add_filter('plugin_action_links_' . YIPLCIFO_PLUGIN_BASENAME, [$this, 'yiplcifo_settings_link']);
            add_action('admin_init', [$this, 'yiplcifo_register_settings_fields']);
            add_action('admin_menu', [$this, 'yiplcifo_settings_submenu']);
        }

        /**
         * Get the URL for the settings page
         *
         * @return string The URL for the settings page
         */
        public static function yiplcifo_get_settings_page_url() {
            return 'options-general.php?page=' . self::$yiplcifo_admin_page_slug;
        }

        // Hook into the plugin action links filter
        public function yiplcifo_settings_link($links) {
            // Create the settings link
            $settings_link = '<a href="' . self::yiplcifo_get_settings_page_url() . '">Settings</a>';
            // Append the link to the existing links array
            array_unshift($links, $settings_link);
            return $links;
        }
        /**
         * Register settings fields
         */
        function yiplcifo_register_settings_fields() {
            // register_setting( 'yiplcifofo_settings', 'yiplcifofo_user_id', ... );
            register_setting(
                self::$yiplcifo_fields_group,
                'yiplcifo_allow_post_type',
                [
                    'type' => 'array',
                    'sanitize_callback' => function ($input) {
                        return array_map('sanitize_text_field', (array) $input);
                    },
                    'default' => []
                ]
            );
            register_setting(
                self::$yiplcifo_fields_group,
                'yiplcifo_footer_title',
                [
                    'type' => 'string',
                    'sanitize_callback' => 'sanitize_text_field',
                    'default' => ''
                ]
            );
        }
        /**
         * 
         */
        function yiplcifo_settings_submenu() {
            add_options_page(
                'YIPL Citation General Settings Required ', // Page title.
                'Citation Settings ', // Menu title.
                'manage_options',     // Capability required to see the menu.
                self::$yiplcifo_admin_page_slug, // Menu slug.
                [$this, 'yiplcifo_render_settings_page'] // Function to display the page content.
            );
        }


        /**
         * 
         */
        function yiplcifo_render_settings_page() {
            // Register metaboxes right before rendering (since add_meta_boxes won't fire)
            add_meta_box(
                'yipl_citation_general_settings',
                'General Settings',
                [$this, 'yiplcifo_render_general_settings_box'],
                'yipl_citation_settings_page',
                'normal',
                'default'
            );
?>
            <div class="wrap">
                <h1 class="wp-heading-inline">YIPL Citation Settings</h1>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('yiplcifo_settings');
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
        function yiplcifo_render_general_settings_box() {
        ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="yiplcifo_allow_post_type">
                            Select Post Types Page To Allow Citation.
                        </label>
                    </th>
                    <td>
                        <?php
                        $allow_post_type = (get_option('yiplcifo_allow_post_type', [])) ?: [];
                        $post_types = get_post_types(['public' => true], 'objects');
                        unset($post_types['attachment']);
                        foreach ($post_types as $key => $value) {
                        ?>
                            <label for="post-type-<?php echo esc_attr($key); ?>">
                                <input type="checkbox" name="yiplcifo_allow_post_type[]" id="post-type-<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value->name); ?>" <?php checked(in_array($value->name, $allow_post_type)); ?>>
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
                        <label for="yiplcifo_footer_title">General Citation Footer Title</label>
                    </th>
                    <td>
                        <?php $footer_title = get_option('yiplcifo_footer_title', ''); ?>
                        <input type="text" name="yiplcifo_footer_title" id="yiplcifo_footer_title" value="<?php echo esc_attr($footer_title); ?>" class="regular-text" placeholder="References">
                        <p class="description">This title will appear in the footer citation section.</p>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <p class="description">
                            The short code to display the citation footnotes in the footer section is '[yipl_citation_footnotes]':
                        <pre>echo do_shortcode('[yipl_citation_footnotes]');</pre>
                        </p>
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
