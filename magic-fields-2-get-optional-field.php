<?php

if ( !function_exists( '_mf2tk_get_optional_field' ) ) {
    function _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id, $option ) {
        global $wpdb;
        $sql = sprintf( 'SELECT w.meta_value FROM %s m INNER JOIN %s w ON m.meta_id = w.meta_id' .
            ' WHERE m.post_id = %d AND m.field_name = "%s" AND m.group_count = %d AND m.field_count = %d ',
            MF_TABLE_POST_META, $wpdb->postmeta, $post_id, $field_name . $option, $group_index, $field_index
        );
        error_log( '##### _mf2tk_get_optional_fields():$sql=' . $sql );
        $value = $wpdb->get_var( $sql );
        error_log( "##### _mf2tk_get_optional_fields():{$field_name}{$option}=" . $value );
        return $value;
    }
}

?>