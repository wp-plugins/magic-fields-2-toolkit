<?php

$srcs = _mf2tk_get_media_srcs( $field_name, $group_index, $field_index, $post_id, get_called_class() );
if ( count( $srcs ) === 1 ) {
    $srcs = [ 'src' => reset( $srcs ) ];
} else if ( !count( $srcs ) ) {
    $html = 'No media found!';
    return;
}

# get optional caption
$caption = _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_caption );
# get optional poster image
$poster = _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_poster );

# merge call attributes with magic field attributes
$options = get_data2( $field_name, $group_index, $field_index, $post_id )['options'];
$defaults = array(
    'loop'     => $options['loop'] ? 'on' : 'off',
    'autoplay' => $options['autoplay'] ? 'on' : 'off',
    'preload'  => $options['preload'],
    'height'   => $options['max_height'],
    'width'    => $options['max_width']
);
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
if ( ( !$height || !$width ) && preg_match( '/<video\s+class="wp-video-shortcode"\s+id="([^"]+)"/', $html, $matches ) ) {
    $id = $matches[1];
    $aspect_ratio = array_key_exists( 'aspect_ratio', $options ) ? $options['aspect_ratio'] : '4:3';
    if ( preg_match( '/([\d\.]+):([\d\.]+)/', $aspect_ratio, $matches ) ) { $aspect_ratio = $matches[1] / $matches[2]; }
    $do_width = !$width ? 'true' : 'false';
    $html .= <<<EOD
<script type="text/javascript">
    jQuery(document).ready(function(){mf2tkResizeVideo("video.wp-video-shortcode#$id",$aspect_ratio,$do_width);});
</script>
EOD;
}
?>