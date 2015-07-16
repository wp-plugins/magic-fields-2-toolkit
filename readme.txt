=== Magic Fields 2 Toolkit ===
Contributors: Magenta Cuda
Donate link:
Tags: shortcodes, templates, custom fields, post copier
Requires at least: 3.6
Tested up to: 4.2
Stable tag: 1.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
A toolkit for the Magic Fields 2 plugin for media oriented CMS web design by non programmers.

== Description ==
This [toolkit](https://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/) transforms the [Magic Fields 2](https://wordpress.org/plugins/magic-fields-2/) plugin into something that can be used by a non programmer to do media oriented CMS web design.
With this toolkit you can design your page content using only HTML, CSS and the [toolkit's shortcodes](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#shortcode).
Using the [toolkit's content templates](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#macros) you can define a reusable template for page contents which can be used to create multiple pages using the page's custom fields to instantiate the template.
This toolkit also simplifies the use of media (images, audio, video and embeds) by providing [configurable shortcodes](https://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#alt_media) for generating HTML wrappers for these media elements.

The current features are:

= Support for coding HTML templates without the need for PHP programming =
* [shortcode for showing Magic Fields 2 custom fields and taxonomies](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#shortcode). In particular, the shortcodes makes it easy to display a table of custom field names and their values.
* [post content templates for HTML and WordPress shortcodes](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#macros) - these templates do not need PHP code.
* [gallery shortcode for showing Magic Fields Media Library images](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#gallery).

= Additional custom fields =
* [fields for WordPress's video and audio shortcodes](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#alt_media).
* An [enhanced image field](https://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#alt_image) that supports a custom click URL and a mouseover popup.
* [alternative related type field](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#alt_related) which uses multiple selection checkboxes instead of a single selection dropdown.
* [numeric field](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#alt_numeric) with support for measurement units suffix and/or currency symbol prefix.
* [URL field](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#alt_url) contains the data for a HTML &lt;a&gt; element.
* [pseudo field for generating a table of Magic Field names and values](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#alt_table).
* field for WordPress's embed shortcode.
* alternative textbox field that allows you to select previously entered data.
* alternative dropdown field that allows you to enter new options directly into the dropdown.

= Search widget =
* [finds posts by Magic Fields 2 field values.](http://magicfields17.wordpress.com/magic-fields-2-search-0-4-1/)

= Miscellaneous enhancements to Magic Fields 2 =
* [Create a copy of a Magic Fields 2 custom post](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#copy) copying all of the Magic Fields' custom fields, groups and taxonomies.
* Identify and delete unreferenced files in folder files_mf.
* Provides an alternative get_audio function that outputs HTML5 audio elements for iPad and iPhone browsers.
* Provides some Magic Fields 2 utility functions for PHP programmers.

Please visit the [Toolkit's online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/) for more details.
**This plugin works with Magic Fields 2.3 and requires at least PHP 5.4.**

== Installation ==
1. Upload the `Magic Fields 2 Toolkit` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Open 'Settings->Magic Fields 2 Toolkit' menu item and enable specific features - all features are not enabled by default.
4. To copy a custom post open the "All Your Custom Post Type" menu item and click on "Create Copy" for the entry of the desired post.
5. To use shortcodes please see [the online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#shortcode).
6. To use content templates please see [the online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#macros).
7. Other features are self-explanatory.
8. The most current documentation is available at [Toolkit's online documentation](http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/).

== Frequently Asked Questions ==

= Does this plugin require Magic Fields 2 to be installed? =

Yes, this plugin is designed to work with Magic Fields 2 custom fields which have a non-standard implementation.

= I have installed the plugin without errors but the plugin does nothing. =

You must enable specific features - all features are not enabled by default. Open 'Settings->Magic Fields 2 Toolkit' menu item and select the features you want.

= After upgrading to version 0.4.6 the search results table of post is not sortable. =

Version 0.4.6 has a new default content macro for sortable tables. However, the toolkit will not automatically replace an existing content macro - because you may have customized it. However, you can restore the default content macro by completely erasing the content macro definition.
   
== Screenshots ==

== Changelog ==
= 1.0 =
* The post content template interpreter has been redesigned and rewritten.
* Video and audio media now support mouse-over popups and clickable links where applicable.
* Css for media elements and the search widget improved to be compatible with more themes.
* Added a sync button to synchronize Magic Fields 2 fields with the current toolkit fields to fix the annoying bug where the toolkit stops working after you upgrade Magic Fields 2;
* Filters moved to the namespace mf2tk to reduce global namespace pollution.
* All shortcodes are now rename-able for consistency and conflict resolution.
* Some code rewritten to improve software quality.

= 0.5.8.3 =
* alt_image_field, alt_video_field, alt_audio_field, alt_embed_field and alt_numeric parameters e.g., width, height, ... can now be overridden by using corresponding show_custom_field parameters
* alt_image_field, alt_video_field, alt_audio_field and alt_embed_field now have a simplified how to use interface
* fix css alignment problems with media elements, in particular removed the bottom margin from media elements to reduce the space between media and caption
* rewrite code to improve speed, stability, security and software quality - use namespaces to reduce global pollution, gracefully handle missing parameters, use $wpdb->prepare(), ...

= 0.5.8.2 =
* fix the really annoying bug where the should be optional mouseover overlay had to be specified

= 0.5.8.1 =
* alt media fields now support showing popups on mouseover
* fix [problem](https://wordpress.org/support/topic/get_data-returns-wrong-options)with Magic Fields get_data function by replacing with function alt_get_data

= 0.5.8 =
* added interactive shell for evaluating shortcodes so that you can quickly see the HTML generated by shortcode processing

= 0.5.7.1 =
* variable assignments now support filters
* if statement now supports an equality condition
* added filters to return field name and field type

= 0.5.7 =
* support assigning custom field values to content template variables
* modify content template conditional inclusion to require both definition and truthy
* support iteration for content template variables

= 0.5.6 =
* content templates now support a post iterator
* fix bugs with the alt_video field type

= 0.5.5 =
* content templates now support iterators for group and field indexes

= 0.5.4 =
* added a gallery shortcode for "image media" and "alt_image" fields
* "how to use" now works for duplicated fields

= 0.5.3 =
* added support for post type specific search result templates

= 0.5.2 =
* added a URL field
* alt_image now has onclick URL property
* added how to use boxes for taxonomies
* inserting content templates can now be done directly from the post content editor - alt_template pseudo field is not necessary
* alt_table field now saves its settings

= 0.5.1 =
* save post content as a content template(content macro)
* psuedo field to generate the shortcode to use a content template(content macro)

= 0.5.0.2 =
* fix some css problems on Firefox and some anomalies with the video shortcode on Firefox

= 0.5.0.1 =
* removed some debugging code

= 0.5 =
* make shortcodes easier to use by automatically generating a bespoke shortcode for each Magic Field
* added a pseudo field that automatically generates a shortcode for table of Magic Fields names and values
* added a numeric field type with a measurement unit suffix and/or currency symbol prefix
* make the video, audio, embed and image field types more user friendly and correct anomalies in the generated HTML elements
* bug fixes, prettify admin settings interface
 
= 0.4.6.3.1 =
* bug fixes, prettify user interface and code maintenance

= 0.4.6.3 =
* fix drag and drop problem on search widgets administrator's interface for older versions of Internet Explorer
* added style sheet for the search widget
* several small enhancements and bug fixes for the search widget

= 0.4.6.2 =
* no changes; just synced readme and plugin version numbers

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

= 0.4.6.2 =
* no changes; just synced readme and plugin version numbers

= 0.4.6.3 =
* fix drag and drop problem on search widgets administrator's interface for older versions of Internet Explorer
* added style sheet for the search widget
* several small enhancements and bug fixes for the search widget

= 0.4.6.3.1 =
* bug fixes, prettify user interface and code maintenance

= 0.5 =
* make shortcodes easier to use by automatically generating a bespoke shortcode for each Magic Field
* added a pseudo field that automatically generates a shortcode for table of Magic Fields names and values
* added a numeric field type with a measurement unit suffix and/or currency symbol prefix
* make the video, audio, embed and image field types more user friendly and correct anomalies in the generated HTML elements
* bug fixes, prettify admin settings interface

 = 0.5.0.1 =
* removed some debugging code

= 0.5.0.2 =
* fix some css problems on Firefox and some anomalies with the video shortcode on Firefox

= 0.5.1 =
* save post content as a content template(content macro)
* psuedo field to generate the shortcode to use a content template(content macro)

= 0.5.2 =
* added a URL field
* alt_image now has onclick URL property
* added how to use boxes for taxonomies
* inserting content templates can now be done directly from the post content editor - alt_template pseudo field is not necessary
* alt_table field now saves its settings

= 0.5.3 =
* added support for post type specific search result templates

= 0.5.4 =
* added a gallery shortcode for "image media" and "alt_image" fields
* "how to use" now works for duplicated fields

= 0.5.5 =
* content templates now support iterators for group and field indexes

= 0.5.6 =
* content templates now support a post iterator
* fix bugs with the alt_video field type

= 0.5.7 =
* support assigning custom field values to content template variables
* modify content template conditional inclusion to require both definition and truthy
* support iteration for content template variables

= 0.5.7.1 =
* variable assignments now support filters
* if statement now supports an equality condition
* added filters to return field name and field type

= 0.5.8 =
* added interactive shell for evaluating shortcodes so that you can quickly see the HTML generated by shortcode processing

= 0.5.8.1 =
* alt media fields now support showing popups on mouseover 
* fix [problem](https://wordpress.org/support/topic/get_data-returns-wrong-options)with Magic Fields get_data function by replacing with function alt_get_data

= 0.5.8.2 =
* fix the really annoying bug where the should be optional mouseover overlay had to be specified

= 0.5.8.3 =
* alt_image_field, alt_video_field, alt_audio_field, alt_embed_field and alt_numeric parameters e.g., width, height, ... can now be overridden by using corresponding show_custom_field parameters
* alt_image_field, alt_video_field, alt_audio_field and alt_embed_field now have a simplified how to use interface
* fix css alignment problems with media elements, in particular removed the bottom margin from media elements to reduce the space between media and caption
* rewrite code to improve speed, stability, security and software quality - use namespaces to reduce global pollution, gracefully handle missing parameters, use $wpdb->prepare(), ...

= 1.0 =
* The post content template interpreter has been redesigned and rewritten.
* Video and audio media now support mouse-over popups and clickable links where applicable.
* Css for media elements and the search widget improved to be compatible with more themes.
* Added a sync button to synchronize Magic Fields 2 fields with the current toolkit fields to fix the annoying bug where the toolkit stops working after you upgrade Magic Fields 2;
* Filters moved to the namespace mf2tk to reduce global namespace pollution.
* All shortcodes are now rename-able for consistency and conflict resolution.
* Some code rewritten to improve software quality.
