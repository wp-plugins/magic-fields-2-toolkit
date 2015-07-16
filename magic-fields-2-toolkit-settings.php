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
    
    public function __construct( ) {
        add_action( 'admin_enqueue_scripts', function( ) {
            wp_enqueue_style( 'mf2tk_admin', plugins_url( 'css/mf2tk_admin.css', __FILE__ ) );
            wp_enqueue_style( 'dashicons' );
            wp_enqueue_script( 'mf2tk_clean_mf_files', plugins_url( 'js/mf2tk_clean_mf_files.js', __FILE__ ), [ 'jquery' ] );
        } );
        add_action( 'admin_init', function() {
            if ( !defined( 'MF_PATH' ) ) { return; }
            $options = get_option( 'magic_fields_2_toolkit_enabled', [ ] );
            foreach ( Magic_Fields_2_Toolkit_Settings::$fields as $field ) {
                Magic_Fields_2_Toolkit_Settings::sync_field_and_option( $field, $options );
            }
            add_settings_section( 'magic_fields_2_toolkit_settings_sec', 'Features',
                function( ) {
                    echo( '<div style="padding:10px 50px;">' . __( 'Use this form to enable specific features.', 'mf2tk' )
                        . '</div>' );
                }, 'magic-fields-2-toolkit-page' );	
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
            add_settings_section( 'magic_fields_2_toolkit_tags_sec', 'Labels',
                function( ) {
                    echo( __( <<<EOD
<div style="padding:10px 50px;">The original labels that the toolkit used are inconsistent.
Use this form to rename them to your liking, e.g., you can use short labels to reduce typing.
Aliases are supported so that you can use the old label and new label simultaneously until the old label is completely
replaced.
N.B. this does not change existing labels in the post content of existing posts.
My current convention is to use a &quot;mt_&quot; prefix but you are free to use your own convention.</div>
EOD
                    , 'mf2tk' ) );
                }, 'magic-fields-2-toolkit-page' );	
            $tags = [
                [ 'show_custom_field',       'shortcode to show a custom field' ],
                [ 'show_custom_field_alias', 'alias shortcode to show a custom field' ],
                [ 'show_macro',              'shortcode to show a content template' ],
                [ 'show_macro_alias',        'alias shortcode to show a content template' ],
                [ 'mt_show_gallery',         'shortcode to show a gallery' ],
                [ 'mt_show_gallery_alias',   'alias shortcode to show a gallery' ],
                [ 'mt_show_tabs',            'shortcode to show tabs' ],
                [ 'mt_show_tabs_alias',      'alias shortcode to tabs' ]
                
            ];
            array_walk( $tags, function( $v, $i, $options ) {
                $name  = $v[0];
                $title = $v[1];
                $value = !empty( $options[ $name ] ) ? $options[ $name ] : '';
                add_settings_field( "magic_fields_2_toolkit-tags-$name", __( $title, 'mf2tk' ), function( $a ) {
                    $name  = $a[0];
                    $value = $a[1];
                    echo( "<input name=\"magic_fields_2_toolkit_tags[$name]\" type=\"text\" value=\"$value\">" );
                }, 'magic-fields-2-toolkit-page', 'magic_fields_2_toolkit_tags_sec', [ $name, $value ] );
            }, mf2tk\get_tags( ) );
            add_settings_section( 'magic_fields_2_toolkit_sync_sec',
                'Sync the Toolkit\'s Fields with the Fields of Magic Fields 2',
                function( ) {
?>
<div>
<div style="width:70%;padding:10px 50px 50px 50px;float:left;">The latest version of the toolkit's fields ( alt_*_field )
must be copied to the &quot;fields_types&quot; folder of &quot;Magic Fields 2" plugin. The toolkit should handle this
automatically. However, this can fail to happen if you update the Magic Fields 2 plugin (Since the toolkit needs to be
installed after &quot;Magic Fields 2&quot;.) or you upgrade the toolkit by manually copying the files (The activation code
will not run in  this case.). You can force the toolkit to synchronize its fields with those in the &quot;fields_types&quot;
folder of &quot;Magic Fields 2" plugin at anytime by clicking the &quot;Sync Fields&quot; button to the right.</div>
<input name="mf2tk-sync-fields" id="mf2tk-sync-fields" class="button button-primary" value="Sync Fields" type="button"
    style="float:left;margin:30px 20px;">
<div style="clear:both;"></div>
</div>
<?php
                }, 'magic-fields-2-toolkit-page' );
                
            register_setting( 'magic-fields-2-toolkit-page', 'magic_fields_2_toolkit_enabled', function( $input ) {
                if ( $input === NULL ) {
                    $input = [ ];
                }
                $options = get_option( 'magic_fields_2_toolkit_enabled', [ ] );
                foreach ( Magic_Fields_2_Toolkit_Settings::$fields as $field ) {
                    $input = Magic_Fields_2_Toolkit_Settings::do_field_type_option( $field, $input, $options );
                }
                return $input;
            } );
            register_setting( 'magic-fields-2-toolkit-page', 'magic_fields_2_toolkit_tags', function( $input ) {
                if ( $input === NULL ) {
                    $input = [ ];
                }
                if ( array_key_exists( 'update_from_tpcti', $input ) ) {
                    # this update was generated by TPCTI so don't sync with TCPTI otherwise you have infinite recursion
                    unset( $input[ 'update_from_tpcti' ] );
                    return $input;
                }
                if ( defined( 'mf2tk\TPCTI_VERSION' ) && mf2tk\TPCTI_VERSION >= 1.0 ) {
                    # synchronize the 'show_macro' tag with the TPCTI option 'shortcode_name' 
                    $tpcti_options = get_option( 'tpcti_options', ( object ) [
                        'shortcode_name' => 'show_macro',
                        'content_macro_post_type' => 'content_macro',
                        'filter' => '@',
                        'post_member' => '.',
                        'use_native_mode' => false
                    ] );
                    $tpcti_options->shortcode_name       = $input[ 'show_macro' ];
                    $tpcti_options->shortcode_name_alias = $input[ 'show_macro_alias' ];
                    update_option( 'tpcti_options', $tpcti_options );
                }
                return $input;
            } );
            
        } );   # add_action( 'admin_init', function() {
            
        add_action( 'admin_menu', function( ) {
            if ( !defined( 'MF_PATH' ) ) {
                return;
            }
            add_options_page( 'Magic Fields 2 Toolkit', 'Magic Fields 2 Toolkit', 'manage_options',
                'magic-fields-2-toolkit-page', function( ) {
                    if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] == TRUE ) {
                    }
                    echo( '<h1>Magic Fields 2 Toolkit</h1><form method="post" action="options.php">' );
                    settings_fields( 'magic-fields-2-toolkit-page' ); 
                    do_settings_sections( 'magic-fields-2-toolkit-page' );
                    submit_button( );
                    echo( '</form>' );
?>
<div style="border:2px solid black;background-color:LightGray;padding:10px;margin:30px 25px;font-size:larger;font-weight:bold;">
For usage instructions please visit <a href="http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4-2/">the online 
documentation</a>.</div>
<?php
            } );
        }, 11 );

        # AJAX action 'wp_ajax_mf2tk_sync_fields' syncs the toolkit's fields with the fields in "Magic Fields 2"

        add_action( 'wp_ajax_mf2tk_sync_fields', function( ) {
            $options = get_option( 'magic_fields_2_toolkit_enabled', [ ] );
            foreach ( Magic_Fields_2_Toolkit_Settings::$fields as $field ) {
                Magic_Fields_2_Toolkit_Settings::sync_field_and_option( $field, $options );
            }
            die( 'The fields: ' . implode( ', ', Magic_Fields_2_Toolkit_Settings::$fields ) . ' have been synchronized.' );
        } );

    }   # public function __construct( ) {

}   # class Magic_Fields_2_Toolkit_Settings {

new Magic_Fields_2_Toolkit_Settings( );
