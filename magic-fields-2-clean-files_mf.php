<?php

/*
 * Copyright 2012 by Magenta Cuda
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * Tested on WordPress 3.5 and Magic Fields 2.1
 */

/*
 * Finds files in folder "wp-content/files_mf" that are not referenced by 
 * published or draft posts.
 *
 * Note that the unlink is intentionally DISABLED. You need to edit the
 * appropiate lines to enable it after verifing the correctness of this.
 */

function get_unreferenced_image_files_in_folder_files_mf() {
  global $wpdb;
  echo( '<h2>Unreferenced Files in Folder "wp-content/files_mf/"</h2>' );
  if ( $handle = opendir( MF_FILES_DIR ) ) {
    $entries = array();
    while ( false !== ($entry = readdir( $handle ) ) ) {
      if ( is_dir( MF_FILES_DIR . $entry ) ) { continue; }
      $entries[] = $entry;    
    }
    closedir($handle);
    echo( '<a href="#" onclick="document.getElementById(\'dir-files-mc\')'
      . '.style.display=\'block\';return false;"><h3>Files of Folder '
      . '"wp-content/files_mf/"'
      . '</h3></a><div id="dir-files-mc" style="border:2px solid black;'
      . 'margin:10px 20px;padding:5px;display:none;"><button style'
      . '="float:right;" onclick="this.parentNode.style.display=\'none\';'
      . 'return false;">X</button><ol>' );
    foreach ( $entries as $entry ) { echo( "<li>\"$entry\"</li>" ); }
    echo( '</ol></div>' );
    $sql = 'SELECT post_id, meta_key, meta_value FROM ' . $wpdb->postmeta
      . ' WHERE meta_key IN (SELECT name FROM ' . MF_TABLE_CUSTOM_FIELDS
        . ' WHERE type = "image" OR type = "audio" OR type = "file" )'
      . ' AND post_id IN (SELECT ID FROM '. $wpdb->posts
        . ' WHERE post_status = "publish" OR post_status = "draft"'
          . ' OR post_status = "auto-draft")';
    $results = $wpdb->get_results( $sql, ARRAY_A );
    echo( '<a href="#" onclick="document.getElementById(\'dir-referenced-mc\')'
      . '.style.display=\'block\';return false;"><h3>Referenced by Published '
      . 'or Draft Posts'
      . '</h3></a><div id="dir-referenced-mc" style="border:2px solid black;'
      . 'margin:10px 20px;padding:5px;display:none;"><button style'
      . '="float:right;" onclick="this.parentNode.style.display=\'none\';'
      . 'return false;">X</button><ol>' );
    $referenced = array();
    foreach ( $results as $result ) {
      if ( !$result['meta_value'] ) { continue; }
      $referenced[] = $result['meta_value'];
      echo( '<li><a href="' . MF_FILES_URL . $result['meta_value'] . '" target='
        . '"_blank"><span style="font-weight:bold;">&quot;'
        . $result['meta_value']
        . '&quot;</span></a>&nbsp;&nbsp; referenced by &nbsp;&nbsp;<a href="'
        . get_permalink( $result['post_id'] ) . '" target="_blank">'
        . '<span style="font-weight:bold;">&quot;'
        . get_the_title( $result['post_id'] ) . '&quot;</span></a>&nbsp;&nbsp;'
        . ' via field &nbsp;&nbsp;<span style="font-weight:bold;">'
        . $result['meta_key'] . '</span></li>' );
    }
    echo( '</ol></div>' );
    $unreferenced = array_merge( array_diff( $entries, $referenced ) );
    echo( '<a href="#" onclick="document.getElementById('
      . '\'dir-unreferenced-mc\').style.display=\'block\';return false;"><h3>'
      . 'Unreferenced by Published or Draft Posts'
      . '</h3></a><div id="dir-unreferenced-mc" style="border:2px solid black;'
      . 'margin:10px 20px;padding:5px;display:block;"><button style'
      . '="float:right;" onclick="this.parentNode.style.display=\'none\';'
      . 'return false;">X</button><ol>' );
    echo( '<form method="post" action="' . get_option('siteurl')
      . '/wp-admin/options-general.php?page=get_unreferenced_files_mc&amp;'
      . 'noheader=true"><ol>' );
    foreach ( $unreferenced as $i => $unreference ) {
      echo( '<li><input type="checkbox" name="to-be-deleted-' . $i . '" value="'
        . $unreference . '">&nbsp;&nbsp;<a href="' . MF_FILES_URL . $unreference
        . '" target="_blank"><span style="font-weight:bold;">&quot;'
        . $unreference . '&quot;</span></a></li>' );
    }
    echo( '</ol><input type="submit" value="Delete Checked"></form></div>' );
  }
}

if ( is_admin() ) {
  if ( strpos( $_SERVER['REQUEST_URI'], 'wp-admin/options-general.php?page=magic-fields-2-toolkit-page' ) !== FALSE ) {
    add_action( 'admin_notices', function() {
      if ( $deleted = get_transient( 'deleted_files_mc17' ) ) {
        echo( "<div style=\"padding:0px 20px;border:1px solid red;margin:20px;\">$deleted</div>" );
        delete_transient( 'deleted_files_mc17' );
      }
    } );
    add_action( 'settings_page_magic-fields-2-toolkit-page', function() {
      echo( "<div style=\"clear:both;padding:20px;border:2px solid black;margin:20px;\">" );
      get_unreferenced_image_files_in_folder_files_mf();
      echo( '</div>' );
    }, 11 );
  }
  if (strpos( $_SERVER['REQUEST_URI'], 'wp-admin/options-general.php?page=get_unreferenced_files_mc' ) !== FALSE ) {
    add_action( 'admin_menu', function() {
      global $_registered_pages;
  	  $hookname = get_plugin_page_hookname( 'get_unreferenced_files_mc', 'options-general.php' );
  	  add_action( $hookname, function() {
        $deleted='<h3>Status of File Delete Requests</h3><ul>';
        $unlinked = 0;
        $not_unlinked = 0;
        foreach ( $_REQUEST as $key => $request ) {
          if ( strpos( $key, 'to-be-deleted-' ) !== 0 ) { continue; }
          if ( unlink( MF_FILES_DIR . $request ) ) {
            $status = '<span style="color:green;">deleted</span>';
            ++$unlinked;
          } else {
            $status = '<span style="color:red;">failed</span>';
            ++$not_unlinked;
          }
          $deleted .= "<li>$status - \"" . MF_FILES_DIR . "$request\"</li>";
        } 
        $deleted .= "</ul><h3><span style=\"color:green;\">deleted $unlinked files</span>, "
          . "&nbsp;&nbsp;<span style=\"color:red;\">failed $not_unlinked files</span>.</h3>";
        set_transient( 'deleted_files_mc17', $deleted, 10 );
        wp_redirect( 'options-general.php?page=magic-fields-2-toolkit-page' );
        
        exit();
      } );
      $_registered_pages[$hookname] = true;
    } );
  }
}

?>