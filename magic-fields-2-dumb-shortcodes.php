<?php

/*
 * Description:   Shortcode for showing Magic Fields 2 custom fields, custom 
 *                groups and custom taxonomies.
 * Documentation: http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-2/#shortcode
 * Author:        Magenta Cuda
 * License:       GPL2
 */

class Magic_Fields_2_Toolkit_Dumb_Shortcodes {
    public function __construct() {
        $wrap_value = function( $value, $field, $type, $filters, $before, $after, $separator ) {
            if ( $filters !== NULL ) {
                foreach( explode ( ';', $filters ) as $filter) {
                    $value = call_user_func( $filter, $value, $field, $type );
                }
            }
            if ( $value === '' ) { return ''; }
            return $before . $value . $after . $separator;
        };
        $wrap_field_value = function( $value, $before, $after, $separator, $label, $field ) {
            if ( $value === '' ) { return ''; }
            return str_replace( '<!--$Field-->', $label, str_replace( '<!--$field-->', $field, $before ) ) . $value
                . str_replace( '<!--$Field-->', $label, $after ) . $separator;
        };
        $show_custom_field = function( $post_id, $the_names, $before, $after, $separator, $filter, $field_before,
            $field_after, $field_separator, $multi_before, $multi_after, $multi_separator, $finals )
            use ( &$show_custom_field, $wrap_value, $wrap_field_value ) {
            global $wpdb;
            $content = '';
            foreach ( explode( ';', $the_names ) as $the_name ) {
                $names = explode( '.', $the_name, 2 );
                $field = $names[0];
                preg_match( '/(\w+\*?)(<(\*|\d+)((,|><)(\*|\d+))?>)?/', $field, $matches );
                #error_log( '$show_custom_field:$matches=' . print_r( $matches, true ) );
                if ( array_key_exists( 1, $matches ) ) { $the_field = $matches[1]; }
                else { return '#ERROR#'; }
                if ( array_key_exists( 3, $matches ) ) { $the_group_index = $matches[3]; }
                else { $the_group_index = 1; }
                if ( array_key_exists( 6, $matches ) ) { $the_field_index = $matches[6]; }
                else { $the_field_index = 1; }
                #error_log( "\$show_custom_field:{$the_field}[{$the_group_index}][{$the_field_index}]" );
                if ( substr( $the_field, strlen( $the_field ) - 2 ) === '_*' ) {
                    $the_field = substr( $the_field, 0, strlen( $the_field ) - 2 );
                    $results = $wpdb->get_results( 'SELECT cf.name, cf.label FROM ' . MF_TABLE_CUSTOM_FIELDS
                        . ' cf INNER JOIN '. MF_TABLE_CUSTOM_GROUPS . ' cg WHERE cg.name = "' . $the_field
                        . '" AND cg.post_type = "' . get_post_type( $post_id ) . '" AND cf.custom_group_id = cg.id'
                        . ' ORDER BY cf.display_order', OBJECT_K );
                    #error_log( 'results=' . print_r( $results, TRUE ) );
                    $fields = array_map( function( $row ) { return $row->label; }, $results );
                    #error_log( 'fields=' . print_r( $fields, TRUE ) );
                } else {
                    $column = $wpdb->get_col( 'SELECT label FROM ' . MF_TABLE_CUSTOM_FIELDS . ' WHERE name = "'
                        . $the_field . '"' );
                    #error_log( 'column=' . print_r( $column, TRUE ) );
                    if ( array_key_exists( 0, $column ) ) { $fields = [ $the_field => $column[0] ]; }
                    else { $fields = [ $the_field => $the_field ]; }
                    #error_log( 'fields=' . print_r( $fields, TRUE ) );
                }
                if ( $the_group_index === '*' ) { $group_indices = get_order_group( key( $fields ), $post_id ); }
                else { $group_indices = [ $the_group_index ]; }
                foreach ( $group_indices as $group_index ) {
                    foreach ( $fields as $field => $label ) {
                        $recursion = FALSE;
                        $field_value = '';
                        if ( $the_field_index === '*' ) {
                            $field_indices = get_order_field( $field, $group_index, $post_id );
                        } else {
                            $field_indices = [ $the_field_index ];
                        }
                        #error_log( $field . '<' . $group_index . ',' . $the_field_index . '> $field_indices='
                        #    . print_r( $field_indices, TRUE ) );
                        foreach ( $field_indices as $field_index ) {
                            $data = get_data( $field, $group_index, $field_index, $post_id );
                            #error_log( '$data=' . print_r( $data, TRUE ) );
                            if ( !$data ) {
                                $terms = get_the_terms( $post_id, $field );
                                if ( is_array( $terms ) ) {
                                    foreach ( wp_list_pluck( $terms, 'name' ) as $term ) {
                                        $field_value .= $wrap_value( $term, $field, 'taxonomy', $filter, $before, $after,
                                            $separator );
                                    }
                                    $column = $wpdb->get_col( 'SELECT name FROM ' . MF_TABLE_CUSTOM_TAXONOMY
                                        . ' WHERE type = "' . $field . '"' );
                                    if ( array_key_exists( 0, $column ) ) { $label = $column[0]; }
                                } else {
                                    $values = get_post_custom_values( $field, $post_id );
                                    if ( is_array( $values ) ) {
                                        foreach ( $values as $value ) {
                                            $field_value .= $wrap_value( $value, $field, NULL, $filter, $before, $after,
                                                $separator );
                                        }
                                    }
                                }   
                                continue;
                            }
                            $value = get( $field, $group_index, $field_index, $post_id );
                            if ( $data['type'] === 'related_type' || $data['type'] === 'alt_related_type' ) {
                                if ( is_array( $value ) ) { $values = $value; }
                                else { $values = [ 0 => $value ]; }
                                foreach ( $values as $value ) {
                                    if ( array_key_exists( 1, $names) ) {
                                        $field_value .= $show_custom_field( $value, $names[1], $before, $after, $separator,
                                            $filter, $field_before, $field_after, $field_separator, $multi_before,
                                            $multi_after, $multi_separator, NULL ) . $field_separator;
                                        $recursion = TRUE;
                                    } else {
                                        $field_value .= $wrap_value( $value, $field, $data['type'], $filter, $before,
                                                $after, $separator );
                                    }
                                }
                            } else {
                                if ( is_array( $value ) ) {
                                    $multi_value = '';
                                    foreach ( $value as $the_value ) {                            
                                        $multi_value .= $wrap_value( $the_value, $field, $data['type'], $filter,
                                            $multi_before, $multi_after, $multi_separator );
                                    }
                                    if ( $multi_separator ) {
                                        $multi_value = substr( $multi_value, 0, strlen( $multi_value )
                                            - strlen( $multi_separator ) );
                                    }
                                    #error_log( '$multi_value=' . $multi_value );
                                    $field_value .= $wrap_value( $multi_value, NULL, NULL, NULL, $before, $after,
                                        $separator );
                                } else {
                                    $field_value .= $wrap_value( $value, $field, $data['type'], $filter, $before, $after,
                                        $separator );
                                }
                            }
                            #error_log( '$field_value="' . $field_value . '"' );
                        }
                        if ( !$recursion ) {
                            if ( $separator ) {
                                $field_value = substr( $field_value, 0, strlen( $field_value ) - strlen( $separator ) );
                            }
                            $content .= $wrap_field_value( $field_value, $field_before, $field_after, $field_separator,
                                $label, $field );
                        } else {
                            $content .= $field_value;
                        }
                        #error_log( '$content="' . $content . '"' );
                    }
                    if ( $field_separator ) {
                        $content = substr( $content, 0, strlen( $content ) - strlen( $field_separator ) );
                    }              
                }
            }
            if ( $finals !== NULL ) {
                foreach( explode( ';', $finals ) as $final ) {
                    $content = call_user_func( $final, $content, $the_names );
                }
            }
            #error_log( '$content="' . $content . '"' );
            return $content;
        };

        add_shortcode( 'show_custom_field', function( $atts ) use ( &$show_custom_field ) {
            global $post;
            #error_log( '$atts=' . print_r( $atts, TRUE ) );
            extract( shortcode_atts( array(
                'field' => 'something',
                'filter' => NULL,
                'before' => '',
                'after' => '',
                'separator' => '',
                'multi_before' => NULL,
                'multi_after' => NULL,
                'multi_separator' => NULL,
                'field_before' => '',
                'field_after' => '',
                'field_separator' => '',
                'final' => NULL,
                'post_id' => NULL
            ), $atts ) );
            if ( $post_id === NULL ) { $post_id = $post->ID; }
            if ( $multi_before === NULL ) { $multi_before = $before; }
            if ( $multi_after === NULL ) { $multi_after = $after; }
            if ( $multi_separator === NULL ) { $multi_separator = $separator; }
            #error_log( '##### show_custom_field:' . print_r( compact( 'field', 'before', 'after', 'filter', 'separator',
            #    'field_before', 'field_after', 'field_separator', 'post_id' ), TRUE ) );
            return $show_custom_field( $post_id, $field, $before, $after, $separator, $filter, $field_before,
                $field_after, $field_separator, $multi_before, $multi_after, $multi_separator, $final);
        } );
        remove_filter( 'the_content', 'wpautop' );
    }
}   

new Magic_Fields_2_Toolkit_Dumb_Shortcodes();

function url_to_link( $value, $field, $type ) {
    if ( ( $type === 'related_type' || $type === 'alt_related_type' ) && is_numeric( $value ) ) {
        $value = '<a href="' . get_permalink( $value ) . '">' . get_the_title ( $value ) . '</a>';
    }
    if ( ( $type === 'image' || $type === 'image_media' ) && is_string( $value ) && strpos( $value, 'http' ) === 0 ) {
        $value = '<a href="' . $value . '">' . $value . '</a>';
    }
    return $value;
}

?>