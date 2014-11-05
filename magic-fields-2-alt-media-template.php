<?php
    #error_log( '##### magic-fields-2-alt-media-template.php:$field=' . print_r( $field, true ) );
    #error_log( '##### magic-fields-2-alt-media-template.php:$mf_post_values=' . print_r( $mf_post_values, true ) );
    $width  = $field['options']['max_width'];
    $height = $field['options']['max_height'];
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
    $fallback_input_value = ( !empty( $mf_post_values[$fallback_field_name][$group_index][$field_index] ) )
        ? $mf_post_values[$fallback_field_name][$group_index][$field_index] : '';
    #error_log( '##### magic-fields-2-alt-media-template.php:$fallback_input_value=' . print_r( $fallback_input_value, true ) );
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
    $alternate_fallback_input_value = ( !empty( $mf_post_values[$alternate_fallback_field_name][$group_index][$field_index] ) )
        ? $mf_post_values[$alternate_fallback_field_name][$group_index][$field_index] : '';
    #error_log( '##### magic-fields-2-alt-media-template.php:$alternate_fallback_input_value='
    #    . print_r( $alternate_fallback_input_value, true ) );
    if ( $alternate_fallback_input_value ) {
        $alternate_fallback_media_shortcode = call_user_func( $wp_media_shortcode,
            array_merge( array( 'src' => $alternate_fallback_input_value ), $dimensions ) );
        #error_log( '##### magic-fields-2-alt-media-template.php:$alternate_fallback_media_shortcode='
        #    . $alternate_fallback_media_shortcode );
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
    $caption_input_value = ( !empty( $mf_post_values[$caption_field_name][$group_index][$field_index] ) )
        ? $mf_post_values[$caption_field_name][$group_index][$field_index] : '';
    $caption_input_value = str_replace( '"', '&quot;', $caption_input_value );
    # setup optional poster image field
    $poster_field_name = $field['name'] . self::$suffix_poster;
    $poster_field_id = "mf2tk-$poster_field_name-$group_index-$field_index";
    $poster_input_name = "magicfields[$poster_field_name][$group_index][$field_index]";
    $poster_input_value = ( !empty( $mf_post_values[$poster_field_name][$group_index][$field_index] ) )
        ? $mf_post_values[$poster_field_name][$group_index][$field_index] : '';
    #error_log( '##### magic-fields-2-alt-media-template.php:$poster_input_value=' . print_r( $poster_input_value, true ) );
    $poster_input_value = str_replace( '"', '&quot;', $poster_input_value );
    $ucfirst_media_type = ucfirst( $media_type );
    # generate and return the HTML
$html = <<<EOD
<div class="text_field_mf">
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
    <div class="mf2tk-field-input-optional">
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
    <div class="mf2tk-field-input-optional">
        <button class="mf2tk-field_value_pane_button">Open</button>
        <h6>How to Use</h6>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <ul>
                <li style="list-style:square inside">Use with the Toolkit's shortcode - (if caption entered):<br>
                    <input type="text" class="mf2tk-how-to-use" size="50" readonly
                        value='[show_custom_field field="$field[name]$index" filter="url_to_media"]'>
                    - <button class="mf2tk-how-to-use">select,</button> copy and paste this into editor above in
                        <strong>Text</strong> mode
                <li style="list-style:square inside">Use with the Toolkit's shortcode - (if caption not entered):<br>
                    <textarea class="mf2tk-how-to-use" rows="4" cols="80" readonly>&lt;div style="width:{$width}px;border:2px solid black;background-color:gray;
        padding:10px;margin:0 auto;"&gt;
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
if ( !$height && preg_match_all( '/<video\s+class="wp-video-shortcode"\s+id="([^"]+)"/', $html, $matches,
    PREG_PATTERN_ORDER ) ) {
    foreach( $matches[1] as $id ) {
        $html .= <<<EOD
<script>
(function(){
  var f=function(){
    console.log("f()");
    var s=false;
    jQuery("video.wp-video-shortcode#$id").parents("div.mejs-container").parents("div.wp-video").each(function(){
      this.style.height="auto";
      console.log("f():this=",this);
      s=true;
    });
    if(!s){window.setTimeout(f,1000);}
  }
  window.setTimeout(f);
}());
</script>
EOD;
    }
}
return $html;
?>