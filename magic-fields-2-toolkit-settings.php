<?php

class Magic_Fields_2_Toolkit_Settings {
    public static function do_field_type_option( $field, $input ) {
        # make the reality (filesystem) match the options for alt_*
        #error_log( '##### Magic_Fields_2_Toolkit_Settings:$input='
        #    . print_r( $input, TRUE ) );
        if ( $input === NULL ) { $input = array(); }
        $new = array_key_exists( $field . '_field', $input );
        $options = get_option( 'magic_fields_2_toolkit_enabled' );
        #error_log( '##### Magic_Fields_2_Toolkit_Settings:$options='
        #    . print_r( $options, TRUE ) );
        if ( $options === FALSE ) { $options = array(); }
        $old = array_key_exists( $field . '_field', $options );
        if ( defined( 'MF_PATH' ) ) {
            $mf_dir = MF_PATH . '/field_types/' . $field . '_field/';
            $my_dir = dirname( __FILE__ ) . '/' . $field . '_field/';
            $files = array( $field . '_field.php', 'preview.jpg', 
                'icon_color.png', 'icon_gray.png' );
            $failed = FALSE;
            if ( $new && !$old ) {
                if ( !file_exists( $mf_dir ) ) {
                    if ( mkdir( $mf_dir, 0777 ) ) {
                        foreach ( $files as $file ) {
                            copy( $my_dir . $file, $mf_dir . $file );
                        }
                    } else {
                        unset( $input[$field . '_field'] );
                        $failed = TRUE;
                    }
                }
            } else if ( !$new && $old ) {
               if ( file_exists( $mf_dir ) ) {
                    foreach ( $files as $file ) {
                        unlink( $mf_dir . $file );
                    }
                    if ( !rmdir( $mf_dir )) {
                        $input[$field . '_field'] = 'enabled';
                        $failed = TRUE;
                    }
               }
            }
            if ( $failed ) {
                add_settings_error(
                    'magic_fields_2_toolkit_' . $field . '_field',
                    esc_attr( $field . '_field' ),
                    'access denied' );
            }
        }
        return $input;
    }
    public function __construct() {
        add_action( 'admin_init', function() {
            register_setting( 'magic_fields_2_toolkit',
                'magic_fields_2_toolkit_enabled', function( $input ) {
                $input = Magic_Fields_2_Toolkit_Settings::do_field_type_option( 'alt_textbox', $input );
                $input = Magic_Fields_2_Toolkit_Settings::do_field_type_option( 'alt_related_type', $input );
                $input = Magic_Fields_2_Toolkit_Settings::do_field_type_option( 'alt_dropdown', $input );
                $input = Magic_Fields_2_Toolkit_Settings::do_field_type_option( 'alt_embed', $input );
                $input = Magic_Fields_2_Toolkit_Settings::do_field_type_option( 'alt_video', $input );
                $input = Magic_Fields_2_Toolkit_Settings::do_field_type_option( 'alt_audio', $input );
                $input = Magic_Fields_2_Toolkit_Settings::do_field_type_option( 'alt_image', $input );
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
                        . __( ' Enabled', 'magic-fields-2-toolkit' )
                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-4-2/#copy">'
                        . __( 'Documentation', 'magic-fields-2-toolkit' )
                        . '</a>'
                    );
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
                        . __( ' Enabled', 'magic-fields-2-toolkit' )
                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-4-2/#shortcode">'
                        . __( 'Documentation', 'magic-fields-2-toolkit' )
                        . '</a>'
                    );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec' );		
            add_settings_field( 'magic_fields_2_toolkit_dumb_macros', 
                __( 'Content Macros', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'dumb_macros]" type="checkbox" '
                        . 'value="enabled"' .( ( is_array( $options )
                        && array_key_exists( 'dumb_macros', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( ' Enabled', 'magic-fields-2-toolkit' )
                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-4-2/#macros">'
                        . __( 'Documentation', 'magic-fields-2-toolkit' )
                        . '</a>'
                    );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec' );		
            add_settings_field( 'magic_fields_2_toolkit_search_using_magic_fields', 
                __( 'Search using Magic Fields', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'search_using_magic_fields]" type="checkbox" '
                        . 'value="enabled"' . ( ( is_array( $options )
                        && array_key_exists( 'search_using_magic_fields', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( ' Enabled', 'magic-fields-2-toolkit' )
                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-4-2/#search">'
                        . __( 'Documentation', 'magic-fields-2-toolkit' )
                        . '</a>'
                    );
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
                        . __( ' Enabled', 'magic-fields-2-toolkit' )
                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-4-2/#unreferenced">'
                        . __( 'Documentation', 'magic-fields-2-toolkit' )
                        . '</a>'
                    );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec' );		
            add_settings_field( 'magic_fields_2_toolkit_alt_related_type_field', 
                __( 'Alt Related Type Field', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'alt_related_type_field]" type="checkbox" '
                        . 'value="enabled"' . ( ( is_array( $options )
                        && array_key_exists( 'alt_related_type_field', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( ' Enabled', 'magic-fields-2-toolkit' )
                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-4-2/#alt_related">'
                        . __( 'Documentation', 'magic-fields-2-toolkit' )
                        . '</a>'
                    );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec' );		
            add_settings_field( 'magic_fields_2_toolkit_alt_embed_field', 
                __( 'Alt Embed Field', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'alt_embed_field]" type="checkbox" '
                        . 'value="enabled"' . ( ( is_array( $options )
                        && array_key_exists( 'alt_embed_field', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( ' Enabled', 'magic-fields-2-toolkit' )
                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-4-2/#embed">'
                        . __( 'Documentation', 'magic-fields-2-toolkit' )
                        . '</a>'
                    );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec' );
            add_settings_field( 'magic_fields_2_toolkit_alt_video_field', 
                __( 'Alt Video Field', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'alt_video_field]" type="checkbox" '
                        . 'value="enabled"' . ( ( is_array( $options )
                        && array_key_exists( 'alt_video_field', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( ' Enabled', 'magic-fields-2-toolkit' )
                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-4-2/#video">'
                        . __( 'Documentation', 'magic-fields-2-toolkit' )
                        . '</a>'
                    );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec' );
            add_settings_field( 'magic_fields_2_toolkit_alt_audio_field', 
                __( 'Alt Audio Field', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'alt_audio_field]" type="checkbox" '
                        . 'value="enabled"' . ( ( is_array( $options )
                        && array_key_exists( 'alt_audio_field', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( ' Enabled', 'magic-fields-2-toolkit' )
                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-4-2/#audio">'
                        . __( 'Documentation', 'magic-fields-2-toolkit' )
                        . '</a>'
                    );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec' );
            add_settings_field( 'magic_fields_2_toolkit_alt_image_field', 
                __( 'Alt Image Field', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    #error_log( '##### add_settings_field:callback():options='
                    #    . print_r( $options, true ) );
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'alt_image_field]" type="checkbox" '
                        . 'value="enabled"' . ( ( is_array( $options )
                        && array_key_exists( 'alt_image_field', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( ' Enabled', 'magic-fields-2-toolkit' )
                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-4-2/#image">'
                        . __( 'Documentation', 'magic-fields-2-toolkit' )
                        . '</a>'
                    );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec' );
            add_settings_field( 'magic_fields_2_toolkit_alt_textbox_field', 
                __( 'Alt Textbox Field', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'alt_textbox_field]" type="checkbox" '
                        . 'value="enabled"' . ( ( is_array( $options )
                        && array_key_exists( 'alt_textbox_field', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( ' Enabled', 'magic-fields-2-toolkit' )
                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-4-2/#alt_textbox">'
                        . __( 'Documentation', 'magic-fields-2-toolkit' )
                        . '</a>'
                    );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec' );
            add_settings_field( 'magic_fields_2_toolkit_alt_dropdown_field', 
                __( 'Alt Dropdown Field', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'alt_dropdown_field]" type="checkbox" '
                        . 'value="enabled"' . ( ( is_array( $options )
                        && array_key_exists( 'alt_dropdown_field', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( ' Enabled', 'magic-fields-2-toolkit' )
                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-4-2/#alt_dropdown">'
                        . __( 'Documentation', 'magic-fields-2-toolkit' )
                        . '</a>'
                    );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec'
            );
            add_settings_field( 'magic_fields_2_toolkit_alt_audio', 
                __( 'Alt Get Audio', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'alt_get_audio]" type="checkbox" '
                        . 'value="enabled"' . ( ( is_array( $options )
                        && array_key_exists( 'alt_get_audio', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( ' Enabled', 'magic-fields-2-toolkit' )
                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-4-2/#alt_audio">'
                        . __( 'Documentation', 'magic-fields-2-toolkit' )
                        . '</a>'
                    );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec'
            );
            add_settings_field( 'magic_fields_2_toolkit_utility_functions', 
                __( 'Utility Functions', 'magic-fields-2-toolkit' ),
                function() use ( $options) {
                    echo( '<input name="magic_fields_2_toolkit_enabled['
                        . 'utility_functions]" type="checkbox" '
                        . 'value="enabled"' . ( ( is_array( $options )
                        && array_key_exists( 'utility_functions', $options ) )
                        ? ' checked' : '' ) . '>'
                        . __( ' Enabled', 'magic-fields-2-toolkit' )
                        . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'
                        . '<a href="http://magicfields17.wordpress.com/'
                        . 'magic-fields-2-toolkit-0-4-2/#utility_functions">'
                        . __( 'Documentation', 'magic-fields-2-toolkit' )
                        . '</a>'
                    );
                },
                'magic-fields-2-toolkit-page',
                'magic_fields_2_toolkit_settings_sec'
            );
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
                        . 'magic-fields-2-toolkit-0-4-2/">'
                        . ' the online documentation</a>.</div>' );
            } );
        }, 11);
    }
}

new Magic_Fields_2_Toolkit_Settings();
