=== Magic Fields 2 Toolkit ===
Contributors: Magenta Cuda
Donate link:
Tags: custom, post, copier, fields, shortcode
Requires at least: 3.5.1
Tested up to: 3.5.1
Stable tag: 0.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Plugin adds some useful features to the Magic Fields 2 plugin.

== Description ==
This plugin adds some useful features to the Magic Fields 2 plugin.
The current features are:

   1. Create a copy of a Magic Fields 2 custom post copying all Magic Fields' custom fields, groups and taxonomies.
   
   2. Supports a shortcode for showing Magic Fields custom fields and taxonomies.
   
   3. Identify and delete unreferenced files in folder files_mf.

== Installation ==
1. Upload the `Magic Fields 2 Toolkit` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Open 'Settings->Magic Fields 2 Toolkit' menu item and enable specific features - all features are
   not enabled by default.
4. To copy a custom post open the "All Your Custom Post Type" menu item and click on "Create Copy" for 
   the entry of the desired post.
5. To use shortcodes enter '[show_custom_field field="your_custom_field" before="your_optional_prefix"
   after="your_optional_suffix" filter="your_optional_filter_function" post_id="your_optional_post_id"]'
   into the post content. You may also use a taxonomy name as a field - in which case the terms of the 
   taxonomy are shown.
   
   Examples:
   
   [show_custom_field field="date" before="&lt;li&gt;" after="&lt;/li&gt;" filter="convert_to_MMDDYYYY" post_id="123"]
   
   [show_custom_field field="date&lt;1,2&gt;"]     (first index is group; second index is field - must specify both)
   
   [show_custom_field field="date&lt;1,*&gt;" before="&lt;div&gt;" after="&lt;/div&gt;"]     (&#42; means loop over all field index values)

   [show_custom_field field="city&lt;1,1&gt;.country&lt;1,1&gt;"]     (recursion supported for related custom posts)
   
   [show_custom_field field="category" after=" "]     (a field can also be a taxonomy name)
   
   [show_custom_field field="your_image" before="&lt;img src='" after="'&gt;"]     (images need to be wrapped in an &lt;img&gt; element)

== Frequently Asked Questions ==
= Does this plugin require Magic Fields 2 to be installed? =
This plugin will also work with standard WordPress custom post types and custom fields but its significance
is that it will also work with the non-standard Magic Fields 2 implementation where generic utilities would
probably not work.

== Screenshots ==

== Changelog ==
= 0.1 =
  Initial release with custom post copy and custom field shortcode features.
  
== Upgrade Notice == 


