<?php

    # included by display_field() of alt_audio_field/alt_audio_field.php and display_field() of 
    # alt_video_field/alt_video_field.php to implement common funtionality

    $index = $group_index === 1 && $field_index === 1 ? '' : "<$group_index,$field_index>";
    $opts = $field[ 'options' ];
    $null = NULL;
    $width  = mf2tk\get_data_option( 'max_width',  $null, $opts, 320 );
    $height = mf2tk\get_data_option( 'max_height', $null, $opts, 240 );
    $dimensions = [];
    if ( $media_type === 'video' ) {
        if ( $width  ) { $dimensions['width']  = $width;  }
        if ( $height ) { $dimensions['height'] = $height; }
    }
    $dimensions['preload'] = 'metadata';
    $attrWidth  = $width  ? " width=\"$width\""   : '';
    $attrHeight = $height ? " height=\"$height\"" : '';
    
    # setup main field
    $field_id = "mf2tk-$field[name]-$group_index-$field_index";
    $input_value = str_replace( '"', '&quot;', $field['input_value'] );
    if ( !empty( $field['input_value'] ) ) {
        $media_shortcode = call_user_func( $wp_media_shortcode,
            array_merge( array( 'src' => $field['input_value'] ), $dimensions ) );
    } else {
        $media_shortcode = '';
    }
    
    # setup fallback field
    $fallback_field_name = $field['name'] . self::$suffix_fallback;
    $fallback_field_id = "mf2tk-$fallback_field_name-$group_index-$field_index";
    $fallback_input_name = "magicfields[$fallback_field_name][$group_index][$field_index]";
    $fallback_input_value = mf2tk\get_mf_post_value( $fallback_field_name, $group_index, $field_index, '' );
    if ( $fallback_input_value ) {
        $fallback_media_shortcode = call_user_func( $wp_media_shortcode,
            array_merge( array( 'src' => $fallback_input_value ), $dimensions ) );
        $fallback_media_button    = 'Hide';
        $fallback_media_display   = 'block';
    } else {
        $fallback_media_shortcode = '';
        $fallback_media_button    = 'Open';
        $fallback_media_display   = 'none';
    }
    
    # setup alternate fallback field
    $alternate_fallback_field_name = $field['name'] . self::$suffix_alternate_fallback;
    $alternate_fallback_field_id = "mf2tk-$alternate_fallback_field_name-$group_index-$field_index";
    $alternate_fallback_input_name = "magicfields[$alternate_fallback_field_name][$group_index][$field_index]";
    $alternate_fallback_input_value = mf2tk\get_mf_post_value( $alternate_fallback_field_name, $group_index, $field_index, '' );
    if ( $alternate_fallback_input_value ) {
        $alternate_fallback_media_shortcode = call_user_func( $wp_media_shortcode,
            array_merge( array( 'src' => $alternate_fallback_input_value ), $dimensions ) );
        $alternate_fallback_media_button    = 'Hide';
        $alternate_fallback_media_display   = 'block';
    } else {
        $alternate_fallback_media_shortcode = '';
        $alternate_fallback_media_button    = 'Open';
        $alternate_fallback_media_display   = 'none';
    }
    #set up caption field
    $caption_field_name = $field['name'] . self::$suffix_caption;
    $caption_input_name = sprintf( 'magicfields[%s][%d][%d]', $caption_field_name, $group_index, $field_index );
    $caption_input_value = mf2tk\get_mf_post_value( $caption_field_name, $group_index, $field_index, '' );
    $caption_input_value = str_replace( '"', '&quot;', $caption_input_value );
    
    # choose how to use text depending on whether a caption is specified or not
    $how_to_use_with_caption_style = 'display:' . ( $caption_input_value ? 'list-item;' : 'none;' );
    $how_to_use_no_caption_style   = 'display:' . ( $caption_input_value ? 'none;'      : 'list-item;' );
    
    # setup optional poster image field
    $poster_field_name = $field['name'] . self::$suffix_poster;
    $poster_field_id = "mf2tk-$poster_field_name-$group_index-$field_index";
    $poster_input_name = "magicfields[$poster_field_name][$group_index][$field_index]";
    $poster_input_value = mf2tk\get_mf_post_value( $poster_field_name, $group_index, $field_index, '' );
    $poster_input_value = str_replace( '"', '&quot;', $poster_input_value );
    $ucfirst_media_type = ucfirst( $media_type );
    
    # setup geometry for no caption image
    $no_caption_padding = 0;
    $no_caption_border = 2;
    $no_caption_width = $width + 2 * ( $no_caption_padding + $no_caption_border );
    
    # generate and return the HTML
$html = <<<EOD
<div class="media_field_mf">
    <!-- main $media_type field -->
    <div class="mf2tk-field-input-main">
        <h6>Main $ucfirst_media_type</h6>
        <div class="mf2tk-field_value_pane">
            <input type="text" name="$field[input_name]" id="$field_id" class="mf2tk-$media_type" maxlength="2048"
                placeholder="URL of the $media_type" value="$input_value" $field[input_validate]>
            <button id="{$field_id}.media-library-button" class="mf2tk-media-library-button">
                Get URL from Media Library</button>
            <button id="{$field_id}.refresh-button" class="mf2tk-alt_media_admin-refresh">Reload Media</button>
            <br>
            <div class="mf2tk-media" style="width:{$width}px;padding-top:10px;margin:auto;">
                $media_shortcode
            </div>
        </div>
    </div>
    <!-- optional fallback $media_type field -->
    <div class="mf2tk-field-input-optional">
        <button class="mf2tk-field_value_pane_button">$fallback_media_button</button>
        <h6>Optional Fallback $ucfirst_media_type</h6>
        <div class="mf2tk-field_value_pane" style="display:$fallback_media_display;clear:both;">
            <input type="text" name="$fallback_input_name" id="$fallback_field_id" class="mf2tk-$media_type" maxlength="2048"
                placeholder="URL of fallback $media_type" value="$fallback_input_value">
            <button id="{$fallback_field_id}.media-library-button" class="mf2tk-media-library-button">
                Get URL from Media Library</button>
            <button id="{$fallback_field_id}.refresh-button" class="mf2tk-alt_media_admin-refresh">Reload Media</button>
            <br>
            <div class="mf2tk-media" style="width:{$width}px;padding-top:10px;margin:auto;">
                $fallback_media_shortcode
            </div>
        </div>
    </div>
    <!-- optional alternate fallback $media_type field -->
    <div class="mf2tk-field-input-optional">
        <button class="mf2tk-field_value_pane_button">$alternate_fallback_media_button</button>
        <h6>Optional Alternate Fallback $ucfirst_media_type</h6>
        <div class="mf2tk-field_value_pane" style="display:$alternate_fallback_media_display;clear:both;">
            <input type="text" name="$alternate_fallback_input_name" id="$alternate_fallback_field_id" class="mf2tk-$media_type"
                maxlength="2048" placeholder="URL of alternate fallback $media_type" value="$alternate_fallback_input_value">
            <button id="{$alternate_fallback_field_id}.media-library-button" class="mf2tk-media-library-button">
                Get URL from Media Library</button>
            <button id="{$alternate_fallback_field_id}.refresh-button" class="mf2tk-alt_media_admin-refresh">
                Reload Media</button>
            <br>
            <div class="mf2tk-media" style="width:{$width}px;padding-top:10px;margin:auto;">
                $alternate_fallback_media_shortcode
            </div>
        </div>
    </div>
    <!-- optional caption field -->
    <div class="mf2tk-field-input-optional mf2tk-caption-field">
        <button class="mf2tk-field_value_pane_button">Open</button>
        <h6>Optional Caption for $ucfirst_media_type</h6>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <input type="text" name="$caption_input_name" maxlength="256" placeholder="optional caption for $media_type"
                value="$caption_input_value">
        </div>
    </div>
    <!-- optional poster image field -->
    <div class="mf2tk-field-input-optional">
        <button class="mf2tk-field_value_pane_button">Open</button>
        <h6>Optional Poster Image for $ucfirst_media_type</h6>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <input type="text" name="$poster_input_name" id="$poster_field_id" class="mf2tk-img" maxlength="2048"
                placeholder="URL of optional poster image" value="$poster_input_value">
            <button id="{$poster_field_id}.media-library-button" class="mf2tk-media-library-button">
                Get URL from Media Library</button>
            <button id="{$poster_field_id}.refresh-button" class="mf2tk-alt_media_admin-refresh">Reload Media</button>
            <br>
            <div style="width:{$width}px;padding-top:10px;margin:auto;">
                <img class="mf2tk-poster" src="$poster_input_value"{$attrWidth}{$attrHeight}>
            </div>
        </div>
    </div>
    <!-- usage instructions -->
    <div class="mf2tk-field-input-optional mf2tk-usage-field">
        <button class="mf2tk-field_value_pane_button">Open</button>
        <h6>How to Use</h6>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <ul>
                <li class="mf2tk-how-to-use-with-caption" style="list-style:square inside;{$how_to_use_with_caption_style}">Use with the Toolkit's shortcode - (with caption):<br>
                    <input type="text" class="mf2tk-how-to-use" size="50" readonly
                        value='[show_custom_field field="$field[name]$index" filter="url_to_media"]'>
                    - <button class="mf2tk-how-to-use">select,</button> copy and paste this into editor above in
                        <strong>Text</strong> mode
                <li class="mf2tk-how-to-use-no-caption" style="list-style:square inside;{$how_to_use_no_caption_style}">Use with the Toolkit's shortcode - (no caption):<br>
                    <textarea class="mf2tk-how-to-use" rows="4" cols="80" readonly>&lt;div style="width:{$no_caption_width}px;border:{$no_caption_border}px solid black;background-color:gray;padding:{$no_caption_padding}px;margin:0 auto;"&gt;
    [show_custom_field field="$field[name]$index" filter="url_to_media"]
&lt;/div&gt;</textarea><br>
                    - <button class="mf2tk-how-to-use">select,</button> copy and paste this into editor above in
                        <strong>Text</strong> mode
                <li style="list-style:square inside">Call the PHP function:<br>
                    alt_{$media_type}_field::get_{$media_type}( "$field[name]", \$group_index, \$field_index, \$post_id, \$atts = array() )
            </ul>
        </div>
    </div>
</div>
<br>
EOD;
if ( $media_type === 'video' && ( !$height || !$width )
    && preg_match_all( '/<video\s+class="wp-video-shortcode"\s+id="([^"]+)"/', $html, $matches, PREG_PATTERN_ORDER ) ) {
    $aspect_ratio = mf2tk\get_data_option( 'aspect_ratio', $null, $opts, '4:3' );
    if ( preg_match( '/([\d\.]+):([\d\.]+)/', $aspect_ratio, $matches1 ) ) { $aspect_ratio = $matches1[1] / $matches1[2]; }
    $do_width = !$width ? 'true' : 'false';
    foreach( $matches[1] as $id ) {
        $html .= <<<EOD
<script type="text/javascript">
    jQuery(document).ready(function(){mf2tkResizeVideo("video.wp-video-shortcode#$id",$aspect_ratio,$do_width);});
</script>
EOD;
    }
}
return $html;
?>