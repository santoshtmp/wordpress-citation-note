<?php

namespace citenote;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('CITENOTE_Data')) {

    /**
     * CITENOTE_Data
     */
    class CITENOTE_Data {

        /**
         * @var array $citenote_allow_post_type []
         */
        public static $citenote_allow_post_type = []; //['region', 'law', 'population', 'statelessness'];

        /**
         * construction
         */
        function __construct() {

            $this::$citenote_allow_post_type = (get_option('citenote_allow_post_type', [])) ?: [];
            // 
            add_filter('the_content', [$this, 'citenote_update_the_content'], 10, 2);
            add_shortcode('citenote_display_list', [$this, 'citenote_shortcode_display_list']);
        }

        /**
         * 
         */
        public static function citenote_get_allow_post_type() {
            return self::$citenote_allow_post_type;
        }

        /**
         * Add citation tooltips or references to content dynamically.
         * https://developer.wordpress.org/reference/hooks/the_content/
         * 
         */
        public function citenote_update_the_content($content, $post_id = '') {
            try {
                // Check allow post type single page
                if (!is_singular(self::$citenote_allow_post_type)) {
                    return $content;
                }
                // 
                $post_id = ($post_id) ? $post_id : get_the_ID();
                if (!in_array(get_post_type($post_id), self::$citenote_allow_post_type)) {
                    return $content;
                }
                // Get the citation meta info from post meta
                $citenote_list = get_post_meta($post_id, 'citenote_list', true);

                if (!is_array($citenote_list) || empty($citenote_list)) {
                    return $content; // If no citations, return original content
                }
                // uasort($citenote_list, function ($a, $b) {
                //     return (int)$a['row_number'] <=> (int)$b['row_number'];
                // });
                $reindexedArray = [];
                foreach ($citenote_list as $item) {
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
                $citenote_list = $reindexedArray;

                // Get global var and Initialize if not set
                global $citenote_post_citaion_list;
                if (!is_array($citenote_post_citaion_list)) {
                    $citenote_post_citaion_list = [];
                }

                // $pattern = '/<citenote_placeholder>(.*?)<\/citenote_placeholder>/'; // it has 1 group
                $pattern = '/<(citenote(?:_placeholder|placeholder))>(.*?)<\/\1>/s'; // it has 2 group
                // Replace with preg_replace_callback
                $content = preg_replace_callback(
                    $pattern,
                    function ($matches) use (&$citenote_post_citaion_list, $citenote_list, $post_id) {
                        // 
                        $citenote_placeholder = trim($matches[2]);
                        if (!$citenote_placeholder) {
                            return ''; // If placeholder is empty, return empty string
                        }
                        // $match_pattern = '/^\[citation_(\d+)\]$/';
                        $match_pattern = '/^citation_(\d+)$/';
                        if (preg_match($match_pattern, $citenote_placeholder, $matches)) {
                            $row_number = $matches[1];
                            $yi_citation_content = isset($citenote_list[$row_number]) ? $citenote_list[$row_number] : '';
                            if (!empty($yi_citation_content)) {
                                if (!isset($citenote_post_citaion_list[$row_number])) {
                                    $yi_citation_content['post_id'] = $post_id;
                                    $citenote_post_citaion_list[$row_number] = $yi_citation_content;
                                }
                                $replace_content = self::citenote_sup_number($post_id, $row_number); // Add superscript for citation number
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
         * 
         */
        public static function citenote_sup_number($post_id, $number_count) {
            return '<sup id="citenote-ref-' . esc_attr($post_id . '-' . $number_count) . '" class="citenote-reference reference-number" aria-label="Citation ' . esc_attr($post_id . '-' . $number_count) . '">' .
                '<a href="#citenote-' . esc_attr($post_id . '-' . $number_count) . '">' .
                '<span class="cite-bracket">[</span>' .
                esc_html($number_count) .
                '<span class="cite-bracket">]</span>' .
                '</a>' .
                '</sup>';
        }


        /**
         * citenote_footnotes
         * https://developer.wordpress.org/reference/functions/add_shortcode/
         * echo do_shortcode('[citenote_footnotes]');
         */
        public static function citenote_citation_footnotes() {
            global $citenote_post_citaion_list;
            $output = '';
            if (!empty($citenote_post_citaion_list)) {
                $footer_title = get_option('citenote_footer_title', '');
                wp_enqueue_style('citation-note-style');
                $output .= '<div class="citation-note-wrapper container">';
                if ($footer_title) {
                    $output .= '<div class="citation-note-footer-title">' . $footer_title . '</div>';
                }
                $output .= '<div class="citation-note-list">';
                ksort($citenote_post_citaion_list); // Sort by row number
                foreach ($citenote_post_citaion_list as $key => $values) {
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
                    $output .= '<div id="citenote-' . esc_attr($post_id . '-' . $row_number) . '"><div class="single-citenote-wrap"><span class="row-number">' . $row_number . '.</span><a class="citenote-uplink" href="#citenote-ref-' . esc_attr($post_id . '-' . $row_number) . '">^</a><div class="citation-note-description">' . $description . '</div></div></div>';
                }
                $output .= '</div>';
                $output .= '</div>';
                $citenote_post_citaion_list = []; // Clear the global variable after use
            }
            return $output;
        }

        /**
         * 
         */
        public function citenote_shortcode_display_list() {
            return self::citenote_citation_footnotes();
        }

        /**
         * ============ END ================
         */
    }
}
