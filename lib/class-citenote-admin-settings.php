<?php

namespace citenote;

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('CITENOTE_Admin_Settings')) {

    /**
     * CITENOTE_Admin_Settings
     */
    class CITENOTE_Admin_Settings {

        public static $citenote_fields_group = 'citenote_settings';
        public static $citenote_admin_page_slug = "citenote";

        /**
         * construction
         */
        function __construct() {
            add_filter('plugin_action_links_' . CITENOTE_PLUGIN_BASENAME, [$this, 'citenote_settings_link']);
            add_action('admin_init', [$this, 'citenote_register_settings_fields']);
            add_action('admin_menu', [$this, 'citenote_settings_submenu']);
        }

        /**
         * Get the URL for the settings page
         *
         * @return string The URL for the settings page
         */
        public static function citenote_get_settings_page_url() {
            return 'options-general.php?page=' . self::$citenote_admin_page_slug;
        }

        // Hook into the plugin action links filter
        public function citenote_settings_link($links) {
            // Create the settings link
            $settings_link = '<a href="' . self::citenote_get_settings_page_url() . '">Settings</a>';
            // Append the link to the existing links array
            array_unshift($links, $settings_link);
            return $links;
        }
        /**
         * Register settings fields
         */
        function citenote_register_settings_fields() {
            register_setting(
                self::$citenote_fields_group,
                'citenote_allow_post_type',
                [
                    'type' => 'array',
                    'sanitize_callback' => function ($input) {
                        return array_map('sanitize_text_field', (array) $input);
                    },
                    'default' => []
                ]
            );
            register_setting(
                self::$citenote_fields_group,
                'citenote_footer_title',
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
        function citenote_settings_submenu() {
            add_options_page(
                'Citation Note Settings', // Page title.
                'Citation Note ', // Menu title.
                'manage_options',     // Capability required to see the menu.
                self::$citenote_admin_page_slug, // Menu slug.
                [$this, 'citenote_render_settings_page'] // Function to display the page content.
            );
        }


        /**
         * 
         */
        function citenote_render_settings_page() {
            // Register metaboxes right before rendering (since add_meta_boxes won't fire)
            add_meta_box(
                'citenote_general_settings',
                'General Settings',
                [$this, 'citenote_render_general_settings_box'],
                self::$citenote_admin_page_slug,
                'normal',
                'default'
            );
?>
            <div class="wrap">
                <h1 class="wp-heading-inline">Citation Note Settings</h1>
                <form method="post" action="options.php">
                    <?php
                    settings_fields('citenote_settings');
                    ?>
                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">
                            <div id="post-body-content">
                                <?php
                                do_meta_boxes(self::$citenote_admin_page_slug, 'normal', null);
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
        function citenote_render_general_settings_box() {
        ?>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row">
                        <label for="citenote_allow_post_type">
                            Select Post Types Page To Allow Citation.
                        </label>
                    </th>
                    <td>
                        <?php
                        $allow_post_type = (get_option('citenote_allow_post_type', [])) ?: [];
                        $post_types = get_post_types(['public' => true], 'objects');
                        unset($post_types['attachment']);
                        foreach ($post_types as $key => $value) {
                        ?>
                            <label for="post-type-<?php echo esc_attr($key); ?>">
                                <input type="checkbox" name="citenote_allow_post_type[]" id="post-type-<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($value->name); ?>" <?php checked(in_array($value->name, $allow_post_type)); ?>>
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
                        <label for="citenote_footer_title">General Citation Footer Title</label>
                    </th>
                    <td>
                        <?php $footer_title = get_option('citenote_footer_title', ''); ?>
                        <input type="text" name="citenote_footer_title" id="citenote_footer_title" value="<?php echo esc_attr($footer_title); ?>" class="regular-text" placeholder="References">
                        <p class="description">This title will appear in the footer citation section.</p>
                    </td>
                </tr>
                <tr>
                    <th></th>
                    <td>
                        <p class="description">
                            The short code to display the citation footnotes in the footer section is '[citenote_display_list]':
                        <pre>echo do_shortcode('[citenote_display_list]');</pre>
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
