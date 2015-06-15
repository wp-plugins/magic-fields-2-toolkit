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
 */
add_action( 'admin_enqueue_scripts', function() {
    wp_enqueue_style( 'mf2tk-jquery-ui', plugins_url( 'css/mf2tk-jquery-ui.min.css', __FILE__ ) );
    wp_enqueue_script( 'jquery-ui-tabs' );
    wp_enqueue_script( 'mf2tk_clean_mf_files', plugins_url( 'js/mf2tk_clean_mf_files.js', __FILE__ ),
        array( 'jquery' ) );
} );

function get_unreferenced_image_files_in_folder_files_mf() {
    global $wpdb;
    if ( !( $handle = opendir( MF_FILES_DIR ) ) ) { return; }
    echo( '<h2 style="text-align:center;font-weight:bold;">Unreferenced Files in Folder ".../wp-content/files_mf/"</h2>' );
    echo( '<div id="mf2tk-unreferenced-files" style="color:#070707;background-color:#d7d7d7;">'
        . '<style scoped>table.mf2tk-unreferenced,table.mf2tk-unreferenced th,table.mf2tk-unreferenced td'
        . '{padding:5px 10px;border:2px solid black;border-collapse:collapse;}'
        . 'table.mf2tk-unreferenced{margin:10px;}</style>'
        . '<ul><li><a href="#dir-files-mc">Files of Folder ".../wp-content/files_mf/"</a></li>'
        . '<li><a href="#dir-referenced-mc">Referenced by Published or Draft Posts</a></li>'
        . '<li><a href="#dir-unreferenced-mc">Unreferenced by Published or Draft Posts</a></li>'
        . '</ul>' );
    $entries = array();
    while ( false !== ($entry = readdir( $handle ) ) ) {
      if ( is_dir( MF_FILES_DIR . $entry ) ) { continue; }
      $entries[] = $entry;    
    }
    closedir($handle);
    $entries = array_map( function( $v ) {
        $o = new stdClass();
        $o->real_name = $v;
        $o->friendly_name = substr( $v, 10);
        $o->size = filesize( MF_FILES_DIR . $v );
        $o->date = date( DATE_RSS, (integer) substr( $v, 0, 10 ) );
        return $o;
    }, $entries );
    usort( $entries, function( $a, $b ) {
        if ( $a->friendly_name === $b->friendly_name ) { return 0; }
        return $a->friendly_name < $b->friendly_name ? -1 : 1;
    } );
    echo( '<div id="dir-files-mc" style="margin:10px 20px;padding:5px;"><table class="mf2tk-unreferenced">' );
    echo( '<tr><th>No.</th><th>Friendly Name</th><th>Real File Name</th><th>Size</th><th>Time Stamp</th></tr>' );
    foreach ( $entries as $i => $entry ) {
        echo( '<tr><td>' . ( $i + 1 ) . '</td>'
            . "<td><a href=\"" . MF_FILES_URL . "$entry->real_name\" target=\"_blank\">$entry->friendly_name</td>"
            . "<td>$entry->real_name</td><td>$entry->size</td><td>$entry->date</td></tr>" );
    }
    echo( '</table></div>' );
    $sql = 'SELECT post_id, meta_key, meta_value FROM ' . $wpdb->postmeta
        . ' WHERE meta_key IN (SELECT name FROM ' . MF_TABLE_CUSTOM_FIELDS
            . ' WHERE type = "image" OR type = "audio" OR type = "file" )'
        . ' AND post_id IN (SELECT ID FROM '. $wpdb->posts
            . ' WHERE post_status = "publish" OR post_status = "draft"'
                . ' OR post_status = "auto-draft") ORDER BY SUBSTR( meta_value, 11 ) ASC';
    $results = $wpdb->get_results( $sql, ARRAY_A );
    echo( '<div id="dir-referenced-mc" style="margin:10px 20px;padding:5px;">'
        . '<table class="mf2tk-unreferenced">'
        . '<th>No.</th><th>Friendly Name</th><th>Real File Name</th><th>Referenced by</th><th>via Field</th>' );
    $referenced = array();
    $previous = '';
    $count = 0;
    foreach ( $results as $result ) {
        $value = $result['meta_value'];
        if ( !$value ) { continue; }
        if ( $value !== $previous ) {
            ++$count;
            $previous = $value;
        }
        $referenced[] = $value;
        echo( '<tr><td>' . $count . '</td>'
            . '<td><a href="' . MF_FILES_URL . $value . '" target="_blank">' . substr( $value, 10 ) . '</a></td>'
            . '<td><a href="' . MF_FILES_URL . $value . '" target="_blank">' . $value . '</a></td>'
            . '<td><a href="' . get_permalink( $result['post_id'] ) . '" target="_blank">'
                . get_the_title( $result['post_id'] ) . '</a></td>'
            . '<td>' . $result['meta_key'] . '</td></tr>' );
    }
    echo( '</table></div>' );
    $entries = array_map( function( $o ) { return $o->real_name; }, $entries );
    $unreferenced = array_merge( array_diff( $entries, $referenced ) );
    echo( '<div id="dir-unreferenced-mc" style="margin:10px 20px;padding:5px;">' );
    echo( '<form method="post" action="' . get_option('siteurl')
      . '/wp-admin/options-general.php?page=get_unreferenced_files_mc&amp;noheader=true">'
      . '<button class="mf2tk-delete-mf-files">Select All</button>&nbsp;&nbsp;'
      . '<button class="mf2tk-delete-mf-files">Clear All</button><br><hr><ol>' );
    foreach ( $unreferenced as $i => $unreference ) {
        echo( '<li><input type="checkbox" class="mf2tk-delete-mf-files" '
            . 'name="to-be-deleted-' . $i . '" value="' . $unreference
            . '">&nbsp;&nbsp;<a href="' . MF_FILES_URL . $unreference
            . '" target="_blank"><span style="font-weight:bold;">&quot;'
            . $unreference . '&quot;</span></a></li>' );
    }
    echo( '</ol><hr><br><input type="submit" value="Delete Checked"></form></div>' );
    echo( '</div>' );
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