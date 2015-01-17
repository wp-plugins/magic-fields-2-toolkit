<?php

class alt_embed_field extends mf_custom_fields {

    private static $suffix_caption = '_mf2tk_caption';

    public function _update_description() {
        global $mf_domain;
        
        $this->description = __( 'This is a Magic Fields 2 field for WordPress\'s embed shortcode facility.', $mf_domain );
    }
  
    public function _options() {
        global $mf_domain;
        
        return array(
            'option'  => array(
                'max_width'  => array(
                    'type'        =>  'text',
                    'id'          =>  'embed_max_width',
                    'label'       =>  __('Width',$mf_domain),
                    'name'        =>  'mf_field[option][max_width]',
                    'default'     =>  '320',
                    'description' =>  'width in pixels',
                    'value'       =>  '320',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ),
                'max_height'  => array(
                    'type'        =>  'text',
                    'id'          =>  'embed_max_height',
                    'label'       =>  __('Height',$mf_domain),
                    'name'        =>  'mf_field[option][max_height]',
                    'default'     =>  '0',
                    'description' =>  'height in pixels - 0 lets the browser set the height to preserve the aspect ratio - recommended',
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
                ),
                'class_name'  => array(
                    'type'        =>  'text',
                    'id'          =>  'class_name',
                    'label'       =>  __( 'Class Name', $mf_domain ),
                    'name'        =>  'mf_field[option][class_name]',
                    'default'     =>  '',
                    'description' =>  'This is the class option of the WordPress caption shortcode' .
                        ' and is set only if a caption is specified',
                    'value'       =>  '',
                    'div_class'   =>  '',
                    'class'       =>  ''
                )
            )
        );
    }

    public function display_field( $field, $group_index = 1, $field_index = 1 ) {
        global $mf_domain, $mf_post_values;
        $width  = $field['options']['max_width'];
        $height = $field['options']['max_height'];
        $args = [];
        if ( $width  ) { $args['width']  = $width;  }
        if ( $height ) { $args['height'] = $height; }
        $caption_field_name = $field['name'] . self::$suffix_caption;
        $caption_input_name = sprintf( 'magicfields[%s][%d][%d]', $caption_field_name, $group_index, $field_index );
        $caption_input_value = !empty( $mf_post_values[$caption_field_name][$group_index][$field_index] )
            ? $mf_post_values[$caption_field_name][$group_index][$field_index] : '';
        $caption_input_value = str_replace( '"', '&quot;', $caption_input_value );
        $embed = wp_oembed_get( $field['input_value'], $args );
        $index = $group_index === 1 && $field_index === 1 ? '' : "<$group_index,$field_index>";
        $output = <<<EOD
<div class="text_field_mf">
    <div class="mf2tk-field-input-main">
        <h6>URL of the Page Containing the Embed Element</h6>
        <div class="mf2tk-field_value_pane">
            <input type="text" name="$field[input_name]" class="mf2tk-alt_embed_admin-url" maxlength="2048"
                placeholder="URL of the page containing the embed element" value="$field[input_value]" $field[input_validate]>
            <button class="mf2tk-alt_embed_admin-refresh">Reload Embed</button>
            <h6 style="display:inline;">This is the Embed element for the URL specified above.</h6>
            <div class="mf2tk-alt_embed_admin-embed" style="width:{$width}px;padding-top:10px;margin:auto;">$embed</div>
        </div>
    </div>
    <!-- optional caption field -->
    <div class="mf2tk-field-input-optional">
        <button class="mf2tk-field_value_pane_button">Open</button>
        <h6>Optional Caption for Embed</h6>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <input type="text" name="$caption_input_name" maxlength="256" placeholder="optional caption for embed"
                value="$caption_input_value">
        </div>
    </div>
    <!-- usage instructions -->    
    <div class="mf2tk-field-input-optional">
        <button class="mf2tk-field_value_pane_button">Open</button>
        <h6 style>How to Use</h6>
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
                    alt_embed_field::get_embed( "$field[name]", $group_index, $field_index, \$post_id, \$width, \$height )
            </ul>
        </div>
    </div>
</div>
<br>
EOD;
        return $output;
    }
  
    static function get_embed( $field_name, $group_index = 1, $field_index = 1, $post_id = NULL, $max_width = NULL,
        $max_height = NULL ) {
        global $wpdb;
        $data = get_data( $field_name, $group_index, $field_index, $post_id );
        # get optional caption
        $caption = _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_caption );
        #error_log( '##### embed_field::get_embed():$caption=' . $caption );
        if ( !$max_width  ) { $max_width  = $data['options']['max_width'];  }
        if ( !$max_height ) { $max_height = $data['options']['max_height']; }
        # If value is not an URL
        if ( substr_compare( $data['meta_value'], 'http:', 0, 5 ) !== 0
            && substr_compare( $data['meta_value'], 'https:', 0, 6 ) !== 0 ) {
            # Then it should be the HTML to be embedded
            return $data['meta_value'];
        } else {
            // Else use oEmbed to get the HTML
            $args = array();
            if ( $max_width  ) { $args['width']  = $max_width;  }
            if ( $max_height ) { $args['height'] = $max_height; }
            $html = wp_oembed_get( $data['meta_value'], $args );
            if ( $caption ) {
                if ( !$max_width ) { $max_width = 240; }
                $class_name = array_key_exists( 'class_name', $data['options'] ) ? $data['options']['class_name'] : null;
                if ( !$class_name ) { $class_name = "mf2tk-{$data['type']}-{$field_name}"; }
                $html = img_caption_shortcode( array( 'width' => $max_width, 'align' => $data['options']['align'],
                    'class' => $class_name, 'caption' => $caption ),
                    "<div style=\"width:{$max_width}px;display:inline-block;padding:0px;margin:0px;\">$html</div>" );
                $html = preg_replace_callback( '/<div\s.*?style=".*?(width:\s*\d+px)/', function( $matches )
                    use ( $max_width ) {
                    return str_replace( $matches[1], "width:{$max_width}px;max-width:100%", $matches[0] );
                }, $html, 1 );
            }
            return $html;
        }
    }
  
}
