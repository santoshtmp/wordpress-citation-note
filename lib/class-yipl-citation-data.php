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
                if(!is_array($yipl_citation_list) || empty($yipl_citation_list)) {
                    return $content; // If no citations, return original content
                }
                // uasort($yipl_citation_list, function ($a, $b) {
                //     return (int)$a['row_number'] <=> (int)$b['row_number'];
                // });
                $reindexedArray = [];
                foreach ($yipl_citation_list as $item) {
                    $reindexedArray[$item['row_number']] = $item;
                }
                $yipl_citation_list = $reindexedArray;

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
                        if (!$yipl_citation_placeholder) {
                            return ''; // If placeholder is empty, return empty string
                        }
                        if (preg_match('/^yi_citation_(\d+)$/', $yipl_citation_placeholder, $matches)) {
                            $row_number = $matches[1];
                            $yi_citation_content = isset($yipl_citation_list[$row_number]) ? $yipl_citation_list[$row_number] : '';
                            if (!empty($yi_citation_content)) {
                                if (!isset($global_yipl_citation_words[$row_number])) {
                                    $global_yipl_citation_words[$row_number] = $yi_citation_content;
                                }
                                $replace_content = self::yipl_citaion_sup_number($row_number); // Add superscript for citation number
                                return $replace_content;
                            }
                        }
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
            return '<sup id="yipl-citation-ref-' . esc_attr($number_count) . '" class="reference" aria-label="Citation ' . esc_attr($number_count) . '">' .
                '<a href="#yipl-citation-note-' . esc_attr($number_count) . '">' .
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
                $footer_title = get_option('yipl_citation_footer_title', '');
                wp_enqueue_style('yipl-citation-style');
                $output .= '<div class="yipl-citations-wrapper container">';
                if ($footer_title) {
                    $output .= '<div class="yipl-citation-footer-title">' . $footer_title . '</div>';
                }
                $output .= '<div class="yipl-citations-list">';
                foreach ($global_yipl_citation_words as $row_number => $values) {
                    if (is_array($values)) {
                        $description = isset($values['description']) ? $values['description'] : '';
                    } elseif (is_string($values)) {
                        $description = $values;
                    } else {
                        $description = '';
                    }
                    $description = trim($description);
                    $output .= '<div id="yipl-citation-note-' . esc_attr($row_number) . '"><div class="single-yipl-note-wrap"><span class="row-number">' . $row_number . '.</span><a href="#yipl-citation-ref-' . esc_attr($row_number) . '">^</a><div class="yipl-citation-description">' . $description . '</div></div></div>';
                }
                $output .= '</div>';
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
