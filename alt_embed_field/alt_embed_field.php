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
                    'description' =>  'width in pixels - this value can be overridden by specifying a "width" parameter' .
                                      ' with the "show_custom_field" shortcode',
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
                    'description' =>  'height in pixels' .
                                       ' - 0 lets the browser set the height to preserve the aspect ratio - recommended' .
                                       ' - this value can be overridden by specifying a "height" parameter' .
                                       ' with the "show_custom_field" shortcode',
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
                    'description' => 'alignment is effective only if a caption is specified' .
                                     ' - this value can be overridden by specifying an "align" parameter with the' .
                                     ' "show_custom_field" shortcode' .
                                     ' - the parameter values are "aligncenter", "alignright" and "alignleft"',
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
                                      ' and is set only if a caption is specified' .
                                      ' - this value can be overridden by specifying a "class_name" parameter with the' .
                                      ' "show_custom_field" shortcode ',
                    'value'       =>  '',
                    'div_class'   =>  '',
                    'class'       =>  ''
                )
            )
        );
    }

    public function display_field( $field, $group_index = 1, $field_index = 1 ) {
        global $mf_domain;
        $opts = $field[ 'options' ];
        $null = NULL;
        $width  = mf2tk\get_data_option( 'max_width',  $null, $opts, 320 );
        $height = mf2tk\get_data_option( 'max_height', $null, $opts, 240 );
        $args = [];
        if ( $width  ) { $args['width']  = $width;  }
        if ( $height ) { $args['height'] = $height; }
        $caption_field_name = $field['name'] . self::$suffix_caption;
        $caption_input_name = sprintf( 'magicfields[%s][%d][%d]', $caption_field_name, $group_index, $field_index );
        $caption_input_value = mf2tk\get_mf_post_value( $caption_field_name, $group_index, $field_index, '' );
        $caption_input_value = str_replace( '"', '&quot;', $caption_input_value );
        # choose how to use text depending on whether a caption is specified or not
        $how_to_use_with_caption_style = 'display:' . ( $caption_input_value ? 'list-item;' : 'none;'      );
        $how_to_use_no_caption_style   = 'display:' . ( $caption_input_value ? 'none;'      : 'list-item;' );
        $embed = wp_oembed_get( $field['input_value'], $args );
        $index = $group_index === 1 && $field_index === 1 ? '' : "<$group_index,$field_index>";
        # setup geometry for no caption image
        $no_caption_padding = 0;
        $no_caption_border = 2;
        $no_caption_width = $width + 2 * ( $no_caption_padding + $no_caption_border );
        # generate and return the HTML
        $output = <<<EOD
<div class="media_field_mf">
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
    <div class="mf2tk-field-input-optional mf2tk-caption-field">
        <button class="mf2tk-field_value_pane_button">Open</button>
        <h6>Optional Caption for Embed</h6>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <input type="text" name="$caption_input_name" maxlength="256" placeholder="optional caption for embed"
                value="$caption_input_value">
        </div>
    </div>
    <!-- usage instructions -->    
    <div class="mf2tk-field-input-optional mf2tk-usage-field">
        <button class="mf2tk-field_value_pane_button">Open</button>
        <h6 style>How to Use</h6>
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
                    alt_embed_field::get_embed( "$field[name]", $group_index, $field_index, \$post_id, \$width, \$height )
            </ul>
        </div>
    </div>
</div>
<br>
EOD;
        return $output;
    }
  
    static function get_embed( $field_name, $group_index = 1, $field_index = 1, $post_id = NULL, $atts = [ ] ) {
        global $wpdb, $post;
        if ( !$post_id ) { $post_id = $post->ID; }
        $data = mf2tk\get_data2( $field_name, $group_index, $field_index, $post_id );
        $opts = $data[ 'options' ];
        # get width and height
        $max_width  = mf2tk\get_data_option( 'width',  $atts, $opts, 320, 'max_width'  );
        $max_height = mf2tk\get_data_option( 'height', $atts, $opts, 240, 'max_height' );
        # get optional caption
        $caption = mf2tk\get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_caption );
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
                $class_name = mf2tk\get_data_option( 'class_name', $atts, $opts );
                $align      = mf2tk\get_data_option( 'align',      $atts, $opts, 'aligncenter' );
                $align      = mf2tk\re_align( $align );
                if ( !$max_width  ) { $max_width = 240;                                    }
                if ( !$class_name ) { $class_name = "mf2tk-{$data['type']}-{$field_name}"; }
                $class_name .= ' mf2tk-alt-embed';
                $html = img_caption_shortcode( array( 'width' => $max_width, 'align' => $align,
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
