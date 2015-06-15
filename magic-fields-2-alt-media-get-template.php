<?php

# included by get_audio() of alt_audio_field/alt_audio_field.php and get_video() of alt_video_field/alt_video_field.php
# to implement common funtionality

$srcs = mf2tk\get_media_srcs( $field_name, $group_index, $field_index, $post_id, get_called_class() );
if ( count( $srcs ) === 1 ) {
    $srcs = [ 'src' => reset( $srcs ) ];
} else if ( !count( $srcs ) ) {
    $html = 'No media found!';
    return;
}

# get optional caption
$caption = mf2tk\get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_caption );

# get optional poster image
$poster = mf2tk\get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_poster );

# merge shortcode parameters with magic field attributes
$options = mf2tk\get_data2( $field_name, $group_index, $field_index, $post_id )['options'];
$null = NULL;
$defaults = [
    'loop'     => mf2tk\get_data_option( 'loop',     $null, $options                           ) ? 'on' : 'off',
    'autoplay' => mf2tk\get_data_option( 'autoplay', $null, $options                           ) ? 'on' : 'off',
    'preload'  => mf2tk\get_data_option( 'preload',  $null, $options, 'metadata'               ),
    'height'   => mf2tk\get_data_option( 'height',   $null, $options, 240,        'max_height' ),
    'width'    => mf2tk\get_data_option( 'width',    $null, $options, 320,        'max_width'  )
];
extract( shortcode_atts( $defaults, $atts ) );

# construct attribute array for wp media shortcode
$atts = $srcs;
if ( $width    ) { $atts['width']    = $width;    }
if ( $height   ) { $atts['height']   = $height;   }
if ( $loop     ) { $atts['loop']     = $loop;     }
if ( $autoplay ) { $atts['autoplay'] = $autoplay; }
if ( $preload  ) { $atts['preload']  = $preload;  }
if ( $poster   ) { $atts['poster']   = $poster;   }
$atts = array_diff_key( $atts, $invalid_atts );
$atts = array_filter( $atts, function( $v ) { return $v !== 'off'; } );
$html = call_user_func( $wp_media_shortcode, $atts );
?>