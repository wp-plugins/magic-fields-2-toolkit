<?php

if ( !function_exists( '_mf2tk_get_optional_field' ) ) {
    include dirname(__FILE__) . '/magic-fields-2-get-optional-field.php';
}

$data = get_data( $field_name, $group_index, $field_index, $post_id );
error_log( '##### magic-fields-2-alt-media-get-template.php:$data=' . print_r( $data, true ) );
$src = $data['meta_value'];

# get optional fallback
$fallback = _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_fallback );
# get optional alternate fallback
$alternate_fallback = _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id,
    self::$suffix_alternate_fallback );
# get optional caption
$caption = _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_caption );
# get optional poster image
$poster = _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_poster );

if ( !$fallback ) {
    $srcs = array( 'src' =>  $src );
} else {
    $srcs = array();
    $extensions = call_user_func( $wp_get_media_extensions );
    $regex = '/\.(' . implode( '|', $extensions ) . ')($|\?)/';
    preg_match( $regex, $src, $matches );
    error_log( '##### magic-fields-2-alt-media-get-template.php:$matches=' . print_r( $matches, true ) );
    $srcs[$matches[1]] = $src;
    preg_match( $regex, $fallback, $matches );
    error_log( '##### magic-fields-2-alt-media-get-template.php:$matches=' . print_r( $matches, true ) );
    $srcs[$matches[1]] = $fallback;
    if ( $alternate_fallback ) {
        preg_match( $regex, $alternate_fallback, $matches );
        error_log( '##### magic-fields-2-alt-media-get-template.php:$matches=' . print_r( $matches, true ) );
        $srcs[$matches[1]] = $alternate_fallback;
    }
}

# merge call attributes with magic field attributes
$defaults = array(
    'loop'     => $data['options']['loop'] ? 'on' : 'off',
    'autoplay' => $data['options']['autoplay'] ? 'on' : 'off',
    'preload'  => $data['options']['preload'],
    'height'   => $data['options']['max_height'],
    'width'    => $data['options']['max_width']
);
extract( shortcode_atts( $defaults, $atts ) );
# construct attribute array for wp media shortcode
$atts = array_merge( $srcs, array( 'width'  => $width, 'height' => $height, 'loop' => $loop, 'autoplay' => $autoplay,
    'preload' => $preload ) );
if ( $poster ) { $atts['poster'] = $poster; }
$atts = array_diff_key( $atts, $invalid_atts );
$atts = array_filter( $atts, function( $v ) { return $v !== 'off'; } );
error_log( '##### magic-fields-2-alt-media-get-template.php:$atts=' . print_r( $atts, true ) );
$html =  call_user_func( $wp_media_shortcode, $atts );

?>