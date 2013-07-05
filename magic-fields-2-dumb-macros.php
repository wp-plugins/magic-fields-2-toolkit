<?php

/*
 * Description:   Macros for HTML and Shortcodes
 * Documentation: http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4/#macros
 * Author:        Magenta Cuda
 * License:       GPL2
 */

class Magic_Fields_2_Toolkit_Dumb_Macros {
    public function __construct() {
        add_action( 'init', function() {
            register_post_type( 'content_macro', array(
                'label' => 'Content Macros',
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
        add_shortcode( 'show_macro', function( $atts ) {
            global $wpdb;
            #error_log( '##### shortcode:show_macro:$atts=' . print_r( $atts, TRUE ) );
            $macro = $wpdb->get_var( "SELECT post_content from $wpdb->posts WHERE post_type = 'content_macro' "
                . "AND post_name = '$atts[macro]'" );
            if ( !$macro ) { return ''; }
            #error_log( '##### shortcode:show_macro:$macro=' . print_r( $macro, TRUE ) );
            unset( $atts['macro'] );
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
                    while ( $if_matches[$i][0][1] < $end_matches[0][0][1] ) {
                        if ( ++$i == count( $if_matches ) ) { break; }
                    }
                    if ( --$i < 0 ) {
                        # error
                        error_log( '##### shortcode:show_macro:Error: unmatched "#endif"' );
                        break;
                    }
                    $include = TRUE;
                    for ( $j = 0; $j <= $i; ++$j ) { if ( !$includes[$j] ) { $include = FALSE; break; } }
                    $exclude_by_parent = !$include && $j != $i;
                    $start0 = $if_matches[$i][0][1];
                    $length0 = ( $end_matches[0][0][1] + strlen( $end_matches[0][0][0] ) ) - $start0;
                    #error_log( '##### shortcode:show_macro:to be replaced="' . substr( $macro, $start0, $length0 ) . '"');
                    if ( $include ) {
                        $start1 = $if_matches[$i][0][1] + strlen( $if_matches[$i][0][0] );
                        $length1 = $end_matches[0][0][1] - $start1;
                        #error_log( '##### shortcode:show_macro:replacement="' . substr( $macro, $start1, $length1 ) . '"');
                        $macro = substr_replace( $macro, substr( $macro, $start1, $length1 ), $start0, $length0 );
                        $offset = $length1 - $length0;
                    } else if ( !$exclude_by_parent ) {
                        #error_log( '##### shortcode:show_macro:replacement=""');
                        $macro = substr_replace( $macro, '', $start0, $length0 );
                        $offset = -$length0;
                    } else {
                        $offset = 0;
                    }
                    array_splice( $if_matches, $i, 1 );
                    array_splice( $includes, $i, 1 );
                    for ( $j = $i; $j < count( $if_matches ); ++$j ) {
                        $if_matches[$j][0][1] += $offset;
                        $if_matches[$j][1][1] += $offset;
                    }
                    if ( $i ) { --$i; }
                    array_shift( $end_matches );
                    for ( $j = 0; $j < count( $end_matches ); ++$j ) { $end_matches[$j][0][1] += $offset; }
                }
                if ( $if_matches || $end_matches ) {
                    error_log( '##### shortcode:show_macro:Error: unmatched "#if" or "#endif"' );
                    # error
                }
            } else if ( $if_count || $end_count ) {
                error_log( '##### shortcode:show_macro:Error: count of "#if" not equal count of "#endif"' );
                # error
            }
            foreach ( $atts as $att => $val ) {
                $macro = str_replace( '$#' . $att . '#', $val, $macro );
            }
            #error_log( '##### shortcode:show_macro:$macro=' . print_r( $macro, TRUE ) );
            $macro = do_shortcode( $macro );
            #error_log( '##### shortcode:show_macro:$macro=' . print_r( $macro, TRUE ) );
            return $macro;
        } );
    }
}   

new Magic_Fields_2_Toolkit_Dumb_Macros();

?>