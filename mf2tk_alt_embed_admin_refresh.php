<?php
global $wpdb;
error_log( '##### mf2tk_alt_embed_admin_refresh.php:$_REQUEST["field"]=' . $_REQUEST['field'] );
preg_match( '/magicfields\[(\w+)\]\[\d+\]\[\d+\]/', $_REQUEST['field'], $matches );
$sql = 'SELECT options FROM ' . MF_TABLE_CUSTOM_FIELDS . " WHERE name = '$matches[1]'";
$result = $wpdb->get_var( $sql );
$options = unserialize( $result );
error_log( '##### mf2tk_alt_embed_admin_refresh.php:$options=' . print_r( $options, true ) );
error_log( '##### mf2tk_alt_embed_admin_refresh.php:$_REQUEST["url"]=' . $_REQUEST['url'] );
echo wp_oembed_get( $_REQUEST['url'], array( 'width' => $options['max_width'], 'height' => $options['max_height'] ) );
die();
?>