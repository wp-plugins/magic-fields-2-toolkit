<?php

/*
Plugin Name: Magic Fields 2 Toolkit
Plugin URI: http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-3/
Description: custom post copier, custom fields shortcodes, ...
Version: 0.3.1
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
            if ( array_key_exists( 'clean_files_mf', $options ) ) {
                include( dirname(__FILE__)
                    . '/magic-fields-2-clean-files_mf.php' );
            }
            if ( array_key_exists( 'search_using_magic_fields', $options ) ) {
                include( dirname(__FILE__)
                    . '/search-by-custom-field.php' );
            }
        }
        add_action( 'plugins_loaded', function() {
            load_plugin_textdomain( 'magic-fields-2-toolkit', FALSE,
                dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
        } );
    }
}

new Magic_Fields_2_Toolkit_Init();

?>
