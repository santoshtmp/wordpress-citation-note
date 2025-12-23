=== Citation Note ===

Contributors: santoshtmp7, younginnovations
Tags: CITENOTE, Citation, reference, footnotes, citation note
Requires at least: 6.8
Requires PHP: 8.0
Tested up to: 6.9
Stable tag: 1.1.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Easily add, manage, and display citations, references, and footnotes in posts, pages, or custom post types using a user-friendly editor interface.

== Description ==

**Citation Note** plugin easily add, manage, and display citations, references, and footnotes in posts, pages, or custom post types using a user-friendly editor interface.

This is developed to help content creators manage and display content citations or references directly from the post/page editor.

This plugin adds a custom meta box with dynamic citation fields that allow users to:
- Add multiple citation entries.
- Remove or reorder citations.
- Output citations in the frontend or within blocks.

Ideal for blogs, research publications, or any content that benefits from structured citation management.

== Installation ==
1. Upload the plugin folder to `/wp-content/plugins/citation-note/` or install via the Plugins menu.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. After activate the plugin, go to the plugin settings screen at `/wp-admin/options-general.php?page=citenote`.
   - Select the post type(s) to apply citations.
   - Set the citation footnote title (this will be displayed on the frontend).

== How to use ==
1. After activate the plugin and select the post type to apply citations.
2. Go to the edit screen of any selected post type.
3. Scroll down to the **Citation List** meta box.
4. Click **Add Citation** to insert new fields.
5. Fill in your citation data (citation number and description).
6. In the content editor, insert the placeholder (e.g., `citation_1`) where the citation reference should appear.
7. Save or update the post.
8. Use the shortcode `[citenote_display_list]` or template function `do_shortcode('[citenote_display_list]')` to render the citation list on the frontend.

== Screenshots ==
1. Plugin settings screen.
2. Citation meta box for entering data.
3. Citation placeholders and shortcode in content.
4. Frontend rendering of citation.

== Frequently Asked Questions ==
= Can we apply to particular post type? =
Yes, we can apply to only selected post type.

= Can I style the citations differently? =
Yes. The plugin includes a CSS file you can override in your theme.

= Where is the citation data stored? =
All citations are saved as post meta data under a structured array key.

= Is this plugin compatible with Gutenberg? =
Yes, it loads only on post editor screens and integrates seamlessly with the block editor.

== Changelog ==
= 1.1.0 =
* Compatible to 6.9
* Fix citenoteplaceholder tag 
= 1.0.0 =
* Initial release of Citation Note.
* Added support for dynamic citation fields and placeholder rendering.
* Included shortcode for citation list output.
* Admin settings to select post types and footnote title.

== Upgrade Notice ==
= 1.0.0 =
Initial release.

== License ==
This plugin is licensed under the GPLv2 or later. For more information, see https://www.gnu.org/licenses/gpl-2.0.html.
