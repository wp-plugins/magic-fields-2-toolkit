<?php
    #error_log( '##### magic-fields-2-alt-media-template.php:$field=' . print_r( $field, true ) );
    #error_log( '##### magic-fields-2-alt-media-template.php:$mf_post_values=' . print_r( $mf_post_values, true ) );
    $width = $field['options']['max_width'];
    if ( !$width ) { $width = 320; }
    $height = $field['options']['max_height'];
    if ( !$height ) { $height = 240; }
    $dimensions = $media_type === 'video' ? array( 'width' => $width, 'height' => $height ) : array();
    $dimensions['preload'] = 'none';
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
    } else {
        $fallback_media_shortcode = '';
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
    } else {
        $alternate_fallback_media_shortcode = '';
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
return <<<EOD
<div class="text_field_mf">
    <!-- main $media_type field -->
    <div>
        <h6>Main $ucfirst_media_type</h6>
        <div>
            <input type="text" name="$field[input_name]" id="$field_id" class="mf2tk-$media_type" maxlength="2048"
                placeholder="URL of the $media_type" value="$input_value" style="width:97%" $field[input_validate]>
            <button id="{$field_id}.media-library-button" class="mf2tk-media-library-button"
                style="font-size:10px;font-weight:bold;padding:0px 5px;">Get URL from Media Library</button>
            <button id="{$field_id}.refresh-button" class="mf2tk-alt_media_admin-refresh"
                style="font-size:10px;font-weight:bold;padding:0px 5px;">Reload Media</button>
            <br>
            <div class="mf2tk-media" style="width:{$width}px;padding-top:10px;margin:auto;">
                $media_shortcode
            </div>
        </div>
    </div>
    <br>
    <!-- optional fallback $media_type field -->
    <div>
        <h6 style="display:inline;">Optional Fallback $ucfirst_media_type</h6>
        <button class="mf2tk-field_value_pane_button" style="font-size:10px;font-weight:bold;padding:0px 5px;">Show</button>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <input type="text" name="$fallback_input_name" id="$fallback_field_id" class="mf2tk-$media_type" maxlength="2048"
                placeholder="URL of fallback $media_type" value="$fallback_input_value" style="width:90%">
            <button id="{$fallback_field_id}.media-library-button" class="mf2tk-media-library-button"
                style="font-size:10px;font-weight:bold;padding:0px 5px;">Get URL from Media Library</button>
            <button id="{$fallback_field_id}.refresh-button" class="mf2tk-alt_media_admin-refresh"
                style="font-size:10px;font-weight:bold;padding:0px 5px;">Reload Media</button>
            <br>
            <div class="mf2tk-media" style="width:{$width}px;padding-top:10px;margin:auto;">
                $fallback_media_shortcode
            </div>
        </div>
    </div>
    <br>
    <!-- optional alternate fallback $media_type field -->
    <div>
        <h6 style="display:inline;">Optional Alternate Fallback $ucfirst_media_type</h6>
        <button class="mf2tk-field_value_pane_button" style="font-size:10px;font-weight:bold;padding:0px 5px;">Show</button>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <input type="text" name="$alternate_fallback_input_name" id="$alternate_fallback_field_id" class="mf2tk-$media_type"
                maxlength="2048" placeholder="URL of alternate fallback $media_type" value="$alternate_fallback_input_value"
                style="width:97%">
            <button id="{$alternate_fallback_field_id}.media-library-button" class="mf2tk-media-library-button"
                style="font-size:10px;font-weight:bold;padding:0px 5px;">Get URL from Media Library</button>
            <button id="{$alternate_fallback_field_id}.refresh-button" class="mf2tk-alt_media_admin-refresh"
                style="font-size:10px;font-weight:bold;padding:0px 5px;">Reload Media</button>
            <br>
            <div class="mf2tk-media" style="width:{$width}px;padding-top:10px;margin:auto;">
                $alternate_fallback_media_shortcode
            </div>
        </div>
    </div>
    <br>
    <!-- optional caption field -->
    <div>
        <h6 style="display:inline;">Optional Caption for $ucfirst_media_type</h6>
        <button class="mf2tk-field_value_pane_button" style="font-size:10px;font-weight:bold;padding:0px 5px;">Show</button>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <input type="text" name="$caption_input_name" maxlength="256" placeholder="optional caption for $media_type"
                value="$caption_input_value" style="width:97%">
        </div>
    </div>
    <br>
    <!-- optional poster image field -->
    <div>
        <h6 style="display:inline;">Optional Poster Image for $ucfirst_media_type</h6>
        <button class="mf2tk-field_value_pane_button" style="font-size:10px;font-weight:bold;padding:0px 5px;">Show</button>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <input type="text" name="$poster_input_name" id="$poster_field_id" class="mf2tk-img" maxlength="2048"
                placeholder="URL of optional poster image" value="$poster_input_value" style="width:97%">
            <button id="{$poster_field_id}.media-library-button" class="mf2tk-media-library-button"
                style="font-size:10px;font-weight:bold;padding:0px 5px;">Get URL from Media Library</button>
            <button id="{$poster_field_id}.refresh-button" class="mf2tk-alt_media_admin-refresh"
                style="font-size:10px;font-weight:bold;padding:0px 5px;">Reload Media</button>
            <br>
            <div style="width:{$width}px;padding-top:10px;margin:auto;">
                <img class="mf2tk-poster" src="$poster_input_value" width="{$field[options][max_width]}"
                    height="{$field[options][max_height]}">
            </div>
        </div>
    </div>
    <!-- usage instructions -->
    <div>
        <h6 style="display:inline;">How to Use</h6>
        <button class="mf2tk-field_value_pane_button" style="font-size:10px;font-weight:bold;padding:0px 5px;">Show</button>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <ul>
                <li style="list-style:square inside">Use with the Toolkit's shortcode:<br>
                    [show_custom_field field="your_field_name" filter="url_to_media"]<br>
                <li style="list-style:square inside">Call the PHP function:<br>
                    alt_{$media_type}_field::get_{$media_type}( \$field_name, \$group_index, \$field_index, \$post_id, \$atts = array() )
            </ul>
        </div>
    </div>
</div>
<br>
EOD;
?>