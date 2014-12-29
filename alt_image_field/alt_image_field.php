<?php

class alt_image_field extends mf_custom_fields {

    private static $suffix_caption = '_mf2tk_caption';
    private static $suffix_link    = '_mf2tk_link';
    
    public function _update_description(){
        global $mf_domain;
        $this->description = __( 'This is an alternate Magic Fields 2 field for images.', $mf_domain );
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
                    'description' =>  'width in pixels',
                    'value'       =>  '320',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ),
                'max_height'  => array(
                    'type'        =>  'text',
                    'id'          =>  'max_height',
                    'label'       =>  __( 'Height', $mf_domain ),
                    'name'        =>  'mf_field[option][max_height]',
                    'default'     =>  '0',
                    'description' =>  'height in pixels - 0 lets the browser set the height to preserve the aspect ratio' .
                                      ' - recommended',
                    'value'       =>  '0',
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
                    'description' => 'alignment is effective only if a caption is specified',
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
        $height = $field['options']['max_height'];
        $attrWidth  = $width  ? " width=\"$width\""   : '';
        $attrHeight = $height ? " height=\"$height\"" : '';
        #set up caption field
        $caption_field_name = $field['name'] . self::$suffix_caption;
        $caption_input_name = sprintf( 'magicfields[%s][%d][%d]', $caption_field_name, $group_index, $field_index );
        $caption_input_value = ( !empty( $mf_post_values[$caption_field_name][$group_index][$field_index] ) )
            ? $mf_post_values[$caption_field_name][$group_index][$field_index] : '';
        #set up link field
        $link_field_name = $field['name'] . self::$suffix_link;
        $link_input_name = sprintf( 'magicfields[%s][%d][%d]', $link_field_name, $group_index, $field_index );
        $link_input_value = ( !empty( $mf_post_values[$link_field_name][$group_index][$field_index] ) )
            ? $mf_post_values[$link_field_name][$group_index][$field_index] : '';
        $index = $group_index === 1 && $field_index === 1 ? '' : "<$group_index,$field_index>";
        $output = <<<EOD
<div class="text_field_mf">
    <!-- main audio field -->
    <div class="mf2tk-field-input-main">
        <h6>URL of Image</h6>
        <div class="mf2tk-field_value_pane">
            <input type="text" name="$field[input_name]" id="$field_id" class="mf2tk-img" maxlength="2048"
                placeholder="URL of the image" value="$input_value" $field[input_validate]>
            <button id="{$field_id}.media-library-button" class="mf2tk-media-library-button">
                Get URL from Media Library</button>
            <button id="{$field_id}.refresh-button" class="mf2tk-alt_media_admin-refresh">Reload Media</button>
            <br>
            <div style="width:{$width}px;padding-top:10px;margin:auto;">
                <img class="mf2tk-poster" src="$input_value"{$attrWidth}{$attrHeight}>
            </div>
        </div>
    </div>
    <!-- optional caption field -->
    <div class="mf2tk-field-input-optional">
        <button class="mf2tk-field_value_pane_button">Open</button>
        <h6>Optional Caption for Image</h6>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <input type="text" name="$caption_input_name" maxlength="256" placeholder="optional caption for image"
                value="$caption_input_value">
        </div>
    </div>
    <!-- optional link field -->
    <div class="mf2tk-field-input-optional">
        <button class="mf2tk-field_value_pane_button">Open</button>
        <h6>Optional Link for Image</h6>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <input type="url" name="$link_input_name" maxlength="2048" placeholder="optional link for image"
                value="$link_input_value">
            <button class="mf2tk-test-load-button" style="float:right;" onclick="event.preventDefault();
                window.open(this.parentNode.querySelector('input[type=\'url\']').value,'_blank');">Test Load</button>
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
                    alt_image_field::get_image( "$field[name]", $group_index, $field_index, \$post_id, \$atts = array() )
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
        $caption = _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_caption );
        $link    = _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_link    );
        $attrWidth  = $width  ? " width=\"$width\""   : '';
        $attrHeight = $height ? " height=\"$height\"" : '';
        $html = <<<EOD
            <div style="display:inline-block;width:{$width}px;padding:0px;">
                <a href="$link" target="_blank"><img src="$data[meta_value]"{$attrWidth}{$attrHeight}></a>
            </div>
EOD;
        #error_log( '##### alt_image_field::get_image():$html=' . $html );
        # attach optional caption
        if ( $caption ) {
            if ( !$width ) { $width = 160; }
            $html = img_caption_shortcode( array( 'width' => $width, 'align' => $data['options']['align'],
                'caption' => $caption ), $html );
            $html = preg_replace_callback( '/<div\s.*?style=".*?(width:\s*\d+px)/', function( $matches ) use ( $width ) {
                return str_replace( $matches[1], "width:{$width}px;max-width:100%", $matches[0] );  
            }, $html, 1 );
            $html = preg_replace_callback( '/(<img\s.*?)>/', function( $matches ) {
                return $matches[1] . 'style="margin:0;max-width:100%">';  
            }, $html, 1 );
        }
        #error_log( '##### alt_image_field::get_image():$html=' . $html );
        return $html;
    }  
}
