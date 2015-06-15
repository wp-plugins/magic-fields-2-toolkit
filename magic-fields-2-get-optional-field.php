<?php

namespace mf2tk {

# get_optional_field() returns the optional field data for a field e.g., the fallback video src of an alt_video_field

if ( !function_exists( 'mf2tk\get_optional_field' ) ) {
    function get_optional_field( $field_name, $group_index, $field_index, $post_id, $option ) {
        global $wpdb;
        $sql = $wpdb->prepare( 'SELECT w.meta_value FROM ' . MF_TABLE_POST_META
            . " m INNER JOIN $wpdb->postmeta w ON m.meta_id = w.meta_id " .
            ' WHERE m.post_id = %d AND m.field_name = %s AND m.group_count = %d AND m.field_count = %d',
            (integer) $post_id, $field_name . $option, (integer) $group_index, (integer) $field_index );
        $value = $wpdb->get_var( $sql );
        return $value;
    }
}

# get_media_srcs() returns an associative array mapping media types specified by file extensions (e.g., mp4, webm, ... ) to URL's

if ( !function_exists( 'mf2tk\get_media_srcs' ) ) {
    function get_media_srcs( $field_name, $group_index, $field_index, $post_id, $class_name ) {
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
        $fallback = get_optional_field( $field_name, $group_index, $field_index, $post_id,
            $class_name::$suffix_fallback );
        # get optional alternate fallback
        $alternate_fallback = get_optional_field( $field_name, $group_index, $field_index, $post_id,
            $class_name::$suffix_alternate_fallback );
        $srcs = [];
        # get file extensions for this media type
        $extensions = call_user_func( $wp_get_media_extensions );
        # use a regex to match the file extension
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

}
?>