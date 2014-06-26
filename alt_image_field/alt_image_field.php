<?php
// initialisation
global $mf_domain;

#error_log( '##### alt_audio_field.php:backtrace=' . print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), true ) );

if ( is_admin() ) {
    wp_enqueue_script( 'mf2tk_alt_media_admin', plugins_url( 'magic-fields-2-toolkit/js/mf2tk_alt_media_admin.js' ),
        array( 'jquery' ) );
}

class alt_image_field extends mf_custom_fields {

    private static $suffix_caption = '_mf2tk_caption';
    
    public function _update_description(){
        global $mf_domain;
        $this->description = __( <<<'EOD'
This is an alternate Magic Fields 2 field for images.
<h3>How to Use</h3>
<ul>
<li style="list-style:square outside">Use with the Toolkit's shortcode:<br>
[show_custom_field field="your_field_name" filter="url_to_media"]<br>
<li style="list-style:square outside">Call the PHP function:<br>
alt_image_field::get_image( $field_name, $group_index, $field_index, $post_id, $atts = array() )
</ul>
EOD
            , $mf_domain );
    }
  
    public function _options() {
        global $mf_domain;
    
        return array(
            'option'  => array(
                'max_width'  => array(
                    'type'        =>  'text',
                    'id'          =>  'max_width',
                    'label'       =>  __( 'Width', $mf_domain ),
                    'name'        =>  'mf_field[option][max_width]',
                    'default'     =>  '320',
                    'description' =>  'width for image',
                    'value'       =>  '320',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ),
                'max_height'  => array(
                    'type'        =>  'text',
                    'id'          =>  'max_height',
                    'label'       =>  __( 'Height', $mf_domain ),
                    'name'        =>  'mf_field[option][max_height]',
                    'default'     =>  '240',
                    'description' =>  'height for image',
                    'value'       =>  '240',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ),
                'align' => array(
                    'type'        => 'select',
                    'id'          => 'align',
                    'label'       => __( 'Alignment', $mf_domain ),
                    'name'        =>  'mf_field[option][align]',
                    'default'     => '',
                    'options'     => array(
                                        'aligncenter' => 'Center',
                                        'alignright'  => 'Right',
                                        'alignleft'   => 'Left',
                                        'alignnone'   => 'None',
                                    ),
                    'add_empty'   => false,
                    'description' => '',
                    'value'       => '',
                    'div_class'   => '',
                    'class'       => ''
                )
            )
        );
    }

    public function display_field( $field, $group_index = 1, $field_index = 1 ) {
        global $mf_domain, $post, $mf_post_values;
        #error_log( '##### alt_image_field::display_field():$field=' . print_r( $field, true ) );
        # setup main field
        $field_id = "mf2tk-$field[name]-$group_index-$field_index";
        $input_value = str_replace( '"', '&quot;', $field['input_value'] );
        $width = $field['options']['max_width'];
        if ( !$width ) { $width = 320; }
        $height = $field['options']['max_height'];
        if ( !$height ) { $height = 240; }
        #set up caption field
        $caption_field_name = $field['name'] . self::$suffix_caption;
        $caption_input_name = sprintf( 'magicfields[%s][%d][%d]', $caption_field_name, $group_index, $field_index );
        $caption_input_value = ( !empty( $mf_post_values[$caption_field_name][$group_index][$field_index] ) )
            ? $mf_post_values[$caption_field_name][$group_index][$field_index] : '';
        $caption_input_value = str_replace( '"', '&quot;', $caption_input_value );
        $output = <<<EOD
<div class="text_field_mf">
    <!-- main $media_type field -->
    <div>
        <h6>URL of Image</h6>
        <div>
            <input type="text" name="$field[input_name]" id="$field_id" class="mf2tk-img" maxlength="2048"
                placeholder="URL of the image" value="$input_value" style="width:97%" $field[input_validate]>
            <button id="{$field_id}.media-library-button" class="mf2tk-media-library-button"
                style="font-size:10px;font-weight:bold;padding:0px 5px;">Get URL from Media Library</button>
            <button id="{$field_id}.refresh-button" class="mf2tk-alt_media_admin-refresh"
                style="font-size:10px;font-weight:bold;padding:0px 5px;">Reload Media</button>
            <br>
            <div style="width:{$width}px;padding-top:10px;margin:auto;">
                <img class="mf2tk-poster" src="$input_value" width="$width" height="$height">
            </div>
        </div>
    </div>
    <br>
    <!-- optional caption field -->
    <div>
        <h6 style="display:inline;">Optional Caption for Image</h6>
        <button class="mf2tk-field_value_pane_button" style="font-size:10px;font-weight:bold;padding:0px 5px;">Show</button>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <input type="text" name="$caption_input_name" maxlength="256" placeholder="optional caption for image"
                value="$caption_input_value" style="width:97%">
        </div>
    </div>
    <br>
    <!-- usage instructions -->    
    <div>
        <h6 style="display:inline;">How to Use</h6>
        <button class="mf2tk-field_value_pane_button" style="font-size:10px;font-weight:bold;padding:0px 5px;">Show</button>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <ul>
                <li style="list-style:square inside">Use with the Toolkit's shortcode:<br>
                    [show_custom_field field="your_field_name" filter="url_to_media"]
                <li style="list-style:square inside">Call the PHP function:<br>
                    alt_image_field::get_image( \$field_name, \$group_index, \$field_index, \$post_id, \$atts = array() )
            </ul>
        </div>
    </div>
</div>
<br>
EOD;
        #error_log( '##### alt_image_field::display_field():$output=' . $output );
        return $output;
    }
  
    static function get_image( $field_name, $group_index = 1, $field_index = 1, $post_id = NULL, $atts = array() ) {
        $data = get_data( $field_name, $group_index, $field_index, $post_id );
        #error_log( '##### alt_image_field::get_image():$data=' . print_r( $data, true ) );
        $width  = !empty( $atts['width'] )  ? $atts['width']  : $data['options']['max_width'];
        $height = !empty( $atts['height'] ) ? $atts['height'] : $data['options']['max_height'];
        # get optional caption
        if ( !function_exists( '_mf2tk_get_optional_field' ) ) {
            include WP_PLUGIN_DIR . '/magic-fields-2-toolkit/magic-fields-2-get-optional-field.php';
        }
        $caption = _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_caption );
        $html = <<<EOD
            <div style="display:inline-block;width:{$width}px;padding:0px;">
                <img src="$data[meta_value]" width="$width" height="$height">
                $html
            </div>
EOD;
        #error_log( '##### alt_image_field::get_image():$html=' . $html );
        # attach optional caption
        if ( $caption ) {
            if ( !$width ) { $width = 160; }
            $html = img_caption_shortcode( array( 'width' => $width, 'align' => $data['options']['align'], 'caption' => $caption ),
                $html );
        }
        #error_log( '##### alt_image_field::get_image():$html=' . $html );
        return $html;
    }  
}
