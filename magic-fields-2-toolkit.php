<?php

/*
Plugin Name: Magic Fields 2 Toolkit
Plugin URI: http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4/
Description: custom post copier, custom fields shortcodes, ...
Version: 0.4
Author: Magenta Cuda
Author URI: http://magentacuda.wordpress.com
License: GPL2
*/

class Magic_Fields_2_Toolkit_Init {
    public function __construct() {
        include( dirname(__FILE__) . '/magic-fields-2-toolkit-settings.php' );
        $options = get_option( 'magic_fields_2_toolkit_enabled' );
        #error_log( '##### Magic_Fields_2_Toolkit_Init:$options='
        #    . print_r( $options, TRUE ) );
        if ( is_array( $options ) ) {
            if ( array_key_exists( 'custom_post_copier', $options ) ) {
                include( dirname(__FILE__)
                    . '/magic-fields-2-custom-post-copier.php' );
            }
            if ( array_key_exists( 'dumb_shortcodes', $options ) ) {
                include( dirname(__FILE__)
                    . '/magic-fields-2-dumb-shortcodes.php' );
            }
            if ( array_key_exists( 'dumb_macros', $options ) ) {
                include( dirname(__FILE__)
                    . '/magic-fields-2-dumb-macros.php' );
            }
            if ( array_key_exists( 'clean_files_mf', $options ) ) {
                include( dirname(__FILE__)
                    . '/magic-fields-2-clean-files_mf.php' );
            }
            if ( array_key_exists( 'search_using_magic_fields', $options ) ) {
                include( dirname(__FILE__)
                    . '/magic-fields-2-search-by-custom-field.php' );
            }
            if ( array_key_exists( 'alt_dropdown_field', $options ) ) {
                add_action( 'save_post', function( $post_id ) {
                    global $wpdb;
                    if ( !array_key_exists( 'magicfields', $_REQUEST ) || !is_array( $_REQUEST['magicfields'] ) ) { return; }
                    #error_log( '##### save_post:$_REQUEST[\'magicfields\']='
                    #    . print_r( $_REQUEST['magicfields'], TRUE ) );
                    $mf_fields = $wpdb->get_results( 'SELECT id, name, post_type, type, options FROM '
                        . MF_TABLE_CUSTOM_FIELDS . ' WHERE type = "alt_dropdown"', OBJECT );
                    foreach ( $_REQUEST['magicfields'] as $field => $values ) {
                        foreach( $mf_fields as $mf_field ) {
                            if ( $mf_field->name === $field && $mf_field->post_type === $_REQUEST['post_type'] ) {
                                $options = unserialize( $mf_field->options );
                                #error_log( '##### save_post:$options=' . print_r( $options, TRUE ) );
                                $updated = FALSE;
                                foreach( $values as $values1 ) {
                                    foreach( $values1 as $values2 ) {
                                        foreach( $values2 as $value ) {
                                            #error_log( '##### save_post:$value=' . print_r( $value, TRUE ) );
                                            $index = strpos( $options['options'], $value );
                                            if ( $index === FALSE || ( $index && !ctype_space(
                                                substr( $options['options'] . "\r\n", $index + strlen( $value ), 1 ) ) ) ) {
                                                $options['options'] = rtrim( $options['options'] ) . "\r\n" . $value;
                                                $updated = TRUE;
                                            }
                                        }
                                    }
                                }
                                if ( $updated ) {
                                    $wpdb->update( MF_TABLE_CUSTOM_FIELDS, array( 'options' => serialize( $options ) ),
                                        array( 'id' => $mf_field->id ) );
                                }
                            }
                        }
                    }
                } );
            }
            if ( array_key_exists( 'alt_get_audio', $options ) ) {
                include( dirname(__FILE__)
                    . '/magic-fields-2-alt-get-audio.php' );
            }
            if ( array_key_exists( 'utility_functions', $options ) ) {
                include( dirname(__FILE__)
                    . '/magic-fields-2-utility-functions.php' );
            }
        }
        add_filter( 'plugin_row_meta', function( $plugin_meta, $plugin_file, $plugin_data, $status ) {
            #error_log( '##### filter:plugin_row_meta:$plugin_file=' . $plugin_file );
            if ( strpos( $plugin_file, basename( __FILE__ ) ) !== FALSE ) {
                $plugin_meta[] = '<a href="' . admin_url( 'options-general.php?page=magic-fields-2-toolkit-page' ) . '">'
                    . __( 'Settings' ) . '</a>';
            }
            return $plugin_meta;
        }, 10, 4 );
        add_action( 'plugins_loaded', function() {
            load_plugin_textdomain( 'magic-fields-2-toolkit', FALSE,
                dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        } );
    }
}

new Magic_Fields_2_Toolkit_Init();

?>
