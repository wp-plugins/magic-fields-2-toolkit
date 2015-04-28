<?php

if ( !function_exists( '_mf2tk_get_optional_field' ) ) {
    function _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id, $option ) {
        global $wpdb;
        $sql = sprintf( 'SELECT w.meta_value FROM %s m INNER JOIN %s w ON m.meta_id = w.meta_id' .
            ' WHERE m.post_id = %d AND m.field_name = "%s" AND m.group_count = %d AND m.field_count = %d ',
            MF_TABLE_POST_META, $wpdb->postmeta, $post_id, $field_name . $option, $group_index, $field_index
        );
        #error_log( '##### _mf2tk_get_optional_fields():$sql=' . $sql );
        $value = $wpdb->get_var( $sql );
        #error_log( "##### _mf2tk_get_optional_fields():{$field_name}{$option}=" . $value );
        return $value;
    }
}

if ( !function_exists( '_mf2tk_get_media_srcs' ) ) {
    function _mf2tk_get_media_srcs( $field_name, $group_index, $field_index, $post_id, $class_name ) {
        switch ( $class_name ) {
        case "alt_audio_field":
            $wp_get_media_extensions = 'wp_get_audio_extensions';
            break;
        case "alt_video_field":
            $wp_get_media_extensions = 'wp_get_video_extensions';
            break;
        default:
            return [];
        }
        # get main src
        $src = get_data2( $field_name, $group_index, $field_index, $post_id )['meta_value'];
        # get optional fallback
        $fallback = _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id,
            $class_name::$suffix_fallback );
        # get optional alternate fallback
        $alternate_fallback = _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id,
            $class_name::$suffix_alternate_fallback );
        $srcs = [];
        $extensions = call_user_func( $wp_get_media_extensions );
        $regex = '/\.(' . implode( '|', $extensions ) . ')($|\?)/';
        foreach( [ $src, $fallback, $alternate_fallback ] as $url ) {
            if ( $url ) {
                if ( preg_match( $regex, $url, $matches ) ) {
                    $srcs[$matches[1]] = $url;
                }
            }
        }
        return $srcs;
    }
}

?>