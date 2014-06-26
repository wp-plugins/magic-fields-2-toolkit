<?php
// initialisation
global $mf_domain;

#error_log( '##### alt_embed_field.php:backtrace=' . print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), true ) );

if ( is_admin() ) {
    wp_enqueue_script( 'mf2tk_alt_media_admin', plugins_url( 'magic-fields-2-toolkit/js/mf2tk_alt_media_admin.js' ),
        array( 'jquery' ) );
}

class alt_embed_field extends mf_custom_fields {

  private static $suffix_caption = '_mf2tk_caption';

    public function _update_description() {
        global $mf_domain;
        
        $this->description = __( <<<'EOD'
This is a Magic Fields 2 field for WordPress's embed shortcode facility.
<h3>How to Use</h3>
<ul>
<li style="list-style:square outside">Use with the Toolkit's shortcode:<br>
[show_custom_field field="your_field_name" filter="url_to_media"]
<li style="list-style:square outside">Call the PHP function:<br>
alt_embed_field::get_embed( $field_name, $group_index, $field_index, $post_id, width = null, $height = null )
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
                    'id'          =>  'embed_max_width',
                    'label'       =>  __('Width',$mf_domain),
                    'name'        =>  'mf_field[option][max_width]',
                    'default'     =>  '320',
                    'description' =>  'width',
                    'value'       =>  '320',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ),
                'max_height'  => array(
                    'type'        =>  'text',
                    'id'          =>  'embed_max_height',
                    'label'       =>  __('Height',$mf_domain),
                    'name'        =>  'mf_field[option][max_height]',
                    'default'     =>  '240',
                    'description' =>  'height',
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
        global $mf_domain, $mf_post_values;
        $width = $field['options']['max_width'];
        if ( !$width ) { $width = 320; }
        $height = $field['options']['max_height'];
        if ( !$height ) { $height = 240; }
        $caption_field_name = $field['name'] . self::$suffix_caption;
        $caption_input_name = sprintf( 'magicfields[%s][%d][%d]', $caption_field_name, $group_index, $field_index );
        $caption_input_value = !empty( $mf_post_values[$caption_field_name][$group_index][$field_index] )
            ? $mf_post_values[$caption_field_name][$group_index][$field_index] : '';
        $caption_input_value = str_replace( '"', '&quot;', $caption_input_value );
        $embed = wp_oembed_get( $field['input_value'], array( 'width' => $width, 'height' => $height ) );
        $output = <<<EOD
<div class="text_field_mf">
    <h6>URL of the Page Containing the Embed Element</h6>
    <input type="text" name="$field[input_name]" class="mf2tk-alt_embed_admin-url" maxlength="2048"
        placeholder="URL of the page containing the embed element" value="$field[input_value]" style="width:97%"
        $field[input_validate]>
    <h6 style="display:inline;">This is the Embed element for the URL specified above.</h6>
    <button class="mf2tk-alt_embed_admin-refresh" style="font-size:10px;font-weight:bold;padding:0px 5px;">
        Reload</button>
    <div class="mf2tk-alt_embed_admin-embed" style="width:{$width}px;padding-top:10px;margin:auto;">$embed</div> 
    <!-- optional caption field -->
    <div>
        <h6 style="display:inline;">Optional Caption for Embed</h6>
        <button class="mf2tk-field_value_pane_button" style="font-size:10px;font-weight:bold;padding:0px 5px;">Show</button>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <input type="text" name="$caption_input_name" maxlength="256" placeholder="optional caption for embed"
                value="$caption_input_value" style="width:97%">
        </div>
    </div>
    <!-- usage instructions -->    
    <div>
        <h6 style="display:inline;">How to Use</h6>
        <button class="mf2tk-field_value_pane_button" style="font-size:10px;font-weight:bold;padding:0px 5px;">Show</button>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <ul>
                <li style="list-style:square inside">Use with the Toolkit's shortcode:<br>
                    [show_custom_field field="your_field_name" filter="url_to_media"]
                <li style="list-style:square inside">Call the PHP function:<br>
                    alt_embed_field::get_embed( \$field_name, \$group_index, \$field_index, \$post_id, \$width = null, \$height = null )
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
        if ( !function_exists( '_mf2tk_get_optional_field' ) ) {
            include WP_PLUGIN_DIR . '/magic-fields-2-toolkit/magic-fields-2-get-optional-field.php';
        }
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
                $html = img_caption_shortcode( array( 'width' => $max_width, 'align' => $data['options']['align'],
                    'caption' => $caption ),
                    "<div style=\"width:{$max_width}px;display:inline-block;padding:0px;margin:0px;\">$html</div>" );
            }
            return $html;
        }
    }
  
}
