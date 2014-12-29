<?php

class Magic_Fields_2_Toolkit_Settings {
    public static $fields = [
        'alt_textbox', 'alt_related_type', 'alt_dropdown', 'alt_numeric', 'alt_url', 'alt_embed', 'alt_video', 'alt_audio',
        'alt_image', 'alt_table', 'alt_template'
    ];
    private static function sync_field_and_option( $field, $options ) {
        if ( array_key_exists( $field . '_field', $options ) ) {
            $mf_dir = MF_PATH . "/field_types/{$field}_field/";
            if ( !file_exists( $mf_dir ) ) {
                if ( !mkdir( $mf_dir, 0777 ) ) {
                    error_log( "mkdir( $mf_dir, 0777 ) failed" );
                }
            }
            if ( file_exists( $mf_dir ) ) {
                $my_dir = dirname( __FILE__ ) . "/{$field}_field/";
                foreach ( [ "{$field}_field.php", 'preview.jpg', 'icon_color.png', 'icon_gray.png' ] as $file ) {
                    if ( !copy( $my_dir . $file, $mf_dir . $file ) ) {
                        error_log( "copy( $my_dir{$file}, $mf_dir{$file} ) failed" );
                    }
                }
            }
        }
    }
    private static function do_field_type_option( $field, $input, $options ) {
        # make the reality (filesystem) match the options for alt_*
        $new = array_key_exists( $field . '_field', $input );
        $old = array_key_exists( $field . '_field', $options );
        if ( defined( 'MF_PATH' ) ) {
            $mf_dir = MF_PATH . "/field_types/{$field}_field/";
            $my_dir = dirname( __FILE__ ) . "/{$field}_field/";
            $files = [ "{$field}_field.php", 'preview.jpg', 'icon_color.png', 'icon_gray.png' ];
            $failed = [];
            if ( $new && !$old ) {
                if ( !file_exists( $mf_dir ) ) {
                    if ( mkdir( $mf_dir, 0777 ) ) {
                        foreach ( $files as $file ) {
                            if ( !copy( $my_dir . $file, $mf_dir . $file ) ) { $failed[] = "copy \"{$my_dir}{$file}\""; }
                        }
                    } else {
                        $failed[] = "mkdir \"$mf_dir\"";
                    }
                    if ( $failed ) { unset( $input["{$field}_field"] ); }
                }
            } else if ( !$new && $old ) {
               if ( file_exists( $mf_dir ) ) {
                    foreach ( $files as $file ) {
                        if ( !unlink( $mf_dir . $file ) ) { $failed[] = "unlink \"{$mf_dir}{$file}\""; }
                    }
                    if ( !rmdir( $mf_dir ) ) { $failed[] = "rmdir \"$mf_dir\""; }
                    if ( $failed ) { $input[$field . '_field'] = 'enabled'; }
               }
            }
            if ( $failed ) {
                add_settings_error( "magic_fields_2_toolkit_{$field}_field", "{$field}_field",
                    implode( ', ', $failed ) . ' failed!' );
            }
        }
        return $input;
    }
    public function __construct() {
        add_action( 'admin_enqueue_scripts', function( ) {
            wp_enqueue_style( 'admin', plugins_url( 'admin.css', __FILE__ ) );
            wp_enqueue_style( 'dashicons' );
        } );
        add_action( 'admin_init', function() {
            if ( !defined( 'MF_PATH' ) ) { return; }
            $options = get_option( 'magic_fields_2_toolkit_enabled', [ ] );
            foreach ( self::$fields as $field ) {
                self::sync_field_and_option( $field, $options );
            }
            register_setting( 'magic_fields_2_toolkit', 'magic_fields_2_toolkit_enabled', function( $input ) {
                if ( $input === NULL ) { $input = [ ]; }
                $options = get_option( 'magic_fields_2_toolkit_enabled', [ ] );
                foreach ( self::$fields as $field ) {
                    $input = self::do_field_type_option( $field, $input, $options );
                }
                return $input;
            } );
            add_settings_section( 'magic_fields_2_toolkit_settings_sec', '',
                function( ) {
                    echo( __( '<h3>Use this form to enable specific features.</h3>', 'mf2tk' ) );
                }, 'magic-fields-2-toolkit-page' );	
            $options = get_option( 'magic_fields_2_toolkit_enabled', [ ] );
            $settings = [
                ['dumb_shortcodes', 'Dumb Shortcodes', 'shortcode'],
                ['dumb_macros', 'Content Templates', 'macros'],
                ['alt_template_field', 'Alt Template Field', 'alt_template'],
                ['alt_table_field', 'Alt Table Field', 'alt_table'],
                ['alt_numeric_field', 'Alt Numeric Field', 'alt_numeric'],
                ['alt_url_field', 'Alt URL Field', 'alt_url'],
                ['alt_related_type_field', 'Alt Related Type Field', 'alt_related'],
                ['alt_embed_field', 'Alt Embed Field', 'embed'],
                ['alt_video_field', 'Alt Video Field', 'video'],
                ['alt_audio_field', 'Alt Audio Field', 'audio'],
                ['alt_image_field', 'Alt Image Field', 'image'],
                ['alt_textbox_field', 'Alt Textbox Field', 'alt_textbox'],
                ['alt_dropdown_field', 'Alt Dropdown Field', 'alt_dropdown'],
                ['search_using_magic_fields', 'Search using Magic Fields', 'search'],
                ['custom_post_copier', 'Custom Post Copier', 'copy'],
                ['clean_files_mf', 'Clean Folder files_mf', 'unreferenced'],
                ['alt_get_audio', 'Alt Get Audio', 'alt_audio'],
                ['utility_functions', 'Utility Functions', 'utility_functions']
            ];
            array_walk( $settings, function( $v, $i, $options ) {
                $name  = $v[0];
                $title = $v[1];
                $help  = $v[2];
                add_settings_field( "magic_fields_2_toolkit_$name", __( $title, 'mf2tk' ),
                    function() use ( $name, $help, $options ) {
                        echo( "<input name=\"magic_fields_2_toolkit_enabled[$name]\" type=\"checkbox\" value=\"enabled\""
                            . ( ( is_array( $options ) && array_key_exists( $name, $options ) ) ? ' checked' : '' ) . '>' );
                        echo( "<a href=\"http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/#$help\" "
                            . 'target="_blank"><div class="dashicons dashicons-info" '
                            . 'style="text-decoration:none;padding-left:7px;"></div></a>' );
                    },
                    'magic-fields-2-toolkit-page', 'magic_fields_2_toolkit_settings_sec' );
            }, $options );
            
        } );
        add_action( 'admin_menu', function() {
            if ( !defined( 'MF_PATH' ) ) { return; }
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
