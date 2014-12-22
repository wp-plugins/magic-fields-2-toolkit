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
$options = get_data( $field_name, $group_index, $field_index, $post_id )['options'];
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
if ( !$height && preg_match( '/<video\s+class="wp-video-shortcode"\s+id="([^"]+)"/', $html, $matches ) ) {
    $id = $matches[1];
    $html .= <<<EOD
<script>
jQuery(document).ready(function(){
  var v=jQuery("video.wp-video-shortcode#$id");
  var f=function(){
    if(!v.length){return;}
    if(v[0].videoWidth&&v[0].videoHeight){
      var e=v.parents("div.mejs-container");
      if(e.length){
        v=v[0];
        v.height=(v.videoHeight/v.videoWidth)*v.width;
        e[0].style.height=v.height+"px";
        e.parents("div.wp-video")[0].style.height=v.height+"px";
        e.find("div.mejs-layer").css("height",v.height+"px");
        return;
      }
    }
    window.setTimeout(f,1000);
  };
  f();
});
</script>
EOD;
}
?>