=== Magic Fields 2 Toolkit ===
Contributors: Magenta Cuda
Donate link:
Tags: custom, post, copier, fields, shortcode
Requires at least: 3.5.1
Tested up to: 3.5.1
Stable tag: 0.3.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Plugin adds some useful features to the Magic Fields 2 plugin.

== Description ==
This plugin adds some useful features to the Magic Fields 2 plugin.
The current features are:

   1. Create a copy of a Magic Fields 2 custom post copying all Magic Fields' custom fields, groups and taxonomies.
   
   2. Supports a shortcode for showing Magic Fields 2 custom fields and taxonomies. In particular, the shortcodes makes it 
easy to display a table of custom field names and their values.
   
   3. Identify and delete unreferenced files in folder files_mf.
   
   4. Provides an alternate related type field which uses multiple selection checkboxes instead of a single selection dropdown.
   
   5. Provides a search widget that does searches based on Magic Fields 2 field values.
   
   6. Provides an alternate textbox field that allows you to select previously entered data.
   
Please visit the [Toolkit's online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-3/) for more 
details.
This plugin requires at least PHP 5.4.

== Installation ==
1. Upload the `Magic Fields 2 Toolkit` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Open 'Settings->Magic Fields 2 Toolkit' menu item and enable specific features - all features are
   not enabled by default.
4. To copy a custom post open the "All Your Custom Post Type" menu item and click on "Create Copy" for 
   the entry of the desired post.
5. To use shortcodes please see
[the online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-3/#shortcode).
6. Other features are self-explanatory.
7. The most current documentation is available at
   [Toolkit's online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-3/).

== Frequently Asked Questions ==

= Does this plugin require Magic Fields 2 to be installed? =

This plugin will also work with standard WordPress custom post types and custom fields but its significance
is that it will also work with the non-standard Magic Fields 2 implementation where generic utilities would
probably not work.

= I have installed the plugin without errors but the plugin does nothing. =

You must enable specific features - all features are not enabled by default. Open 'Settings->Magic Fields 2 
Toolkit' menu item and select the features you want.

== Screenshots ==

== Changelog ==

= 0.3.1 =
Minor correction to 0.3

= 0.3 =
* Added a search widget for Magic Fields.
* Added an alternate textbox field that allows you to select previously entered data.

= 0.2 =
* Added an alternate related type field which uses multiple selection checkboxes instead of a single selection
dropdown.
* Enhanced shortcodes to make it easier to display a table of field names and their values.
  
= 0.1 =
* Initial release with custom post copy and custom field shortcode features.
  
== Upgrade Notice ==

= 0.2 =
This version provides new features and improvements to previous features.

= 0.3 =
This version provides new features.

= 0.3.1 =
A minor correction made.
