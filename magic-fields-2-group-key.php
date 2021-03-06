<?php

namespace mf2tk {

# find the real numeric index given the symbolic index

function get_group_index_for_key( $group_name, $field_name, $group_index, $key_name='mf2tk_key' ) {
    global $wpdb;
	if ( !$group_name ) {
		$group_name = $wpdb->get_var( $wpdb->prepare( 'SELECT g.name FROM ' . MF_TABLE_CUSTOM_FIELDS . ' f INNER JOIN '
            . MF_TABLE_CUSTOM_GROUPS . ' g ON f.custom_group_id = g.id WHERE f.name = %s', $field_name ) );
	}
	if ( !$group_name ) { return -1; }
	return $wpdb->get_var( $wpdb->prepare( "SELECT m.group_count FROM $wpdb->postmeta w INNER JOIN " . MF_TABLE_POST_META
		. " m ON w.meta_id = m.meta_id WHERE w.meta_key = %s AND w.meta_value = %s",
        "{$group_name}_{$key_name}", $group_index ) );
}

function alt_get( $field_name, $group_index=1, $field_index=1, $post_id=NULL, $key_name='mf2tk_key'  ) {
    if ( !is_int( $group_index ) ) {
        if ( !( $group_index = get_group_index_for_key( $field_name, $group_index, $post_id, $key_name ) ) ) {
            return NULL;
        }
    }
    return get( $field_name, $group_index, $field_index, $post_id );
}
 
function alt_get_data( $field_name, $group_index=1, $field_index=1, $post_id=NULL, $key_name='mf2tk_key' ) {
    if ( !is_int( $group_index ) ) {
        if ( !( $group_index = get_group_index_for_key( $field_name, $group_index, $post_id, $key_name ) ) ) {
            return NULL;
        }
    }
    return get_data( $field_name, $group_index, $field_index, $post_id );
}

}