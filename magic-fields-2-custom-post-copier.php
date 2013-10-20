<?php

/*
 * Description:   Create a copy of a Magic Fields 2 custom post including the
 *                Magic Fields 2 custom fields, custom groups and custom
 *                taxonomies.
 * Documentation: http://magicfields17.wordpress.com/toolkit
 * Author:        Magenta Cuda
 * License:       GPL2
 *
 * To copy a custom post open the "All Your Custom Post Type" menu item and 
 * click on "Create Copy" for the entry of the desired post.
 */

/*  Copyright 2013  Magenta Cuda  (email:magenta.cuda@yahoo.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as 
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

$magic_fields_2_custom_post_copier_filter = function( $actions, $post ) {
    $actions['ufmf2_duplicate_post'] = '<a href="'
        . admin_url( 'admin.php?action=magic_fields_2_toolkit_copy_post&post='
        . $post->ID ) . '">' . __( 'Create Copy', 'magic-fields-2-toolkit' )
        . '</a>';
    return $actions;
};
add_filter( 'post_row_actions', $magic_fields_2_custom_post_copier_filter, 10,
    2 );
add_filter( 'page_row_actions', $magic_fields_2_custom_post_copier_filter, 10,
    2 );
unset( $magic_fields_2_custom_post_copier_filter );

add_action( 'admin_action_magic_fields_2_toolkit_copy_post', function() {
    global $wpdb;
    #error_log( '$_REQUEST=' . print_r( $_REQUEST, TRUE ) );
    try {
        $result = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->posts
            . ' WHERE ID = ' . $_REQUEST['post'], ARRAY_A );
        $post = $result[0];
        #error_log( 'original $post=' . print_r( $post, TRUE ) );
        unset( $post['ID'] );
        $post['guid'] = '';
        $post['post_name'] = '';
        $post['post_status'] = 'draft';
        $post['post_title'] = 'Copy of ' . $post['post_title'];
        $post['post_author'] = get_current_user_id();
        $post['post_date'] = current_time( 'mysql' );
        $post['post_date_gmt'] = '0000-00-00 00:00:00';
        $post['post_modified'] = $post['post_date'];
		$post['post_modified_gmt'] = get_gmt_from_date( $post['post_date'] );
        #error_log( 'copy $post=' . print_r( $post, TRUE ) );
        if ( false === $wpdb->insert( $wpdb->posts, $post ) ) {
            throw new Exception( '' );
        }
        $id = (int) $wpdb->insert_id; 
        $wpdb->update( $wpdb->posts, array( 'guid' => get_permalink( $id ) ),
            array( 'ID' => $id ) );
        #$post2 = $wpdb->get_results( 'SELECT * FROM ' . $wpdb->posts
        #    . ' WHERE ID = ' . $id, ARRAY_A )[0];
        #error_log( 'copied $post2=' . print_r( $post2, TRUE ) );
        
        $new_meta_id = array();
        foreach( $wpdb->get_results( 'SELECT * FROM ' . $wpdb->postmeta
            . ' WHERE post_id = ' . $_REQUEST['post'], ARRAY_A ) as $row ) {
            if ( $row['meta_key'] == 'edit_last'
                || $row['meta_key'] == 'edit_lock' ) { continue; }
            $old_meta_id = $row['meta_id'];
            unset( $row['meta_id'] );
            $row[post_id] = $id;
            #error_log( 'postmeta row=' . print_r( $row, TRUE ) );
            $wpdb->insert( $wpdb->postmeta, $row );
            $new_meta_id[$old_meta_id] = (int) $wpdb->insert_id;
        }
        
        if ( defined( 'MF_TABLE_POST_META' ) ) {
            foreach( $wpdb->get_results( 'SELECT * FROM ' . MF_TABLE_POST_META
                . ' WHERE post_id = ' . $_REQUEST['post'], ARRAY_A ) as $row ) {
                $row['post_id'] = $id;
                $row['meta_id'] = $new_meta_id[ $row['meta_id'] ];
                #error_log( 'mf_post_meta row=' . print_r( $row, TRUE ) );
                $wpdb->insert( MF_TABLE_POST_META, $row );
            }
        }
        
        foreach( $wpdb->get_results( 'SELECT * FROM '
            . $wpdb->term_relationships . ' WHERE object_id = '
            . $_REQUEST['post'], ARRAY_A ) as $row ) {
            $row['object_id'] = $id;
            #error_log( 'term_relationships row=' . print_r( $row, TRUE ) );
            $wpdb->insert( $wpdb->term_relationships, $row );
            $result = $wpdb->get_col( 'SELECT count FROM ' . $wpdb->term_taxonomy
                . ' WHERE term_taxonomy_id = ' . $row['term_taxonomy_id'] );
            $count = $result[0];
            #error_log( 'count=' . print_r( $count, TRUE ) );
            $wpdb->update( $wpdb->term_taxonomy, array( 'count' => ( $count + 1 ) ),
               array( 'term_taxonomy_id' => $row['term_taxonomy_id'] ) );
        }
        
        wp_redirect( admin_url( 'post.php?action=edit&post=' . $id ) );
    } catch (Exception $e) {
      set_transient( 'magic_fields_2_custom_post_copier_error',
          "copy of ... failed", 10 );
        wp_redirect( admin_url( 'edit.php?post_type='
          . $_REQUEST['post_type'] ) );
    }
} );

if ( is_admin() ) {
  if ( $error = get_transient( 'magic_fields_2_custom_post_copier_error' ) ) {
    add_action('admin_notices', function() use ($error) {
       echo '<div class="error"><p>' . $error . '</p></div>';
    } );
    delete_transient( 'magic_fields_2_custom_post_copier_error' );
  }
}

?>