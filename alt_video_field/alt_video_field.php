<?php
// initialisation
global $mf_domain;

error_log( '##### alt_video_field.php:backtrace=' . print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), true ) );

if ( is_admin() ) {
    wp_enqueue_script( 'mf2tk_alt_media_admin', plugins_url( 'magic-fields-2-toolkit/js/mf2tk_alt_media_admin.js' ),
        array( 'jquery' ) );
}

class alt_video_field extends mf_custom_fields {

    private static $suffix_fallback = '_mf2tk_fallback';
    private static $suffix_alternate_fallback = '_mf2tk_alternate_fallback';
    private static $suffix_caption = '_mf2tk_caption';
    private static $suffix_poster = '_mf2tk_poster';

    public function _update_description(){
        global $mf_domain;
        $this->description = __( <<<'EOD'
This is a Magic Fields 2 field for WordPress's video shortcode facility.
<h3>How to Use</h3>
<ul>
<li style="list-style:square outside">Use with the Toolkit's shortcode:<br>
[show_custom_field field="your_field_name" filter="url_to_media"]<br>
<li style="list-style:square outside">Call the PHP function:<br>
alt_video_field::get_video( $field_name, $group_index, $field_index, $post_id, $atts = array() )
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
                    'id'          =>  'max_height',
                    'label'       =>  __('Height',$mf_domain),
                    'name'        =>  'mf_field[option][max_height]',
                    'default'     =>  '240',
                    'description' =>  'height',
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
                                        'alignnone'   => 'None',
                                        'aligncenter' => 'Center',
                                        'alignright'  => 'Right',
                                        'alignleft'   => 'Left',
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
        $media_type = 'video';
        $wp_media_shortcode = 'wp_video_shortcode';
        $output = include WP_PLUGIN_DIR . '/magic-fields-2-toolkit/magic-fields-2-alt-media-template.php';
        error_log( '##### alt_audio_field::display_field():$output=' . print_r( $output, true ) );
        return $output;
    }
  
    
    static function get_video( $field_name, $group_index = 1, $field_index = 1, $post_id = NULL, $atts = array() ) {
        $wp_get_media_extensions = 'wp_get_video_extensions';
        $wp_media_shortcode = 'wp_video_shortcode';
        $invalid_atts = array();
        include WP_PLUGIN_DIR . '/magic-fields-2-toolkit/magic-fields-2-alt-media-get-template.php';
        error_log( '##### alt_video_field::get_video():$html=' . $html );
        if ( $caption ) {
            $width = !empty( $atts['width'] ) ? $atts['width'] : $data['options']['max_width'];
            if ( !$width ) { $width = 160; }
            $html = img_caption_shortcode( array( 'width' => $width, 'align' => $data['options']['align'], 'caption' => $caption ),
                $html );
        }
        error_log( '##### alt_video_field::get_video():$html=' . $html );        
        return $html;
    }  
}
