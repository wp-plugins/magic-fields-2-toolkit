<?php

/*
 * Description:   Shortcode for showing Magic Fields 2 custom fields, custom 
 *                groups and custom taxonomies.
 * Documentation: http://magicfields17.wordpress.com/toolkit
 * Author:        Magenta Cuda
 * License:       GPL2
 *
 * shortcode examples
 * [show_custom_field field="date" before="<li>" after="</li>" filter="convert_to_MMDDYYYY" post_id="123"]
 * [show_custom_field field="date<1,2>"]     (first index is group index; second index is field index - must specify both indices)
 * [show_custom_field field="date<1,*>" before="<div>" after="</div>"]     (loop over all field index values)
 * [show_custom_field field="city<1,1>.country<1,1>"]     (recursion supported for related custom posts)
 * [show_custom_field field="category" after="<br>"]     (a field can also be a taxonomy name)
 */

class Magic_Fields_2_Toolkit_Dumb_Shortcodes {
    public function __construct() {
        $show_custom_field = function( $post_id, $names, $before, $after, $filter ) use ( &$show_custom_field ) {
            $content = '';
            $names = explode( '.', $names, 2 );
            $field = $names[0];
            preg_match( '/(\w+)(<(\*|\d+)((,|><)(\*|\d+))?>)?/', $field, $matches );
            #error_log( '$show_custom_field:$matches=' . print_r( $matches, true ) );
            if ( array_key_exists( 1, $matches ) ) { $field = $matches[1]; } else { return '#ERROR#'; }
            if ( array_key_exists( 3, $matches ) ) { $group_index = $matches[3]; } else { $group_index = 1; }
            if ( array_key_exists( 6, $matches ) ) { $field_index = $matches[6]; } else { $field_index = 1; }
            #error_log( "\$show_custom_field:{$field}[{$group_index}][{$field_index}]" );
            if ( $group_index === '*' ) { $group_indices = get_order_group( $field, $post_id ); }
            else { $group_indices = [ $group_index ]; }
            foreach ( $group_indices as $group_index ) {
                if ( $field_index === '*' ) { $field_indices = get_order_field( $field, $group_index, $post_id ); }
                else { $field_indices = [ $field_index ]; }
                foreach ( $field_indices as $field_index ) {
                    $data = get_data( $field, $group_index, $field_index, $post_id );
                    if ( !$data ) {
                        $terms = get_the_terms( $post_id, $field );
                        if ( is_array( $terms ) ) {
                            foreach ( wp_list_pluck( $terms, 'name' ) as $term ) {
                                $content .= $before . $term . $after;
                            }
                        }
                        break;
                    }
                    #echo( '<pre>$data = ' . print_r( $data, true ) . '</pre>' );
                    $value = get( $field, $group_index, $field_index, $post_id );
                    if ($data['type'] === 'related_type' ) {
                        if (array_key_exists( 1, $names) ) {
                            $content .= $show_custom_field( $value, $names[1], $before, $after, $filter );
                        } else {
                            $content .= $before . get_the_title ( $value ) . $after;
                        }
                    } else {
                        if ( is_array( $value ) ) {
                            foreach( $value as $the_value ) {                            
                                if ( $filter ) { $value = call_user_func( $filter, $the_value ); }
                                $content .= $before . $the_value . $after;
                            }
                        } else {
                            if ( $filter ) { $value = call_user_func( $filter, $value ); }
                            $content .= $before . $value . $after;
                        }
                    }
                }
            }
            return $content;
        };

        add_shortcode( 'show_custom_field', function( $atts ) use ( &$show_custom_field ) {
            global $post;
            extract( shortcode_atts( array(
                'field' => 'something',
                'before' => '',
                'after' => '',
                'filter' => '',
                'post_id' => ''
            ), $atts ) );
            if ( !$post_id ) { $post_id = $post->ID; }
            #error_log( '##### show_custom_field:' . print_r( compact( 'field', 'before', 'after', 'filter', 'post_id' ),
            #    TRUE ) );
            return $show_custom_field( $post_id, $field, $before, $after, $filter );
        } );
    }
}   

new Magic_Fields_2_Toolkit_Dumb_Shortcodes();
?>