=== YIPL Citation Footnotes ===

Contributors: santoshtmp7, younginnovations
Tags: YIPL, yiplcifo, citation, reference, academic, editor, custom fields
Requires at least: 5.0
Requires PHP: 7.4
Tested up to: 6.8
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

This plugin add and manage citation fields for WordPress posts using a user-friendly editor interface.

== Description ==

**YIPL Citation Footnotes** WordPress plugin is developed to help content creators manage and display academic citations or references directly from the post/page editor.

This plugin adds a custom meta box with dynamic citation fields that allow users to:
- Add multiple citation entries
- Remove or reorder citations
- Store data securely using WordPress meta
- Output citations in the frontend or within blocks

Ideal for blogs, research publications, or any content that benefits from structured citation management.

== Installation and Usage ==
1. Upload the plugin folder to `/wp-content/plugins/yipl-citation/` or install via the Plugins menu.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. After activation, go to the plugin settings screen at `/wp-admin/options-general.php?page=yipl-citation-settings`.
   - Select the post type(s) to apply citations to.
   - Set the citation footnote title (this will be displayed on the frontend).
4. Go to the edit screen of any selected post type.
5. Scroll down to the **Citation Footnotes** meta box.
6. Click **Add Citation** to insert new fields.
7. Fill in your citation data (citation number and description).
8. In the content editor, insert the placeholder (e.g., `citation_1`) where the citation reference should appear.
9. Save or update the post.
10. Use the shortcode `[yipl_citation_list]` or template function `do_shortcode('[yipl_citation_list]')` to render the citation list on the frontend.


== Screenshots ==
1. Settings screen to configure post types and footnote title.
2. Meta box interface for entering citation data.
3. Citation placeholders inserted in post content.
4. Frontend rendering of citation list.

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
= 1.0.0 =
* Initial release of YIPL Citation.
* Added support for dynamic citation fields and placeholder rendering.
* Included shortcode for citation list output.
* Admin settings to select post types and footnote title.

== Upgrade Notice ==
= 1.0.0 =
Initial release.

== License ==
This plugin is licensed under the GPLv2 or later. For more information, see https://www.gnu.org/licenses/gpl-2.0.html.
