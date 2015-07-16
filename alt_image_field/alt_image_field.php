<?php

class alt_image_field extends mf_custom_fields {

    private static $suffix_caption = '_mf2tk_caption';
    private static $suffix_link    = '_mf2tk_link';
    private static $suffix_hover   = '_mf2tk_hover';
    
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
                    'description' =>  'width in pixels - this value can be overridden by specifying a "width" parameter' .
                                      ' with the "show_custom_field" shortcode',
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
                                      ' - recommended - this value can be overridden by specifying a "height" parameter' .
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
                    'description' => 'alignment is effective only if a caption is specified ' .
                                     '- this value can be overridden by specifying an "align" parameter with the ' .
                                     '"show_custom_field" shortcode ' .
                                     '- the parameter values are "aligncenter", "alignright" and "alignleft"',
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
                ),
                'popup_width'  => array(
                    'type'        =>  'text',
                    'id'          =>  'popup_width',
                    'label'       =>  __( 'Mouseover Popup Width', $mf_domain ),
                    'name'        =>  'mf_field[option][popup_width]',
                    'default'     =>  '320',
                    'description' =>  'mouseover popup width in pixels - this value can be overridden by specifying a ' .
                                      '"popup_width" parameter with the "show_custom_field" shortcode',
                    'value'       =>  '320',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ),
                'popup_height'  => array(
                    'type'        =>  'text',
                    'id'          =>  'popup_height',
                    'label'       =>  __( 'Mouseover Popup Height', $mf_domain ),
                    'name'        =>  'mf_field[option][popup_height]',
                    'default'     =>  '240',
                    'description' =>  'mouseover popup height in pixels - this value can be overridden by specifying a ' .
                                      '"popup_height" parameter with the "show_custom_field" shortcode',
                    'value'       =>  '240',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ),
                'popup_style'  => array(
                    'type'        =>  'text',
                    'id'          =>  'popup_style',
                    'label'       =>  __( 'Mouseover Popup Style', $mf_domain ),
                    'name'        =>  'mf_field[option][popup_style]',
                    'default'     =>  'background-color:white;border:2px solid black;',
                    'description' =>  'mouseover popup style - this value can be overridden by specifying a ' .
                                      '"popup_style" parameter with the "show_custom_field" shortcode',
                    'value'       =>  'background-color:white;border:2px solid black;',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ),
                'popup_classname' => [
                    'type'        =>  'text',
                    'id'          =>  'popup_classname',
                    'label'       =>  __( 'Mouseover Popup Classname', $mf_domain ),
                    'name'        =>  'mf_field[option][popup_classname]',
                    'default'     =>  'background-color:white;border:2px solid black;',
                    'description' =>  'mouseover popup classname - this value can be overridden by specifying a ' .
                                      '"popup_classname" parameter with the "show_custom_field" shortcode',
                    'value'       =>  '',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ]
            )
        );
    }

    public function display_field( $field, $group_index = 1, $field_index = 1 ) {
        global $mf_domain, $post;
        # setup main field
        $field_id = "mf2tk-$field[name]-$group_index-$field_index";
        $input_value = str_replace( '"', '&quot;', $field['input_value'] );
        $opts = $field[ 'options' ];
        $null = NULL;
        $width  = mf2tk\get_data_option( 'max_width',  $null, $opts, 320 );
        $height = mf2tk\get_data_option( 'max_height', $null, $opts, 240 );
        $attrWidth  = $width  ? " width=\"$width\""   : '';
        $attrHeight = $height ? " height=\"$height\"" : '';
        #set up caption field
        $caption_field_name = $field['name'] . self::$suffix_caption;
        $caption_input_name = sprintf( 'magicfields[%s][%d][%d]', $caption_field_name, $group_index, $field_index );
        $caption_input_value = mf2tk\get_mf_post_value( $caption_field_name, $group_index, $field_index, '' );
        # choose how to use text depending on whether a caption is specified or not
        $how_to_use_with_caption_style = 'display:' . ( $caption_input_value ? 'list-item;' : 'none;'      );
        $how_to_use_no_caption_style   = 'display:' . ( $caption_input_value ? 'none;'      : 'list-item;' );
        #set up link field
        $link_field_name = $field['name'] . self::$suffix_link;
        $link_input_name = sprintf( 'magicfields[%s][%d][%d]', $link_field_name, $group_index, $field_index );
        $link_input_value = mf2tk\get_mf_post_value( $link_field_name, $group_index, $field_index, '' );
        #set up hover field
        $hover_field_name = $field['name'] . self::$suffix_hover;
        $hover_input_name = sprintf( 'magicfields[%s][%d][%d]', $hover_field_name, $group_index, $field_index );
        $hover_input_value = mf2tk\get_mf_post_value( $hover_field_name, $group_index, $field_index, '' );
        $index = $group_index === 1 && $field_index === 1 ? '' : "<$group_index,$field_index>";
        # setup geometry for no caption image
        $no_caption_padding = 0;
        $no_caption_border = 2;
        $no_caption_width = $width + 2 * ( $no_caption_padding + $no_caption_border );
        # generate and return the HTML
        $output = <<<EOD
<div class="media_field_mf">
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
    <div class="mf2tk-field-input-optional mf2tk-caption-field">
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
    <!-- optional hover field -->
    <div class="mf2tk-field-input-optional">
        <button class="mf2tk-field_value_pane_button">Open</button>
        <h6>Optional Mouseover Popup for Image</h6>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <textarea name="$hover_input_name" rows="8" cols="80"
                placeholder="Enter post content fragment with show_custom_field and/or show_macro shortcodes. This will be displayed as a popup when the mouse is over the image. Although this could be plain HTML, using a reusable content template is probably more convenient.">$hover_input_value</textarea>
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
  
    static function get_image( $field_name, $group_index = 1, $field_index = 1, $post_id = NULL, $atts = [ ] ) {
        global $post;
        if ( $post_id === NULL ) {
            $post_id = $post->ID;
        }
        $data = mf2tk\get_data2( $field_name, $group_index, $field_index, $post_id );
        $opts = $data[ 'options' ];
        $width  = mf2tk\get_data_option( 'width',  $atts, $opts, 320, 'max_width'  );
        $height = mf2tk\get_data_option( 'height', $atts, $opts, 240, 'max_height' );
        # get optional caption
        $caption = mf2tk\get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_caption );
        # get optional link
        $link    = mf2tk\get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_link    );
        # get optional mouse-over popup
        $hover   = mf2tk\get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_hover   );
        $attrWidth  = $width  ? " width=\"$width\""   : '';
        $attrHeight = $height ? " height=\"$height\"" : '';
        # if an optional mouse-over popup has been specified let the containing div handle the mouse-over event 
        if ( $hover ) {
            $popup_width     = mf2tk\get_data_option( 'popup_width',     $atts, $opts, 320 );
            $popup_height    = mf2tk\get_data_option( 'popup_height',    $atts, $opts, 240 );
            $popup_style     = mf2tk\get_data_option( 'popup_style',     $atts, $opts      );
            $popup_classname = mf2tk\get_data_option( 'popup_classname', $atts, $opts      );
            $popup_classname = 'mf2tk-overlay' . ( $popup_classname ? ' ' . $popup_classname : '' );
            $hover = mf2tk\do_macro( [ 'post' => $post_id ], $hover );
            $hover_class = 'mf2tk-hover';
            $overlay = <<<EOD
<div class="$popup_classname"
    style="display:none;position:absolute;z-index:10000;text-align:center;width:{$popup_width}px;height:{$popup_height}px;{$popup_style}">
    $hover
</div>
EOD;
        } else {
            $hover_class = '';
            $overlay = '';
        }
        $html = <<<EOD
<div class="$hover_class" style="display:inline-block;width:{$width}px;padding:0px;">
    <a href="$link" target="_blank"><img src="$data[meta_value]"{$attrWidth}{$attrHeight}></a>
    $overlay
</div>
EOD;
        # if an optional caption has been specified wrap image with caption container
        if ( $caption ) {
            $align      = mf2tk\get_data_option( 'align',      $atts, $opts, 'aligncenter' );
            $align      = mf2tk\re_align( $align );
            $class_name = mf2tk\get_data_option( 'class_name', $atts, $opts                );
            if ( !$width ) { $width = 160; }
            if ( !$class_name ) { $class_name = "mf2tk-{$data['type']}-{$field_name}"; }
            $class_name .= ' mf2tk-alt-image';
            $html = img_caption_shortcode( array( 'width' => $width, 'align' => $align,
                'class' => $class_name, 'caption' => $caption ), $html );
            $html = preg_replace_callback( '/<div\s.*?style=".*?(width:\s*\d+px)/', function( $matches ) use ( $width ) {
                return str_replace( $matches[1], "width:{$width}px;max-width:100%", $matches[0] );  
            }, $html, 1 );
            $html = preg_replace_callback( '/(<img\s.*?)>/', function( $matches ) {
                return $matches[1] . ' style="margin:0;max-width:100%">';  
            }, $html, 1 );
        }
        return $html;
    }  
}
