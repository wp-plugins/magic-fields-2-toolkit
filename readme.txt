=== Magic Fields 2 Toolkit ===
Contributors: Magenta Cuda
Donate link:
Tags: custom, post, copier, fields, shortcodes, macros
Requires at least: 3.6
Tested up to: 3.9
Stable tag: 0.4.6.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Plugin adds some useful features to the Magic Fields 2 plugin.

== Description ==
This plugin adds some useful features to the Magic Fields 2 plugin.
The current features are:

   1. Create a copy of a Magic Fields 2 custom post copying all of the Magic Fields' custom fields, groups and taxonomies.
   
   2. Supports a shortcode for showing Magic Fields 2 custom fields and taxonomies. In particular, the shortcodes makes it easy to display a table of custom field names and their values.
   
   3. Identify and delete unreferenced files in folder files_mf.

   4. Provides an alternative related type field which uses multiple selection checkboxes instead of a single selection dropdown.

   5. Provides a search widget that does searching for Magic Fields 2 field values.

   6. Provides an alternative textbox field that allows you to select previously entered data.

   7. Supports HTML and WordPress macros for post content.

   8. Provides an alternative dropdown field that allows you to enter new options directly into the dropdown.

   9. Provides an alternative get_audio function that outputs HTML5 audio elements for iPad and iPhone browsers.

   10. Provides some Magic Fields 2 utility functions for PHP programmers.
   
   11. Provides a field for WordPress's embed shortcode.
   
   12. Provides a field for WordPress's video and audio shortcodes.
   
Please visit the [Toolkit's online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/) for more details.
**This plugin works with Magic Fields 2.2.2 and requires at least PHP 5.4.**

== Installation ==
1. Upload the `Magic Fields 2 Toolkit` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Open 'Settings->Magic Fields 2 Toolkit' menu item and enable specific features - all features are not enabled by default.
4. To copy a custom post open the "All Your Custom Post Type" menu item and click on "Create Copy" for the entry of the desired post.
5. To use shortcodes please see [the online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#shortcode).
6. To use content macros please see [the online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#macros).
7. Other features are self-explanatory.
8. The most current documentation is available at [Toolkit's online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/).

== Frequently Asked Questions ==

= Does this plugin require Magic Fields 2 to be installed? =

This plugin is designed to work with Magic Fields 2's custom post types and custom fields which have a non-standard implementation where generic utilities would probably not work.

= I have installed the plugin without errors but the plugin does nothing. =

You must enable specific features - all features are not enabled by default. Open 'Settings->Magic Fields 2 Toolkit' menu item and select the features you want.

= After upgrading to version 0.4.6 the search results table of post is not sortable. =

Version 0.4.6 has a new default content macro for sortable tables. However, the toolkit will not automatically replace an existing content macro - because you may have customized it. However, you can restore the default content macro by completely erasing the content macro definition.
   
== Screenshots ==

== Changelog ==
= 0.4.6.1 =
* upgrade a utility file to current version

= 0.4.6 =
* The search results table of posts is now a sortable table. Note that this requires a manual upgrade of search widget's content macro.

= 0.4.5.3 =
* fix pagination bug for search results output
* added psuedo file __post_author
* added search by post author
* added support for post type specific css file for alternate search result output
* omit select post type if there is only one post type

= 0.4.5.2 =
* 0.4.5.1 was the wrong version

= 0.4.5.1 =
* optionally display seach results using a content macro
* supports drag and drop to change order of search fields
* for searches optionally set query type to is_search so only excerpts are displayed for applicable themes
* provides a field for WordPress's embed shortcode.
* rovides a field for WordPress's video and audio shortcodes.

= 0.4.2.5 =
fix error in macro definition

= 0.4.2.4 =
fix a bad uri.

= 0.4.2.3 =
corrected version number errors again.

= 0.4.2.2 =
corrected version number errors.

= 0.4.2.1 =
corrected errors in this readme.

= 0.4.2 =
* show_custom_fields now can iterate over a list of posts making a table of data from multiple posts easy to do
* macro shortcode now understands inline macros so you do not have to create a content macro post
* macro conditionals can now have a #else# block
* macros can now have default arguments
* added psuedo field __post_title

= 0.4.1 =
* ordering fields first by group index then by field name
* excluding fields by class name, e.g. -alpha means exclude fields of class alpha
* injecting the class name into field prefixes and suffixes using the html comment &lt;!&#8211;$class&#8211;&gt;
* a psuedo parent field __parent so that the parent post can be referenced in a recursion
* wildcarding the group name, e.g. *_*&lt;*,*&gt; is now valid
* a special group member field mf2tk_key which will specify the group index as text and whose class is the group class
* indexing groups by name instead of integers using the value of the mf2tk_key, e.g. alpha_beta&lt;gamma,*&gt; instead of alpha_beta&lt;1,*&gt;
* classes for groups using the class of the mf2tk_key which can be used to include/exclude groups
* group_before/group_after with replacement of html comments &lt;!&#8211;$Group&#8211;&gt;, &lt;!&#8211;$class&#8211;&gt;, &lt;!&#8211;$group&#8211;&gt;
* changed search from using the multiple select html element to multiple select html checkboxes

= 0.4 =
* Added new features: macros for post content, an alternative dropdown field, an alternative HTML5 compatible get_audio() function and utility functions.
* Enhanced the shortcode feature with more parameters and the search feature with configurable parameters.
 
= 0.3.1 =
* Minor correction to 0.3

= 0.3 =
* Added a search widget for Magic Fields.
* Added an alternative textbox field that allows you to select previously entered data.

= 0.2 =
* Added an alternative related type field which uses multiple selection checkboxes instead of a single selection dropdown.
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

= 0.4.2 =
This version completes the feature set that I envisioned for this toolkit.

= 0.4.2.1 =
correct errors in this readme.

= 0.4.2.2 =
corrected version number errors.

= 0.4.2.3 =
corrected version number errors again.

= 0.4.2.4 =
fix a bad uri.

= 0.4.2.5 =
fix error in macro definition

= 0.4.5.1 =
added new search features
added fields for embed, audio and video

= 0.4.5.2 =
0.4.5.1 was the wrong version

= 0.4.5.3 =
* fix pagination bug for search results output
* added psuedo file __post_author
* added search by post author
* added support for post type specific css file for alternate search result output
* omit select post type if there is only one post type

= 0.4.6 =
* The search results table of posts is now a sortable table. Note that this requires a manual upgrade of search widget's content macro.

= 0.4.6.1 =
* upgrade a utility file to current version
