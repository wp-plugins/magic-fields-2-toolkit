<?php
global $wpdb;
# TODO: The suffixes should not be defined in multiple places - not here and alt_video_field.php and alt_audio_field.php
$suffix_fallback = '_mf2tk_fallback';
$suffix_alternate_fallback = '_mf2tk_alternate_fallback';
#error_log( '##### mf2tk_alt_media_admin_refresh.php:$_REQUEST["field"]=' . $_REQUEST['field'] );
preg_match( '/magicfields\[(\w+)\]\[\d+\]\[\d+\]/', $_REQUEST['field'], $matches );
#error_log( '##### mf2tk_alt_media_admin_refresh.php:$matches=' . print_r( $matches, true) );
$field = $matches[1];
$field = str_replace( $suffix_alternate_fallback, '', $field );
$field = str_replace( $suffix_fallback, '', $field );
#error_log( '##### mf2tk_alt_media_admin_refresh.php:$field=' . print_r( $field, true) );
$result = $wpdb->get_row( 'SELECT type, options FROM ' . MF_TABLE_CUSTOM_FIELDS . " WHERE name = '$field'", ARRAY_A );
#error_log( '##### mf2tk_alt_media_admin_refresh.php:$result=' . print_r( $result, true ) );
$wp_media_shortcode = $result['type'] === 'alt_video' ? 'wp_video_shortcode' : 'wp_audio_shortcode';
$options = unserialize( $result['options'] );
#error_log( '##### mf2tk_alt_media_admin_refresh.php:$options=' . print_r( $options, true ) );
$dimensions = [];
if ( $result['type'] === 'alt_video' ) {
    if ( $options['max_width']  ) { $dimensions['width']  = $options['max_width'];  }
    if ( $options['max_height'] ) { $dimensions['height'] = $options['max_height']; }
}
$html = call_user_func( $wp_media_shortcode, array_merge( array( 'src' => $_REQUEST['url'] ), $dimensions ) );
$html = "<div class=\"mf2tk-new-wp-media-shortcode\">$html</div>";
$html .= <<<EOD
<script type="text/javascript">
(function($){
    var settings = {};
    if ( $( document.body ).hasClass( 'mce-content-body' ) ) {
        return;
    }
    if ( typeof _wpmejsSettings !== 'undefined' ) {
        settings.pluginPath = _wpmejsSettings.pluginPath;
    }
	$('div.mf2tk-new-wp-media-shortcode .wp-audio-shortcode, div.mf2tk-new-wp-media-shortcode .wp-video-shortcode')
        .mediaelementplayer( settings );
}(jQuery));
EOD;
if ( ( !$options['max_height'] || !$options['max_width'] )
    && preg_match( '/<video\s+class="wp-video-shortcode"\s+id="([^"]+)"/', $html, $matches ) ) {
    $id = $matches[1];
    $aspect_ratio = array_key_exists( 'aspect_ratio', $options ) ? $options['aspect_ratio'] : '4:3';
    if ( preg_match( '/([\d\.]+):([\d\.]+)/', $aspect_ratio, $matches ) ) { $aspect_ratio = $matches[1] / $matches[2]; }
    $do_width = !$options['max_width'] ? 'true' : 'false';
    $html .= "mf2tkResizeVideo(\"div.mf2tk-new-wp-media-shortcode video.wp-video-shortcode#$id\",$aspect_ratio,$do_width);";
}
$html .= 'jQuery("div.mf2tk-new-wp-media-shortcode").removeClass("mf2tk-new-wp-media-shortcode");</script>';
echo $html;
die();
?>