<?php
/*
 * This code derived from "mf_front_end.php" of Magic Fields 2 by Hunk and Gnuget
 * License: GPL2
 */
 
function alt_get_audio( $field_name, $group_index=1, $field_index=1, $post_id=NULL ) {
  global $post;

  if ( !$post_id ) { $post_id = $post->ID; }
  
  $audio = get( $field_name, $group_index, $field_index, $post_id );
  
  if ( empty( $audio ) ) { return FALSE; }

  $mime_type = array(
    'mp3' => 'audio/mpeg',
    'wav' => 'audio/wav',
    'ogg' => 'audio/ogg',
  );

  $extension = strtolower( pathinfo( $audio,  PATHINFO_EXTENSION ) );

  #error_log( 'alt_get_audio():$_SERVER[\'HTTP_USER_AGENT\']=' . print_r( $_SERVER['HTTP_USER_AGENT'], true ) );
  #error_log( 'alt_get_audio():$extension=' . print_r( $extension, true ) );

  # the condition in the following if statement may need adjustment
  if ( strpos( $_SERVER['HTTP_USER_AGENT'], 'iPad;' ) !== FALSE
    || strpos( $_SERVER['HTTP_USER_AGENT'], 'iPhone;' ) !== FALSE ) {
  #if ( strpos( $_SERVER['HTTP_USER_AGENT'], 'Windows ' ) ) { # for testing against Windows
    if ( array_key_exists( $extension, $mime_type ) ) {
      $player = '<audio controls><source src="' . $audio . '" type="' . $mime_type[$extension] .'"></audio>';
    } else {
      'Your browser cannot play ' . $extension . ' audio files.';
    }
  } else {
    $player = stripslashes(trim("\<div style=\'padding-top:3px;\'\>\<object classid=\'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\' codebase='\http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=7,0,19,0\' width=\'95%\' height=\'20\' wmode=\'transparent\' \>\<param name=\'movie\' value=\'".MF_URL."js/singlemp3player.swf?file=".urlencode($audio)."\' wmode=\'transparent\' /\>\<param name=\'quality\' value=\'high\' wmode=\'transparent\' /\>\<embed src=\'".MF_URL."js/singlemp3player.swf?file=".urlencode($audio)."' width=\'50\%\' height=\'20\' quality=\'high\' pluginspage=\'http://www.macromedia.com/go/getflashplayer\' type=\'application/x-shockwave-flash\' wmode=\'transparent\' \>\</embed\>\</object\>\</div\>"));
  }
  
  #error_log( 'alt_get_audio():$player=' . print_r( $player, true ) );

  return $player;
}
