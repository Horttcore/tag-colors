=== Tag Colors ===
Contributors: ralfhortt
Tags: taxonomy, tags, color, term meta
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Assign a color to taxonomy terms. Colors are stored as term meta (REST-ready).

== Description ==

Adds a color picker to the add/edit screens for taxonomies you register, plus a color column on the term list.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/` or install through the Plugins screen.
2. Activate the plugin.
3. In your theme or plugin, call `Tag_Colors::register_support()` for each taxonomy (on `init`).

== Usage ==

Register support for a taxonomy:

`Tag_Colors::register_support( 'post_tag' );`

Read a term's color in templates:

`tag_colors_get_color( $term );`

== Changelog ==

= 1.0.0 =
* Store colors in term meta instead of a single site option
* Register term meta for the REST API with `sanitize_hex_color`
* CSRF checks, capability checks, and output escaping
* Public helper `tag_colors_get_color()`
* Requires WordPress 5.8+ and PHP 7.4+

= 0.2 =
* Bugfix: To remove a color from a tag, leave the color field empty
* Default registration for `post_tag` removed

= 0.1 =
* Initial release
