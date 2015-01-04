<?php

/*  Copyright 2013  Magenta Cuda

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Magic_Fields_2_Toolkit_Init {
    public function __construct() {
        global $wpdb;
        add_action( 'admin_init', function() {
            if ( !is_plugin_active( 'magic-fields-2/main.php' ) ) {
                add_action( 'admin_notices', function() {
?>
<div style="clear:both;font-weight:bold;border:2px solid red;padding:5px 10px;margin:10px;">Magic Templates requires that plugin <a href="https://wordpress.org/plugins/magic-fields-2/">Magic Fields 2</a> be installed
and activated.</div> 
<?php
                } );
            }
        } );
        add_action( 'admin_enqueue_scripts', function( $hook ) {
            global $wp_scripts;
            if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) { return; }
            wp_enqueue_style( 'admin', plugins_url( 'admin.css', __FILE__ ) );
            wp_enqueue_script( 'mf2tk_alt_media_admin', plugins_url( 'magic-fields-2-toolkit/js/mf2tk_alt_media_admin.js' ),
                array( 'jquery' ) );
            $options = get_option( 'magic_fields_2_toolkit_enabled', [ ] );
            $mf2tkDisableHowToUse = array_key_exists( 'dumb_shortcodes', $options ) ? 'false' : 'true';
            $wp_scripts->add_data( 'mf2tk_alt_media_admin', 'data', "var mf2tkDisableHowToUse=$mf2tkDisableHowToUse;" );
        } );
        include( dirname(__FILE__) . '/magic-fields-2-toolkit-settings.php' );
        $options = get_option( 'magic_fields_2_toolkit_enabled', [ ] );
        #error_log( '##### Magic_Fields_2_Toolkit_Init:$options='
        #    . print_r( $options, TRUE ) );
        if ( is_array( $options ) ) {
            if ( array_key_exists( 'custom_post_copier', $options ) ) {
                include( dirname(__FILE__)
                    . '/magic-fields-2-custom-post-copier.php' );
            }
            if ( array_key_exists( 'dumb_shortcodes', $options ) ) {
                include_once( dirname(__FILE__)
                    . '/magic-fields-2-dumb-shortcodes-kai.php' );
            }
            if ( array_key_exists( 'dumb_macros', $options ) ) {
                #include( dirname(__FILE__)
                #    . '/magic-fields-2-dumb-macros-kai.php' );
                #include( dirname(__FILE__)
                #    . '/magic-fields-2-field-spec-to-field-list.php' );
                include( dirname(__FILE__)
                    . '/magic-fields-2-dumb-macros.php' );
                # posts some pre-defined content macros as useful examples
				#if ( !get_posts( array(
				#    'name' => 'table',
				#	'post_type' => 'content_macro',
				#	#'post_status' => 'publish',
				#	'posts_per_page' => 1
				#) ) ) {
                if ( !$wpdb->get_var( <<<EOD
                    SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'content_macro'
                        AND post_title = 'Table' AND post_status = 'publish'
EOD
                ) ) {
					wp_insert_post( array(
						'post_type' => 'content_macro',
						'post_name' => 'table',
						'post_title' => 'Table',
						'post_status' => 'publish',
						'post_content' => <<<'EOD'
<!--
     Show table of fields.
     This macro has one required parameter: macro.
     macro is the slug of this post - table.
     This macro has one optional parameter: fields.
     fields is a list of field names in show_custom_field's
     field parameter format. If not specified "*_*<*,*> is used.
     For best results mf2tk_key needs to be defined. This macro has 
     an example of #if($#alpha#)# ... #else# ... #endif# and
     examples of default parameters.
-->

<!-- $#fields# = "__default_*<*,*>"; -->
<!-- $#fields# = '__default_*<*,*>'; -->

<table>
#if($#fields#)#
[show_custom_field field="$#fields#"
    field_before="<tr><td><!--$Field--></td><td>"
    field_after="</td></tr>"
    separator=", " filter="url_to_link2"]
#else#
[show_custom_field field="*_*<*,*>"
    group_before="<tr><td><!--$Group--></td><td>&nbsp;</td></tr>"
    field_before="<tr><td><!--$Field--></td><td>"
    field_after="</td></tr>"
    separator=", " filter="url_to_link2"]
#endif#
</table>
EOD
					) );
				}
				#if ( !get_posts( array(
				#    'name' => 'horizontal-table-for-group',
				#	'post_type' => 'content_macro',
				#	#'post_status' => 'publish',
				#	'posts_per_page' => 1
				#) ) ) {
                if ( !$wpdb->get_var( <<<EOD
                    SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'content_macro'
                        AND post_title = 'Horizontal Table for a Group' AND post_status = 'publish'
EOD
                ) ) {
					wp_insert_post( array(
						'post_type' => 'content_macro',
						'post_name' => 'horizontal-table-for-group',
						'post_title' => 'Horizontal Table for a Group',
						'post_status' => 'publish',
						'post_content' => <<<'EOD'
<!-- Show custom fields of a group horizontally.
     This macro has three parameters: macro, group and title.
     macro is the slug of this post - horizontal-table-for-group.
     group is the group name and title is a label for the
     upper left corner of the table. Column labels are 
     generated from the field labels.
     For best results the mf2tk_key needs to be defined. --> 
<table>
<!-- to show just the field names hide field values with display:none -->
<tr><td>$#title#</td>[show_custom_field
    field="$#group#_*<1,1>"
    field_before="<td><!--$Field-->"
    before="<span style='display:none'>" after="</span>"
    field_after="</td>"
]</tr>
[show_custom_field
    field="$#group#_*<*,*>"
    group_before="<tr><td><!--$Group--></td>" 
    field_before="<td>"
    filter="url_to_link2" separator=", "
    field_after="</td>"
    group_after="</tr>"   
]
</table>
EOD
					) );
				}
				#if ( !get_posts( array(
				#    'name' => 'horizontal-table-for-group-with-group-columns',
				#	'post_type' => 'content_macro',
				#	#'post_status' => 'publish',
				#	'posts_per_page' => 1
				#) ) ) {
                if ( !$wpdb->get_var( <<<EOD
                    SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'content_macro'
                        AND post_title = 'Horizontal Table for a Group with Group Columns' AND post_status = 'publish'
EOD
                ) ) {
					wp_insert_post( array(
						'post_type' => 'content_macro',
						'post_name' => 'horizontal-table-for-group-with-group-columns',
						'post_title' => 'Horizontal Table for a Group with Group Columns',
						'post_status' => 'publish',
						'post_content' => <<<'EOD'
<!-- Show custom fields of a group horizontally with a column 
     displaying all fields in a group.
     This macro has three parameters: macro, group and title.
     macro is the slug of this post
         - horizontal-table-for-group-with-group-columns.
     group is the group name and title is a label for the
     upper left corner of the table. Column labels are 
     generated from the group labels.
     For best results the mf2tk_key needs to be defined. --> 
<table>
<tr><td>$#title#</td>
<!-- to show just the group labels hide field values with display:none -->
[show_custom_field
    field="$#group#_mf2tk_key<*,1>f"
    group_before="<td><!--$Group-->"
    before="<span style='display:none'>" after="</span>"
    group_after="</td>"
]
</tr>
[show_custom_field
    field="$#group#_*<*,*>f"
    field_before="<tr><td><!--$Field--></td><td>"
    group_separator="</td><td>"
    separator=", " filter="url_to_link2" 
    field_after="</td></tr>"
]
</table>
EOD
					) );
				}
				#if ( !get_posts( array(
				#    'name' => 'boxes-by-group',
				#	'post_type' => 'content_macro',
				#	#'post_status' => 'publish',
				#	'posts_per_page' => 1
				#) ) ) {
                if ( !$wpdb->get_var( <<<EOD
                    SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'content_macro'
                        AND post_title = 'Boxes by Group' AND post_status = 'publish'
EOD
                ) ) {
					wp_insert_post( array(
						'post_type' => 'content_macro',
						'post_name' => 'boxes-by-group',
						'post_title' => 'Boxes by Group',
						'post_status' => 'publish',
						'post_content' => <<<'EOD'
<!-- Show custom fields of a post in boxes with a box displaying
     all fields in a group.
     This macro has two parameters: macro, class_filter.
     macro is the slug of this post - boxes-by-group.
     class_filter is an optional class/group include/exclude filter.
     This macro uses conditional text inclusion to handle the
     optional class_filter.
     For best results the mf2tk_key needs to be defined. -->
[show_custom_field
    field="*_*<*,*>#if($#class_filter#)#:$#class_filter##endif#"
    separator=", "
    field_before="<div><div style='width:30%;float:left;clear:both;'><!--$Field-->:</div><div>"
    field_after="&nbsp;</div></div>"
    group_before="<div id='<!--$group-->' class='<!--$class-->'
        style='padding:5px;border:2px solid black;margin:5px;'>
        <div style='border-bottom:1px solid red;'>
        <!--$Group-->:</div>"
    group_after="</div>"
    filter="url_to_link2"]                        
EOD
					) );
				}
                if ( !$wpdb->get_var( <<<EOD
                    SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'content_macro'
                        AND post_title = 'Table of Posts' AND post_status = 'publish'
EOD
                ) ) {
					wp_insert_post( array(
						'post_type' => 'content_macro',
						'post_name' => 'table-of-posts',
						'post_title' => 'Table of Posts',
						'post_status' => 'publish',
						'post_content' => <<<'EOD'
<!--
     Show a table of posts.
     This macro has three parameters: macro, posts and fields.
     macro is the slug of this post - table-of-posts.
     posts is a post specification
          a list of post ids or a post tag pattern
     fields is a field specification
          a list of field names or a field name pattern
--> 
<table>
[show_custom_field
    post_id="$#posts##1"
    field="$#fields#"
    before="<span style='display:none;'>"
    after="</span>"
    field_before="<td><!--$Field-->"
    field_after="</td>
    post_before="<tr>"
    post_after="</tr>"]
[show_custom_field
    post_id="$#posts#"
    field="$#fields#"
    separator=", "
    field_before="<td>"
    field_after="</td>
    post_before="<tr>"
    post_after="</tr>"
    filter="url_to_link2"]
</table>
EOD
					) );
				}
                if ( !$wpdb->get_var( <<<EOD
                    SELECT COUNT(*) FROM $wpdb->posts WHERE post_type = 'content_macro'
                        AND post_title = 'Horizontal Table' AND post_status = 'publish'
EOD
                ) ) {
					wp_insert_post( array(
						'post_type' => 'content_macro',
						'post_name' => 'horizontal-table',
						'post_title' => 'Horizontal Table',
						'post_status' => 'publish',
						'post_content' => <<<'EOD'
<!--
     Show custom fields horizontally with labels on top.
     This macro has two parameters: macro and fields.
     macro is the slug of this post - horizontal-table.
     fields is a field list specification which has a default of
     __default_<*,*>
     The labels are generated from the field labels.
--> 
<!-- $#fields# = "__default_*<*,*>"; -->
<table>
<!-- to show just the field names hide field values with display:none -->
<tr>[show_custom_field field="$#fields#"
    field_before="<td><!--$Field-->"
    before="<span style='display:none'>" after="</span>"
    field_after="</td>"
]</tr>
<tr>[show_custom_field field="$#fields#"
    field_before="<td>"
    filter="url_to_link2" separator=", "
    field_after="</td>"
]</tr>
</table>
EOD
					) );
				}
                
            } else {
			}
            if ( array_key_exists( 'clean_files_mf', $options ) && is_admin() ) {
                include( dirname(__FILE__)
                    . '/magic-fields-2-clean-files_mf.php' );
            }
            if ( array_key_exists( 'search_using_magic_fields', $options ) ) {
                include( dirname(__FILE__)
                    . '/magic-fields-2-search-by-custom-field-kai.php' );
            }
            if ( array_key_exists( 'alt_dropdown_field', $options ) ) {
                add_action( 'save_post', function( $post_id ) {
                    global $wpdb;
                    if ( !array_key_exists( 'magicfields', $_REQUEST ) || !is_array( $_REQUEST['magicfields'] ) ) { return; }
                    #error_log( '##### save_post:$_REQUEST[\'magicfields\']='
                    #    . print_r( $_REQUEST['magicfields'], TRUE ) );
                    $mf_fields = $wpdb->get_results( 'SELECT id, name, post_type, type, options FROM '
                        . MF_TABLE_CUSTOM_FIELDS . ' WHERE type = "alt_dropdown"', OBJECT );
                    foreach ( $_REQUEST['magicfields'] as $field => $values ) {
                        foreach( $mf_fields as $mf_field ) {
                            if ( $mf_field->name === $field && $mf_field->post_type === $_REQUEST['post_type'] ) {
                                $options = unserialize( $mf_field->options );
                                #error_log( '##### save_post:$options=' . print_r( $options, TRUE ) );
                                $updated = FALSE;
                                foreach( $values as $values1 ) {
                                    foreach( $values1 as $values2 ) {
                                        foreach( $values2 as $value ) {
                                            #error_log( '##### save_post:$value=' . print_r( $value, TRUE ) );
                                            $index = strpos( $options['options'], $value );
                                            if ( $index === FALSE || ( $index && !ctype_space(
                                                substr( $options['options'] . "\r\n", $index + strlen( $value ), 1 ) ) ) ) {
                                                $options['options'] = rtrim( $options['options'] ) . "\r\n" . $value;
                                                $updated = TRUE;
                                            }
                                        }
                                    }
                                }
                                if ( $updated ) {
                                    $wpdb->update( MF_TABLE_CUSTOM_FIELDS, array( 'options' => serialize( $options ) ),
                                        array( 'id' => $mf_field->id ) );
                                }
                            }
                        }
                    }
                } );
            }
            if ( array_key_exists( 'alt_get_audio', $options ) ) {
                include( dirname(__FILE__)
                    . '/magic-fields-2-alt-get-audio.php' );
            }
            if ( array_key_exists( 'utility_functions', $options ) ) {
                include( dirname(__FILE__)
                    . '/magic-fields-2-utility-functions.php' );
            }
            if ( is_admin() && array_key_exists( 'alt_embed_field', $options ) ) {
                add_action( 'wp_ajax_' . 'mf2tk_alt_embed_admin_refresh', function() {
                    include dirname(__FILE__) . '/mf2tk_alt_embed_admin_refresh.php';
                } );
            }
            if ( is_admin()
                && ( array_key_exists( 'alt_video_field', $options ) || array_key_exists( 'alt_audio_field', $options ) ) ) {
                add_action( 'wp_ajax_' . 'mf2tk_alt_media_admin_refresh', function() {
                    include dirname(__FILE__) . '/mf2tk_alt_media_admin_refresh.php';
                } );
            }
            if ( array_key_exists( 'alt_video_field', $options ) || array_key_exists( 'alt_audio_field', $options )
                || array_key_exists( 'alt_embed_field', $options ) || array_key_exists( 'alt_image_field', $options ) ) {
                include_once dirname( __FILE__ ) . '/magic-fields-2-get-optional-field.php';
            }
            if ( array_key_exists( 'dumb_macros', $options ) ) {
                add_action( 'admin_print_footer_scripts', function() {
                    global $hook_suffix;
                    if ( $hook_suffix === 'post.php' || $hook_suffix === 'post-new.php' ) {
?>
<script type="text/javascript">
    (function(){
        var a=document.createElement("a");
        a.className="button";
        a.href="#";
        a.textContent="Save as Template";
        jQuery("a#insert-media-button").after(a);
        jQuery(a).click(function(){
            var slug=jQuery("div#slugdiv input#post_name").val();
            var title=jQuery("div#post-body-content div#titlediv input#title").val();
            var text=jQuery("div#post-body-content div#wp-content-editor-container textarea#content").val();
            jQuery.post(ajaxurl,{action:'mf2tk_update_content_macro',slug:slug,title:title,text:text},function(r){
                alert(r);
            });
        });
        var a=document.createElement("a");
        a.className="button";
        a.href="#";
        a.textContent="Insert Template";
        jQuery("a#insert-media-button").after(a);
        jQuery(a).click(function(){
            var windowWidth=jQuery(window).width();
            var windowHeight=jQuery(window).height();
            var width=windowWidth>800?800:Math.floor(windowWidth*9/10);
            var height=Math.floor(windowHeight*9/10);
            var div=jQuery("div#mf2tk-alt-template");
            var style=div[0].style;
            style.position="fixed";
            style.width=width+"px";
            style.height=height+"px";
            style.overflow="auto";
            style.left=Math.floor((windowWidth-width)/2)+"px";
            style.top=Math.floor((windowHeight-height)/2)+"px";
            style.backgroundColor="lightgray";
            style.border="3px solid black";
            style.zIndex=100000;
            style.display="block";
            div.find("button#button-mf2tk-alt-template-close").click(function(){
                this.parentNode.style.display="none";
            });
        });
    }());
</script>
<?php
                    }   # if ( $hook_suffix === 'post.php' ) {
                } );   # add_action( 'admin_print_footer_scripts', function() {
                if ( is_admin() ) {
                    add_action( 'wp_ajax_mf2tk_update_content_macro', function() {
                        global $wpdb;
                        $ids = $wpdb->get_col( <<<EOD
SELECT ID FROM $wpdb->posts WHERE post_type = 'content_macro'
    AND post_title = '$_POST[title]' AND post_status = 'publish'
EOD
                        );
                        $post = [
                            'post_type' => 'content_macro',
                            'post_name' => $_POST['slug'],
                            'post_title' => $_POST['title'],
                            'post_status' => 'publish',
                            'post_content' => $_POST['text']
                        ];
                        if ( $ids ) {
                            $post['ID'] = $ids[0];
                            $id0 = wp_update_post( $post );
                        } else {
                            $id1 = wp_insert_post( $post );
                        }
                        die( !empty ( $id0 ) ? "Content template $id0 updated."
                            : ( !empty( $id1 ) ? "Content template $id1 created."
                                : "Error: Content template not created/updated." ) );
                    } ); # add_action( 'wp_ajax_mf2tk_update_content_macro', function() {
                } #   if ( is_admin() ) {
            } #   if ( array_key_exists( 'dumb_macros', $options ) ) {
        }   # if ( is_array( $options ) ) {
        #add_filter( 'plugin_row_meta', function( $plugin_meta, $plugin_file, $plugin_data, $status ) {
        #    #error_log( '##### filter:plugin_row_meta:$plugin_file=' . $plugin_file );
        #    if ( strpos( $plugin_file, basename( __FILE__ ) ) !== FALSE ) {
        #        $plugin_meta[] = '<a href="' . admin_url( 'options-general.php?page=magic-fields-2-toolkit-page' ) . '">'
        #            . __( 'Settings' ) . '</a>';
        #    }
        #    return $plugin_meta;
        #}, 10, 4 );
		add_filter( 'plugin_action_links', function( $actions, $plugin_file, $plugin_data, $context ) {
            if ( strpos( $plugin_file, basename( __FILE__ ) ) !== FALSE ) {
                array_unshift( $actions, '<a href="' . admin_url( 'options-general.php?page=magic-fields-2-toolkit-page' ) . '">'
                    . __( 'Settings', 'mf2tk' ) . '</a>' );
            }
			return $actions;
		}, 10, 4 );
        add_action( 'plugins_loaded', function() {
            load_plugin_textdomain( 'magic-fields-2-toolkit', FALSE,
                dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        } );
    }
}

new Magic_Fields_2_Toolkit_Init();
