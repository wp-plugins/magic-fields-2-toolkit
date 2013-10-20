<?php

/*
 * Module Name:   Search Magic Fields 2 Widget
 * Module URI:    http://magicfields17.wordpress.com/magic-fields-2-search-0-4-1/
 * Description:   Widget for searching Magic Fields 2 custom fields and custom taxonomies and also post_content.
 * Documentation: http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-4/#search
 * Version:       0.4.1
 * Author:        Magenta Cuda
 * Author URI:    http://magentacuda.wordpress.com
 * License:       GPL2
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

# testing multiple select dropdown vs multiple select checkboxes - this version uses multiple select checkboxes
 
class Search_Using_Magic_Fields_Widget extends WP_Widget {

    public static $get_form_for_post_type = 'get_form_for_post_type';
    # maximum number of items to show per custom field
    public static $sql_limit = '16';
    public static $optional_text_value_suffix = '-mf2tk-optional-text-value';
 
	public function __construct() {
		parent::__construct(
            'search_magic_fields',
            __( 'Search using Magic Fields' ),
            array(
                'classname' => 'search_magic_fields_widget',
                'description' => __( "Search for Custom Posts using Magic Fields" )
            )
        );
	}

	public function widget( $args, $instance ) {
        global $wpdb;
        extract( $args );
        #error_log( '##### Search_Using_Magic_Fields_Widget::widget():$instance=' . print_r( $instance, TRUE ) );
        # initially show only post type selection form
        # after post type selected use ajax to retrieve post specific form
?>
<form id="search-using-magic-fields-<?php echo $this->number; ?>" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
<input id="magic_fields_search_form" name="magic_fields_search_form" type="hidden" value="magic-fields-search">
<input id="magic_fields_search_widget_option" name="magic_fields_search_widget_option" type="hidden"
    value="<?php echo $this->option_name; ?>">
<input id="magic_fields_search_widget_number" name="magic_fields_search_widget_number" type="hidden"
    value="<?php echo $this->number; ?>">
<h2>Search:</h2>
<div class="magic-field-parameter" style="padding:5px 10px;border:2px solid black;margin:5px;">
<h3>post type:</h3>
<select id="post_type" name="post_type" required style="width:100%;">
<option value="no-selection">--select post type--</option>
<?php
        $results = $wpdb->get_results( 'SELECT post_type, count(*) FROM ' . $wpdb->posts . ' WHERE post_status = "publish"'
            . ' GROUP BY post_type ORDER BY count(*) DESC LIMIT ' . Search_Using_Magic_Fields_Widget::$sql_limit, ARRAY_A );
        foreach ( $results as $result ) {
            $name = $result['post_type'];
            if ( $name === 'attachment' || $name === 'revision' || $name === 'nav_menu_item' || $name === 'content_macro' ) {
                continue;
            }
            if ( !in_array( $name, array_keys( $instance ) ) ) { continue; }
?>      
<option value="<?php echo $name; ?>"><?php echo $name . ' (' . $result['count(*)'] . ')'; ?></option>
<?php
        }
?>
</select>
</div>
<div id="magic-fields-parameters"></div>
<input type="submit" value="Search">
</form>
<script>
jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> select#post_type").change(function(){
    //console.log("select#post_type change");
    jQuery.post(
        '<?php echo admin_url( 'admin-ajax.php' ); ?>',
        {
            action : '<?php echo Search_Using_Magic_Fields_Widget::$get_form_for_post_type; ?>',
            post_type: jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> select#post_type option:selected")
                .val(),
            magic_fields_search_widget_option:
                jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> input#magic_fields_search_widget_option").val(),
            magic_fields_search_widget_number:
                jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> input#magic_fields_search_widget_number").val()
        },
        function(response){
            //console.log(response);
            jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> div#magic-fields-parameters").html(response);
        }
    );
});
</script>
<?php
	}
    
    public function update( $new, $old ) {
        #error_log( '##### Search_Using_Magic_Fields_Widget::update():backtrace='
        #    . print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), TRUE ) );
        #error_log( '##### Search_Using_Magic_Fields_Widget::update():$_POST=' . print_r( $_POST, TRUE ) );    
        #error_log( '##### Search_Using_Magic_Fields_Widget::update():$old=' . print_r( $old, TRUE ) );
        #error_log( '##### Search_Using_Magic_Fields_Widget::update():$new=' . print_r( $new, TRUE ) );
        return array_map( function( $values ) { return array_map( strip_tags, $values ); }, $new );
    }
    
    public function form( $instance ) {
        global $wpdb;
        #error_log( '##### Search_Using_Magic_Fields_Widget::form():$instance=' . print_r( $instance, TRUE ) );
        # show the configuration form to select custom fields for the given post type
?>
<h4>Select Search Fields for:</h4>
<?php
        $results = $wpdb->get_results( 'SELECT post_type, count(*) FROM ' . $wpdb->posts . ' WHERE post_status = "publish"'
            . ' GROUP BY post_type ORDER BY count(*) DESC LIMIT ' . Search_Using_Magic_Fields_Widget::$sql_limit, ARRAY_A );
        foreach ( $results as $result ) {
            $name = $result['post_type'];
            if ( $name === 'attachment' || $name === 'revision' || $name === 'nav_menu_item' || $name === 'content_macro' ) {
                continue;
            }
            $selected = $instance[$name];
?>
<!--
<h4>Select Search Fields for <?php echo $name . ' (' . $result['count(*)'] . ')'; ?></h4>
<select id="<?php echo $this->get_field_id( $name ); ?>" name="<?php echo $this->get_field_name( $name ); ?>[]"
    multiple size="4" style="width:100%;">
-->
<div class="scpbcfw-search-fields" style="padding:5px 10px;border:2px solid black;margin:5px;">
<span style="font-size=16px;font-weight:bold;float:left;"><?php echo $name . ' (' . $result['count(*)'] . ')'; ?>:</span>
<button class="scpbcfw-display-button" style="font-size:12px;font-weight:bold;padding:3px;float:right;">Open</button>
<div style="clear:both;"></div>
<div class="scpbcfw-search-field-values" style="display:none;">
<?php
            foreach ( get_taxonomies( '', 'objects' ) as $taxonomy ) {
                #error_log( '##### $taxonomy=' . print_r( $taxonomy, TRUE ) );
                if ( !in_array( $name, $taxonomy->object_type ) ) { continue; }
                # tag taxonomy stuff with a special prefix
                $tax_type = ( $taxonomy->hierarchical ) ? 'tax-cat-' : 'tax-tag-';
                $tax_label = ( $taxonomy->hierarchical ) ? ' (category)' : ' (tag)';
?>
<!--
<option value="<?php echo $tax_type . $taxonomy->name; ?>"
    <?php echo ( $selected && in_array( $tax_type . $taxonomy->name, $selected ) ) ? ' selected' : ''; ?>>
    <?php echo $taxonomy->name . $tax_label; ?></option>
-->
<input type="checkbox"
    id="<?php echo $this->get_field_id( $name ); ?>"
    name="<?php echo $this->get_field_name( $name ); ?>[]"
    value="<?php echo $tax_type . $taxonomy->name; ?>"
    <?php if ( $selected && in_array( $tax_type . $taxonomy->name, $selected ) ) { echo ' checked'; } ?>>
    <?php echo $taxonomy->name . $tax_label; ?><br>
<?php
            }    
            $fields = $wpdb->get_results( 'SELECT name, label FROM ' . MF_TABLE_CUSTOM_FIELDS
                . ' WHERE post_type = "' . $name . '" ORDER BY custom_group_id, display_order', OBJECT_K );
            # give post_content a special name since it requires special handkling
            $fields['pst-std-post_content'] = (object) array( 'label' => 'Post Content' );
            foreach ( $fields as $meta_key => $field ) {
                if ( $field->label == 'mf2tk_key' ) { continue; }
?>
<!--
<option value="<?php echo $meta_key; ?>"
    <?php echo ( $selected && in_array( $meta_key, $selected ) ) ? ' selected' : ''; ?>>
    <?php echo $field->label . ' (field)'; ?></option>
-->
<input type="checkbox"
    id="<?php echo $this->get_field_id( $name ); ?>"
    name="<?php echo $this->get_field_name( $name ); ?>[]"
    value="<?php echo $meta_key; ?>"
    <?php if ( $selected && in_array( $meta_key, $selected ) ) { echo ' checked'; } ?>>
    <?php echo $field->label . ' (field)'; ?><br>
<?php
            }
?>
<!--
</select>
-->
</div>
</div>
<?php
        }
?>
<script type="text/javascript">
jQuery("button.scpbcfw-display-button").click(function(event){
    if(jQuery(this).text()=="Open"){
        jQuery(this).text("Close");
        jQuery("div.scpbcfw-search-field-values",this.parentNode).css("display","block");
    }else{
        jQuery(this).text("Open");
        jQuery("div.scpbcfw-search-field-values",this.parentNode).css("display","none");
    }
    return false;
});
</script>
<?php
    }
}

add_action(
    'widgets_init',
    function() {
        register_widget( 'Search_Using_Magic_Fields_Widget' );
    }
);

if ( is_admin() ) {
    #error_log( '##### add_action( \'wp_ajax_nopriv_'
    #    . Search_Using_Magic_Fields_Widget::$get_form_for_post_type . ', ... )' );
    # Use the no privilege version also in privileged mode
    add_action(
        'wp_ajax_' . Search_Using_Magic_Fields_Widget::$get_form_for_post_type,
        function() {
            do_action( 'wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::$get_form_for_post_type );
        }
    );
    # This ajax action will generate and return the search form for the given post type
    add_action(
        'wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::$get_form_for_post_type,
        function() {
            global $wpdb;
            #error_log( '##### wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::$get_form_for_post_type . ':$_REQUEST='
            #    . print_r( $_REQUEST, TRUE ) );
            $option = get_option( $_REQUEST['magic_fields_search_widget_option'] );
            #error_log( '##### wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::$get_form_for_post_type . ':$option='
            #    . print_r( $option, TRUE ) );
            $number = $_REQUEST['magic_fields_search_widget_number'];
            $selected = $option[$_REQUEST['magic_fields_search_widget_number']][$_REQUEST['post_type']];
            #error_log( '##### wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::$get_form_for_post_type . ':$selected='
            #    . print_r( $selected, TRUE ) );
            foreach ( get_taxonomies( '', 'objects' ) as $taxonomy ) {
                #error_log( '##### $taxonomy=' . print_r( $taxonomy, TRUE ) );
                if ( in_array( $_REQUEST['post_type'], $taxonomy->object_type ) ) {
                    $tax_type = ( $taxonomy->hierarchical ) ? 'tax-cat-' : 'tax-tag-';
                    if ( !in_array( $tax_type . $taxonomy->name, $selected ) ) { continue; }
                    #$cloud = wp_tag_cloud( [ 'format' => 'array', 'taxonomy' => $taxonomy->name ] );
                    #error_log( '##### $cloud=' . print_r( $cloud, TRUE ) );
                    #$terms = [];
                    #foreach ( $cloud as $term ) {
                    #    $terms[substr( $term, strpos( $term, '>' ) + 1, -4 )]
                    #        = sscanf( substr( $term, strpos( $term, 'title=\'' ) + 7 ), '%d' )[0];
                    #}
                    #arsort( $terms, SORT_NUMERIC );
                    #error_log( '##### $terms=' . print_r( $terms, TRUE ) );
                    $results = $wpdb->get_results( 'SELECT t.name, COUNT(*) from '
                        . $wpdb->term_relationships . ' r, ' . $wpdb->term_taxonomy . ' x, ' . $wpdb->terms . ' t, '
                        . $wpdb->posts . ' p WHERE r.term_taxonomy_id = x.term_taxonomy_id AND x.term_id = t.term_id '
                        . 'AND r.object_id = p.ID AND x.taxonomy = "' . $taxonomy->name . '" AND p.post_type = "'
                        . $_REQUEST['post_type'] .'" GROUP BY t.term_id ORDER BY COUNT(*) DESC LIMIT '
                        . Search_Using_Magic_Fields_Widget::$sql_limit, ARRAY_A );
                    #error_log( '##### $results=' . print_r( $results, TRUE ) );
                    $terms = array();
                    foreach ( $results as $result ) { $terms[$result['name']] = $result['COUNT(*)']; }
                    #error_log( '##### $terms=' . print_r( $terms, TRUE ) );
?>
<!--
<div class="magic-field-parameter" style="padding:5px 10px;border:2px solid black;margin:5px;">
<h3><?php echo $taxonomy->name ?>:</h3>
<select id="<?php echo $tax_type . $taxonomy->name ?>" name="<?php echo $tax_type . $taxonomy->name ?>[]" multiple size="4"
    style="width:100%;">
<option value="no-selection" selected>--no <?php echo $taxonomy->name ?> selected--</option>
-->
<div class="scpbcfw-search-fields" style="padding:5px 10px;border:2px solid black;margin:5px;">
<span style="font-size=16px;font-weight:bold;float:left;"><?php echo $taxonomy->name ?>:</span>
<button class="scpbcfw-display-button" style="font-size:12px;font-weight:bold;padding:3px;float:right;">Open</button>
<div style="clear:both;"></div>
<div class="scpbcfw-search-field-values" style="display:none;">
<?php
                    foreach ( $terms as $term => $value ) {
?>
<!--
<option value="<?php echo $term ?>"><?php echo $term . ' (' . $value . ')'; ?></option>
-->
<input type="checkbox"
    id="<?php echo $meta_key; ?>"
    name="<?php echo $tax_type . $taxonomy->name; ?>[]"
    value="<?php echo $term; ?>">
    <?php echo $term . ' (' . $value . ')'; ?><br>
<?php
                    }
?>
<!--
<option value="add-new">--Enter New Search Value--</option>
</select>
-->
<?php
                    if ( count( $results ) == Search_Using_Magic_Fields_Widget::$sql_limit ) {
                    #if ( TRUE ) {   # TODO: remove for testing only
                        # too many distinct terms for this custom taxonomy so allow user to also manually enter search value
?>
<input id="<?php $meta_key . Search_Using_Magic_Fields_Widget::$optional_text_value_suffix; ?>"
    name="<?php echo "{$tax_type}{$taxonomy->name}" . Search_Using_Magic_Fields_Widget::$optional_text_value_suffix; ?>"
    class="for-select" type="text" style="width:90%;" placeholder="--Enter Search Value--">
<?php
                    }
?>
</div>
</div>
<?php
                }
            }
            #$meta_keys = $wpdb->get_col( 'SELECT DISTINCT m.meta_key FROM ' . $wpdb->postmeta . ' m, ' . $wpdb->posts . ' p '
            #    . 'WHERE m.post_id = p.ID AND p.post_type = "' . $_REQUEST['post_type'] .'"' );
            $fields = $wpdb->get_results( 'SELECT name, type, label FROM ' . MF_TABLE_CUSTOM_FIELDS
                . ' WHERE post_type = "' . $_REQUEST['post_type'] . '" ORDER BY custom_group_id, display_order', OBJECT_K );
            $fields['pst-std-post_content'] = (object) array( 'type' => 'multiline', 'label' => 'Post Content' );
            foreach ( $fields as $meta_key => $field ) {
                #if ( $meta_key === '_edit_last' || $meta_key === '_edit_lock' ) { continue; }
                #error_log( '##### $meta_key=' . $meta_key );
                if ( !in_array( $meta_key, $selected ) ) { continue; } 
?>
<!--
<div class="magic-field-parameter" style="padding:5px 10px;border:2px solid black;margin:5px;">
<h3><?php echo $field->label ?>:</h3>
-->
<div class="scpbcfw-search-fields" style="padding:5px 10px;border:2px solid black;margin:5px;">
<span style="font-size=16px;font-weight:bold;float:left;"><?php echo $field->label ?>:</span>
<button class="scpbcfw-display-button" style="font-size:12px;font-weight:bold;padding:3px;float:right;">Open</button>
<div style="clear:both;"></div>
<div class="scpbcfw-search-field-values" style="display:none;">
<?php
                if ( $field->type === 'multiline' || $field->type === 'markdown_editor' ) {
                    # values are multiline so just let user manually enter search values
?>
<input id="<?php echo $meta_key ?>" name="<?php echo $meta_key ?>" class="for-input" type="text" style="width:90%;"
    placeholder="--Enter Search Value--">
</div>
</div>
<?php
                    continue;
                }
                $results = $wpdb->get_results( 'SELECT wm.meta_value, count(*) FROM ' . $wpdb->postmeta . ' wm INNER JOIN '
                    . MF_TABLE_POST_META . ' mm INNER JOIN ' . $wpdb->posts . ' wp ON wm.meta_id = mm.meta_id '
                    . ' AND wm.post_id = wp.ID WHERE wm.meta_key = "' . $meta_key . '" AND wp.post_type = "'
                    . $_REQUEST['post_type'] . '" GROUP BY wm.meta_value ORDER BY count(*) DESC LIMIT '
                    . Search_Using_Magic_Fields_Widget::$sql_limit, ARRAY_A );
?>
<!--
<select id="<?php echo $meta_key ?>" name="<?php echo $meta_key ?>[]" multiple size="4" style="width:100%;<?php
if ( $field->type === 'file' || $field->type === 'image' || $field->type === 'audio' ) {
    echo 'text-align:right;';
}
?>">
<option value="no-selection" selected>--no <?php echo $field->label ?> selected--</option>
-->
<?php
                $values = array();
                foreach ( $results as $result ) {
                    if ( !$result['meta_value'] ) { continue; }
                    #error_log( '##### ' . $meta_key . ': ' . print_r( $result, TRUE ) );
                    if ( $field->type === 'related_type' ) {
                        $value = get_the_title( $result['meta_value'] );
                    } else if ( $field->type === 'image_media' ) {
                        $value = $wpdb->get_col( 'SELECT guid from ' . $wpdb->posts . ' WHERE ID = ' . $result['meta_value'] );
                        if ( $value ) { $value = substr( $value[0], strrpos( $value[0], '/' ) + 1 ); }
                        else { $value = ''; }
                    } else if ( $field->type === 'alt_related_type' || $field->type === 'checkbox_list' 
                        || $field->type === 'dropdown' || $field->type === 'alt_dropdown' ) {
                        #error_log( '##### $type === \'alt_related_type\' $result=' . print_r( $result, TRUE ) );
                        $entries = unserialize( $result['meta_value'] );
                        $count = $result['count(*)'];
                        for ( $i = 0; $i < $count; $i++ ) {
                            $values = array_merge( $values, $entries );
                        }
                        #error_log( '##### $type === \'alt_related_type\' $values=' . print_r( $values, TRUE ) );
                        continue;
                    } else {
                        $value = $result['meta_value'];
                    }
?>
<!--
<option value="<?php echo $result['meta_value'] ?>"<?php
if ( $field->type === 'file' || $field->type === 'image' || $field->type === 'audio' ) {
    echo ' style="text-align:right;"';
}
?>><?php echo $value . ' (' . $result['count(*)'] . ')'; ?></option>
-->
<input type="checkbox"
    id="<?php echo $meta_key; ?>"
    name="<?php echo $meta_key; ?>[]"
    value="<?php echo $result['meta_value']; ?>">
    <?php echo $value . ' (' . $result['count(*)'] . ')'; ?><br>
<?php
                }
                if ( $field->type === 'alt_related_type' || $field->type === 'checkbox_list'
                    || $field->type === 'dropdown' || $field->type === 'alt_dropdown' ) {
                    $values = array_count_values( $values );
                    arsort( $values, SORT_NUMERIC );
                    #error_log( '##### $type === \'alt_related_type\' $values=' . print_r( $values, TRUE ) );
                    foreach ( $values as $key => $value) {
                        if ( $field->type === 'alt_related_type' ) {
?>
<!--
<option value="<?php echo $key ?>"><?php echo get_the_title( $key ) . ' (' . $value . ')'; ?></option>
-->
<input type="checkbox"
    id="<?php echo $meta_key; ?>"
    name="<?php echo $meta_key; ?>[]"
    value="<?php echo $key; ?>">
    <?php echo get_the_title( $key ) . ' (' . $value . ')'; ?><br>
<?php
                        } else {
?>
<!--
<option value="<?php echo $key ?>"><?php echo $key . ' (' . $value . ')'; ?></option>
-->
<input type="checkbox"
    id="<?php echo $meta_key; ?>"
    name="<?php echo $meta_key; ?>[]"
    value="<?php echo $key; ?>">
    <?php echo $key . ' (' . $value . ')'; ?><br>
<?php
                        }
                    }
                }
                if ( count( $results ) == Search_Using_Magic_Fields_Widget::$sql_limit && ( $field->type !== 'related_type'
                    && $field->type !== 'alt_related_type' && $field->type !== 'image_media' ) ) {
?>
<!--
<option value="add-new">--Enter New Search Value--</option>
-->
<?php
                #}
                    # for these types also allow user to manually enter search values
?>
<!--
</select>
<input id="<?php ?>" name="<?php ?>" class="for-select" type="text" style="width:90%;display:none;"
    placeholder="--Enter Search Value--">
-->
<input id="<?php echo $meta_key . Search_Using_Magic_Fields_Widget::$optional_text_value_suffix; ?>"
    name="<?php echo $meta_key . Search_Using_Magic_Fields_Widget::$optional_text_value_suffix; ?>"
    class="for-select" type="text" style="width:90%;" placeholder="--Enter Search Value--">
<?php
                }
?>
</div>
</div>
<?php
            }
?>
<script type="text/javascript">
jQuery("form#search-using-magic-fields-<?php echo $number; ?> div.magic-field-parameter select").change(function(){
    //console.log(jQuery("option:selected",this).text());
    if(jQuery("option:selected:last",this).text()=="--Enter New Search Value--"){
        jQuery(this).css("display","none");
        var input=jQuery("input",this.parentNode).css("display","inline").val("").get(0);
        input.focus();
        input.select();
    }
});
jQuery("form#search-using-magic-fields-<?php echo $number; ?> div.magic-field-parameter input.for-select").change(function(){
    //console.log(jQuery(this).val());
    //console.log(jQuery(this).text());
    var value=jQuery(this).val();
    var select=jQuery("select",this.parentNode);
    //var last=jQuery("option:last",select).detach();
    //last.prop("selected",false);
    //select.append(last);
    jQuery("option:last",select).prop("selected",false);
    if(value){
        var first=jQuery("option:first",select).detach();
        select.prepend('<option value="'+value+'" selected>'+value+'</option>');
        select.prepend(first);
        jQuery(this).val("");
    }
    select.css("display","inline");
    jQuery(this).css("display","none");
    //jQuery(this).val("*enter new value*");
});
jQuery("form#search-using-magic-fields-<?php echo $number; ?> div.magic-field-parameter input.for-select")
    .blur(function(){
    jQuery(this).change();
});
//jQuery("form#search-using-magic-fields div.magic-field-parameter input.for-input").focus(function(){this.select();});
jQuery("form#search-using-magic-fields-<?php echo $number; ?> div.magic-field-parameter input.for-select")
    .keydown(function(e){
    if(e.keyCode==13){jQuery(this).blur();return false;}
});
</script>
<script type="text/javascript">
jQuery("button.scpbcfw-display-button").click(function(event){
    if(jQuery(this).text()=="Open"){
        jQuery(this).text("Close");
        jQuery("div.scpbcfw-search-field-values",this.parentNode).css("display","block");
    }else{
        jQuery(this).text("Open");
        jQuery("div.scpbcfw-search-field-values",this.parentNode).css("display","none");
    }
    return false;
});
</script>
<?php
            die();
        }
    );
} else {
    add_action(
        'wp_enqueue_scripts',
        function() {
            wp_enqueue_script( 'jquery' );
        }
    );
    #add_action(
    #    'pre_get_posts',
    #    function( $query ) {
    #        if ( !$query->is_main_query() || !array_key_exists( 'magic_fields_form', $_REQUEST ) ) { return; }
    #        #error_log( '##### pre_get_posts():backtrace=' . print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), TRUE ) );
    #        error_log( '$query=' . print_r( $query, TRUE ) );
    #    }
    #);
	#add_filter(
    #    'posts_search',
    #    function( $search, $query ) {
    #        if ( $query->is_main_query() ) { 
    #            error_log( '$search=' . $search );
    #        }
    #        return $search;
    #    },
    #    10, 2
    #);
	add_filter(
        'posts_where',
        function( $where, $query ) {
            global $wpdb;
            if ( $query->is_main_query() && array_key_exists( 'magic_fields_search_form', $_REQUEST ) ) {
                #error_log( '##### posts_where:$_REQUEST=' . print_r( $_REQUEST, TRUE ) );
                # merge optional text values into the checkboxes array
                $suffix_len = strlen( Search_Using_Magic_Fields_Widget::$optional_text_value_suffix );
                foreach ( $_REQUEST as $index => &$request ) {
                    #error_log( '##### posts_where:$index=' . $index );
                    #error_log( '##### posts_where:$request=' . print_r ( $request, TRUE ) );
                    if ( $request
                        && substr_compare( $index, Search_Using_Magic_Fields_Widget::$optional_text_value_suffix, -$suffix_len ) === 0 ) {
                        $index = substr( $index, 0, strlen( $index ) - $suffix_len );
                        if ( is_array( $_REQUEST[$index] ) || !array_key_exists( $index, $_REQUEST ) ) {
                            $_REQUEST[$index][] = $request;
                            # kill the original request
                            $request = NULL;
                        }
                    }
                }
                #error_log( '##### posts_where:$_REQUEST=' . print_r( $_REQUEST, TRUE ) );
                #error_log( 'posts_where:$where=' . $where );
                $sql = 'SELECT p.ID FROM ' . $wpdb->posts . ' p WHERE p.post_type = "' . $_REQUEST['post_type'] . '"';
                foreach ( $_REQUEST as $key => $values ) {
                    if ( $key === 'magic_fields_search_form' || $key === 'magic_fields_search_widget_option'
                        || $key === 'magic_fields_search_widget_number' || $key === 'post_type' ) {
                        continue;
                    }
                    $prefix = substr( $key, 0, 8 );
                    if ( $prefix === 'tax-cat-' || $prefix === 'tax-tag-' || $prefix === 'pst-std-' ) {
                        continue;
                    }
                    if ( !is_array( $values) ) {
                        if ( $values ) { $values = array( $values ); }
                        else { $values = array(); }
                    }
                    if ( !$values || $values[0] === 'no-selection' ) {
                        continue;
                    }
                    $sql .= ' AND EXISTS ( SELECT * FROM ' . $wpdb->postmeta . ' w INNER JOIN ' . MF_TABLE_POST_META
                        . ' m ON w.meta_id = m.meta_id WHERE (';
                    foreach ( $values as $value ) {
                        if ( $value !== $values[0] ) { $sql .= ' OR '; }
                        $sql .= '(w.meta_key = "' . $key . '" AND ' . 'w.meta_value LIKE "%' . $value . '%")';
                    }
                    $sql .= ') AND w.post_id = p.ID )';
                }
                #error_log( '##### posts_where:meta $sql=' . $sql );
                $ids0 = $wpdb->get_col( $sql );
                $sql = 'SELECT ID FROM ' . $wpdb->posts . ' p WHERE p.post_type = "' . $_REQUEST['post_type'] . '"';
                foreach ( $_REQUEST as $key => $values ) {
                    if ( $key === 'magic_fields_search_form' || $key === 'magic_fields_search_widget_option'
                        || $key === 'magic_fields_search_widget_number' || $key === 'post_type' ) {
                        continue;
                    }
                    $prefix = substr( $key, 0, 8 );
                    if ( $prefix !== 'tax-cat-' && $prefix !== 'tax-tag-' ) {
                        continue;
                    }
                    if ( !is_array( $values) ) {
                        if ( $values ) { $values = array( $values ); }
                        else { $values = array(); }
                    }
                    if ( !$values || $values[0] === 'no-selection' ) {
                        continue;
                    }
                    $taxonomy = substr( $key, 8 );
                    $sql2 = '';
                    foreach ( $values as $value ) {
                        if ( $sql2 ) { $sql2 .= ' OR '; }
                        $term = get_term_by( 'name', $value, $taxonomy, OBJECT );
                        #error_log( '##### posts_where:$value=' . $value . ', $term=' . print_r( $term, TRUE ) );
                        if ( !$term ) { continue; }
                        $sql2 .= "term_taxonomy_id = $term->term_taxonomy_id"; 
                    }
                    if ( !$sql2 ) {
                        $sql2 = '1 = 2';
                    }
                    $sql .= " AND EXISTS ( SELECT * FROM $wpdb->term_relationships WHERE ( $sql2 ) AND object_id = p.ID )";
                }
                #error_log( '##### posts_where:tax $sql=' . $sql );
                $ids1 = $wpdb->get_col( $sql );
                $ids = array_intersect( $ids0, $ids1 );
                $where = ' AND ' . $wpdb->posts . '.post_status = "publish" AND ' . $wpdb->posts
                    . '.ID IN (' . implode( ',', $ids ) . ')';
                if ( array_key_exists( 'pst-std-post_content', $_REQUEST ) && $_REQUEST['pst-std-post_content'] ) {
                    #&& $_REQUEST['pst-std-post_content'] != '*enter search value*' ) {
                    $where .= ' AND ( post_content LIKE "%' . $_REQUEST['pst-std-post_content']
                        . '%" OR post_title LIKE "%' . $_REQUEST['pst-std-post_content'] . '%" )';
                }
                #error_log( '##### posts_where:$where=' . $where );
            }
            return $where;
        },
        10, 2
    );
    
    # add null 'query_string' filter to force parse_str() call in WP::build_query_string() - otherwise name gets set ? TODO: why?
    add_filter( 'query_string', function( $arg ) { return $arg; } );

	#add_filter(
    #    'posts_join',
    #    function( $join, $query ) {
    #        global $wpdb;
    #        if ( $query->is_main_query() && array_key_exists( 'magic_fields_form', $_REQUEST ) ) {
    #            error_log( '$join=' . $join );
    #            $join = ' INNER JOIN ' . $wpdb->postmeta . ' ON ' . $wpdb->posts . '.ID = '
    #                . $wpdb->postmeta . '.post_id';
    #            error_log( '$join=' . $join );
    #        }
    #        return $join;
    #    },
    #    10, 2
    #);
	#add_filter(
    #    'posts_groupby',
    #    function( $groupby, $query ) {
    #        global $wpdb;
    #        if ( $query->is_main_query() && array_key_exists( 'magic_fields_form', $_REQUEST ) ) {
    #            error_log( '$groupby=' . $groupby );
    #            $groupby = $wpdb->posts . '.ID';
    #            error_log( '$groupby=' . $groupby );
    #        }
    #        return $groupby;
    #    },
    #    10, 2
    #);
    #add_filter(
    #    'query',
    #    function( $query ) {
    #        global $wp_query;
    #        if ( is_object( $wp_query ) && $wp_query->is_main_query()
    #            && array_key_exists( 'magic_fields_form', $_REQUEST ) ) { 
    #            error_log( '$query=' . $query );
    #        }
    #        return $query;
    #    }
    #);
}

?>