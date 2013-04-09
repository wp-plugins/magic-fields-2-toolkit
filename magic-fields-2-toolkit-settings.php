<?php

class Magic_Fields_2_Toolkit_Settings {
    public function __construct() {
        add_action( 'admin_init', function() {
            register_setting( 'magic_fields_2_toolkit',
                'magic_fields_2_toolkit_enabled', function( $input ) {
                error_log( '##### Magic_Fields_2_Toolkit_Settings:$input='
                    . print_r( $input, TRUE ) );
                $new = array_key_exists( 'alt_related_type_field', $input );
                $options = get_option( 'magic_fields_2_toolkit_enabled' );
                $old = array_key_exists( 'alt_related_type_field', $options );
                if ( defined( 'MF_PATH' ) ) {
                    $mf_dir = MF_PATH . '/field_types/alt_related_type_field/';
                    $my_dir = dirname( __FILE__ ) . '/alt_related_type_field/';
                    $files = [ 'alt_related_type_field.php', 'preview.jpg', 
                        'icon_color.png', 'icon_gray.png' ];
                    $failed = FALSE;
                    if ( $new && !$old ) {
                        if ( !file_exists( $mf_dir ) ) {
                            if ( mkdir( $mf_dir, 0777 ) ) {
                                foreach ( $files as $file ) {
                                    copy( $my_dir . $file, $mf_dir . $file );
                                }
                            } else {
                                unset( $input['alt_related_type_field'] );
                                $failed = TRUE;
                            }
                        }
                    } else if ( !$new && $old ) {
                       if ( file_exists( $mf_dir ) ) {
                            foreach ( $files as $file ) {
                                unlink( $mf_dir . $file );
                            }
                            if ( !rmdir( $mf_dir )) {
                                $input['alt_related_type_field'] = 'enabled';
                                $failed = TRUE;
                            }
                       }
                    }
                    if ( $failed ) {
                        add_settings_error(
                            'magic_fields_2_toolkit_alt_related_type_field',
                            esc_attr( 'alt_related_type_field' ),
                            'access denied' );
                    }
                }
                return $input;
            } );
            add_settings_section( 'magic_fields_2_toolkit_settings_sec', '',
                function() {
                    echo( __( 'Use this form to enable specific features.',
                        'magic-fields-2-toolkit' ) );
                }, 'magic-fields-2-toolkit-page' );	
            $options = get_option( 'magic_fields_2_toolkit_enabled' );
            #error_log( '##### Magic_Fields_2_Toolkit_Settings:$options='
            #    . print_r( $options, TRUE ) );
            add_settings_field( 'magic_fields_2_toolkit_custom_post_copier', 
                __( 'Custom Post Copier', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'custom_post_copier]" type="checkbox" '
                        . 'value="enabled"' . ( ( is_array( $options )
                        && array_key_exists( 'custom_post_copier', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( 'Enabled', 'magic-fields-2-toolkit' ) );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec' );		
            add_settings_field( 'magic_fields_2_toolkit_dumb_shortcodes', 
                __( 'Dumb Shortcodes', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'dumb_shortcodes]" type="checkbox" '
                        . 'value="enabled"' .( ( is_array( $options )
                        && array_key_exists( 'dumb_shortcodes', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( 'Enabled', 'magic-fields-2-toolkit' ) );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec' );		
            add_settings_field( 'magic_fields_2_toolkit_clean_files_mf', 
                __( 'Clean Folder files_mf', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'clean_files_mf]" type="checkbox" '
                        . 'value="enabled"' . ( ( is_array( $options )
                        && array_key_exists( 'clean_files_mf', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( 'Enabled', 'magic-fields-2-toolkit' ) );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec' );		
            add_settings_field( 'magic_fields_2_toolkit_alt_related_type_field', 
                __( 'Alt Related Field Type', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'alt_related_type_field]" type="checkbox" '
                        . 'value="enabled"' . ( ( is_array( $options )
                        && array_key_exists( 'alt_related_type_field', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( 'Enabled', 'magic-fields-2-toolkit' ) );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec' );		
        } );
        add_action( 'admin_menu', function() {
            add_options_page( 'Magic Fields 2 Toolkit', 'Magic Fields 2 Toolkit',
                'manage_options', 'magic-fields-2-toolkit-page',
                function() {
                    #error_log( '##### backtrace=' . print_r( debug_backtrace(
                    #    DEBUG_BACKTRACE_IGNORE_ARGS ), TRUE ) );
                    if ( isset( $_GET['settings-updated'] )
                        && $_GET['settings-updated'] == TRUE ) {
                        #error_log( '##### $_GET=' . print_r( $_GET, TRUE ) );
                        #error_log( '##### $_POST=' . print_r( $_POST, TRUE ) );
                    }
                    echo( '<h1>Magic Fields 2 Toolkit</h1>' );
                    echo( '<form method="post" action="options.php">' );
                    settings_fields( 'magic_fields_2_toolkit' ); 
                    do_settings_sections( 'magic-fields-2-toolkit-page' );
                    submit_button();
                    echo( '</form>' );
                    echo( '<div style="border:2px solid black;'
                        . 'background-color:LightGray;padding:10px;'
                        . 'margin:30px 25px;font-size:larger;'
                        . 'font-weight:bold;">'
                        . 'For usage instructions please visit '
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-2/">'
                        . ' the online documentation</a>.</div>' );
            } );
        }, 11);
    }
}

new Magic_Fields_2_Toolkit_Settings();

?>