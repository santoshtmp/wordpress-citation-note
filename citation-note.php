<?php

/**
 * Plugin Name: Citation Note
 * Plugin URI: 
 * Description: Easily add, manage, and display citations, references, and footnotes in posts, pages, or custom post types using a user-friendly editor interface.
 * Tags: CITENOTE, Citation, reference, footnotes, citation note
 * Contributors: santoshtmp7, younginnovations
 * Requires at least: 6.8
 * Requires PHP: 8.0
 * Tested up to: 6.9
 * Version: 1.1.0
 * Author: santoshtmp7
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: citation-note
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// define constant named
define('CITENOTE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CITENOTE_PLUGIN_URL', plugin_dir_url(__FILE__));
define('CITENOTE_PLUGIN_BASENAME', plugin_basename(__FILE__));

// 
if (! class_exists('CITENOTE')) {
    /**
     * CITENOTE main class
     * 
     */
    class CITENOTE {

        /**
         * construction
         */
        function __construct() {
            $include_paths = [
                CITENOTE_PLUGIN_DIR . '/lib',
            ];
            $this->citenote_include_path_files($include_paths);

            new \citenote\CITENOTE_Data();
            new \citenote\CITENOTE_Admin_Settings();
            new \citenote\CITENOTE_Editor_Fields();

            add_action('init', [$this, 'citenote_register_scripts']);
        }

        /**
         * 
         */
        function citenote_register_scripts() {
            wp_register_style(
                "citation-note-style",
                CITENOTE_PLUGIN_URL . 'assets/css/citation-note-style.css',
                [],
                filemtime(CITENOTE_PLUGIN_DIR . 'assets/css/citation-note-style.css')

            );
            wp_enqueue_script(
                'citation-note-script',
                CITENOTE_PLUGIN_URL . 'assets/js/citation-note.js',
                ['jquery'],
                filemtime(CITENOTE_PLUGIN_DIR . 'assets/js/citation-note.js'),
                true
            );
        }


        /**
         * citenote_get_path
         *
         * Returns the plugin path to a specified file.
         *
         * @param   string $filename The specified file.
         * @return  string
         */
        function citenote_get_path($filename = '') {
            return CITENOTE_PLUGIN_DIR . ltrim($filename, '/');
        }

        /**
         * Includes a file within the plugin.
         *
         * @param   string $filename The specified file.
         * @return  void
         */
        function citenote_include($filename = '') {
            $file_path = $this->citenote_get_path($filename);
            if (file_exists($file_path)) {
                include_once $file_path;
            }
        }


        /**
         * requires all ".php" files from dir defined in "include_dir_paths" at first level.
         * @param array $include_dir_paths will be [__DIR__.'/inc'];
         */
        function citenote_include_path_files($include_dir_paths) {
            foreach ($include_dir_paths as $key => $file_path) {
                if (!file_exists($file_path)) {
                    continue;
                }
                foreach (new \DirectoryIterator($file_path) as $file) {
                    if ($file->isDot() || $file->isDir()) {
                        continue;
                    }
                    $fileExtension = $file->getExtension(); // Get the current file extension
                    if ($fileExtension != "php") {
                        continue;
                    }
                    // $fileName = $file->getFilename(); // Get the full name of the current file.
                    $filePath = $file->getPathname(); // Get the full path of the current file
                    if ($filePath) {
                        require_once $filePath;
                    }
                }
            }
        }
        //  ======== END =======
    }
}

// Execute CITENOTE main class
new CITENOTE();
