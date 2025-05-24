<?php

/**
 * Plugin Name: YIPL Citation Footnotes
 * Plugin URI: 
 * Description: YIPL Citation plugin purpose to make content citation and footernotes.
 * Tags: YIPL, Citation
 * Contributors: santoshtmp7, younginnovations
 * Requires at least: 6.8
 * Requires PHP: 7.4
 * Version: 1.0.0
 * Author: YIPL-santoshtmp7
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yipl-citation
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// define yipl_citation constant named
define('YIPL_CITATION_PATH', plugin_dir_path(__FILE__));
define('YIPL_CITATION_URL', plugin_dir_url(__FILE__));
define('YIPL_CITATION_BASENAME', plugin_basename(__FILE__));

// include utility functions file
require_once dirname(__FILE__) . '/includes/yipl-citation-utility.php';

/**
 * 
 */
$include_paths = [
    YIPL_CITATION_PATH . '/lib',
];
yipl_citation_include_path_files($include_paths);

