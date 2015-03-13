<?php

/*
 * Description:   Macros for HTML and Shortcodes
 * Documentation: http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4/#macros
 * Author:        Magenta Cuda
 * License:       GPL2
 */

/*  Copyright 2013  Magenta Cuda  (email:magenta.cuda@yahoo.com)

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

class Magic_Fields_2_Toolkit_Dumb_Macros {
    public function __construct() {
        add_action( 'init', function() {
            register_post_type( 'content_macro', array(
                'label' => 'Content Templates',
                'description' => 'defines a single macro for HTML and WordPress shortcodes macro',
                'public' => TRUE,
                'exclude_from_search' => TRUE,
                'public_queryable' => FALSE,
                'show_ui' => TRUE,
                'show_in_nav_menus' => FALSE,
                'show_in_menu' => TRUE,
                'menu_position' => 50
            ) );
        } );
        add_filter( 'post_row_actions', function( $actions, $post ) {
            if ( get_post_type( $post ) == 'content_macro' ) { unset( $actions['view'] ); }
            return $actions;
        }, 10, 2 );
        # $alt_template() outputs the HTML for selecting the content template 
        $alt_template = function( ) {
?>
<!-- start alt_template -->
<div id="mf2tk-alt-template" style="display:none;">
    <h3 style="float:left;">Get Content Template</h3>
    <button id="button-mf2tk-alt-template-close" style="float:right;">X</button>
    <div style="clear:both;">
<?php
            $alt_template = new alt_template_field();
            echo $alt_template->display_field( null, null, null );
?>
    </div>
</div>
<!-- end alt_template -->
<?php
        };   # $alt_template = function( ) {
        add_action( 'admin_footer-post.php', $alt_template );
        add_action( 'admin_footer-post-new.php', $alt_template );
        $do_macro = null;
        $do_macro = function( $atts, $macro ) use ( &$do_macro ) {
            global $post;
            global $wpdb;
            static $saved_inline_macros = [ ];
            $mf_table_custom_groups = MF_TABLE_CUSTOM_GROUPS;
            $mf_table_custom_fields = MF_TABLE_CUSTOM_FIELDS;
            $mf_table_post_meta = MF_TABLE_POST_META;
            if ( !$atts ) { $atts = [ ]; }
            #if ( $macro ) { $macro = htmlspecialchars_decode( $macro ); }
            # first check if the macro invocation has a post parameter of the form a comma separated list of post ids
            # or related_type fields or alt_related_type fields
            if ( is_array( $atts ) && array_key_exists( 'post', $atts ) ) {
                $att_post = $atts['post'];
                unset( $atts['post'] );
                $result = '';
                foreach ( explode( ';', $att_post ) as $post_id ) {
                    if ( !is_numeric( $post_id ) ) {
                        # post parameter is a related_type field or alt_related_type field
                        $post_ids = explode( ',', do_shortcode( <<<EOD
[show_custom_field field="$post_id" separator="," filter="tk_filter_by_type__related_type__alt_related_type"]
EOD
                        ) );
                    } else {
                        $post_ids = [ $post_id ];
                    }
                    $save_post = $post;
                    foreach ( $post_ids as $post_id1 ) {
                        if ( !$post_id1 ) { continue; }
                        $post = get_post( $post_id1 );
                        $result .= $do_macro( $atts, $macro );
                    }
                    $post = $save_post;
                }
                return $result;
            }
            # next check if the macro invocation has an iterator parameter of the format
            # group_iterator="iterator_name:group_name" or field_iterator="iterator_name:field_name<group_index>"
            # only one iterator parameter is allowed per macro invocation but macros can be nested to allow nested iterations
            if ( is_array( $atts ) && ( $group = array_key_exists( 'group_iterator', $atts )
                or $field = array_key_exists( 'field_iterator', $atts ) ) ) {
                if ( $group ) {
                    # find the group indexes
                    list( $iterator_name, $group_name ) = explode( ':', $atts['group_iterator'] );
                    unset( $atts['group_iterator'] );
                    $indexes = $wpdb->get_col( $wpdb->prepare( <<<EOD
SELECT m.group_count FROM $mf_table_custom_groups g, $mf_table_custom_fields f, $mf_table_post_meta m
    WHERE g.id = f.custom_group_id AND f.name = m.field_name
        AND g.name = %s AND g.post_type = '$post->post_type' AND m.post_id = $post->ID
    GROUP BY m.group_count ORDER BY m.group_count
EOD
                    , $group_name ) );
                } else if ( $field ) {
                    # find the field indexes; * for group index means over all groups
                    if ( preg_match( '/^(\w+):(\w+)(<(\*|\d+)>)?$/', $atts['field_iterator'], $matches ) ) {
                        unset( $atts['field_iterator'] );
                        if ( array_key_exists( 4, $matches ) and $matches[4] !== '*' ) {
                            $indexes = $wpdb->get_col( $wpdb->prepare( <<<EOD
SELECT m.field_count FROM $mf_table_post_meta m
    WHERE m.field_name = %s AND m.post_id = $post->ID AND m.group_count = %d ORDER BY m.field_count
EOD
                                , $matches[2], $matches[4] ) );
                        } else {
                            $indexes = $wpdb->get_col( $wpdb->prepare( <<<EOD
SELECT m.field_count FROM $mf_table_post_meta m
    WHERE m.field_name = %s AND m.post_id = $post->ID GROUP BY m.field_count ORDER BY m.field_count
EOD
                                , $matches[2] ) );
                        }
                        $iterator_name = $matches[1];
                    } else {
                    }
                }
                if ( empty( $indexes ) ) {
                    return <<<EOD
<div style="border:2px solid red;padding:5px;">Error: invalid group_iterator or field_iterator parameter</div>
EOD;
                }
                $result = '';
                foreach ( $indexes as $index ) {
                    $atts[$iterator_name] = $index;
                    $result .= $do_macro( $atts, $macro );
                }
                return $result;
            }
            # finally check for generic iterator parameter - iterator="name:12345;"abcde";'abcde';alpha<1,1>"
            if ( is_array( $atts ) && array_key_exists( 'iterator', $atts ) ) {
                if ( ( $ret = preg_match( '#^(\w+):((\s*(\d+|"[^"]*"|\'[^\']*\'|[^;]+)(;|$))+)#',
                    $atts['iterator'], $matches ) ) === 1 ) {
                    unset( $atts['iterator'] );
                    $iterator_name = $matches[1];
                    if ( ( $ret = preg_match_all( '#(\s*(\d+)|\s*"([^"]*)"|\s*\'([^\']*)\'|\s*([^\s;]+))(;|$)#',
                        $matches[2], $matches1, PREG_SET_ORDER ) ) !== false && $ret !== 0 ) {
                        $iterator_values = [];
                        foreach( $matches1 as $matches2 ) {
                            if ( $matches2[2] ) {          # number
                                $iterator_values[] = $matches2[2];
                            } else if ( $matches2[3] ) {   # double quoted string
                                $iterator_values[] = $matches2[3];
                            } else if ( $matches2[4] ) {   # single quoted string
                                $iterator_values[] = $matches2[4];
                            } else if ( $matches2[5] ) {   # magic field specifier
                                $iterator_values = array_merge( $iterator_values, explode( '!@#$%', do_shortcode( <<<EOD
[show_custom_field field="$matches2[5]" separator="!@#$%"]
EOD
                                ) ) );
                            }
                        }
                    } else {
                        return '<div style="border:2px solid red;color:red;padding:5px;">show_macro error: '
                            . 'invalid iterator parameter.</div>';
                    }           
                } else {
                    return '<div style="border:2px solid red;color:red;padding:5px;">show_macro error: '
                        . 'invalid iterator parameter.</div>';
                }    
                $result = '';
                foreach ( $iterator_values as $iterator_value ) {
                    if ( !$iterator_value ) { continue; }
                    $atts[$iterator_name] = $iterator_value;
                    $result .= $do_macro( $atts, $macro );
                }
                return $result;
            }
            if ( !$macro ) {
                if ( !empty( $atts['macro'] ) ) {
                    if ( !empty( $saved_inline_macros[$atts['macro']] ) ) {
                        # saved inline macro defintions have priority over Content Macro definitions
                        $macro = $saved_inline_macros[$atts['macro']];
                    } else {
                        $macro = $wpdb->get_var( "SELECT post_content from $wpdb->posts WHERE post_type = 'content_macro' "
                            . "AND post_name = '$atts[macro]'" );
                        if ( !$macro ) {
                            return '<div style="border:2px solid red;color:red;padding:5px;">'
                                . "show_macro error: \"$atts[macro]\" is not the slug of a Content Macro.</div>";
                        }
                    }
                } else {
                    return '<div style="border:2px solid red;color:red;padding:5px;">show_macro error: no macro specified.</div>';
                }
            } else {
                # There is an inline macro definition
                if ( !empty( $atts['save_inline_macro_as'] ) ) {
                    # save inline macro defintion for later use in the same session
                    $saved_inline_macros[$atts['save_inline_macro_as']] = $macro;
                }
            }
            unset( $atts['macro'] );
            # scan for defaults of the form <!-- $#alpha# = "beta"; --> or <!-- $#alpha# = 'beta'; -->
            # or <!-- $#alpha = beta<1,1>; -->
            if ( preg_match_all( '/<!--\s*\$#([\w-]+)#\s*=\s*(("([^"]+)")|(\'([^\']+)\')|([^;]+));\s*-->/',
                $macro, $assignments, PREG_SET_ORDER ) ) {
                foreach ( $assignments as $assignment ) {
                    if ( !array_key_exists( $assignment[1], $atts ) ) {
                        if ( array_key_exists( 7, $assignment ) ) {
                            # assignment is to value of custom field
                            if ( $filter = strpos( $assignment[7], "@" ) ) {
                                $field = substr( $assignment[7], 0, $filter );
                                $filter = substr( $assignment[7], $filter + 1 );
                                $atts[$assignment[1]] = do_shortcode( <<<EOD
[show_custom_field field="$field" filter="$filter" separator=","]
EOD
                                );
                            } else {
                                $atts[$assignment[1]] = do_shortcode( <<<EOD
[show_custom_field field="$assignment[7]" separator=","]
EOD
                                );
                            }
                        } else {
                            # assignment is to a string constant
                            $atts[$assignment[1]] = trim( $assignment[2], '"\'' );
                        }
         
                    } else {
                    }
                }
            }
            # first handle conditional text inclusion
            # if statement is #if($#alpha#)# or #if($#alpha#=$#beta#)# or #if($#alpha#="gamma")# or #if($#alpha#='delta')#
            $if_count = preg_match_all(
                '/#if\(\s*\$#([\w-]+)#(\s*=\s*((\$#([\w-]+)#)|(("|\'|&#8217;|&#8221;)(.*?)\7)|(&#8220;(.*?)&#8221;)|(&#8216;(.*?)&#8217;)))?\s*\)#/',
                $macro, $if_matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
            $end_count = preg_match_all( '/#endif#/', $macro, $end_matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
            if ( $if_count && $if_count == $end_count ) {
                $includes = array_map( function( $match ) use ( $atts ) {
                    if ( !array_key_exists( $match[1][0], $atts ) ) { return false; }
                    $value = $atts[$match[1][0]];
                    if ( array_key_exists( 11, $match ) ) {
                        # #if($#alpha#='gamma')#
                        return $value === $match[12][0];
                    } else if ( array_key_exists( 9, $match ) ) {
                        # #if($#alpha#="gamma")#
                        return $value === $match[10][0];
                    } else if ( array_key_exists( 7, $match ) ) {
                        # #if($#alpha#='delta')#
                        # #if($#alpha#="gamma")#
                        return $value === $match[8][0];
                    } else if ( array_key_exists( 4, $match ) ) {
                        # #if($#alpha#=$#beta#)#
                        if ( array_key_exists( $match[5][0], $atts ) ) { return $atts[$match[5][0]] === $value; }
                        return $value === '';
                    } else if ( !array_key_exists( 2, $match ) ) {
                        # #if($#alpha#)#
                        return !is_null( $value ) && $value !== '';
                    }
                }, $if_matches );
                $i = 0;
                while ( $if_matches && $end_matches ) {
                    # find if that matches the first endif
                    while ( $if_matches[$i][0][1] < $end_matches[0][0][1] ) {
                        if ( ++$i == count( $if_matches ) ) { break; }
                    }
                    if ( --$i < 0 ) {
                        # error
                        #error_log( '##### shortcode:show_macro:Error: unmatched "#endif"' );
                        return 'show_macro:Error: unmatched "#endif"';
                        #break;
                    }
                    $include = TRUE;
                    for ( $j = 0; $j <= $i; ++$j ) { if ( !$includes[$j] ) { $include = FALSE; break; } }
                    $exclude_by_parent = !$include && $j != $i;
                    $start0 = $if_matches[$i][0][1];
                    $length0 = ( $end_matches[0][0][1] + strlen( $end_matches[0][0][0] ) ) - $start0;
                    #error_log( '##### shortcode:show_macro:to be replaced="' . substr( $macro, $start0, $length0 ) . '"');
                    $start1 = $if_matches[$i][0][1] + strlen( $if_matches[$i][0][0] );
                    $length1 = $end_matches[0][0][1] - $start1;
                    if ( ( $start2 = strpos( $macro, '#else#', $start1 ) ) !== FALSE ) {
                        $length1 = $start2 - $start1;
                        $start2 += 6;
                        $length2 = $end_matches[0][0][1] - $start2;
                    } else {
                        $start2 = $start1;   # irrelevant since $length2 == 0
                        $length2 = 0;
                    }
                    if ( $include ) {
                        # replace with #if($#...#)# clause
                        #error_log( '##### shortcode:show_macro:replacement="' . substr( $macro, $start1, $length1 ) . '"');
                        $macro = substr_replace( $macro, substr( $macro, $start1, $length1 ), $start0, $length0 );
                        $offset = $length1 - $length0;
                    } else if ( !$exclude_by_parent ) {
                        # replace with #else# clause
                        #error_log( '##### shortcode:show_macro:replacement=""');
                        $macro = substr_replace( $macro, substr( $macro, $start2, $length2 ), $start0, $length0 );
                        $offset = $length2 - $length0;
                    } else {
                        $offset = 0;
                    }
                    # remove the matched if
                    array_splice( $if_matches, $i, 1 );
                    array_splice( $includes, $i, 1 );
                    # adjust offsets after text replacement
                    for ( $j = $i; $j < count( $if_matches ); ++$j ) {
                        $if_matches[$j][0][1] += $offset;
                        $if_matches[$j][1][1] += $offset;
                    }
                    if ( $i ) { --$i; }
                    #remove the matched endif
                    array_shift( $end_matches );
                    # adjust offsets after text replacement
                    for ( $j = 0; $j < count( $end_matches ); ++$j ) { $end_matches[$j][0][1] += $offset; }
                }
                if ( $if_matches || $end_matches ) {
                    # error
                    #error_log( '##### shortcode:show_macro:Error: unmatched "#if" or "#endif"' );
                    return 'show_macro:Error: unmatched "#if" or "#endif"';
                }
            } else if ( $if_count || $end_count ) {
                # error
                #error_log( '##### shortcode:show_macro:Error: count of "#if" not equal count of "#endif"' );
                return 'show_macro:Error: count of "#if" not equal count of "#endif"';
            }
            # finally do macro replacements
            foreach ( $atts as $att => $val ) {
                $macro = str_replace( '$#' . $att . '#', $val, $macro );
            }
            $macro = do_shortcode( $macro );
            return $macro;
        };   # $do_macro = function( $atts, $macro ) {
        add_shortcode( 'show_macro', $do_macro );
        for ( $i = 1; $i < 9; $i++ ) { add_shortcode( 'show_macro' . $i, $do_macro ); }
    }   # public function __construct() {

}   

new Magic_Fields_2_Toolkit_Dumb_Macros();

?>