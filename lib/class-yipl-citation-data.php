<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

if (! class_exists('YIPL_CITATION_DATA')) {

    /**
     * YIPL_CITATION_DATA
     */
    class YIPL_CITATION_DATA {

        /**
         * @var array $allow_post_type []
         */
        public static $allow_post_type = []; //['region', 'law', 'population', 'statelessness'];

        /**
         * construction
         */
        function __construct() {

            $this::$allow_post_type = (get_option('yipl_citation_allow_post_type', [])) ?: [];
            // 
            add_filter('the_content', [$this, 'yipl_citation_update_the_content'], 10, 2);
            add_shortcode('yipl_citation_footnotes', [$this, 'yipl_citation_footnotes_shortcode']);
        }

        /**
         * 
         */
        public static function get_allow_post_type() {
            return self::$allow_post_type;
        }


        /**
         * get_yipl_citation_post
         * get all publish data in ASC menu_order 
         */
        public static function get_yipl_citation_post($yipl_citation_ids = '') {
            $yipl_citation = [];
            $yipl_citation_arg = array(
                'post_type' => YIPL_CITATION_POST_TYPE::$post_slug,
                'posts_per_page' => -1,
                'post_status' => array('publish'),
                'orderby' => 'menu_order',
                'order' => 'ASC'
            );
            if ($yipl_citation_ids) {
                if (is_string($yipl_citation_ids) || is_int($yipl_citation_ids)) {
                    $yipl_citation_ids = array_map('intval', explode(',', $yipl_citation_ids));
                }
                if (is_array($yipl_citation_ids)) {
                    $yipl_citation_arg['post__in'] = $yipl_citation_ids;
                } else {
                    return '';
                }
            }
            $yipl_citation_posts = get_posts($yipl_citation_arg);
            foreach ($yipl_citation_posts  as $key => $value) {
                $term = get_the_title($value->ID);
                if ($term) {
                    $yipl_citation[$value->ID] = [
                        'id' => $value->ID,
                        'slug' => $value->post_name,
                        'term' => $term,
                        'description' => get_post_meta($value->ID, '_yipl_citation_description', true),
                    ];
                }
            }
            return $yipl_citation;
        }

        /**
         * Add citation tooltips or references to content dynamically.
         * https://developer.wordpress.org/reference/hooks/the_content/
         * 
         */
        public function yipl_citation_update_the_content($content, $post_id = '') {
            try {
                // Check allow post type single page
                if (!is_singular(self::$allow_post_type)) {
                    return $content;
                }
                // 
                $post_id = ($post_id) ? $post_id : get_the_ID();
                if (!in_array(get_post_type($post_id), self::$allow_post_type)) {
                    return $content;
                }
                $yipl_citation_list = get_post_meta($post_id, 'yipl_citation_list', true);
                // Get global var and Initialize if not set
                global $global_yipl_citation_words;
                if (!is_array($global_yipl_citation_words)) {
                    $global_yipl_citation_words = [];
                }

                $pattern = '/<yipl_citation_placeholder>(.*?)<\/yipl_citation_placeholder>/';
                // Replace with preg_replace_callback
                $content = preg_replace_callback(
                    $pattern,
                    function ($matches) use (&$global_yipl_citation_words, $yipl_citation_list) {
                        $yipl_citation_placeholder = trim($matches[1]);
                        if (preg_match('/^yi_citation_post_(\d+)$/', $yipl_citation_placeholder, $matches)) {
                            $yipl_citation_post_id = $matches[1];
                            $yipl_citation_list = self::get_yipl_citation_post($yipl_citation_post_id);
                            $yi_citation_content = $yipl_citation_list[$yipl_citation_post_id];
                        } else if (preg_match('/^yi_citation_(\d+)$/', $yipl_citation_placeholder, $matches)) {
                            $yipl_citation = $matches[1];
                            $yi_citation_content = $yipl_citation_list[$yipl_citation];
                        } else {
                            $yi_citation_content =  '';
                        }
                        if (!isset($global_yipl_citation_words[$yipl_citation_placeholder])) {
                            $global_yipl_citation_words[$yipl_citation_placeholder] = $yi_citation_content;
                        }
                        $keys = array_keys($global_yipl_citation_words);
                        $count = array_search($yipl_citation_placeholder, $keys) + 1;
                        $replace_content = self::yipl_citaion_sup_number($count); // Add superscript for citation number

                        return $replace_content;
                    },
                    $content
                );
            } catch (\Throwable $th) {
                //throw $th;
            }
            return $content;
        }


        /**
         * yipl_citaion_sup_num_content
         */
        public static function yipl_citaion_sup_number($number_count) {
            return '<sup id="cite_ref-' . esc_attr($number_count) . '" class="reference" aria-label="Citation ' . esc_attr($number_count) . '">' .
                '<a href="#yipl-citation-' . esc_attr($number_count) . '">' .
                '<span class="cite-bracket">[</span>' .
                esc_html($number_count) .
                '<span class="cite-bracket">]</span>' .
                '</a>' .
                '</sup>';
        }


        /**
         * yipl_citation_footnotes
         * https://developer.wordpress.org/reference/functions/add_shortcode/
         * echo do_shortcode('[yipl_citation_footnotes]');
         */
        public static function yipl_citation_footnotes() {
            global $global_yipl_citation_words;
            $output = '';
            if (!empty($global_yipl_citation_words)) {
                wp_enqueue_style('yipl-citation-style');
                $output .= '<div class="yipl-citations-wrapper container">';
                $output .= '<ol class="yipl-citations-list">';
                $id = 1;
                foreach ($global_yipl_citation_words as $placeolder => $values) {
                    $description = isset($values['description']) ? $values['description'] : '';
                    $output .= '<li id="yipl-citation-' . esc_attr($id) . '"> <div class="yipl-citation-description">' . $description . '</div></li>';
                    $id++;
                }
                $output .= '</ol>';
                $output .= '</div>';
            }
            return $output;
        }

        /**
         * 
         */
        public function yipl_citation_footnotes_shortcode() {
            return self::yipl_citation_footnotes();
        }

        /**
         * ============ END ================
         */
    }
}
