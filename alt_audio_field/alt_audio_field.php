<?php

class alt_audio_field extends mf_custom_fields {

    public static $suffix_fallback           = '_mf2tk_fallback';
    public static $suffix_alternate_fallback = '_mf2tk_alternate_fallback';
    public static $suffix_caption            = '_mf2tk_caption';
    public static $suffix_poster             = '_mf2tk_poster';
    public static $suffix_link               = '_mf2tk_link';
    public static $suffix_hover              = '_mf2tk_hover';

    public function _update_description( ) {
        global $mf_domain;
        $this->description = __( 'This is a Magic Fields 2 field for WordPress\'s audio shortcode facility.', $mf_domain );
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
                    'description' =>  'width in pixels for optional caption and/or optional image' .
                                      ' - this value can be overridden by specifying a "width" parameter' .
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
                    'default'     =>  '240',
                    'description' =>  'height in pixels for the optional image' .
                                      ' - 0 lets the browser set the height to preserve the aspect ratio - recommended' .
                                      ' - this value can be overridden by specifying a "height" parameter' .
                                      ' with the "show_custom_field" shortcode',
                    'value'       =>  '240',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ),
                'loop' => array(
                    'type'        => 'checkbox',
                    'id'          => 'loop',
                    'label'       => __( 'Loop to beginning when finished and continue playing', $mf_domain ),
                    'name'        => 'mf_field[option][loop]',
                    'default'     =>  '',
                    'description' =>  'this value can be overridden by specifying an "loop" parameter with the' .
                                      ' "show_custom_field" shortcode - the parameter value is "on" or "off"',
                    'value'       =>  '',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ),
                'autoplay' => array(
                    'type'        => 'checkbox',
                    'id'          => 'autoplay',
                    'label'       => __( 'Automatically play as soon as the media file is ready', $mf_domain ),
                    'name'        => 'mf_field[option][autoplay]',
                    'default'     =>  '',
                    'description' =>  'this value can be overridden by specifying an "autoplay" parameter with the' .
                                      ' "show_custom_field" shortcode - the parameter value is "on" or "off"',
                    'value'       =>  '',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ),
                'preload' => array(
                    'type'        => 'select',
                    'id'          => 'preload',
                    'label'       => __( 'When the page loads', $mf_domain ),
                    'name'        =>  'mf_field[option][preload]',
                    'default'     => 'metadata',
                    'options'     => array(
                                        'metadata' => 'load only the metadata',
                                        'none'     => 'Do not load the audio',
                                        'auto'     => 'Load the entire audio'
                                    ),
                    'add_empty'   => false,
                    'description' => 'this value can be overridden by specifying a "preload" parameter with the' .
                                     ' "show_custom_field" shortcode - the parameter value is "metadata", "none"' .
                                     ' or "auto" - "auto" loads the entire audio',
                    'value'       => 'metadata',
                    'div_class'   => '',
                    'class'       => ''
                ),
                'align' => array(
                    'type'        => 'select',
                    'id'          => 'align',
                    'label'       => __( 'Alignment', $mf_domain ),
                    'name'        =>  'mf_field[option][align]',
                    'default'     => 'aligncenter',
                    'options'     => array(
                                        'aligncenter' => 'Center',
                                        'alignright'  => 'Right',
                                        'alignleft'   => 'Left',
                                        'alignnone'   => 'None',
                                    ),
                    'add_empty'   => false,
                    'description' => 'alignment is effective only if a caption is specified' .
                                     ' - this value can be overridden by specifying an "align" parameter with the' .
                                     ' "show_custom_field" shortcode ' .
                                     ' - the parameter values are "aligncenter", "alignright" and "alignleft"',
                    'value'       => 'aligncenter',
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
                                      ' - this value can be overridden by specifying a "class_name" parameter with the ' .
                                      ' "show_custom_field" shortcode ',
                    'value'       =>  '',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ),
                'popup_width'  => [
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
                ],
                'popup_height'  => [
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
                ],
                'popup_style'  => [
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
                ],
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
        $media_type = 'audio';
        $wp_media_shortcode = 'wp_audio_shortcode';
        $output = include WP_PLUGIN_DIR . '/magic-fields-2-toolkit/magic-fields-2-alt-media-template.php';
        return $output;
    }
  
    static function get_audio( $field_name, $group_index = 1, $field_index = 1, $post_id = NULL, $atts = array() ) {
        global $post;
        $media_type = 'audio';
        if ( !$post_id ) {
            $post_id = $post->ID;
        }
        $wp_media_shortcode = 'wp_audio_shortcode';
        $original_atts = $atts;   # save $atts since magic-fields-2-alt-media-get-template.php will modify $atts
        # since magic-fields-2-alt-media-get-template.php is shared with audio some entries are media specific
        $invalid_atts = array( 'width' => true, 'height' => true, 'poster' => true );
        include WP_PLUGIN_DIR . '/magic-fields-2-toolkit/magic-fields-2-alt-media-get-template.php';
        $data = mf2tk\get_data2( $field_name, $group_index, $field_index, $post_id );
        $opts = $data[ 'options' ];
        $width  = mf2tk\get_data_option( 'width',  $original_atts, $opts, 320, 'max_width'  );
        $height = mf2tk\get_data_option( 'height', $original_atts, $opts, 240, 'max_height' );
        $attrWidth  = $width  ? " width=\"$width\""   : '';
        $attrHeight = $height ? " height=\"$height\"" : '';
        # attach optional poster image
        if ( $poster ) {
            # if an optional mouse-over popup has been specified let the containing div handle the mouse-over event
            # N.B. the mouse-over popup and clickable link feature for audio requires that a poster image be specified
            if ( $hover ) {
                $popup_width     = mf2tk\get_data_option( 'popup_width',     $original_atts, $opts, 320 );
                $popup_height    = mf2tk\get_data_option( 'popup_height',    $original_atts, $opts, 240 );
                $popup_style     = mf2tk\get_data_option( 'popup_style',     $original_atts, $opts,
                                                          'background-color:white;border:2px solid black;' );
                $popup_classname = mf2tk\get_data_option( 'popup_classname', $original_atts, $opts      );
                $popup_classname = 'mf2tk-overlay' . ( $popup_classname ? ' ' . $popup_classname : '' );
                $hover = mf2tk\do_macro( [ 'post' => $post_id ], $hover );
                $hover_class = 'mf2tk-hover';
                $overlay = <<<EOD
<div class="$popup_classname"
    style="display:none;position:absolute;z-index:10000;text-align:center;width:{$popup_width}px;height:{$popup_height}px;{$popup_style}">
    $hover
</div>
EOD;
            $html = <<<EOD
<div class="$hover_class" style="display:inline-block;width:{$width}px;padding:0px;margin:0px;">
    <a href="$link" target="_blank"><img src="$poster"{$attrWidth}{$attrHeight}></a>
    $overlay
</div>
$html
EOD;
            } else {
                $html = <<<EOD
<div style="display:inline-block;width:{$width}px;padding:0px;">
    <img src="$poster"{$attrWidth}{$attrHeight}>
    $html
</div>
EOD;
            }
        }
        # attach optional caption
        if ( $caption ) {
            $align      = mf2tk\get_data_option( 'align',      $original_atts, $opts, 'aligncenter' );
            $align      = mf2tk\re_align( $align );
            $class_name = mf2tk\get_data_option( 'class_name', $original_atts, $opts                );
            if ( !$width      ) { $width = 160;                                        }
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
