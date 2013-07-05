<?php

/*
 * Description:   Shortcode for showing Magic Fields 2 custom fields, custom 
 *                groups and custom taxonomies.
 * Documentation: http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4/#shortcode
 * Author:        Magenta Cuda
 * License:       GPL2
 */

class Magic_Fields_2_Toolkit_Dumb_Shortcodes {
    public function __construct() {
        $wrap_value = function( $value, $field, $type, $filters, $before, $after, $separator, $classes = array() ) {
            if ( $filters !== NULL ) {
                foreach( explode ( ';', $filters ) as $filter) {
                    if ( function_exists( $filter ) ) {
                        $value = call_user_func( $filter, $value, $field, $type, $classes );
                    }
                }
            }
            if ( $value === NULL || $value === '' || $value === FALSE ) { return ''; }
            return $before . $value . $after . $separator;
        };
        $wrap_field_value = function( $value, $before, $after, $separator, $label, $field, $field_rename, $path ) {
            if ( function_exists( $field_rename ) ) {
                list( $label, $field ) = call_user_func( $field_rename, $label, $field, $path );
            } else {
                $label = trim( $path . '.' . $label, '.' );
            }
            $label = str_replace( ' ', '&nbsp;', $label );
            return str_replace( '<!--$Field-->', $label, str_replace( '<!--$field-->', $field, $before ) ) . $value
                . str_replace( '<!--$Field-->', $label, $after ) . $separator;
        };
        $show_custom_field = function( $post_id, $the_names, $before, $after, $separator, $filter, $field_before,
            $field_after, $field_separator, $multi_before, $multi_after, $multi_separator, $field_rename, $finals, $path )
            use ( &$show_custom_field, $wrap_value, $wrap_field_value ) {
            global $wpdb;
            $content = '';
            $the_fields = $the_names;
            if ( !substr_compare( $the_fields, '{', 0, 1 ) ) { $the_fields = trim( $the_fields, '{}' ); }
            #preg_match_all( '/(([^{};]+)(.{[^{}]+})?)(;|$)/', $the_fields, $fields );
            preg_match_all( '/(([^{};]+)(\.{[^{}]+})?)(;|$)/', $the_fields, $fields );
            $the_fields = $fields[1];
            #error_log( '##### $show_custom_field():$the_fields=' . print_r( $the_fields, TRUE ) );
            foreach ( $the_fields as $the_name ) {
                $names = explode( '.', $the_name, 2 );
                $field = $names[0];
                preg_match( '/([\w-]+\*?)(<(\*|\d+)((,|><)(\*|\d+))?>)?(:([a-zA-Z0-9_]+,?)+)?/', $field, $matches );
                #error_log( '##### $show_custom_field:$matches=' . print_r( $matches, true ) );
                if ( array_key_exists( 1, $matches ) ) { $the_field = $matches[1]; }
                else { return '#ERROR#'; }
                if ( array_key_exists( 3, $matches ) ) { $the_group_index = $matches[3]; }
                else { $the_group_index = 1; }
                if ( array_key_exists( 6, $matches ) ) { $the_field_index = $matches[6]; }
                else { $the_field_index = 1; }
                if ( array_key_exists( 7, $matches ) ) {
                    $the_classes = $matches[7];
                    $the_classes = explode( ',', ltrim( $the_classes, ':' ) );
                } else {
                    $the_classes = NULL;
                }
                #error_log( '##### $show_custom_field():$the_field=' . $the_field . ', $the_classes='
                #    . ( is_array( $the_classes ) ? implode( ', ', $the_classes ) : 'NULL' ) );
                #error_log( "\$show_custom_field:{$the_field}[{$the_group_index}][{$the_field_index}]" );
                $not_magic_field = FALSE;
                if ( substr( $the_field, strlen( $the_field ) - 2 ) === '_*' ) {
                    $the_field = substr( $the_field, 0, strlen( $the_field ) - 2 );
                    $the_field_data = $wpdb->get_results( 'SELECT cf.name, cf.label, cf.description, cf.type FROM '
                        . MF_TABLE_CUSTOM_FIELDS . ' cf INNER JOIN '. MF_TABLE_CUSTOM_GROUPS . ' cg WHERE cg.name = "'
                        . $the_field . '" AND cg.post_type = "' . get_post_type( $post_id )
                        . '" AND cf.custom_group_id = cg.id' . ' ORDER BY cf.display_order', OBJECT_K );
                    if( !$the_field_data ) { continue; }
                    #error_log( 'results=' . print_r( $results, TRUE ) );
                    $fields = array_map( function( $row ) { return $row->label; }, $the_field_data );
                    #error_log( 'fields=' . print_r( $fields, TRUE ) );
                } else {
                    $the_field_data = $wpdb->get_results( 'SELECT name, label, description, type FROM '
                        . MF_TABLE_CUSTOM_FIELDS . ' WHERE name = "' . $the_field . '" AND post_type = "'
                        . get_post_type( $post_id ) . '"', OBJECT_K );
                    #error_log( 'column=' . print_r( $column, TRUE ) );
                    if ( $the_field_data ) {
                        $fields = array( $the_field => $the_field_data[$the_field]->label );
                    } else {
                        $fields = array( $the_field => $the_field );
                        $not_magic_field = TRUE;
                    }
                    #error_log( 'fields=' . print_r( $fields, TRUE ) );
                }
                if ( $the_group_index === '*' ) { $group_indices = get_order_group( key( $fields ), $post_id ); }
                else { $group_indices = array( $the_group_index ); }
                foreach ( $group_indices as $group_index ) {
                    foreach ( $fields as $field => $label ) {
                        $field_value = '';
                        $skip_field = FALSE;
                        $recursion = FALSE;
                        if ( $the_field_index === '*' ) {
                            $field_indices = get_order_field( $field, $group_index, $post_id );
                            if ( !$field_indices ) { $field_indices = array( 1 ); }
                        } else {
                            $field_indices = array( $the_field_index );
                        }
                        #error_log( $field . '<' . $group_index . ',' . $the_field_index . '> $field_indices='
                        #    . print_r( $field_indices, TRUE ) );
                        foreach ( $field_indices as $field_index ) {
                            $data = (array) $the_field_data[$field];
                            #$data = get_data( $field, $group_index, $field_index, $post_id );
                            #error_log( '$field=' . $field . ', $data=' . print_r( $data, TRUE ) );
                            #error_log( '##### $field=' . $field . ', $post_id=' . $post_id
                            #    . ', $data[\'type\']=' . $data['type']
                            #    . ', $data[\'description\']=' . $data['description'] );
                            #error_log( '##### $field=' . $field . ', $post_id=' . $post_id
                            #    . ', $datax[\'type\']=' . $datax['type']
                            #    . ', $datax[\'description\']=' . $datax['description'] );
                            if ( $not_magic_field ) {
                                if ( $the_classes || $field_index > 1 ) { continue; }
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
                                    #error_log( '##### get_post_custom_values()=' . print_r( $values, TRUE ) );
                                    if ( is_array( $values ) ) {
                                        foreach ( $values as $value ) {
                                            if ( is_object( $value ) || is_array( $value ) ) { $value = serialize( $value ); }
                                            $field_value .= $wrap_value( $value, $field, NULL, $filter, $before, $after,
                                                $separator );
                                        }
                                    }
                                }   
                                continue;
                            }
                            $value = get( $field, $group_index, $field_index, $post_id );
                            #error_log( '##### $field=' . $field . ', $data[\'description\']=' . $data['description'] );
                            preg_match( '/\[\*([a-zA-Z0-9_]+,?)+\*\]/', $data['description'], $classes );
                            if ( $classes ) { $classes = explode( ',', trim( $classes[0], '[]*' ) ); }
                            #error_log( '##### $show_custom_field():$field=' . $field . ', $classes='
                            #    . implode( ', ', $classes ) );
                            if ( $the_classes && ( !$classes || !array_intersect( $the_classes, $classes ) ) ) {
                                $skip_field = TRUE;
                                continue;
                            }
                            if ( $data['type'] === 'related_type' || $data['type'] === 'alt_related_type' ) {
                                if ( $value ) {
                                    if ( is_array( $value ) ) { $values = $value; }
                                    else { $values = array( 0 => $value ); }
                                    foreach ( $values as $value ) {
                                        if ( $value ) {
                                            if ( array_key_exists( 1, $names ) ) {
                                                #error_log( '##### $show_custom_field():$names=' . print_r( $names, TRUE ) );
                                                $field_value .= $show_custom_field( $value, $names[1], $before, $after,
                                                    $separator, $filter, $field_before, $field_after, $field_separator,
                                                    $multi_before, $multi_after, $multi_separator, $field_rename, NULL,
                                                    $path . '.' . $label ) . $field_separator;
                                                $recursion = TRUE;
                                            } else {
                                                $field_value .= $wrap_value( $value, $field, $data['type'], $filter, $before,
                                                        $after, $separator, $classes );
                                            }
                                        }
                                    }
                                }
                            } else {
                                if ( is_array( $value ) ) {
                                    if ( $value ) {
                                        $multi_value = '';
                                        foreach ( $value as $the_value ) {                            
                                            $multi_value .= $wrap_value( $the_value, $field, $data['type'], $filter,
                                                $multi_before, $multi_after, $multi_separator, $classes );
                                        }
                                        if ( $multi_separator && substr( $multi_value, strlen( $multi_value )
                                                - strlen( $multi_separator ) ) === $multi_separator ) {
                                            $multi_value = substr( $multi_value, 0, strlen( $multi_value )
                                                - strlen( $multi_separator ) );
                                        }
                                        #error_log( '$multi_value=' . $multi_value );
                                        $field_value .= $wrap_value( $multi_value, NULL, NULL, NULL, $before, $after,
                                            $separator, $classes );
                                    }
                                } else {
                                    $field_value .= $wrap_value( $value, $field, $data['type'], $filter, $before, $after,
                                        $separator, $classes );
                                }
                            }
                            #error_log( '$field_value="' . $field_value . '"' );
                        }
                        if ( $skip_field ) { continue; }
                        if ( !$recursion ) {
                            if ( $separator
                                && substr( $field_value, strlen( $field_value ) - strlen( $separator ) ) === $separator ) {
                                $field_value = substr( $field_value, 0, strlen( $field_value ) - strlen( $separator ) );
                            }
                            $content .= $wrap_field_value( $field_value, $field_before, $field_after, $field_separator,
                                $label, $field, $field_rename, $path );
                        } else {
                            $content .= $field_value;
                        }
                        #error_log( '$content="' . $content . '"' );
                    }
                    if ( $field_separator
                        && substr( $content, strlen( $content ) - strlen( $field_separator ) ) === $field_separator ) {
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
                'field_rename' => '',
                'final' => NULL,
                'post_id' => NULL
            ), $atts ) );
            if ( $post_id === NULL ) { $post_id = $post->ID; }
            if ( $multi_before === NULL ) { $multi_before = $before; }
            if ( $multi_after === NULL ) { $multi_after = $after; }
            if ( $multi_separator === NULL ) { $multi_separator = $separator; }
            #error_log( '##### show_custom_field:' . print_r( compact( 'field', 'before', 'after', 'filter', 'separator',
            #    'field_before', 'field_after', 'field_separator', 'post_id' ), TRUE ) );
            if ( is_numeric( $post_id) ) {
                return $show_custom_field( $post_id, $field, $before, $after, $separator, $filter, $field_before,
                    $field_after, $field_separator, $multi_before, $multi_after, $multi_separator, $field_rename, $final, '' );
            } else {
            }
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