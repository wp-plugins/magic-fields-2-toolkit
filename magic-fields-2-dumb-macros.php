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
            #error_log( '###### action:post_row_actions:$actions=' . print_r( $actions, TRUE ) );
            if ( get_post_type( $post ) == 'content_macro' ) { unset( $actions['view'] ); }
            return $actions;
        }, 10, 2 );
        $do_macro = function( $atts, $macro ) {
            global $post;
            global $wpdb;
            $mf_table_custom_groups = MF_TABLE_CUSTOM_GROUPS;
            $mf_table_custom_fields = MF_TABLE_CUSTOM_FIELDS;
            $mf_table_post_meta = MF_TABLE_POST_META;
            static $saved_inline_macros = array();
            #error_log( '##### shortcode:show_macro:$atts=' . print_r( $atts, TRUE ) );
            if ( $group = array_key_exists( 'group_iterator', $atts )
                or $field = array_key_exists( 'field_iterator', $atts ) ) {
                #$indexes = $wpdb->get_col( "SELECT $column FROM " . MF_TABLE_POST_META
                #    . " WHERE post_id = $post->ID AND field_name = '$field_name'" );
                if ( $group ) {
                    list( $iterator_name, $group_name ) = explode( ':', $atts['group_iterator'] );
                    unset( $atts['group_iterator'] );
                    $indexes = $wpdb->get_col( $wpdb->prepare( <<<EOD
SELECT m.group_count FROM $mf_table_custom_groups g, $mf_table_custom_fields f, $mf_table_post_meta m
    WHERE g.id = f.custom_group_id AND f.name = m.field_name AND g.name = %s AND m.post_id = $post->ID
EOD
                    , $group_name ) );
                } else if ( $field ) {
                    preg_match( '^/(\w+):(\w+)<(\d+)>$/', $atts['field_iterator'], $matches );
                    unset( $atts['field_iterator'] );
                    $indexes = $wpdb->get_col( $wpdb->prepare( <<<EOD
SELECT m.field_count FROM $mf_table_post_meta m WHERE m.field_name = %s AND m.post_id = $post->ID AND m.group_count = %d
EOD
                        , $matches[2], $matches[3] ) );
                    $iterator_name = $matches[1];
                }
                $result = '';
                foreach ( $indexes as $index ) {
                    $atts[$iterator_name] = $index;
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
                #error_log( '##### shortcode:show_macro:$macro=' . print_r( $macro, TRUE ) );
                if ( !empty( $atts['save_inline_macro_as'] ) ) {
                    # save inline macro defintion for later use in the same session
                    $saved_inline_macros[$atts['save_inline_macro_as']] = $macro;
                }
            }
            unset( $atts['macro'] );
            # scan for defaults of the form <!-- $#alpha# = "beta"; --> or <!-- $#alpha# = 'beta'; -->
            preg_match_all( '/<!--\s*\$#([\w-]+)#\s*=\s*(("([^"]+)")|(\'([^\']+)\'));\s*-->/', $macro, $assignments, PREG_SET_ORDER );
            #error_log( '##### shortcode:show_macro:$assignments=' . print_r( $assignments, TRUE ) );
            foreach ( $assignments as $assignment ) {
                if ( !array_key_exists( $assignment[1], $atts ) ) {
                    $atts[$assignment[1]] = trim( $assignment[2], '"\'' );
                    #error_log( '##### shortcode:show_macro:$atts[\'' . $assignment[1] . '\']=\'' . $atts[$assignment[1]] . '\';' );
                }
            }
            # first handle conditional text inclusion
            $if_count = preg_match_all( '/#if\(\s*\$#([\w-]+)#\s*\)#/', $macro, $if_matches,
                PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
            #error_log( '##### shortcode:show_macro:$if_matches=' . print_r( $if_matches, TRUE ) );
            $end_count = preg_match_all( '/#endif#/', $macro, $end_matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE );
            #error_log( '##### shortcode:show_macro:$end_matches=' . print_r( $end_matches, TRUE ) );
            if ( $if_count && $if_count == $end_count ) {
                $includes = array_map( function( $match ) use ( $atts ) {
                    return ( array_key_exists( $match[1][0], $atts ) );
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
            #error_log( '##### shortcode:show_macro:$macro=' . print_r( $macro, TRUE ) );
            $macro = do_shortcode( $macro );
            #error_log( '##### shortcode:show_macro:$macro=' . print_r( $macro, TRUE ) );
            return $macro;
        };
        add_shortcode( 'show_macro', $do_macro );
    }
}   

new Magic_Fields_2_Toolkit_Dumb_Macros();

?>