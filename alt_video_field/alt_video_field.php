<?php

class alt_video_field extends mf_custom_fields {

    public static $suffix_fallback = '_mf2tk_fallback';
    public static $suffix_alternate_fallback = '_mf2tk_alternate_fallback';
    public static $suffix_caption = '_mf2tk_caption';
    public static $suffix_poster = '_mf2tk_poster';

    public function _update_description(){
        global $mf_domain;
        $this->description = __( 'This is a Magic Fields 2 field for WordPress\'s video shortcode facility.', $mf_domain );
    }
  
    public function _options() {
        global $mf_domain;
    
        return array(
            'option'  => array(
                'max_width'  => array(
                    'type'        =>  'text',
                    'id'          =>  'max_width',
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
                    'id'          =>  'max_height',
                    'label'       =>  __('Height',$mf_domain),
                    'name'        =>  'mf_field[option][max_height]',
                    'default'     =>  '0',
                    'description' =>  'height in pixels - 0 lets the browser set the height to preserve the aspect ratio' .
                                      ' - recommended but you must at least load the meta data on page load ' .
                                      ' - 0 does not work for Flash videos',
                    'value'       =>  '0',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ),
                'loop' => array(
                    'type'        => 'checkbox',
                    'id'          => 'loop',
                    'label'       => __( 'Loop to beginning when finished and continue playing', $mf_domain ),
                    'name'        => 'mf_field[option][loop]',
                    'default'     =>  '',
                    'description' =>  '',
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
                    'description' =>  '',
                    'value'       =>  '',
                    'div_class'   =>  '',
                    'class'       =>  ''
                ),
                'preload' => array(
                    'type'        => 'select',
                    'id'          => 'preload',
                    'label'       => __( 'When the page loads', $mf_domain ),
                    'name'        =>  'mf_field[option][preload]',
                    'default'     => '',
                    'options'     => array(
                                        'metadata' => 'load only the metadata',
                                        'none'     => 'Do not load the video',
                                        'auto'     => 'Load the entire video'
                                    ),
                    'add_empty'   => false,
                    'description' => '',
                    'value'       => '',
                    'div_class'   => '',
                    'class'       => ''
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
                ),
                'aspect_ratio'  => array(
                    'type'        =>  'text',
                    'id'          =>  'aspect_ratio',
                    'label'       =>  __( 'Default Aspect Ratio - width:height, e.g. 16:9', $mf_domain ),
                    'name'        =>  'mf_field[option][aspect_ratio]',
                    'default'     =>  '4:3',
                    'description' =>  'If the browser determines the height then use this as the aspect ratio ' .
                                      'when the browser is unable to determine the aspect ratio - i.e. for Flash videos',
                    'value'       =>  '4:3',
                    'div_class'   =>  '',
                    'class'       =>  ''
                )
            )
        );
    }

    public function display_field( $field, $group_index = 1, $field_index = 1 ) {
        global $mf_domain, $post, $mf_post_values;
        $media_type = 'video';
        $wp_media_shortcode = 'wp_video_shortcode';
        $output = include WP_PLUGIN_DIR . '/magic-fields-2-toolkit/magic-fields-2-alt-media-template.php';
        #error_log( '##### alt_audio_field::display_field():$output=' . print_r( $output, true ) );
        return $output;
    }
  
    
    static function get_video( $field_name, $group_index = 1, $field_index = 1, $post_id = NULL, $atts = array() ) {
        global $post;
        if ( !$post_id ) { $post_id = $post->ID; }
        $wp_get_media_extensions = 'wp_get_video_extensions';
        $wp_media_shortcode = 'wp_video_shortcode';
        $data = get_data2( $field_name, $group_index, $field_index, $post_id );
        $invalid_atts = array();
        include WP_PLUGIN_DIR . '/magic-fields-2-toolkit/magic-fields-2-alt-media-get-template.php';
        #error_log( '##### alt_video_field::get_video():$html=' . $html );
        if ( $caption ) {
            $width = !empty( $atts['width'] ) ? $atts['width'] : $data['options']['max_width'];
            if ( !$width ) { $width = 160; }
            $class_name = array_key_exists( 'class_name', $data['options'] ) ? $data['options']['class_name'] : null;
            if ( !$class_name ) { $class_name = "mf2tk-{$data['type']}-{$field_name}"; }
            $html = img_caption_shortcode( array( 'width' => $width, 'align' => $data['options']['align'],
                'class' => $class_name, 'caption' => $caption ), $html );
            $html = preg_replace_callback( '/<div\s.*?style=".*?(width:\s*\d+px)/', function( $matches ) use ( $width ) {
                return str_replace( $matches[1], "width:{$width}px", $matches[0] );  
            }, $html, 1 );
        }      
        return $html;
    }  
}
