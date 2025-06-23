<?php

namespace yiplcifo;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('YIPLCIFO_Data')) {

    /**
     * YIPLCIFO_Data
     */
    class YIPLCIFO_Data {

        /**
         * @var array $yiplcifo_allow_post_type []
         */
        public static $yiplcifo_allow_post_type = []; //['region', 'law', 'population', 'statelessness'];

        /**
         * construction
         */
        function __construct() {

            $this::$yiplcifo_allow_post_type = (get_option('yiplcifo_allow_post_type', [])) ?: [];
            // 
            add_filter('the_content', [$this, 'yiplcifo_update_the_content'], 10, 2);
            add_shortcode('yipl_citation_footnotes', [$this, 'yiplcifo_shortcode_yipl_citation_footnotes']);
        }

        /**
         * 
         */
        public static function yiplcifo_get_allow_post_type() {
            return self::$yiplcifo_allow_post_type;
        }

        /**
         * Add citation tooltips or references to content dynamically.
         * https://developer.wordpress.org/reference/hooks/the_content/
         * 
         */
        public function yiplcifo_update_the_content($content, $post_id = '') {
            try {
                // Check allow post type single page
                if (!is_singular(self::$yiplcifo_allow_post_type)) {
                    return $content;
                }
                // 
                $post_id = ($post_id) ? $post_id : get_the_ID();
                if (!in_array(get_post_type($post_id), self::$yiplcifo_allow_post_type)) {
                    return $content;
                }
                // Get the citation meta info from post meta
                $yipl_citation_list = get_post_meta($post_id, 'yipl_citation_list', true);

                if (!is_array($yipl_citation_list) || empty($yipl_citation_list)) {
                    return $content; // If no citations, return original content
                }
                // uasort($yipl_citation_list, function ($a, $b) {
                //     return (int)$a['row_number'] <=> (int)$b['row_number'];
                // });
                $reindexedArray = [];
                foreach ($yipl_citation_list as $item) {
                    if (!isset($item['row_number']) || !is_numeric($item['row_number'])) {
                        continue; // Skip items without a valid row number
                    }
                    $item['row_number'] = (int) $item['row_number']; // Ensure row_number is an integer
                    if ($item['row_number'] <= 0) {
                        continue; // Skip items with row_number less than or equal to 0
                    }
                    // Reindex the array using row_number as the key
                    if (isset($reindexedArray[$item['row_number']])) {
                        // If a duplicate row_number exists, you can handle it as needed
                        // For example, you might want to skip it or log a warning
                        continue; // Skip duplicates
                    }
                    // Ensure row_number is a valid integer and reindex
                    $reindexedArray[$item['row_number']] = $item;
                }
                $yipl_citation_list = $reindexedArray;

                // Get global var and Initialize if not set
                global $yiplcifo_post_citaion_list;
                if (!is_array($yiplcifo_post_citaion_list)) {
                    $yiplcifo_post_citaion_list = [];
                }

                $pattern = '/<yipl_citation_placeholder>(.*?)<\/yipl_citation_placeholder>/';
                // Replace with preg_replace_callback
                $content = preg_replace_callback(
                    $pattern,
                    function ($matches) use (&$yiplcifo_post_citaion_list, $yipl_citation_list, $post_id) {
                        // 
                        $yipl_citation_placeholder = trim($matches[1]);
                        if (!$yipl_citation_placeholder) {
                            return ''; // If placeholder is empty, return empty string
                        }
                        // $match_pattern = '/^\[citation_(\d+)\]$/';
                        $match_pattern = '/^citation_(\d+)$/';
                        if (preg_match($match_pattern, $yipl_citation_placeholder, $matches)) {
                            $row_number = $matches[1];
                            $yi_citation_content = isset($yipl_citation_list[$row_number]) ? $yipl_citation_list[$row_number] : '';
                            if (!empty($yi_citation_content)) {
                                if (!isset($yiplcifo_post_citaion_list[$row_number])) {
                                    $yi_citation_content['post_id'] = $post_id;
                                    $yiplcifo_post_citaion_list[$row_number] = $yi_citation_content;
                                }
                                $replace_content = self::yiplcifo_sup_number($post_id, $row_number); // Add superscript for citation number
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
        public static function yiplcifo_sup_number($post_id, $number_count) {
            return '<sup id="yiplcifo-ref-' . esc_attr($post_id . '-' . $number_count) . '" class="yiplcifo-reference reference-number" aria-label="Citation ' . esc_attr($post_id . '-' . $number_count) . '">' .
                '<a href="#yiplcifo-note-' . esc_attr($post_id . '-' . $number_count) . '">' .
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
        public static function yiplcifo_citation_footnotes() {
            global $yiplcifo_post_citaion_list;
            $output = '';
            if (!empty($yiplcifo_post_citaion_list)) {
                $footer_title = get_option('yiplcifo_footer_title', '');
                wp_enqueue_style('yipl-citation-style');
                $output .= '<div class="yipl-citations-wrapper container">';
                if ($footer_title) {
                    $output .= '<div class="yipl-citation-footer-title">' . $footer_title . '</div>';
                }
                $output .= '<div class="yipl-citations-list">';
                ksort($yiplcifo_post_citaion_list); // Sort by row number
                foreach ($yiplcifo_post_citaion_list as $key => $values) {
                    $row_number = $key;
                    $post_id = get_the_ID();
                    if (is_array($values)) {
                        $description = isset($values['description']) ? $values['description'] : '';
                        $row_number = isset($values['row_number']) ? $values['row_number'] : $row_number;
                        $post_id = isset($values['post_id']) ? $values['post_id'] : $post_id;
                    } elseif (is_string($values)) {
                        $description = $values;
                    } else {
                        $description = '';
                    }
                    $description = trim($description);
                    $output .= '<div id="yiplcifo-note-' . esc_attr($post_id . '-' . $row_number) . '"><div class="single-yipl-note-wrap"><span class="row-number">' . $row_number . '.</span><a class="yiplcifo-uplink" href="#yiplcifo-ref-' . esc_attr($post_id . '-' . $row_number) . '">^</a><div class="yipl-citation-description">' . $description . '</div></div></div>';
                }
                $output .= '</div>';
                $output .= '</div>';
                $yiplcifo_post_citaion_list = []; // Clear the global variable after use
            }
            return $output;
        }

        /**
         * 
         */
        public function yiplcifo_shortcode_yipl_citation_footnotes() {
            return self::yiplcifo_citation_footnotes();
        }

        /**
         * ============ END ================
         */
    }
}
