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
            add_filter('the_content', [$this, 'yipl_citation_update_the_content']);
            add_shortcode('yipl_citation_footnotes', [$this, 'yipl_citation_footnotes_shortcode']);
        }

        /**
         * 
         */
        public static function get_allow_post_type() {
            return self::$allow_post_type;
        }

        /**
         * 
         */
        public static function get_skip_tags($default = false) {
            $skip_tags = ['h1', 'h2', 'h3', 'h4', 'h5', 'span'];
            if ($default) {
                return $skip_tags;
            }
            $skip_tags = (get_option('yipl_citation_skip_tags', [])) ?: $skip_tags;
            $skip_tags = array_merge($skip_tags, ['code', 'pre', 'a', 'script', 'style', 'sup']);
            return $skip_tags;
        }



        /**
         * get_all_yipl_citation
         * get all publish data in ASC menu_order 
         */
        public static function get_all_yipl_citation($yipl_citation_slug = '') {
            $yipl_citation = [];
            $yipl_citation_arg = array(
                'post_type' => YIPL_CITATION_POST_TYPE::$post_slug,
                'posts_per_page' => -1,
                'post_status' => array('publish'),
                'orderby' => 'menu_order',
                'order' => 'ASC'
            );
            if ($yipl_citation_slug && is_string($yipl_citation_slug)) {
                $yipl_citation_arg['name'] = $yipl_citation_slug;
            }
            if (is_array($yipl_citation_slug) && count($yipl_citation_slug)) {
                $yipl_citation_arg['post_name__in'] = $yipl_citation_slug;
            }
            $yipl_citation_posts = get_posts($yipl_citation_arg);
            foreach ($yipl_citation_posts  as $key => $value) {
                $term = get_the_title($value->ID);
                if ($term) {
                    $yipl_citation[$term] = [
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
        public function yipl_citation_update_the_content($content) {
            try {
                // Check allow post type single page
                if (!is_singular(self::$allow_post_type)) {
                    return $content;
                }

                // Get global var and Initialize if not set
                global $global_yipl_citation_words;
                if (!is_array($global_yipl_citation_words)) {
                    $global_yipl_citation_words = [];
                }

                // check if this is auto detect citation word or not
                $auto_detect_yipl_citation = (get_option('default_auto_detect_citation_word', '')) ?: '';
                $auto_detect_yipl_citation = get_post_meta(get_the_ID(), '_auto_detect_yipl_citation', true) ?: $auto_detect_yipl_citation;
                if ($auto_detect_yipl_citation) {

                    $citations = self::get_all_yipl_citation();
                    $positions = [];

                    // 
                    // Initialize DOM
                    libxml_use_internal_errors(true);
                    $dom = new DOMDocument();

                    $html = '<div id="yipl-citation-data-wrapper">' . mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8') . '</div>';
                    if (!$dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD)) {
                        return $content;
                    }

                    $xpath = new DOMXPath($dom);
                    // Only process visible text
                    $textNodes = $xpath->query('//*[@id="yipl-citation-data-wrapper"]//text()');
                    foreach ($textNodes as $node) {
                        if (in_array($node->parentNode->nodeName, self::get_skip_tags())) {
                            continue;
                        }

                        $originalText = $node->nodeValue;
                        // Find citation words in content and record their positions
                        foreach ($citations as $word => $definition) {
                            $pattern = '/\b(' . preg_quote($word, '/') . ')\b/u';
                            if (preg_match($pattern, $originalText, $matches, PREG_OFFSET_CAPTURE)) {
                                $position = $matches[0][1];
                                if (!isset($positions[$word])) {
                                    $positions[$word] = $position;
                                }
                            }
                        }
                    }

                    // Sort matched words by first appearance
                    asort($positions);
                    // Save to global variable
                    foreach (array_keys($positions) as $word) {
                        if (!isset($global_yipl_citation_words[$word])) {
                            $global_yipl_citation_words[$word] = $citations[$word];
                        }
                    }
                    $current_yipl_citation_words = $global_yipl_citation_words;
                    // Replace occurrence of each word with citation
                    foreach ($textNodes as $node) {
                        if (in_array($node->parentNode->nodeName, self::get_skip_tags())) {
                            continue;
                        }

                        $text = $node->nodeValue;

                        foreach ($current_yipl_citation_words as $word => $values) {
                            $pattern = '/\b(' . preg_quote($word, '/') . ')\b/i';
                            // $content = preg_replace($pattern, $replace_content, $content, -1);

                            $text = preg_replace_callback(
                                $pattern,
                                function ($matches) use (&$current_yipl_citation_words, $word) {
                                    $word_original = $matches[1];
                                    // Get index from current matched words
                                    $keys = array_keys($current_yipl_citation_words);
                                    $count = array_search($word, $keys) + 1;
                                    unset($current_yipl_citation_words[$word]);
                                    // Replace with citation
                                    $replace_content = $word_original . self::yipl_citaion_sup_number($count);

                                    return $replace_content;
                                },
                                $text,
                                1
                            );
                        }

                        // Clear node
                        // $node->nodeValue = '';
                        $frag = $dom->createDocumentFragment();
                        @$frag->appendXML($text);
                        if ($frag) {
                            $node->parentNode->replaceChild($frag, $node);
                        }
                    }

                    $wrapper = $dom->getElementById('yipl-citation-data-wrapper');
                    if (!$wrapper) {
                        return $content; // Fallback if wrapper wasn't found
                    }

                    $content = '';
                    foreach ($wrapper->childNodes as $child) {
                        $content .= $dom->saveHTML($child);
                    }
                } else {
                    $pattern = '/<yipl_citation_slug>(.*?)<\/yipl_citation_slug>/';
                    // Replace with preg_replace_callback
                    $content = preg_replace_callback(
                        $pattern,
                        function ($matches) use (&$global_yipl_citation_words) {
                            $slug_yipl_citation = $matches[1];

                            if (!isset($global_yipl_citation_words[$slug_yipl_citation])) {
                                $global_yipl_citation_words[$slug_yipl_citation] = $slug_yipl_citation;
                            }
                            $keys = array_keys($global_yipl_citation_words);
                            $count = array_search($slug_yipl_citation, $keys) + 1;
                            $replace_content = self::yipl_citaion_sup_number($count); // Add superscript for citation number

                            return $replace_content;
                        },
                        $content
                    );
                }
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
                foreach ($global_yipl_citation_words as $word => $values) {
                    $description = isset($values['description']) ? $values['description'] : '';
                    if (!$description) {
                        $yipl_citation_selected = self::get_all_yipl_citation($word);
                        // Extract key and value
                        foreach ($yipl_citation_selected as $key => $values) {
                            $word = $key;
                            $description = isset($values['description']) ? $values['description'] : '';
                            break; // Only one item expected, so break after first
                        }
                    }
                    $output .= '<li id="yipl-citation-' . esc_attr($id) . '"><div class="yipl-citation-term">' . esc_html($word) . '</div> <div class="yipl-citation-description">' . $description . '</div></li>';
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
