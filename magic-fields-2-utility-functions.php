<?php

/*
 * Copyright 2012 by Magenta Cuda
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * Tested on WordPress 3.5 and Magic Fields 2.1
 */

namespace mf2tk;

/*
 * get_field_id() returns the internal numerical id of the given magic field 
 * in the given post type.
 */

function get_field_id( $field_name, $post_type = NULL ) {
    global $post, $wpdb; 
    static $field_id_cache = array();
    
    if ( !$post_type ) { $post_type = get_post_type( $post->ID ); }

    if ( !array_key_exists( $post_type, $field_id_cache ) ) {
        $field_id_cache[$post_type] = array();

        $sql = sprintf(
            "SELECT name, id FROM %s WHERE post_type = '%s'",
            MF_TABLE_CUSTOM_FIELDS, $post_type );

        $results = $wpdb->get_results( $sql, ARRAY_A );
        foreach ( $results as $result ) {
            $field_id_cache[$post_type][$result['name']] = $result['id'];
        }
    }

    return $field_id_cache[$post_type][$field_name];
}

/*
 * get_group_id() returns the internal numerical id of the given group in the 
 * given post type.
 */

function get_group_id( $group_name, $post_type = NULL ) {
    global $post, $wpdb; 
    static $group_id_cache = array();
    
    if ( !$post_type ) { $post_type = get_post_type( $post->ID ); }

    if ( !array_key_exists( $post_type, $group_id_cache ) ) {
        $group_id_cache[$post_type] = array();

        $sql = sprintf(
            "SELECT name, id FROM %s WHERE post_type = '%s'",
            MF_TABLE_CUSTOM_GROUPS, $post_type );

        $results = $wpdb->get_results( $sql, ARRAY_A );
        foreach ( $results as $result ) {
            $group_id_cache[$post_type][$result['name']] = $result['id'];
        }
    }

    return $group_id_cache[$post_type][$group_name];
}

/*
 * get_field_names() returns a numerically indexed array of all magic field 
 * names in the given post type including fields in groups.
 */

function get_field_names( $post_type = NULL ) {
    global $post, $wpdb; 
    
    if ( !$post_type ) { $post_type = get_post_type( $post->ID ); }
    
    $sql = sprintf( "SELECT name FROM %s WHERE post_type = '%s' " .
        "ORDER BY custom_group_id, display_order",
        MF_TABLE_CUSTOM_FIELDS, $post_type );

    $results = $wpdb->get_results( $sql, ARRAY_A );

    return array_map( '\mf2tk\_get_name_mc', $results );
}

function _get_name_mc( $row ) {
    return $row[ 'name' ];
}
        
/*
 * get_group_names() returns a numerically indexed array of all magic field 
 * group names in the given post type.
 */

function get_group_names( $post_type = NULL ) {
    global $post, $wpdb; 
    
    if ( !$post_type ) { $post_type = get_post_type( $post->ID ); }
  
    $sql = sprintf(
        "SELECT name FROM %s WHERE post_type = '%s'",
        MF_TABLE_CUSTOM_GROUPS, $post_type );

    $results = $wpdb->get_results( $sql, ARRAY_A );

    return array_map( '\mf2tk\_get_name_mc', $results );
}
 
/*
 * get_field_names_in_group() returns a numerically indexed array of all magic
 * field names in the given group in the given post type.
 */

function get_field_names_in_group( $group_name, $post_type = NULL ) {
    global $post, $wpdb; 

    if ( !$post_type ) { $post_type = get_post_type( $post->ID ); }
    $group_id = get_group_id( $group_name, $post_type );

    $sql = sprintf( "SELECT name FROM %s WHERE post_type = '%s' " .
        "AND custom_group_id = %d ORDER BY display_order",
        MF_TABLE_CUSTOM_FIELDS, $post_type, $group_id );

    $results = $wpdb->get_results( $sql, ARRAY_A );

    return array_map( '\mf2tk\_get_name_mc', $results );
}

/*
 * get_field_type() returns the type of the field $field in a post of post type 
 * $post_type. The type is a string - 'textbox', 'checkbox_list',
 * 'radiobutton_list', 'related_type', ... 
 */

function get_field_type( $field, $post_type = NULL ) {
    global $wpdb, $post;
    static $field_type_cache = array();

    if ( !$post_type ) { $post_type = get_post_type( $post->ID ); }

    if ( !array_key_exists( $post_type, $field_type_cache ) ) {
        $field_type_cache[$post_type] = array();

        $sql = sprintf(
            "SELECT name, type FROM %s WHERE post_type = '%s'",
            MF_TABLE_CUSTOM_FIELDS, $post_type );

        $results = $wpdb->get_results( $sql, ARRAY_A );
        foreach ( $results as $result ) {
            $field_type_cache[$post_type][$result['name']] = $result['type'];
        }
    }

    return $field_type_cache[$post_type][$field];
}

/*
 * get_field_options() returns the options of the field $field in a post of 
 * post type $post_type. The options are returned as an array. The keys of
 * array are option names and the values are option values.
 */

function get_field_options( $field, $post_type = NULL ) {
    global $wpdb, $post;
    static $field_options_cache = array();

    if ( !$post_type ) { $post_type = get_post_type( $post->ID ); }

    if ( !array_key_exists( $post_type, $field_options_cache ) ) {
        $field_options_cache[$post_type] = array();

        $sql = sprintf(
            "SELECT name, options FROM %s WHERE post_type = '%s'",
            MF_TABLE_CUSTOM_FIELDS, $post_type );

        $results = $wpdb->get_results( $sql, ARRAY_A );
        foreach ( $results as $result ) {
            $field_options_cache[$post_type][$result['name']] = unserialize( $result['options'] );
        }
    }

    return $field_options_cache[$post_type][$field];
}

/*
 * do_magic_fields() iterates over all the magic fields of the given post in
 * order by group calling the function $callback with arguments field name,
 * group index, field index, field value and field type. The returned values of
 * $callback are concatenated and finally returned by do_magic_fields(). If no 
 * $callback is specified the default function _dump_magic_field_mc() is called.
 * This gives a dump of all magic fields.
 */

function do_magic_fields( $callback = NULL, $post_id = NULL ) {
    global $post, $wpdb; 
    
    if ( !$callback ) { $callback = '\mf2tk\_dump_magic_field_mc'; }
    if ( !$post_id ) { $post_id = $post->ID; }
    $post_type = get_post_type( $post_id );
    $output = '';
    foreach ( \mf2tk\get_group_names( $post_type ) as $group_name ) {
        $field_names = \mf2tk\get_field_names_in_group( $group_name, $post_type );
        #error_log( '##### do_magic_fields():$field_names=' . print_r( $field_names, TRUE ) );
        foreach ( $field_names as $field_name ) {
            if ( $group_indices = get_order_group( $field_name, $post_id ) ) { break; }
        }    
        #error_log( '##### do_magic_fields():$group_indices=' . print_r( $group_indices, TRUE ) );
        foreach ( $group_indices as $group_index ) {
            foreach ( $field_names as $field_name ) {
                $field_indices = get_order_field( $field_name, $group_index,
                    $post_id );
                #error_log( '##### do_magic_fields():$field_indices=' . print_r( $field_indices, TRUE ) );
                foreach ( $field_indices as $field_index ) {
                    $value = get( $field_name, $group_index, $field_index,
                        $post_id );
                    $type = \mf2tk\get_field_type( $field_name, $post_type );
                    #error_log( "##### do_magic_fields():$field_name=" . print_r( $value, TRUE ) );
                    $output .= call_user_func( $callback, $field_name, $group_index,
                        $field_index, $value, $type ) . "\n";
                }
            }
        }
    }
    return $output;
}

function _dump_magic_field_mc( $field, $group_index, $field_index, $value, $type ) {
    return $type . ': ' . $field . '[' . $group_index . '][' . $field_index . '] = '
        . print_r( $value, true );
}

/*
 * display_magic_fields() displays all magic fields of the given post in a 
 * easy to read format.
 */

function display_magic_fields( $callback = NULL, $post_id = NULL ) {
    global $post, $wpdb; 
    
    if ( !$callback ) { $callback = '\mf2tk\display_magic_field'; }
    if ( !$post_id ) { $post_id = $post->ID; }
    $post_type = get_post_type( $post_id );
    echo( '<div style="border:3px solid black;padding:5px 10px;margin:5px;">' );
    foreach ( get_group_names( $post_type ) as $group_name ) {
        echo( '<div style="border:2px solid black;margin:5px 0;">' );
        $field_names = get_field_names_in_group( $group_name, $post_type );
        foreach ( $field_names as $field_name ) {
            if ( $group_indices = get_order_group( $field_name, $post_id ) ) { break; }
        }    
        echo( '<ul>' );
        foreach ( $group_indices as $group_index ) {
            echo( '<li style="list-style-type:' . ( ( count( $group_indices ) > 1 )
                ? 'square' : 'none' ) . ';">' );
            echo( '<strong>' . $group_name . 
                ( ( count( $group_indices ) > 1 ) ? '[' . $group_index . ']'
                : '' ) . '</strong>' );
            foreach ( $field_names as $field_name ) {
                $field_indices = get_order_field( $field_name, $group_index,
                    $post_id );
                echo( '<ul>' );
                foreach ( $field_indices as $field_index ) {
                    $value = get( $field_name, $group_index, $field_index,
                        $post_id, $field_indices );
                    $type = get_field_type( $field_name, $post_type );
                    $options = get_field_options( $field_name, $post_type );
                    call_user_func( $callback, $field_name, $group_index,
                        $field_index, $value, $type, $options, $field_indices );
                }
                echo( '</ul>' );
            }
            echo( '</li>' );
        }
        echo( '</ul>' );
        echo( '</div>' );
    }
    echo( '</div>' );
}

function display_magic_field( $field, $group_index, $field_index, $value, $type,
    $options, $field_indices ) {
    static $display_index = 0;
    ++$display_index;
    echo( '<li style="list-style-type:' . ( ( $field_index == 1 ) ? 'square'
        : 'none' ) . ';">' );
    echo $type . ': <strong>' . $field .
        ( ( count( $field_indices ) > 1 ) ? '[' . $field_index . ']' : '' ) .
        '</strong> = ';
    switch ( $type ) {
    case 'textbox':
    case 'alt_textbox':
    case 'radiobutton_list':
    case 'datepicker':
        echo( $value );
        break;
    case 'checkbox_list':
        echo( implode( ', ', $value ) );
        break;
    case 'dropdown':
    case 'alt_dropdown':
        if ( $options[ 'multiple' ] ) {
            echo( implode( ', ', $value ) );
        } else {
            echo( $value );
        }
        break;
    case 'checkbox':
        echo( $value );
        break;
    case 'image':
    case 'image_media':
        $display_id = "overlay-mc-$display_index";
        echo( '<a href="#" onclick="document.getElementById(\'' . $display_id . '\').style.display=\'block\';return false;">' . $value . '</a>' );
?>
<div id="<?php echo( $display_id ); ?>" style="display:none;position:fixed;left:0px;top:0px;right:0px;bottom:0px;z-index:100;background-color:rgba(63,63,63,0.7);">
  <div style="position:absolute;left:50px;top:50px;bottom:50px;right:50px;border:3px solid black;background-color:white;padding:10px;text-align:center;overflow:auto;"
    onclick="document.getElementById('<?php echo( $display_id ); ?>').style.display='none';return false;">
    <div style="text-align:right;"><button style="background-color:crimson;color:white;">X</button></div><img src="<?php echo( $value ); ?>"></div></div>
<?php
        break;
    case 'file':
        echo( '<a href="' . $value . '">' . $value . '</a>' );
        break;
    case 'related_type':
    case 'alt_related_type':
        if ( is_array( $value ) ) { $values = $value; }
        else { $values = array( 0 => $value ); }
        foreach( $values as $value ) {
            $display_id = "overlay-mc-$display_index";
            echo( '<a href="#" onclick="document.getElementById(\'' . $display_id . '\').style.display=\'block\';return false;">'
              . get_the_title( $value ) . '</a>' );
?>
<div id="<?php echo( $display_id ); ?>" style="display:none;position:fixed;left:0px;top:0px;right:0px;bottom:0px;z-index:100;background-color:rgba(63,63,63,0.7);">
  <div style="position:absolute;left:50px;top:50px;bottom:50px;right:50px;border:3px solid black;background-color:white;padding:10px;text-align:center;overflow:auto;"
    onclick="document.getElementById('<?php echo( $display_id ); ?>').style.display='none';return false;">
    <div style="text-align:right;"><button style="background-color:crimson;color:white;">X</button></div>
    <iframe src="<?php echo( get_permalink( $value ) ); ?>" style="width:100%;height:90%;"></iframe></div></div>
<?php
        }
        break;
    case 'multiline':
    case 'markdown_editor':
        $display_id = "overlay-mc-$display_index";
        echo( '<a href="#" onclick="document.getElementById(\'' . $display_id . '\').style.display=\'block\';return false;">'
          . substr( htmlspecialchars( $value ), 0, 64 ) . ( ( strlen( $value ) > 64 ) ? ' ...' : '' ) . '</a>' );
?>
<div id="<?php echo( $display_id ); ?>" style="display:none;position:fixed;left:0px;top:0px;right:0px;bottom:0px;z-index:100;background-color:rgba(63,63,63,0.7);">
  <div style="position:absolute;left:50px;top:50px;bottom:50px;right:50px;border:3px solid black;background-color:white;padding:10px;text-align:center;overflow:auto;"
    onclick="document.getElementById('<?php echo( $display_id ); ?>').style.display='none';return false;">
    <div style="text-align:right;"><button style="background-color:crimson;color:white;">X</button></div><?php echo( $value ); ?></div></div>
<?php
        break;
    case 'color_picker':
        echo( $value . '&nbsp;=>&nbsp;<span style="background-color:' . $value .
            ';">&nbsp;&nbsp;&nbsp;&nbsp;</span>' );
        break;
    case 'audio':
        echo( '<a href="' . $value . '">' . $value . '</a>' );
        if ( function_exists( 'alt_get_audio' ) ) {
            echo( alt_get_audio( $field, $group_index, $field_index ) );
        } else {
            echo( get_audio( $field, $group_index, $field_index ) );
        }
        break;
    default:
        echo( print_r( $value, true ) );
    }
    echo( '</li>' );
}

?>
