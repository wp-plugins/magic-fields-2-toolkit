=== Magic Fields 2 Toolkit ===
Contributors: Magenta Cuda
Donate link:
Tags: custom, post, copier, fields, shortcodes, macros
Requires at least: 3.6
Tested up to: 3.6.1
Stable tag: 0.4.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Plugin adds some useful features to the Magic Fields 2 plugin.

== Description ==
This plugin adds some useful features to the Magic Fields 2 plugin.
The current features are:

   1. Create a copy of a Magic Fields 2 custom post copying all of the Magic Fields' custom fields, groups and taxonomies.
   
   2. Supports a shortcode for showing Magic Fields 2 custom fields and taxonomies. In particular, the shortcodes makes it 
easy to display a table of custom field names and their values.
   
   3. Identify and delete unreferenced files in folder files_mf.

   4. Provides an alternative related type field which uses multiple selection checkboxes instead of a single selection dropdown.

   5. Provides a search widget that does searching for Magic Fields 2 field values.

   6. Provides an alternative textbox field that allows you to select previously entered data.

   7. Supports HTML and WordPress macros for post content.

   8. Provides an alternative dropdown field that allows you to enter new options directly into the dropdown.

   9. Provides an alternative get_audio function that outputs HTML5 audio elements for iPad and iPhone browsers.

   10. Provides some Magic Fields 2 utility functions for PHP programmers.
   
Please visit the [Toolkit's online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4/) for more 
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
[the online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4/#shortcode).
6. To use content macros please see
[the online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4/#macros).
7. Other features are self-explanatory.
8. The most current documentation is available at
   [Toolkit's online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-1/).

== Frequently Asked Questions ==

= Does this plugin require Magic Fields 2 to be installed? =

Some features of this plugin will also work with standard WordPress custom post types and custom fields  
but its significance is that it will also work with the non-standard Magic Fields 2 implementation where 
generic utilities would probably not work.

= I have installed the plugin without errors but the plugin does nothing. =

You must enable specific features - all features are not enabled by default. Open 'Settings->Magic Fields 2 
Toolkit' menu item and select the features you want.

== Screenshots ==

== Changelog ==

= 0.4.1 =
* added support for ordering fields first by group index then by field name
* added support for excluding fields by class name, e.g. -alpha means exclude fields of class alpha
* added support for injecting class name into field prefixes and suffixes using the html comment <!--$class-->
* added support for a psuedo parent field __parent so that the parent post can be referenced in a recursion
* added support for wildcarding the group name, e.g. *_*<*,*> is now valid
* added support for a special group member field mf2tk_key which will specify the group index as text and whose class is the group class
* added support for indexing groups by name instead of integers using mf2tk_key, e.g. alpha_beta<gamma,*> instead of alpha_beta<1,*>
* added support for assigning classes to groups using the class of mf2tk_key which can be used to include/exclude groups
* added support for group_before/group_after with replacement of html comments <!--$Group-->, <!--$class-->, <!--$group-->
* changed search from using multiple select html element to multiple select html checkboxes

= 0.4 =
* Added new features: macros for post content, an alternative dropdown field, an alternative HTML5 compatible 
get_audio() function and utility functions.
* Enhanced the shortcode feature with more parameters and the search feature with configurable parameters.
 
= 0.3.1 =
* Minor correction to 0.3

= 0.3 =
* Added a search widget for Magic Fields.
* Added an alternative textbox field that allows you to select previously entered data.

= 0.2 =
* Added an alternative related type field which uses multiple selection checkboxes instead of a single selection
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

= 0.4 =
This version adds some new features and improves the shortcode and search features.

= 0.4.1 =
Many small and some significant enhancements were added to existing features.
