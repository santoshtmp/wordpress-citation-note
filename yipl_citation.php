<?php

/**
 * Plugin Name: YIPL Citation Footnotes
 * Plugin URI: 
 * Description: YIPL Citation Footnotes plugin purpose to add, manage and display citation for page/post/CPT using a user-friendly editor interface.
 * Tags: YIPL, Citation, Footnotes, Citation Plugin, reference, academic, editor, custom fields
 * Contributors: santoshtmp7, younginnovations
 * Requires at least: 6.8
 * Requires PHP: 8.0
 * Tested up to: 6.8
 * Version: 1.0.0
 * Author: santoshtmp7
 * License: GPLv2 or later
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yipl-citation
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

// define yipl_citation constant named
define('YIPLCIFO_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YIPLCIFO_PLUGIN_URL', plugin_dir_url(__FILE__));
define('YIPLCIFO_PLUGIN_BASENAME', plugin_basename(__FILE__));


// include utility functions file
require_once dirname(__FILE__) . '/includes/yipl-citation-utility.php';
