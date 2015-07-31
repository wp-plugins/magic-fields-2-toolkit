<?php

/*
 * Description:   Shortcode for showing Magic Fields 2 custom fields, custom 
 *                groups and custom taxonomies.
 * Documentation: http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4/#shortcode
 * Author:        Magenta Cuda
 * License:       GPL2
 */

/*  Copyright 2013  Magenta Cuda

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

namespace {

include_once( dirname( __FILE__ ) . '/magic-fields-2-group-key.php' );
include_once( dirname( __FILE__ ) . '/magic-fields-2-post-filter.php' );

class Magic_Fields_2_Toolkit_Dumb_Shortcodes {
    use Magic_Fields_2_Toolkit_Post_Filter;
    
    private static $options = NULL;
	private static $recursion_separator = '>';
    
    public static function initialize( ) {
   
        self::$options = $options = mf2tk\get_tags( );
        
        # add shortcodes
        
        if ( !empty( $options[ 'show_custom_field' ] ) ) {
            add_shortcode( $options[ 'show_custom_field' ], 'Magic_Fields_2_Toolkit_Dumb_Shortcodes::show_custom_field_wrapper' );
        }
        if ( !empty( $options[ 'show_custom_field_alias' ] ) ) {
            add_shortcode( $options[ 'show_custom_field_alias' ], 'Magic_Fields_2_Toolkit_Dumb_Shortcodes::show_custom_field_wrapper' );
        }
        
        if ( !empty( $options[ 'mt_show_gallery' ] ) ) {
            add_shortcode( $options[ 'mt_show_gallery' ], 'Magic_Fields_2_Toolkit_Dumb_Shortcodes::mt_show_gallery' );
        }
        if ( !empty( $options[ 'mt_show_gallery_alias' ] ) ) {
            add_shortcode( $options[ 'mt_show_gallery_alias' ], 'Magic_Fields_2_Toolkit_Dumb_Shortcodes::mt_show_gallery' );
        }

        if ( !empty( $options[ 'mt_show_tabs' ] ) ) {
            add_shortcode( $options[ 'mt_show_tabs' ], 'Magic_Fields_2_Toolkit_Dumb_Shortcodes::mt_show_tabs' );
        }
        if ( !empty( $options[ 'mt_show_tabs_alias' ] ) ) {
            add_shortcode( $options[ 'mt_show_tabs_alias' ], 'Magic_Fields_2_Toolkit_Dumb_Shortcodes::mt_show_tabs' );
        }

        remove_filter( 'the_content', 'wpautop' );
        
        # enqueue styles and scripts
        
        add_action( 'wp_enqueue_scripts', function( ) {
            wp_enqueue_style( 'mf2tk_media', plugins_url( 'css/mf2tk_media.css', __FILE__ ) );
            wp_enqueue_style( 'mf2tk-jquery-ui', plugins_url( 'css/mf2tk-jquery-ui.min.css', __FILE__ ) );
            #wp_add_inline_style( '', '' );
            wp_enqueue_script( 'mf2tk_alt_media', plugins_url( 'js/mf2tk_alt_media.js', __FILE__ ), [ 'jquery' ] );
            wp_enqueue_script( 'jquery-ui-tabs' );
        } );
    }   # public static function initialize() {

    # wrapper for the individual values of a field - modifies the values of a field
    
    public static function wrap_value( $value, $field, $type, $filters, $before, $after, $separator, $classes = [ ],
        $group_index = 0, $field_index = 0, $post_id = 0, $atts = [ ] ) {
        if ( $filters !== NULL ) {
            foreach( explode ( ';', $filters ) as $filter) {
                # filters should now be in the namespace mf2tk but for compatibility with older code also check
                # the global namespace. N.B. global filters are now deprecated
                if ( ( $mf2tk = function_exists( 'mf2tk\\' . $filter ) ) || function_exists( $filter ) ) {
                    $value = call_user_func( ( $mf2tk ? 'mf2tk\\' : '' ) . $filter,
                        $value, $field, $type, $classes, $group_index, $field_index, $post_id, $atts );
                } else if ( preg_match( '/(\w+?__)(\w+)/', $filter, $matches ) ) {
                    # this is filter with an __ suffix; the prefix is the name of the function that implements the
                    # filter; the suffix is passed as the first argument to the function
                    if ( ( $mf2tk = function_exists( 'mf2tk\\' . $matches[1] ) ) || function_exists( $matches[1] ) ) {
                        $value = call_user_func( ( $mf2tk ? 'mf2tk\\' : '' ) . $matches[1],
                            $matches[2], $value, $field, $type, $classes, $group_index, $field_index, $post_id, $atts );
                    }
                }
            }
        }
        if ( $value === NULL || $value === '' || $value === FALSE ) {
            return '';
        }
        return $before . $value . $after . $separator;
    }   # public static function wrap_value( $value, $field, $type, $filters, $before, $after, $separator, $classes = array(),
        
    # wrapper for a field - does things such as prepend a field name

    public static function wrap_field_value( $value, $before, $after, $separator, $label, $field, $class, $field_rename, $path ) {
        if ( function_exists( $field_rename ) ) {
            list( $label, $field ) = call_user_func( $field_rename, $label, $field, $path );
        } else {
            $label = trim( $path . self::$recursion_separator . $label, self::$recursion_separator );
        }
        $label = str_replace( ' ', '&nbsp;', $label );
        # do text substitution for the embedded comments '<!--$class-->', '<!--$field-->' and '<!--$Field-->' in the $before and $after text
        return str_replace( '<!--$class-->', $class, str_replace( '<!--$Field-->', $label, str_replace( '<!--$field-->', $field, $before ) ) ). $value
            . str_replace( '<!--$class-->', $class, str_replace( '<!--$Field-->', $label, str_replace( '<!--$field-->', $field, $after ) ) ) . $separator;
    }   # public static function wrap_field_value( $value, $before, $after, $separator, $label, $field, $class, $field_rename, $path ) {

    # wrapper for a group of fields - does things like add a header for a group of fields

    public static function wrap_group_value( $value, $before, $after, $separator, $label, $group, $index, $class, $rename, $path ) {
        if ( !$label ) {
            $label = $group;
        }
        if ( function_exists( $rename ) ) {
            list( $label, $group ) = call_user_func( $rename, $label, $group, $path );
        } else {
            $label = trim( $path . self::$recursion_separator . $label, self::$recursion_separator );
        }
        $label = str_replace( ' ', '&nbsp;', $label );
        # do text substitution for the embedded comments '<!--$class-->', '<!--$group-->' and '<!--$Group-->' in the $before and $after text
        return str_replace( '<!--$class-->', $class, str_replace( '<!--$Group-->', $label, str_replace( '<!--$group-->', $group, $before ) ) ). $value
            . str_replace( '<!--$class-->', $class, str_replace( '<!--$Group-->', $label, str_replace( '<!--$group-->', $group, $after ) ) ) . $separator;
    }   # public static function wrap_group_value( $value, $before, $after, $separator, $label, $group, $index, $class, $rename, $path ) {

    # remove_trailing_separator() removes a trailing $separator if it exists from $text and returns the result
    
    public static function remove_trailing_separator( $text, $separator ) {
        $separator_length = strlen( $separator );
        if ( $separator_length && strlen( $text ) >= $separator_length && substr_compare( $text, $separator, -$separator_length ) === 0 ) {
            return substr( $text, 0, -$separator_length );
        }
        return $text;
    }
    # show_custom_field() implements the [mt_field] shortcode

    public static function show_custom_field( $post_id, $the_names, $before, $after, $separator, $filter, $field_before, $field_after, $field_separator,
        $field_rename, $group_before, $group_after, $group_separator, $group_rename, $multi_before, $multi_after, $multi_separator, $finals, $path,
        $parent_ids = [ ], $atts = [ ] ) {
        global $wpdb;
        $content = '';
        $the_fields = $the_names;
        if ( !substr_compare( $the_fields, '{', 0, 1 ) ) {
            $the_fields = trim( $the_fields, '{}' );
        }
        # field parameter is of the form: field_specifier1;field_specifier2;field_specifier3 ...
        preg_match_all( '/(([^{};]+)(\.{[^{}]+})?)(;|$)/', $the_fields, $fields );
        $the_fields = $fields[1];
        foreach ( $the_fields as $the_name ) {
            # do one field specifier of a field parameter of the form: field_specifier1;field_specifier2;field_specifier3 ...
            # first separate field specifier into path components
            $names = explode( '.', $the_name, 2 );
            # do first path component
            # because the wordpress editor seems to insert noise spaces trim the component 
            $field = trim( $names[0] );
            if ( !preg_match( '/((\*_\*)|([\w-]+(\*)?))(<(\*|[\w\s]+)((,|><)(\*|\d+))?>)?(g|f)?(:((\*?-?[a-zA-Z0-9_]+),?)+)?/', $field, $matches )
                || $matches[0] != $field ) {
                return '<div style="border:2px solid red;color:red;padding:5px;">'
                    . "\"$field\" is an invalid field expression for short code: show_custom_field.</div>";
            }
            if ( array_key_exists( 1, $matches ) ) {
                $the_field = $matches[1];
            } else {
                return '#ERROR#';
            }
            # if no index is specified use the default 1
            if ( array_key_exists( 6, $matches ) ) {
                $the_group_index = $matches[6];
            } else {
                $the_group_index = 1;
            }
            if ( array_key_exists( 9, $matches ) ) {
                $the_field_index = $matches[9];
            } else {
                $the_field_index = 1;
            }
            $fields_by_group = ( array_key_exists( 10, $matches ) && $matches[10] == 'f' ) ? FALSE : TRUE;
            # extract the field filter, field exclusion filter, group filter and group exclusion filter
            $the_group_excludes = [ ];
            $the_group_classes = [ ];
            $the_excludes = [ ];
            $the_classes = [ ];
            if ( array_key_exists( 11, $matches ) ) {
                $raw_classes = explode( ',', substr( $matches[11], 1 ) );
                foreach ( $raw_classes as $raw_class ) {
                    if (        substr_compare( $raw_class, '*-', 0, 2 ) === 0 ) {
                        $the_group_excludes[ ] = substr( $raw_class, 2 );
                    } else if ( substr_compare( $raw_class, '*',  0, 1 ) === 0 ) {
                        $the_group_classes[ ]  = substr( $raw_class, 1 );
                    } else if ( substr_compare( $raw_class, '-',  0, 1 ) === 0 ) {
                        $the_excludes[ ]       = substr( $raw_class, 1 );
                    } else {
                        $the_classes[ ]        = $raw_class;
                    }
                }
            } else {
                $the_classes = NULL;
            }
            # extract the group name
            $all_group_names = $wpdb->get_col( $wpdb->prepare(
                'SELECT name FROM ' . MF_TABLE_CUSTOM_GROUPS . ' WHERE post_type = %s', get_post_type( $post_id ) ) );
            if ( substr( $the_field, 0, 2 ) === '*_' ) {
                # group name is the wild card so use all group names
                $group_names = $all_group_names;
            } else if ( substr_compare( $the_field, '__default_', 0, 10 ) === 0 ) {
                # default group explicitly specified
                $group_names = [ '__default' ];
            } else {
                # the group name should be delimited by an underscore
                # N.B. group names should not have embedded underscores - this is a bad design deficiency in Magic Fields 2               
                $group_names = explode( '_', $the_field, 2 );
                if ( count( $group_names ) > 1 ) {
                    # remove the field name from the array
                    array_pop( $group_names );
                    if ( !in_array( $group_names[0], $all_group_names ) ) {
                        # the group name is not valid so assume the underscore was part of the field name
                        $group_names = [ '__default' ];
                    }
                } else {
                    # no group specified so use the default group
                    $group_names = [ '__default' ];
                }
            }
            $the_field0 = $the_field;
            foreach ( $group_names as $group_name ) {
                # $mf2tk_key_name is the toolkit's group key field
                $mf2tk_key_name = ( $group_name != '__default' ? $group_name . '_' : '' ) . 'mf2tk_key';
                if ( $the_field0 === '*_*' ) {
                    $the_field = $group_name . '_*';
                }
                $not_magic_field = FALSE;
                if ( substr_compare( $the_field, '_*', -2, 2 ) === 0 ) {
                    $the_group = substr( $the_field, 0, strlen( $the_field ) - 2 );
                    # get the label for all the fields in the group and also get the toolkit's group key field as we will use that later
                    $the_field_data = $wpdb->get_results( $wpdb->prepare( 'SELECT cf.name, cf.label, cf.description, cf.type FROM '
                        . MF_TABLE_CUSTOM_FIELDS . ' cf INNER JOIN ' . MF_TABLE_CUSTOM_GROUPS
                        . ' cg WHERE cg.name = %s AND cg.post_type = %s AND cf.custom_group_id = cg.id ORDER BY cf.display_order',
                        $the_group, get_post_type( $post_id ) ), OBJECT_K );
                    if( !$the_field_data ) {
                        continue;
                    }
                    # remove the psuedo fields
                    $the_field_data = array_filter( $the_field_data, function( $data ) {
                        return $data->type !== 'alt_table' && $data->type !== 'alt_template';
                    } );
                    # $fields maps field names to field labels
                    $fields = array_map( function( $row ) { return $row->label; }, $the_field_data );
                    # remove the toolkit's group key from $fields
                    if ( array_key_exists( $mf2tk_key_name, $fields ) ) {
                        unset( $fields[$mf2tk_key_name] );
                    }
                } else {
                    # get the label for a single field and also get the toolkit's group key field as we will use that later
                    $the_field_data = $wpdb->get_results( $wpdb->prepare( 'SELECT name, label, description, type FROM ' . MF_TABLE_CUSTOM_FIELDS
                        . ' WHERE name IN ( %s, %s ) AND post_type = %s', $the_field, $mf2tk_key_name, get_post_type( $post_id ) ), OBJECT_K );
                    if ( $the_field_data && isset( $the_field_data[ $the_field ] ) ) {
                        # $fields maps field names to field labels
                        $fields = [ $the_field => $the_field_data[ $the_field ]->label ];
                    } else {
                        # not a magic field so just use the field name as the label
                        $fields = [ $the_field => $the_field ];
                        $not_magic_field = TRUE;
                    }
                }
                # handle the toolkit's group key field
                if ( array_key_exists( $mf2tk_key_name, $the_field_data ) && ($mf2tk_key_data = (array) $the_field_data[ $mf2tk_key_name ] ) ) {
                    # extract the classes for the group from the description of the toolkit's group key field
                    if ( preg_match( '/\[\*([a-zA-Z0-9_]+,?)+\*\]/', $mf2tk_key_data['description'], $mf2tk_key_classes ) === 1 ) {
                        $mf2tk_key_classes = explode( ',', trim( $mf2tk_key_classes[0], '[]*' ) );
                    }
                }
                if ( !isset( $mf2tk_key_classes ) ) {
                    $mf2tk_key_classes = [ ];
                }
                # apply the group class filters
                if ( $the_group_classes && ( !$mf2tk_key_classes || !array_intersect( $the_group_classes, $mf2tk_key_classes ) ) ) {
                    continue;
                }
                # apply the group exclusion filters
                if ( $the_group_excludes && ( $mf2tk_key_classes && array_intersect( $the_group_excludes, $mf2tk_key_classes ) ) ) {
                    continue;
                }
                if ( $the_group_index === '*' ) {
                    # get all possible group indexes for $fields
                    $group_indices = $wpdb->get_col( $wpdb->prepare( 'SELECT DISTINCT group_count FROM ' . MF_TABLE_POST_META
                        . " WHERE post_id = %d AND field_name in ( '" . implode( '\', \'', array_keys( $fields ) ) . '\' ) ORDER BY group_count ASC',
                        (integer) $post_id ) );
                } else if ( !is_numeric( $the_group_index ) ) {
                    # this is a symbolic group index so resolve to a numeric index using get_group_index_for_key()
                    if ( function_exists( 'mf2tk\get_group_index_for_key' ) ) {
                        $group_indices = [ mf2tk\get_group_index_for_key( $the_group, $the_field, $the_group_index ) ];
                    } else {
                        $group_indices = [ -1 ];
                    }
                } else {
                    $group_indices = [ $the_group_index ];
                }
                $fields1 = $fields;
                # outer field loop
                foreach ( $fields1 as $field1 => $label1 ) {
                    $skip_field1 = FALSE;
                    $groups_value = '';
                    foreach ( $group_indices as $group_index ) {
                        # $mf2tk_key_value is the symbolic index for this group also used as the group label
                        $mf2tk_key_value = mf2tk\get( $mf2tk_key_name, $group_index, 1, $post_id );
                        $fields_value = '';
                        # inner field loop
                        foreach ( $fields as $field2 => $label2 ) {
                            # use outer or inner field loop depending on order mode
                            if ( $fields_by_group ) {
                                $field = $field2;
                                $label = $label2;
                            } else {
                                $field = $field1;
                                $label = $label1;
                            }
                            $field_value = '';
                            $recursion = FALSE;
                            $skip_field2 = FALSE;
                            $classes = null;
                            if ( $not_magic_field ) {
                                # these are psuedo fields (__parent, __post_title, __post_arthor), taxonomy fields and ordinary custom fields
                                if ( !$the_classes ) {
                                    if ( $field == '__parent' ) {
                                        # handle the psuedo field __parent
                                        if ( array_key_exists( 1, $names ) ) {
                                            $parent_ids1 = $parent_ids;
                                            $parent_id = array_pop( $parent_ids1 );
                                            $label = $wpdb->get_var( $wpdb->prepare(
                                                'SELECT name FROM ' . MF_TABLE_POSTTYPES . ' WHERE type = %s', get_post_type( $parent_id ) ) );
                                            $field_value .= self::show_custom_field( $parent_id, $names[1], $before, $after, $separator, $filter,
                                                $field_before, $field_after, $field_separator, $field_rename, $group_before, $group_after, $group_separator,
                                                $group_rename, $multi_before, $multi_after, $multi_separator, NULL, $path . self::$recursion_separator . $label,
                                                $parent_ids1, $atts ) . $field_separator;
                                            $recursion = TRUE;
                                        } else {
                                            $field_value .= self::wrap_value( end( $parent_ids ), $field, 'related_type', $filter, $before, $after,
                                                $separator );
                                            reset( $parent_ids );
                                        }
                                    } else if ( $field == '__post_title' ) {
                                        # handle the psuedo field __post_title as a related_type if url_to_link is available
                                        $url_to_link_available = (boolean) array_intersect( [ 'url_to_link', 'url_to_link2' ], explode( ';', $filter ) );
                                        $field_value .= self::wrap_value( ( $url_to_link_available ? $post_id : get_the_title( $post_id ) ), $field,
                                            ( $url_to_link_available ? 'related_type' : 'textbox' ), $filter, $before, $after, $separator );
                                        $label = "Post";
                                    } else if ( $field == '__post_author' ) {
                                        # handle the psuedo field __post_author which may be linkable
                                        $author = $wpdb->get_results( $wpdb->prepare(
                                            "SELECT u.ID, u.display_name FROM $wpdb->users u, $wpdb->posts p WHERE p.ID = %d AND u.ID = p.post_author",
                                            (integer) $post_id ), OBJECT );
                                        # TODO author id and display name
                                        $url_to_link_available = (boolean) array_intersect( [ 'url_to_link', 'url_to_link2' ], explode( ';', $filter ) );
                                        $field_value .= self::wrap_value( ( $url_to_link_available ? $author[0]->ID : $author[0]->display_name ), $field,
                                            ( $url_to_link_available ? 'author' : 'textbox' ), $filter, $before, $after, $separator );
                                        $label = "Author";
                                    } else if ( ( $terms = get_the_terms( $post_id, $field ) ) && is_array( $terms ) ) {
                                        # $field is a taxonomy field
                                        foreach ( wp_list_pluck( $terms, 'name' ) as $term ) {
                                            $field_value .= self::wrap_value( $term, $field, 'taxonomy', $filter, $before, $after, $separator );
                                        }
                                        $column = $wpdb->get_col( $wpdb->prepare( 'SELECT name FROM ' . MF_TABLE_CUSTOM_TAXONOMY . ' WHERE type = %s',
                                            $field ) );
                                        if ( array_key_exists( 0, $column ) ) {
                                            $label = $column[0];
                                        }
                                    } else {
                                        # finally try as an ordinary custom field
                                        $values = get_post_custom_values( $field, $post_id );
                                        if ( is_array( $values ) ) {
                                            foreach ( $values as $value ) {
                                                # if not a scalar then serialize it as we cannot handle non scalars
                                                if ( is_object( $value ) || is_array( $value ) ) {
                                                    $value = serialize( $value );
                                                }
                                                $field_value .= self::wrap_value( $value, $field, NULL, $filter, $before, $after, $separator );
                                            }
                                        }
                                    }
                                }
                            } else {
                                # handle magic fields
                                if ( $the_field_index === '*' ) {
                                    # wild card so get all possible field indexes
                                    $field_indices = get_order_field( $field, $group_index, $post_id );
                                    if ( !$field_indices ) {
                                        $field_indices = [ 1 ];
                                    }
                                } else {
                                    $field_indices = [ $the_field_index ];
                                }
                                foreach ( $field_indices as $field_index ) {
                                    $data = (array) $the_field_data[ $field ];
                                    if ( in_array( 'tk_use_raw_value', explode( ';', $filter ) ) ) {
                                        # if the psuedo filter "tk_use_raw_value" is specified return the raw value from database
                                        $value = mf2tk\get_data2( $field, $group_index, $field_index, $post_id )['meta_value'];
                                    } else if ( $data[ 'type' ] === 'alt_numeric' ) {
                                        # alt_numeric is a toolkit field and always should process its raw value as returned by alt_numeric_field::get_numeric()
                                        $value = alt_numeric_field::get_numeric( $field, $group_index, $field_index, $post_id, $atts );
                                    } else {
                                        $value = mf2tk\get( $field, $group_index, $field_index, $post_id );
                                    }
                                    preg_match( '/\[\*([a-zA-Z0-9_]+,?)+\*\]/', $data[ 'description' ], $classes );
                                    if ( $classes ) {
                                        $classes = explode( ',', trim( $classes[0], '[]*' ) );
                                    }
                                    # apply the class filter
                                    if ( $the_classes && ( !$classes || !array_intersect( $the_classes, $classes ) ) ) {
                                        $skip_field1 = $skip_field2 = TRUE;
                                        continue;
                                    }
                                    # apply the class exclusion filter
                                    if ( $the_excludes && ( $classes && array_intersect( $the_excludes, $classes ) ) ) {
                                        $skip_field1 = $skip_field2 = TRUE;
                                        continue;
                                    }
                                    if ( $data[ 'type' ] === 'related_type' || $data[ 'type' ] === 'alt_related_type' ) {
                                        if ( $value ) {
                                            # $value is a single post id or an array of post ids
                                            if ( is_array( $value ) ) {
                                                $values = $value;
                                            } else {
                                                $values = [ 0 => $value ];
                                            }
                                            foreach ( $values as $value ) {
                                                if ( $value ) {
                                                    # $value is a single post id
                                                    if ( array_key_exists( 1, $names ) ) {
                                                        # $names[1] is a field specifier
                                                        $parent_ids1 = $parent_ids;
                                                        array_push( $parent_ids1, $post_id );
                                                        # recursively call show_custom_field() with post id set to $value
                                                        $field_value .= self::show_custom_field( $value, $names[1], $before, $after, $separator, $filter,
                                                            $field_before, $field_after, $field_separator, $field_rename, $group_before, $group_after,
                                                            $group_separator, $group_rename, $multi_before, $multi_after, $multi_separator, NULL,
                                                            $path . self::$recursion_separator . $label, $parent_ids1, $atts ) . $field_separator;
                                                        $recursion = TRUE;
                                                    } else {
                                                        # no additional field specifier so $value is the terminal value
                                                        $field_value .= self::wrap_value( $value, $field, $data['type'], $filter, $before, $after, $separator,
                                                            $classes );
                                                    }
                                                }
                                            }
                                        }
                                    } else {
                                        # value is not a post id
                                        if ( is_array( $value ) ) {
                                            # value is multi valued
                                            if ( $value ) {
                                                # wrap each individual value as a value of a multi-valued field and concatenate into $multi_value
                                                # this will apply the filter $filter and append the separator $multi_separator
                                                $multi_value = '';
                                                foreach ( $value as $the_value ) {
                                                    $multi_value .= self::wrap_value( $the_value, $field, $data['type'], $filter, $multi_before, $multi_after,
                                                        $multi_separator, $classes );
                                                }
                                                # remove the trailing separator
                                                 $multi_value= self::remove_trailing_separator( $multi_value, $multi_separator );
                                                # finally wrap as a value of a field, i.e. prepend the $before prefix and append the $after suffix
                                                $field_value .= self::wrap_value( $multi_value, NULL, NULL, NULL, $before, $after, $separator, $classes );
                                            }
                                        } else {
                                            # single value
                                            $field_value .= self::wrap_value( $value, $field, $data['type'], $filter, $before, $after, $separator, $classes,
                                                $group_index, $field_index, $post_id, $atts );
                                        }
                                    }
                                } # foreach ( $field_indices as $field_index ) { # results in $field_value
                            }
                            # if using outer field loop do only one iteration on inner loop
                            if ( !$fields_by_group ) {
                                break;
                            }
                            if ( $skip_field2 ) {
                                continue;
                            }
                            if ( !$recursion ) {
                                # remove the trailing separator
                                $field_value = self::remove_trailing_separator( $field_value, $separator );
                                # wrap the field
                                $fields_value .= self::wrap_field_value( $field_value, $field_before, $field_after, $field_separator, $label, $field,
                                    is_array( $classes ) ? implode( ' ', $classes ) : '', $field_rename, $path );
                            } else {
                                # already wrapped by a recursive call so just use the returned value
                                $fields_value .= $field_value;
                            }
                        } # foreach ( $fields as $field2 => $label2 ) { # results in $fields_value
                        if ( $fields_by_group ) {
                            # remove the trailing separator
                            $fields_value = self::remove_trailing_separator( $fields_value, $field_separator );
                            # wrap the group
                            $content .= self::wrap_group_value( $fields_value, $group_before, $group_after, $group_separator, $mf2tk_key_value,
                                "$group_name-$group_index", $group_index, is_array( $mf2tk_key_classes ) ? implode( ' ', $mf2tk_key_classes ) : '',
                                $group_rename, $path );
                        } else {
                            if ( $skip_field1 ) {
                                break;
                            }
                            if ( !$recursion ) {
                                # remove the trailing separator
                                $field_value = self::remove_trailing_separator( $field_value, $separator );
                                # wrap the group
                                $groups_value .= self::wrap_group_value( $field_value, $group_before, $group_after, $group_separator, $mf2tk_key_value,
                                    "$group_name-$group_index", $group_index, is_array( $mf2tk_key_classes ) ? implode( ' ', $mf2tk_key_classes ) : '',
                                    $group_rename, $path );
                            } else {
                                # already wrapped by a recursive call so just use the returned value
                                $groups_value .= $field_value;
                            }
                        }
                    } # foreach ( $group_indices as $group_index ) { # results in $content or $groups_value
                    if ( $fields_by_group ) {
                        # remove trailing group separator
                        $content = self::remove_trailing_separator( $content, $group_separator );
                        # if using inner field loop do outer field loop only once
                        break;
                    }
                    if ( $skip_field1 ) {
                        continue;
                    }
                    # remove trailing group separator
                    $groups_value = self::remove_trailing_separator( $groups_value, $group_separator );
                    if ( !$recursion ) {
                        # wrap the field
                        $content .= self::wrap_field_value( $groups_value, $field_before, $field_after, $field_separator, $label, $field,
                            is_array( $classes ) ? implode( ' ', $classes ) : '', $field_rename, $path );
                    } else {
                        # already wrapped by a recursive call so just use the returned value
                        $content .= $groups_value;
                    }
                } # foreach ( $fields1 as $field1 => $label1 ) { # results in $content
            } # foreach ( $group_names as $group_name ) {
            $content .= $field_separator;
        } # foreach ( $the_fields as $the_name ) {
        # remove trailing field separator
        $content = self::remove_trailing_separator( $content, $field_separator );
        if ( $finals !== NULL ) {
            foreach( explode( ';', $finals ) as $final ) {
                $content = call_user_func( $final, $content, $the_names );
            }
        }
        return $content;
    }   # public static function show_custom_field( $post_id, $the_names, $before, $after, $separator, $filter, $field_before,

    # show_custom_field_wrapper() handles iteration over multiple posts
    
    public static function show_custom_field_wrapper( $atts ) {
        global $post;
        extract( shortcode_atts( [
            'field' => 'something',
            'before' => '',
            'after' => '',
            'separator' => '',
            'filter' => NULL,
            'multi_before' => NULL,
            'multi_after' => NULL,
            'multi_separator' => NULL,
            'field_before' => '',
            'field_after' => '',
            'field_separator' => '',
            'field_rename' => '',
            'group_before' => '',
            'group_after' => '',
            'group_separator' => '',
            'group_rename' => '',
            'final' => NULL,
            'post_id' => NULL,
            'post_before' => '',
            'post_after' => ''
        ], $atts ) );
        if ( $post_id === NULL ) {
            $post_id = $post->ID;
        }
        if ( $multi_before === NULL ) {
            $multi_before = $before;
        }
        if ( $multi_after === NULL ) {
            $multi_after = $after;
        }
        if ( $multi_separator === NULL ) {
            $multi_separator = $separator;
        }
        if ( is_numeric( $post_id) ) {
            # single numeric post id
            $rtn = '';
            if ( $post_before ) {
                $rtn .= $post_before;
            }
            $rtn .= self::show_custom_field( $post_id, $field, $before, $after, $separator, $filter, $field_before, $field_after, $field_separator,
            $field_rename, $group_before, $group_after, $group_separator, $group_rename, $multi_before, $multi_after, $multi_separator, $final, '', [ ],
                $atts );
            if ( $post_after ) {
                $rtn .= $post_after;
            }
            return $rtn;
        } else {
            # handle multiple posts
            # first get list of post ids
            $post_ids = Magic_Fields_2_Toolkit_Dumb_Shortcodes::get_posts_with_spec( $post_id );
            $rtn = '';
            foreach ( $post_ids as $post_id ) {
                # do each post accumulating the output in $rtn
                if ( $post_before ) {
                    $rtn .= $post_before;
                }
                $rtn .= self::show_custom_field( $post_id, $field, $before, $after, $separator, $filter, $field_before, $field_after, $field_separator,
                    $field_rename, $group_before, $group_after, $group_separator, $group_rename, $multi_before, $multi_after, $multi_separator, $final, '',
                    [ ], $atts );
                if ( $post_after ) {
                    $rtn .= $post_after;
                }
            }
            return $rtn;
        }
    }   # public static function show_custom_field_wrapper( $atts ) {

    # The shortcode [mt_gallery] displays the selected images in a standard WordPress gallery.
    # Since, the standard WordPress gallery only works with images in its Media Library only images
    # in fields of type image_media_field and alt_image_field are selected. Images in fields of type
    # image_field are ignored since they are stored in a proprietary non-WordPress standard way.
    # The filter tk_filter_by_type__image_media__alt_image() is used to accomplish this.
    
    public static function mt_show_gallery( $atts ) {
        global $post;
        extract( shortcode_atts( [
            'field'   => 'something',
            'post_id' => NULL,
            'filter'  => NULL,
            'final'   => NULL,
            'mode'    => 'wordpress'
        ], $atts ) );
        if ( $post_id === NULL ) {
            $post_id = $post->ID;
        }
        if ( strtolower( $mode ) === 'wordpress' ) {
            if ( is_numeric( $post_id) ) {
                # single numeric post id
                $rtn = self::show_custom_field( $post_id, $field, '', '', ',', 'tk_use_raw_value;tk_filter_by_type__image_media__alt_image',
                    '', '', ',', '', '', '', '', '', NULL, NULL, NULL, NULL, '', [ ], $atts );
            } else {
                # handle multiple posts
                # first get list of post ids
                $post_ids = Magic_Fields_2_Toolkit_Dumb_Shortcodes::get_posts_with_spec( $post_id );
                $rtn = '';
                foreach ( $post_ids as $post_id ) {
                    # do each post accumulating the output in $rtn
                    $rtn .= ',' . self::show_custom_field( $post_id, $field, '', '', ',', 'tk_use_raw_value;tk_filter_by_type__image_media__alt_image',
                        '', '', ',', '', '', '', '', '', NULL, NULL, NULL, NULL, '', [ ], $atts );
                }
            }
            $upload_base_url = wp_upload_dir()[ 'baseurl' ];
            $upload_base_url_length = strlen( $upload_base_url );
            $rtn = implode( ',', array_filter( array_map( function( $v ) use ( $upload_base_url, $upload_base_url_length ) {
                global $wpdb;
                if ( is_numeric( $v ) ) {
                    return $v;
                }
                if ( strpos( $v, $upload_base_url ) !== 0 ) {
                    return FALSE;
                }
                $path = substr( $v, $upload_base_url_length + 1 );
                $post_id = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key = '_wp_attached_file' AND meta_value = %s",
                    $path ) );
                if ( $post_id ) {
                    return $post_id[0];
                }
                return FALSE;
            }, explode( ',', $rtn ) ) ) );
            $gallery = "[gallery ids=\"$rtn\""; 
            $atts = array_diff_key( $atts, [ 'field' => TRUE, 'post_id' => TRUE, 'filter' => TRUE, 'final' => TRUE ] );
            foreach ( $atts as $key => $value ) {
                $gallery .= " $key=\"$value\"";
            }
            $gallery .= ']';
            return do_shortcode( $gallery );
        } else if ( strtolower( $mode ) === 'toolkit' ) {
            # In 'toolkit' mode display the selected alt_image fields with uniform small size and float left alignment.
            # The mouse-over popup and clickable link options are also applied.
            $atts[ 'align' ] = 'alignleft';
            if ( !array_key_exists( 'width', $atts ) ) {
                $atts[ 'width' ] = '120';
            }
            if ( is_numeric( $post_id) ) {
                # single numeric post id
                return self::show_custom_field( $post_id, $field, '', '', '', 'tk_filter_by_type__alt_image;url_to_media', '', '', ',', '', '', '', '', '',
                    NULL, NULL, NULL, NULL, '', [ ], $atts ) . '<br style="clear:both;">';
            } else {
                # handle multiple posts
                # first get list of post ids
                $post_ids = Magic_Fields_2_Toolkit_Dumb_Shortcodes::get_posts_with_spec( $post_id );
                $rtn = '';
                foreach ( $post_ids as $post_id ) {
                    # do each post accumulating the output in $rtn
                    $rtn .= self::show_custom_field( $post_id, $field, '', '', '', 'tk_filter_by_type__alt_image;url_to_media', '', '', ',', '', '', '', '',
                        '', NULL, NULL, NULL, NULL, '', [ ], $atts );
                }
                return $rtn . '<br style="clear:both;">';
            }
        }
    }   # public static function mt_show_gallery( $atts ) {

    # The shortcode [mt_tabs] shows each enclosed [mt_template] shortcode in its own tab. This shortcode has the form:
    # [mt_tabs class="..."]
    # [mt_template macro="..." title="..."][/mt_template]
    # [mt_template macro="..." title="..."][/mt_template]
    # [/mt_tabs]
    # N.B. requires jQuery UI

    public static function mt_show_tabs( $atts, $macro ) {
        static $tabs_id = 0;
        ++$tabs_id;
        static $tab_id = 0;
        $first_tab_id = $tab_id;
        $titles = [ ];
        $options = self::$options;
        $macro = preg_replace_callback( "#\\[({$options['show_macro']}|{$options['show_macro_alias']}).*?title=(\"|')(.*?)\\2.*?\\[/\\1]#",
            function( $matches ) use ( &$tab_id, &$titles ) {
                $titles[] = $matches[3];
                return '<div id="mf2tk-tab-' . $tab_id++ . '">' . do_shortcode( $matches[0] ) . '</div>';
        }, $macro );
        $head = '<ul>';
        for ( $i = $first_tab_id; $i < $tab_id; $i++ ) {
            $head .= "<li><a href=\"#mf2tk-tab-{$i}\">" . $titles[ $i - $first_tab_id ] . '</a></li>';
        }
        $head .= '</ul>';
        return "<div id=\"mf2tk-tabs-{$tabs_id}\" class=\"mf2tk-mt_tabs-jquery_pre_tabs\">" . $head . $macro . '</div>';
    }   # public static function mt_show_tabs( $atts, $macro ) {

}   # class Magic_Fields_2_Toolkit_Dumb_Shortcodes {

Magic_Fields_2_Toolkit_Dumb_Shortcodes::initialize();

}   # namespace {

namespace mf2tk {

# filters show be defined in the namespace mf2tk. global filters are deprecated but are still resolved for now

# url_to_link() is a filter that wraps a linkable value with an <a> html element. This function has been superseded by
# url_to_link2() and exists only for compatibility with older code.

function url_to_link( $value, $field, $type ) {
    global $wpdb;
    if ( ( $type === 'related_type' || $type === 'alt_related_type' ) && is_numeric( $value ) ) {
        $value = '<a href="' . get_permalink( $value ) . '">' . get_the_title ( $value ) . '</a>';
    } else if ( ( $type === 'image' || $type === 'image_media' ) && is_string( $value ) && strpos( $value, 'http' ) === 0 ) {
        $value = '<a href="' . $value . '">' . $value . '</a>';
    } else if ( $type === 'author' ) {
        $author = $wpdb->get_results( $wpdb->prepare(
            "SELECT u.display_name, u.user_url FROM $wpdb->users u WHERE u.ID = %s", $value ), OBJECT );
        if ( $author[0]->user_url ) {
            $value = '<a href="' . $author[0]->user_url . '">' . $author[0]->display_name . '</a>';
        } else {
            $value = $author[0]->display_name;
        }
    }
    return $value;
}

# url_to_link2() is a filter that wraps a linkable value with an <a> html element.

function url_to_link2( $value, $field, $type, $classes, $group_index, $field_index, $post_id, $atts ) {
    global $wpdb;
    if ( ( $type === 'related_type' || $type === 'alt_related_type' ) && is_numeric( $value ) ) {
        $value = '<a href="' . get_permalink( $value ) . '">' . get_the_title ( $value ) . '</a>';
    }  else if ( ( $type === 'image' || $type === 'file' || $type === 'audio' )
        && is_string( $value ) && strpos( $value, 'http' ) === 0 ) {
        $value = '<a href="' . $value . '">' . substr( $value, strrpos( $value, '/' ) + 11 ) . '</a>';
    } else if ( $type === 'image_media' && is_string( $value ) && strpos( $value, 'http' ) === 0 ) {
        $value = '<a href="' . $value . '">' . substr( $value, strrpos( $value, '/' ) + 1 ) . '</a>';
    } else if ( $type === 'author' ) {
        $author = $wpdb->get_results( $wpdb->prepare(
            "SELECT u.display_name, u.user_url FROM $wpdb->users u WHERE u.ID = %s", $value ), OBJECT );
        if ( $author[0]->user_url ) {
            $value = '<a href="' . $author[0]->user_url . '">' . $author[0]->display_name . '</a>';
        } else {
            $value = $author[0]->display_name;
        }
    } else if ( ( $type === 'alt_embed' || $type === 'alt_video' || $type === 'alt_audio' || $type === 'alt_image' )
        && is_string( $value ) && strpos( $value, 'http' ) === 0 ) {
        $value = '<a href="' . $value . '">' . substr( $value, strrpos( $value, '/' ) + 1 ) . '</a>';
    } else if ( $type === 'alt_url' ) {
        $value = \alt_url_field::get_url( $field, $group_index, $field_index, $post_id, $atts );
    }
    return $value;
}

function tk_value_as_checkbox( $value, $field, $type ) {
    if ( $type === 'checkbox' ) {
        $checked = $value ? 'checked' : '';
        $value = "<input type=\"checkbox\"$checked readonly>";
    }
    return $value;
}

function tk_value_as_color( $value, $field, $type ) {
    if ( $type === 'color_picker' ) {
        $value = "<div style='display:inline-block;width:0.66em;height:0.66em;padding:0;border:1px solid black;background-color:$value;'></div>";
    }
    return $value;
}

# The function tk_value_as_audio() returns the HTML to play the audio media. This function does not use the
# WordPress "[audio]" shortcode instead it directly uses the HTML5 audio element.

function tk_value_as_audio( $value, $field, $type, $classes, $group_index, $field_index, $post_id ) {
    if ( $value && ( $type === 'audio' || $type === 'alt_audio' ) ) {
        $mime_type = array(
          'mp3' => 'audio/mpeg',
          'wav' => 'audio/wav',
          'ogg' => 'audio/ogg',
        );
        if ( $type === 'audio' ) {
            $type = strtolower( pathinfo( $value, PATHINFO_EXTENSION ) );
            $srcs = [ $type => $value ];
        } else {
            $srcs = get_media_srcs( $field, $group_index, $field_index, $post_id, 'alt_audio_field' );
        }
        if ( count( $srcs ) ) {
            $value = '<audio controls>';
            foreach ( $srcs as $type => $url ) {
                if ( array_key_exists( $type, $mime_type ) ) {
                    $value .= "<source src=\"$url\" type=\"" . $mime_type[$type] . '">';
                }
            }
            $value .= 'Your browser does not support the audio element.';
            $value .= '</audio>';
        } else {
            $value = 'Invalid audio sources';
        }
    }
    return $value;
}

# The function tk_value_as_image__() is invoked on filters with names beginning with "tk_value_as_image__"
# e.g., "tk_value_as_image__w320", "tk_value_as_image__h240". The suffix is a "w" or "h" followed by a size.
# The suffix of the filter is passed in the $parm argument. The return value is the HTML for an image with
# the specified width or height.

function tk_value_as_image__( $parm, $value, $field, $type ) {
    if ( $type === 'image' || $type === 'image_media' || $type === 'alt_image' ) {
        $height = '';
        $width = '';
        if ( substr( $parm, 0, 1 ) === 'h' ) {
            $height = ' height="' . substr( $parm, 1 ) . '"';
        } else if ( substr( $parm, 0, 1 ) === 'w' ) {
            $width  = ' width="'  . substr( $parm, 1 ) . '"';
        }
        $value = "<a href=\"$value\"><img src=\"$value\"{$width}{$height}></a>";
     }
    return $value;
}

# The function tk_value_as_video__() is invoked on filters with names beginning with "tk_value_as_video__"
# e.g., "tk_value_as_video__w320", "tk_value_as_video__h240". The suffix is a "w" or "h" followed by a size.
# The suffix of the filter is passed in the $parm argument. The return value is the HTML for a video element
# with the specified width or height. This function does not use the WordPress "[video]" shortcode instead it
# directly uses the HTML5 video element and hence will not work with Flash (flv) videos.

function tk_value_as_video__( $parm, $value, $field, $type, $classes, $group_index, $field_index, $post_id ) {
    static $i = 0;
    if ( $type === 'alt_video' ) {
        $int_height = 0;
        $height = '';
        $int_width = 0;
        $width = '';
        if ( substr( $parm, 0, 1 ) === 'h' ) {
            $int_height = substr( $parm, 1 );
            $height = ' height="' . $int_height . '"';
        } else if ( substr( $parm, 0, 1 ) === 'w' ) {
            $int_width = substr( $parm, 1 );
            $width  = ' width="'  . $int_width . '"';
        }
        ++$i;
        $id = "tk_video-$i";
        $srcs = get_media_srcs( $field, $group_index, $field_index, $post_id, 'alt_video_field' );
        if ( count( $srcs ) === 1 ) {
            $url = reset( $srcs );
            $type = key( $srcs );
            if ( $type === 'flv' ) { if ( $int_width ) { $height = ' height="' . intval( 3 * $int_width / 4 ) . '"'; }
            else { $width = ' width="' . intval( 4 * $int_height / 3 ) . '"'; } }
            $value = "<video id=\"$id\" src=\"$url\"{$width}{$height} controls=\"controls\"></video>";
        } else if ( count( $srcs ) > 1 ) {
            $value = "<video id=\"$id\"{$width}{$height} controls=\"controls\">";
            foreach ( $srcs as $type => $url ) {
                if ( $type === 'flv' ) { $type = 'x-flv'; }
                $value .= "<source src=\"$url\" type=\"video/$type\">";
            }
            $value .= '</video>';
        } else {
            $value = 'Invalid video sources';
        }
    }
    return $value;
}

# url_to_media() is the filter for alt media fields. It returns the HTML for displaying the media according
# to parameters specified for the field, e.g., width, height, caption, autoplay, ... url_to_media() uses the
# standard WordPress "[video]", "[audio]" and "[embed]" shortcodes to display the media. alt_image is defined
# by the toolkit to behave like the other media elements.

function url_to_media( $value, $field, $type, $classes, $group_index, $field_index, $post_id, $atts ) {
    if ( !$post_id || !$group_index || !$field_index ) { return $value; }
    if ( $type === 'alt_embed' ) {
        $value = \alt_embed_field::get_embed( $field, $group_index, $field_index, $post_id, $atts );
    } else if ( $type === 'alt_video' ) {
        $value = \alt_video_field::get_video( $field, $group_index, $field_index, $post_id, $atts );
    } else if ( $type === 'alt_audio' ) {
        $value = \alt_audio_field::get_audio( $field, $group_index, $field_index, $post_id, $atts );
    } else if ( $type === 'alt_image' ) {
        $value = \alt_image_field::get_image( $field, $group_index, $field_index, $post_id, $atts );
    }
    return $value;
}

function media_url_to_link( $value, $field, $type ) {
    if ( $type === 'alt_embed' || $type === 'alt_video' || $type === 'alt_audio' || $type === 'alt_image' ) {
        return '<a href="' . $value . '">' . $value . '</a>';
    }
    return $value;
}

# The function tk_filter_by_type__() is invoked on filters with names beginning with "tk_filter_by_type__"
# e.g., "tk_filter_by_type__image_media__alt_image". The suffix is a "__" separated list of field types.
# The suffix of the filter is passed in the $parm argument. The return value is the input value if the 
# field type is in the list of field types and '' otherwise.

function tk_filter_by_type__( $parm, $value, $field, $type, $classes, $group_index, $field_index, $post_id ) {
    $types = explode( '__', $parm );
    if ( in_array( $type, $types) ) {
        return $value;
    }
    return '';
}

function tk_field_name( $value, $field ) {
    return $field;
}

function tk_field_type( $value, $field, $type ) {
    return $type;
}

}   # namespace mf2tk {


?>