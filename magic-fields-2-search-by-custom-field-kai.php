<?php
/*
 * Module Name:   Search Magic Fields 2 Widget
 * Module URI:    http://magicfields17.wordpress.com/magic-fields-2-search-0-4-1/
 * Description:   Widget for searching Magic Fields 2 custom fields and custom taxonomies and also post_content.
 * Documentation: http://magicfields17.wordpress.com/magic-fields-2-search-0-4-1/
 * Version:       0.4.5
 * Author:        Magenta Cuda (PHP), Black Charger (JavaScript)
 * Author URI:    http://magentacuda.wordpress.com
 * License:       GPL2
 */
/*  
    Copyright 2013  Magenta Cuda

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
/*
    I have decided to unbundle the search widget from my Magic Fields 2 Toolkit
    as it is independently viable and since I will soon end active development 
    on the rest of the toolkit. This search widget can search for posts by the 
    value of custom fields, taxonomies and post content. The widget uses user 
    friendly substitutions for the actual values in the database when 
    appropriate, e.g. post title is substituted for post id in related type 
    custom fields.
 */
 
list( $major, $minor ) = sscanf( phpversion(), '%D.%D' );
#error_log( '##### Magic_Fields_2_Toolkit_Init::__construct():phpversion()=' . $major . ',' . $minor );
$tested_major = 5;
$tested_minor = 4;
if ( !( $major > $tested_major || ( $major == $tested_major && $minor >= $tested_minor ) ) ) {
    add_action( 'admin_notices', function() use ( $major, $minor, $tested_major, $tested_minor ) {
        echo <<<EOD
<div style="padding:10px 20px;border:2px solid red;margin:50px 20px;font-weight:bold;">
    Search Magic Fields 2 Widget will not work with PHP version $major.$minor;
    Please uninstall it or upgrade your PHP version to $tested_major.$tested_minor or later.
</div>
EOD;
    } );
    return;
}

if ( is_admin() ) {
    add_action( 'admin_enqueue_scripts', function() {
        wp_enqueue_script( 'jquery-ui-draggable' );
        wp_enqueue_script( 'jquery-ui-droppable' );
    } );
}

class Search_Using_Magic_Fields_Widget extends WP_Widget {
    const GET_FORM_FOR_POST_TYPE = 'get_form_for_post_type';         # the AJAX action to get the form for post type selected by user
    const SQL_LIMIT = '16';                                          # maximum number of items to show per custom field
    #const SQL_LIMIT = '2';                                          # TODO: this limit for testing only replace with above
    const OPTIONAL_TEXT_VALUE_SUFFIX = '-mf2tk-optional-text-value'; # suffix for additional text input for a custom field
    const OPTIONAL_MINIMUM_VALUE_SUFFIX = '-stcfw-minimum-value';    # suffix to append to optional minimum/maximum value text 
    const OPTIONAL_MAXIMUM_VALUE_SUFFIX = '-stcfw-maximum-value';    #     inputs for a numeric search field
    const DEFAULT_CONTENT_MACRO = <<<'EOD'
<div style="width:99%;overflow:auto;">
<div class="scpbcfw-result-container"$#table_width#>
<table class="scpbcfw-result-table">
[show_custom_field post_id="$#a_post#" field="__post_title;$#fields#"
    before="<span style='display:none;'>"
    after="</span>"
    field_before="<th class='scpbcfw-result-table-head-<!--$field-->' style='padding:5px;'><!--$Field-->"
    field_after="</th>
    post_before="<tr>"
    post_after="</tr>"
]
[show_custom_field post_id="$#posts#" field="__post_title;$#fields#"
    separator=", "
    field_before="<td class='scpbcfw-result-table-detail-<!--$field-->' style='padding:5px;'>"
    field_after="</td>
    post_before="<tr>"
    post_after="</tr>"
    filter="url_to_link;media_url_to_link"
]
</table>
</div>
</div>
EOD;
	public function __construct() {
		parent::__construct( 'search_magic_fields', __( 'Search using Magic Fields' ),
            array( 'classname' => 'search_magic_fields_widget', 'description' => __( "Search for Custom Posts using Magic Fields" ) )
        );
	}

    # widget() emits a form to select the post type; after user selects a post type
    # an AJAX request is sent back to retrieve the post type specific search form
    
	public function widget( $args, $instance ) {
        global $wpdb;
        #error_log( '##### Search_Using_Magic_Fields_Widget::widget():$this='     . print_r( $this,     true ) );
        #error_log( '##### Search_Using_Magic_Fields_Widget::widget():$args='     . print_r( $args,     true ) );
        #error_log( '##### Search_Using_Magic_Fields_Widget::widget():$instance=' . print_r( $instance, true ) );
        extract( $args );
        # initially show only post type selection form after post type selected use AJAX to retrieve post specific form
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
        # get data for the administrator selected post types
        $selected_types = '"' . implode( '", "', array_diff( array_keys( $instance ),
            array( 'maximum_number_of_items', 'set_is_search', 'enable_table_view_option', 'table_shortcode', 'table_width' ) ) )
            . '"';
        #error_log( '##### Search_Using_Magic_Fields_Widget::widget():$selected_types=' . print_r( $selected_types, true ) );
        $SQL_LIMIT = self::SQL_LIMIT;
        $types = $wpdb->get_results( <<<EOD
            SELECT post_type, COUNT(*) count FROM $wpdb->posts
                WHERE post_type IN ( $selected_types ) AND post_status = "publish" 
                GROUP BY post_type ORDER BY count DESC LIMIT $SQL_LIMIT
EOD
            , OBJECT_K );
        #error_log( '##### Search_Using_Magic_Fields_Widget::form():$types=' . print_r( $types, TRUE ) );
        foreach ( $types as $name => $type ) {
?>      
<option value="<?php echo $name; ?>"><?php echo "$name ($type->count)"; ?></option>
<?php
        }   # foreach ( $types as $name => $type ) {
?>
</select>
</div>
<div id="magic-fields-parameters"></div>
<div id="magic-fields-submit-box" style="display:none">
<div style="border:2px solid black;padding:5px;margin:5px;border-radius:7px;">
<div style="text-align:center;margin:10px;">
Results should satisfy<br> 
<input type="radio" name="magic-fields-search-and-or" value="and" checked><strong>All</strong>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="magic-fields-search-and-or" value="or"><strong>Any</strong></br>
of the search conditions.
</div>
<?php
        if ( $instance['enable_table_view_option'] === 'table view option enabled' ) {
?>
<hr>
<div style="margin:10px">
<input type="checkbox" name="magic-fields-show-using-macro" value="use macro" style="float:right;margin-top:5px;margin-left:5px;">
Show search results in alternate format:
</div>
<?php
        }
?>
</div>
<div style="text-align:right;">
<input id="magic-fields-search" type="submit" value="Start Search" style="color:black;border:2px solid black;" disabled>
&nbsp;&nbsp;
</div>
</div>
</form>
<script>
jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> select#post_type").change(function(){
    jQuery.post(
        '<?php echo admin_url( 'admin-ajax.php' ); ?>',{
            action:'<?php echo Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE; ?>',
            mf2tk_get_form_nonce:'<?php echo wp_create_nonce( Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE ); ?>',
            post_type:jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> select#post_type option:selected").val(),
            magic_fields_search_widget_option:
                jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> input#magic_fields_search_widget_option").val(),
            magic_fields_search_widget_number:
                jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> input#magic_fields_search_widget_number").val()
        },
        function(response){
            jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> div#magic-fields-parameters").html(response);
            jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> input#magic-fields-search").prop("disabled",false);
            jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> div#magic-fields-submit-box").css("display","block");
        }
    );
});
</script>
<?php
	}   # public function widget( $args, $instance ) {
    
    public function update( $new, $old ) {
        #error_log( '##### Search_Using_Magic_Fields_Widget::update():$_POST=' . print_r( $_POST, TRUE ) );    
        #error_log( '##### Search_Using_Magic_Fields_Widget::update():$old=' . print_r( $old, TRUE ) );
        #error_log( '##### Search_Using_Magic_Fields_Widget::update():$new=' . print_r( $new, TRUE ) );
        #return array_map( function( $values ) {
        #    return is_array( $values) ? array_map( strip_tags, $values ) : strip_tags( $values );
        #}, $new );
        return $new;
    }   # public function update( $new, $old ) {
    
    # form() emits a form for the administrator to select the post types and custom fields that the user will be allowed to search
    
    public function form( $instance ) {
        global $wpdb;
        #error_log( '##### Search_Using_Magic_Fields_Widget::form():$instance=' . print_r( $instance, true ) );
        # show the configuration form to select custom fields for the given post type
?>
<h4>Select Search Fields and Content Macro Display Fields for:</h4>
<?php
        # use all Magic Fields 2 custom post types and the WordPress built in "post" and "page" types
        $mf2_types = '"' . implode( '", "', $wpdb->get_col( 'SELECT type FROM ' . MF_TABLE_POSTTYPES ) ) . '", "post", "page"'; 
        $SQL_LIMIT = self::SQL_LIMIT;
        $types = $wpdb->get_results( <<<EOD
            SELECT post_type, COUNT(*) count FROM $wpdb->posts
                WHERE post_type IN ( $mf2_types ) AND post_status = "publish" 
                GROUP BY post_type ORDER BY count DESC LIMIT $SQL_LIMIT
EOD
            , OBJECT_K );
        #error_log( '##### Search_Using_Magic_Fields_Widget::form():$types=' . print_r( $types, TRUE ) );
        # First get the number of posts tagged by post type and taxonomy, since a single post tagged with multiple tags
        # should be counted only once the sql is somewhat complicated
        $db_taxonomies = $wpdb->get_results( <<<EOD
            SELECT post_type, taxonomy, count(*) count
                FROM ( SELECT p.post_type, x.taxonomy, r.object_id
                    FROM $wpdb->term_relationships r, $wpdb->term_taxonomy x, $wpdb->terms t, $wpdb->posts p
                    WHERE r.term_taxonomy_id = x.term_taxonomy_id AND x.term_id = t.term_id AND r.object_id = p.ID
                        AND p.post_type IN ( $mf2_types )
                    GROUP BY p.post_type, x.taxonomy, r.object_id ) d 
                GROUP BY post_type, taxonomy
EOD
        , OBJECT );
        #error_log( '##### Search_Using_Magic_Fields_Widget::form():$db_taxonomies=' . print_r( $db_taxonomies, TRUE ) );
        $wp_taxonomies = get_taxonomies( '', 'objects' );
        #error_log( '##### Search_Using_Magic_Fields_Widget::form():$wp_taxonomies=' . print_r( $wp_taxonomies, TRUE ) );
        # do all post types
        foreach ( $types as $name => $type ) {
            #error_log( '##### Search_Using_Magic_Fields_Widget::form():$name=' . $name );
            $selected      = $instance[$name];
            $show_selected = $instance['show-' . $name];
?>
<div class="scpbcfw-search-fields" style="padding:5px 10px;border:2px solid black;margin:5px;">
<span style="font-size=16px;font-weight:bold;float:left;"><?php echo "$name ($type->count)"; ?>:</span>
<button class="scpbcfw-display-button" style="font-size:12px;font-weight:bold;padding:3px;float:right;">Open</button>
<div style="clear:both;"></div>
<div class="scpbcfw-search-field-values" style="display:none;">
<style scoped>
div.mf2tk-selectable-field-after{height:2px;background-color:white;}
div.mf2tk-selectable-field-after.mf2tk-hover{background-color:black;}
div.mf2tk-selectable-taxonomy-after{height:2px;background-color:white;}
div.mf2tk-selectable-taxonomy-after.mf2tk-hover{background-color:black;}
</style>
<!-- before drop point -->
<div><div class="mf2tk-selectable-taxonomy-after"></div></div>
<?php
            # do taxonomies first
            $the_taxonomies = array();
            foreach ( $db_taxonomies as &$db_taxonomy ) {
                if ( $db_taxonomy->post_type != $name ) { continue; }
                $wp_taxonomy =& $wp_taxonomies[$db_taxonomy->taxonomy];
                $the_taxonomies[$wp_taxonomy->name] =& $db_taxonomy;
            }
            unset( $db_taxonomy, $wp_taxonomy );
            $previous = !empty( $instance['tax-order-' . $name] ) ? explode( ';', $instance['tax-order-' . $name] ) : array();
            # remove taxonomy prefixes
            $previous = array_map( function( $value ) {
                $value = str_replace( 'tax-cat-', '', $value, $count );
                if ( !$count ) { $value = str_replace( 'tax-tag-', '', $value ); }
                return $value;
            }, $previous );
            #error_log( '##### Search_Using_Magic_Fields_Widget::form():$previous=' . print_r( $previous, true ) );
            $current = array_keys( $the_taxonomies );
            #error_log( '##### Search_Using_Magic_Fields_Widget::form():$current=' . print_r( $current, true ) );
            $previous = array_intersect( $previous, $current );
            #error_log( '##### Search_Using_Magic_Fields_Widget::form():$previous=' . print_r( $previous, true ) );
            $new = array_diff( $current, $previous );
            #error_log( '##### Search_Using_Magic_Fields_Widget::form():$new=' . print_r( $new, true ) );
            $current = array_merge( $previous, $new );
            #error_log( '##### Search_Using_Magic_Fields_Widget::form():$current=' . print_r( $current, true ) );
            foreach ( $current as $tax_name ) {
                $db_taxonomy =& $the_taxonomies[$tax_name];
                #error_log( '##### Search_Types_Custom_Fields_Widget::form():$name=' . $name
                #    . ', $db_taxonomy=' . print_r( $db_taxonomy, TRUE ) );
                $wp_taxonomy = $wp_taxonomies[$db_taxonomy->taxonomy];
                #error_log( '##### $taxonomy=' . print_r( $taxonomy, TRUE ) );
                #error_log( '##### Search_Types_Custom_Fields_Widget::form():$name=' . $name
                #    . ', $wp_taxonomy=' . print_r( $wp_taxonomy, TRUE ) );
                $tax_type = ( $wp_taxonomy->hierarchical ) ? 'tax-cat-' : 'tax-tag-';
                $tax_label = ( $wp_taxonomy->hierarchical ) ? ' (category)' : ' (tag)';
?>
<div class="mf2tk-selectable-taxonomy">
    <input type="checkbox"
        class="mf2tk-selectable-taxonomy" 
        id="<?php echo $this->get_field_id( $name ); ?>"
        name="<?php echo $this->get_field_name( $name ); ?>[]"
        value="<?php echo $tax_type . $wp_taxonomy->name; ?>"
        <?php if ( $selected && in_array( $tax_type . $wp_taxonomy->name, $selected ) ) { echo ' checked'; } ?>>
    <input type="checkbox"
        id="<?php echo $this->get_field_id( 'show-' . $name ); ?>"
        class="scpbcfw-select-content-macro-display-field"
        name="<?php echo $this->get_field_name( 'show-' . $name ); ?>[]"
        value="<?php echo $tax_type . $wp_taxonomy->name; ?>"
        <?php if ( $show_selected && in_array( $tax_type . $wp_taxonomy->name, $show_selected ) ) { echo ' checked'; } ?>
        <?php if ( $instance && !isset( $instance['enable_table_view_option'] ) ) { echo 'disabled'; } ?>>
        <?php echo "{$wp_taxonomy->label}{$tax_label} ($db_taxonomy->count)"; ?>
    <!-- a drop point -->
    <div class="mf2tk-selectable-taxonomy-after"></div>
</div>
<?php
            }   # foreach ( $db_taxonomies as $db_taxonomy ) {
            # now do custom fields and post content
            $MF_TABLE_CUSTOM_FIELDS = MF_TABLE_CUSTOM_FIELDS;
            $SQL_LIMIT = self::SQL_LIMIT;
            # Again the sql is tricky to avoid double counting posts with repeating fields
            $fields = $wpdb->get_results( <<<EOD
                SELECT name, type, label, COUNT(*) count
                    FROM ( SELECT f.name, f.type, f.label, m.post_id
                        FROM $MF_TABLE_CUSTOM_FIELDS f, $wpdb->postmeta m, $wpdb->posts p 
                        WHERE m.meta_key = f.name AND m.post_id = p.ID
                            AND p.post_type = "$name" AND f.post_type = "$name" AND m.meta_value IS NOT NULL
                                AND m.meta_value != "" AND m.meta_key != "mf2tk_key"
                        GROUP BY f.name, m.post_id ) d
                    GROUP BY name ORDER BY count DESC LIMIT $SQL_LIMIT
EOD
                , OBJECT_K );
            #error_log( '##### Search_Using_Magic_Fields_Widget::form():$fields=' . print_r( $fields, TRUE ) );
            # add the post_content field giving it a special name since it requires special handling
            $fields['pst-std-post_content'] 
                = (object) array( 'label' => 'Post Content', 'type' => 'multiline', 'count' => $type->count );
            $previous = !empty( $instance['order-' . $name] ) ? explode( ';', $instance['order-' . $name] ) : array();
            $current = array_keys( $fields );
            #error_log( '##### Search_Using_Magic_Fields_Widget::form():$current=' . print_r( $current, true ) );
            $previous = array_intersect( $previous, $current );
            #error_log( '##### Search_Using_Magic_Fields_Widget::form():$previous=' . print_r( $previous, true ) );
            $new = array_diff( $current, $previous );
            #error_log( '##### Search_Using_Magic_Fields_Widget::form():$new=' . print_r( $new, true ) );
            $current = array_merge( $previous, $new );
            #error_log( '##### Search_Using_Magic_Fields_Widget::form():$current=' . print_r( $current, true ) );
?>
<!-- before drop point -->
<div><div class="mf2tk-selectable-field-after"></div></div>
<?php
            foreach ( $current as $meta_key ) {
                $field =& $fields[$meta_key];
                if ( substr_compare( $meta_key, 'mf2tk_key', -9 ) === 0 ) { continue; }
?>
<div class="mf2tk-selectable-field">
    <input type="checkbox" class="mf2tk-selectable-field" id="<?php echo $this->get_field_id( $name ); ?>"
        name="<?php echo $this->get_field_name( $name ); ?>[]" value="<?php echo $meta_key; ?>"
        <?php if ( $selected && in_array( $meta_key, $selected ) ) { echo ' checked'; } ?>>
    <input type="checkbox" id="<?php echo $this->get_field_id( 'show-' . $name ); ?>"
        name="<?php echo $this->get_field_name( 'show-' . $name ); ?>[]"
        <?php if ( $field->type !== 'multiline' || $field->type !== 'markdown_editor' ) {
            echo 'class="scpbcfw-select-content-macro-display-field"'; } ?>
        value="<?php echo $meta_key; ?>" <?php if ( $show_selected && in_array( $meta_key, $show_selected ) ) { echo ' checked'; } ?>
        <?php if ( ( $instance && !isset( $instance['enable_table_view_option'] ) ) || $field->type === 'multiline'
            || $field->type === 'markdown_editor' ) { echo 'disabled'; } ?>>
        <?php echo "$field->label (field) ($field->count)"; ?>
    <!-- a drop point -->
    <div class="mf2tk-selectable-field-after"></div>
</div>
<?php
            }   # foreach ( $fields as $meta_key => $field ) {
?>
<input type="hidden" class="mf2tk-selectable-taxonomy-order" id="<?php echo $this->get_field_id( 'tax-order-' . $name ); ?>"
    name="<?php echo $this->get_field_name( 'tax-order-' . $name ); ?>"
    value="<?php echo isset( $instance['tax-order-' . $name] ) ? $instance['tax-order-' . $name] : ''; ?>">
<input type="hidden" class="mf2tk-selectable-field-order" id="<?php echo $this->get_field_id( 'order-' . $name ); ?>"
    name="<?php echo $this->get_field_name( 'order-' . $name ); ?>"
    value="<?php echo isset( $instance['order-' . $name] ) ? $instance['order-' . $name] : ''; ?>">
</div>
</div>
<?php
        }   # foreach ( $types as $name => $type ) {
?>
<div style="border:2px solid gray;padding:5px;margin:5px;border-radius:7px;">
<div style="padding:10px;border:1px solid gray;margin:5px;">
<input type="number" min="4" max="1024" 
    id="<?php echo $this->get_field_id( 'maximum_number_of_items' ); ?>"
    name="<?php echo $this->get_field_name( 'maximum_number_of_items' ); ?>"
    value="<?php echo !empty( $instance['maximum_number_of_items'] ) ? $instance['maximum_number_of_items'] : 16; ?>"
    size="4" style="float:right;text-align:right;">
Maximum number of items to display per custom field:
<div style="clear:both;"></div>
</div>
<div style="padding:10px;border:1px solid gray;margin:5px;">
<input type="checkbox"
    id="<?php echo $this->get_field_id( 'set_is_search' ); ?>"
    name="<?php echo $this->get_field_name( 'set_is_search' ); ?>"
    value="is search" <?php if ( isset( $instance['set_is_search'] ) ) { echo 'checked'; } ?>
    style="float:right;margin-top:5px;margin-left:5px;">
Display search results using the same template as the default WordPress search:
<div style="clear:both;"></div>
</div>
<div style="padding:10px;border:1px solid gray;margin:5px;">
<input type="checkbox"
    id="<?php echo $this->get_field_id( 'enable_table_view_option' ); ?>"
    name="<?php echo $this->get_field_name( 'enable_table_view_option' ); ?>"
    value="table view option enabled" <?php if ( !$instance || isset( $instance['enable_table_view_option'] ) ) { echo 'checked'; } ?>
    style="float:right;margin-top:5px;margin-left:5px;">
Enable option to display search results using a content macro:
<div style="clear:both;"></div>
</div>
<div style="padding:10px;border:1px solid gray;margin:5px;">
<input type="number" min="256" max="8192" 
    id="<?php echo $this->get_field_id( 'table_width' ); ?>"
    name="<?php echo $this->get_field_name( 'table_width' ); ?>"
    <?php if ( !empty( $instance['table_width'] ) ) { echo "value=\"$instance[table_width]\""; } ?>
    <?php if ( $instance && !isset( $instance['enable_table_view_option'] ) ) { echo 'disabled'; } ?>
    placeholder="from css"
    size="5" style="float:right;text-align:right;">
Width in pixels of the container used to display the search results:
<div style="clear:both;"></div>
</div>
<div style="padding:10px;border:1px solid gray;margin:5px;">
The content macro to use to display the search results:
<textarea
    id="<?php echo $this->get_field_id( 'table_shortcode' ); ?>"
    name="<?php echo $this->get_field_name( 'table_shortcode' ); ?>"
    rows="8" <?php if ( $instance && !isset( $instance['enable_table_view_option'] ) ) { echo 'disabled'; } ?>
    placeholder="The default content macro will be used."
    style="width:90%;">
<?php 
    if ( !empty( $instance['table_shortcode'] ) ) {
        $macro = $instance['table_shortcode'];
        #error_log( '##### Search_Using_Magic_Fields_Widget::form():[from $instance]$macro=' . $macro );
    } else {
        $macro = Search_Using_Magic_Fields_Widget::DEFAULT_CONTENT_MACRO;
        #error_log( '##### Search_Using_Magic_Fields_Widget::form():[from code]$macro=' . $macro );
    }
    $macro = htmlspecialchars( $macro );
    echo $macro;
?>
</textarea>
<div style="clear:both;"></div>
</div>
</div>
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
jQuery("input[type='checkbox']#<?php echo $this->get_field_id( 'enable_table_view_option' ); ?>").change(function(event){
    jQuery("input[type='number']#<?php echo $this->get_field_id( 'table_width' ); ?>").prop("disabled",!jQuery(this).prop("checked"));
    jQuery("textarea#<?php echo $this->get_field_id( 'table_shortcode' ); ?>").prop("disabled",!jQuery(this).prop("checked"));
    jQuery("input[type='checkbox'].scpbcfw-select-content-macro-display-field").prop("disabled",!jQuery(this).prop("checked"));
});
jQuery(document).ready(function(){
    jQuery("div.mf2tk-selectable-field").draggable({cursor:"crosshair",revert:true});
    jQuery("div.mf2tk-selectable-field-after").droppable({accept:"div.mf2tk-selectable-field",tolerance:"touch",
        hoverClass:"mf2tk-hover",drop:function(e,u){
            jQuery(this.parentNode).after(u.draggable);
            var o="";
            jQuery("input.mf2tk-selectable-field[type='checkbox']",this.parentNode.parentNode).each(function(i){
                o+=jQuery(this).val()+";";
            });
            jQuery("input.mf2tk-selectable-field-order[type='hidden']",this.parentNode.parentNode).val(o);
    }});
    jQuery("div.mf2tk-selectable-taxonomy").draggable({cursor:"crosshair",revert:true});
    jQuery("div.mf2tk-selectable-taxonomy-after").droppable({accept:"div.mf2tk-selectable-taxonomy",tolerance:"touch",
        hoverClass:"mf2tk-hover",drop:function(e,u){
            jQuery(this.parentNode).after(u.draggable);
            var o="";
            jQuery("input.mf2tk-selectable-taxonomy[type='checkbox']",this.parentNode.parentNode).each(function(i){
                o+=jQuery(this).val()+";";
            });
            jQuery("input.mf2tk-selectable-taxonomy-order[type='hidden']",this.parentNode.parentNode).val(o);
    }});
});
</script>
<?php
    }   # public function form( $instance ) {
    
     public static function &join_arrays( $op, &$arr0, &$arr1 ) {
        $is_arr0 = is_array( $arr0 );
        $is_arr1 = is_array( $arr1 );
        if ( $is_arr0 || $is_arr1 ) {
            if ( $op == 'AND' ) {
                if ( $is_arr0 && $is_arr1 ) { $arr = array_intersect( $arr0, $arr1 ); }
                else if ( $is_arr0 ) { $arr = $arr0; } else { $arr = $arr1; }
            } else {
                if ( $is_arr0 && $is_arr1 ) { $arr = array_unique( array_merge( $arr0, $arr1 ) ); }
                else if ( $is_arr0 ) { $arr = $arr0; } else { $arr = $arr1; }
            }
            return $arr;
        }
        return FALSE;
    }
}   # class Search_Using_Magic_Fields_Widget extends WP_Widget

add_action( 'widgets_init', function() {
        register_widget( 'Search_Using_Magic_Fields_Widget' );
} );

if ( is_admin() ) {
    #error_log( '##### add_action( \'wp_ajax_nopriv_'
    #    . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE . ', ... )' );
    # Use the no privilege version also in privileged mode
    add_action( 'wp_ajax_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE, function() {
        do_action( 'wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE );
    } );
    # This ajax action will generate and return the search form for the given post type
    add_action( 'wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE, function() {
        global $wpdb;
        #error_log( '##### action:wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE . ':$_REQUEST='
        #    . print_r( $_REQUEST, TRUE ) );
        #error_log( '##### action:wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE . ':$_POST='
        #    . print_r( $_POST, TRUE ) );
        if ( !isset( $_POST['mf2tk_get_form_nonce'] ) || !wp_verify_nonce( $_POST['mf2tk_get_form_nonce'],
            Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE ) ) {
            error_log( '##### action:wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE . ':nonce:die' );
            die;
        }
        $option = get_option( $_REQUEST['magic_fields_search_widget_option'] );
        #error_log( '##### action:wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE . ':$option='
        #    . print_r( $option, TRUE ) );
        $number = $_REQUEST['magic_fields_search_widget_number'];
        $selected = $option[$number][$_REQUEST['post_type']];
        $SQL_LIMIT = $option[$number]['maximum_number_of_items'];
        #error_log( '##### action:wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE . '():$selected='
        #    . print_r( $selected, TRUE ) );
        # first do selected taxonomies
        $taxonomies = array();
        $wp_taxonomies = get_taxonomies( '', 'objects' );
        foreach ( $wp_taxonomies as &$taxonomy ) {
            #error_log( '##### $taxonomy=' . print_r( $taxonomy, TRUE ) );
            if ( !in_array( $_REQUEST['post_type'], $taxonomy->object_type ) ) { continue; }
            $tax_name = ( $taxonomy->hierarchical ? 'tax-cat-' : 'tax-tag-' ) . $taxonomy->name;
            if ( in_array( $tax_name, $selected ) ) { $taxonomies[$tax_name] =& $taxonomy; }
        }
        unset ( $taxonomy );
        foreach ( $selected as $tax_name ) {
            if ( !array_key_exists( $tax_name, $taxonomies ) ) { continue; }
            $taxonomy =& $taxonomies[$tax_name];
            #error_log( '##### $taxonomy=' . print_r( $taxonomy, TRUE ) );
            $tax_type = ( $taxonomy->hierarchical ) ? 'tax-cat-' : 'tax-tag-';
            $results = $wpdb->get_results( <<<EOD
                SELECT x.term_taxonomy_id, t.name, COUNT(*) count
                    FROM $wpdb->term_relationships r, $wpdb->term_taxonomy x, $wpdb->terms t, $wpdb->posts p
                    WHERE r.term_taxonomy_id = x.term_taxonomy_id AND x.term_id = t.term_id AND r.object_id = p.ID
                        AND x.taxonomy = "$taxonomy->name" AND p.post_type = "$_REQUEST[post_type]"
                        GROUP BY x.term_taxonomy_id ORDER BY count DESC LIMIT $SQL_LIMIT
EOD
                , OBJECT );
            if ( !$results ) { continue; }
            #error_log( '##### $results=' . print_r( $results, TRUE ) );
?>
<div class="scpbcfw-search-fields" style="padding:5px 10px;border:2px solid black;margin:5px;">
<span style="font-size=16px;font-weight:bold;float:left;"><?php echo $taxonomy->name ?>:</span>
<button class="scpbcfw-display-button" style="font-size:12px;font-weight:bold;padding:3px;float:right;">Open</button>
<div style="clear:both;"></div>
<div class="scpbcfw-search-field-values" style="display:none;">
<?php
            foreach ( $results as $result ) {
?>
<input type="checkbox" id="<?php echo $meta_key; ?>" name="<?php echo $tax_type . $taxonomy->name; ?>[]"
    value="<?php echo $result->term_taxonomy_id; ?>"><?php echo "$result->name ($result->count)"; ?><br>
<?php
            }   # foreach ( $results as $result ) {
            if ( count( $results ) == $SQL_LIMIT ) {
                # too many distinct terms for this custom taxonomy so allow user to also manually enter search value
?>
<input id="<?php $meta_key . Search_Using_Magic_Fields_Widget::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    name="<?php echo "{$tax_type}{$taxonomy->name}" . Search_Using_Magic_Fields_Widget::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    class="for-select" type="text" style="width:90%;" placeholder="--Enter Search Value--">
<?php
            }
?>
</div>
</div>
<?php
        }   # foreach ( get_taxonomies( '', 'objects' ) as $taxonomy ) {
        unset( $taxonomy );
        # now do the selected fields
        $fields = $wpdb->get_results( 'SELECT name, type, label FROM ' . MF_TABLE_CUSTOM_FIELDS
            . " WHERE post_type = '$_REQUEST[post_type]' ORDER BY custom_group_id, display_order", OBJECT_K );
        $fields['pst-std-post_content'] = (object) array( 'type' => 'multiline', 'label' => 'Post Content' );
        foreach ( $selected as $meta_key ) {
            #error_log( '##### $meta_key=' . $meta_key );
            if ( !array_key_exists( $meta_key, $fields ) ) { continue; }
            $field =& $fields[$meta_key];
?>
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
            $results = $wpdb->get_results( <<<EOD
                SELECT meta_value, COUNT(*) count FROM
                    ( SELECT distinct m.meta_value, m.post_id FROM $wpdb->postmeta m, $wpdb->posts p
                        WHERE m.post_id = p.ID AND m.meta_key = "$meta_key" AND p.post_type = "$_REQUEST[post_type]" ) d
                        GROUP BY meta_value ORDER BY count DESC LIMIT $SQL_LIMIT
EOD
                , OBJECT_K );
            $values = array();   # to be used by serialized fields
            $numeric = TRUE;
            foreach ( $results as $meta_value => $result ) {
                if ( !$meta_value ) { continue; }
                #error_log( '##### ' . $meta_key . ': ' . print_r( $result, TRUE ) );
                if ( $field->type === 'related_type' ) {
                    $value = get_the_title( $meta_value );
                } else if ( $field->type === 'image_media' ) {
                    $value = $wpdb->get_col( "SELECT guid FROM $wpdb->posts WHERE ID = '$meta_value'" );
                    if ( $value ) { $value = substr( $value[0], strrpos( $value[0], '/' ) + 1 ); }
                    else { $value = ''; }
                } else if ( $field->type === 'image' ) {
                    # for Magic Fields 2 porprietary image data strip time stamp prefix
                    $value = substr( $meta_value, 10 );
                } else if ( $field->type === 'alt_related_type' || $field->type === 'checkbox_list' 
                    || $field->type === 'dropdown' || $field->type === 'alt_dropdown' ) {
                    # These are the serialized fields. Since individual values may be embedded in multiple rows
                    # two passes will be needed - one to accumulate the counts and another to display the counts
                    #error_log( '##### $type === \'alt_related_type\' $result=' . print_r( $result, TRUE ) );
                    $entries = unserialize( $meta_value );
                    for ( $i = 0; $i < $result->count; $i++ ) { $values = array_merge( $values, $entries ); }
                    #error_log( '##### $type === \'alt_related_type\' $values=' . print_r( $values, TRUE ) );
                    continue;   # skip display which will be done later on a second pass
                } else {
                    $value = $meta_value;
                }
?>
<input type="checkbox" id="<?php echo $meta_key; ?>" name="<?php echo $meta_key; ?>[]"
    value="<?php echo $meta_value; ?>"><?php echo "$value ($result->count)"; ?><br>
<?php
                if ( $field->type == 'textbox' ) {
                    if ( !is_numeric( $meta_value ) ) { $numeric = FALSE; }
                }
            }   # foreach ( $results as $result ) {
            # now do second pass on the serialized fields
            if ( $values ) {
                #error_log( '##### action:wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE . ':$values='
                #    . print_r( $values, TRUE ) );
                # get count of individual values
                $values = array_count_values( $values );
                arsort( $values, SORT_NUMERIC );
                #error_log( '##### action:wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE . ':$values='
                #    . print_r( $values, TRUE ) );
                foreach ( $values as $key => $value ) {   # $key is value and $value is count
?>
<input type="checkbox" id="<?php echo $meta_key; ?>" name="<?php echo $meta_key; ?>[]"
<?php
                    # "serialize" the value - this is what the value would look like in a serialized array
                    echo 'value=\';s:' . strlen( $key ) . ':"' . $key . '";\'>';
                    # for alt_related_type use post title instead of post id
                    if ( $field->type === 'alt_related_type' ) { echo get_the_title( $key ) . "($value)<br>"; }
                    else { echo "$key ($value)<br>"; }
                }   # foreach ( $values as $key => $value) {
            }   # if ( $values ) {
            if ( count( $results ) == $SQL_LIMIT && ( $field->type !== 'related_type'
                && $field->type !== 'alt_related_type' && $field->type !== 'image_media' ) ) {
                # for these types also allow user to manually enter search values
?>
<input id="<?php echo $meta_key . Search_Using_Magic_Fields_Widget::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    name="<?php echo $meta_key . Search_Using_Magic_Fields_Widget::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    class="for-select" type="text" style="width:90%;" placeholder="--Enter Search Value--">
<?php
            }
            if ( $field->type == 'slider' || $field->type == 'datepicker' || ( $field->type == 'textbox' && $numeric ) ) {
                # only show minimum/maximum input textbox for numeric and date custom fields
?>
<h4>Range Search</h4>
<input id="<?php echo $meta_key . Search_Using_Magic_Fields_Widget::OPTIONAL_MINIMUM_VALUE_SUFFIX; ?>"
    name="<?php echo $meta_key . Search_Using_Magic_Fields_Widget::OPTIONAL_MINIMUM_VALUE_SUFFIX; ?>"
    class="for-select" type="text" style="width:90%;" placeholder="--Enter Minimum Value--">
<input id="<?php echo $meta_key . Search_Using_Magic_Fields_Widget::OPTIONAL_MAXIMUM_VALUE_SUFFIX; ?>"
    name="<?php echo $meta_key . Search_Using_Magic_Fields_Widget::OPTIONAL_MAXIMUM_VALUE_SUFFIX; ?>"
    class="for-select" type="text" style="width:90%;" placeholder="--Enter Maximum Value--">
<?php
            }
?>
</div>
</div>
<?php
        }   # foreach ( $fields as $meta_key => $field ) {
        unset( $field );
?>
<script type="text/javascript">
jQuery("form#search-using-magic-fields-<?php echo $number; ?> div.magic-field-parameter select").change(function(){
    if(jQuery("option:selected:last",this).text()=="--Enter New Search Value--"){
        jQuery(this).css("display","none");
        var input=jQuery("input",this.parentNode).css("display","inline").val("").get(0);
        input.focus();
        input.select();
    }
});
jQuery("form#search-using-magic-fields-<?php echo $number; ?> div.magic-field-parameter input.for-select").change(function(){
    var value=jQuery(this).val();
    var select=jQuery("select",this.parentNode);
    jQuery("option:last",select).prop("selected",false);
    if(value){
        var first=jQuery("option:first",select).detach();
        select.prepend('<option value="'+value+'" selected>'+value+'</option>');
        select.prepend(first);
        jQuery(this).val("");
    }
    select.css("display","inline");
    jQuery(this).css("display","none");
});
jQuery("form#search-using-magic-fields-<?php echo $number; ?> div.magic-field-parameter input.for-select")
    .blur(function(){
    jQuery(this).change();
});
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
    } );   # add_action( 'wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE,
} else {
    add_action( 'wp_enqueue_scripts', function() {
        wp_enqueue_script( 'jquery' );
    } );
    add_action( 'parse_query', function( &$query ) {
        if ( !$query->is_main_query() || !array_key_exists( 'magic_fields_search_form', $_REQUEST ) ) { return; }
        #error_log( '##### action:parse_query():$_REQUEST=' . print_r( $_REQUEST, true ) );
        #error_log( '##### action:parse_query():$args=' . print_r( $query, true ) );
        $option = get_option( $_REQUEST['magic_fields_search_widget_option'] );
        #error_log( '##### action:parse_query():$option=' . print_r( $option, true ) );
        $number = $_REQUEST['magic_fields_search_widget_number'];
        if ( isset( $option[$number]['set_is_search'] ) ) { $query->is_search = true; }
    } );
	add_filter( 'posts_where', function( $where, &$query ) {
        global $wpdb;
        if ( !$query->is_main_query() || !array_key_exists( 'magic_fields_search_form', $_REQUEST ) ) { return $where; }
        #error_log( '##### filter:posts_where():$query=' . print_r( $query, true ) );
        #error_log( '##### filter:posts_where():$_REQUEST=' . print_r( $_REQUEST, true ) );
        $and_or = $_REQUEST['magic-fields-search-and-or'] == 'and' ? 'AND' : 'OR';
        # first get taxonomy name to term_taxonomy_id transalation table in case we need the translations
        $results = $wpdb->get_results( <<<EOD
            SELECT x.taxonomy, t.name, x.term_taxonomy_id
                FROM $wpdb->term_taxonomy x, $wpdb->terms t
                WHERE x.term_id = t.term_id
EOD
            , OBJECT );
        $term_taxonomy_ids = array();
        foreach ( $results as $result ) {
            $term_taxonomy_ids[$result->taxonomy][strtolower( $result->name)] = $result->term_taxonomy_id;
        }
        #error_log( '##### filter:posts_where:$term_taxonomy_ids=' . print_r( $term_taxonomy_ids, TRUE ) );
        # merge optional text values into the checkboxes array
        $suffix_len = strlen( Search_Using_Magic_Fields_Widget::OPTIONAL_TEXT_VALUE_SUFFIX );
        foreach ( $_REQUEST as $index => &$request ) {
            #error_log( '##### filter:posts_where:$index=' . $index );
            #error_log( '##### filter:posts_where:$request=' . print_r ( $request, TRUE ) );
            if ( $request
                && substr_compare( $index, Search_Using_Magic_Fields_Widget::OPTIONAL_TEXT_VALUE_SUFFIX, -$suffix_len ) === 0 ) {
                # using raw user input data directly so we need to be careful - possible sql injection
                $request = str_replace( '\'', '', $request );
                $index = substr( $index, 0, strlen( $index ) - $suffix_len );
                if ( is_array( $_REQUEST[$index] ) || !array_key_exists( $index, $_REQUEST ) ) {
                    if ( substr_compare( $index, 'tax-', 0, 4 ) === 0 ) {
                        # for taxonomy values must replace the value with the corresponding term_taxonomy_id
                        $tax_name = substr( $index, 8 );
                        if ( !array_key_exists( $tax_name, $term_taxonomy_ids )
                            || !array_key_exists( strtolower( $request ), $term_taxonomy_ids[$tax_name] ) ) {
                            # kill the original request
                            $request = NULL;
                            continue;
                        }
                        $request = $term_taxonomy_ids[$tax_name][strtolower( $request )];
                    }
                    $_REQUEST[$index][] = $request;
                }    
                # kill the original request
                $request = NULL;
            }
        }
        unset( $request );
        # merge optional min/max values for numeric custom fields into the checkboxes array
        $suffix_len = strlen( Search_Using_Magic_Fields_Widget::OPTIONAL_MINIMUM_VALUE_SUFFIX );
        foreach ( $_REQUEST as $index => &$request ) {
            if ( $request && ( ( $is_min
                = substr_compare( $index, Search_Using_Magic_Fields_Widget::OPTIONAL_MINIMUM_VALUE_SUFFIX, -$suffix_len ) === 0 )
                || substr_compare( $index, Search_Using_Magic_Fields_Widget::OPTIONAL_MAXIMUM_VALUE_SUFFIX, -$suffix_len ) === 0
            ) ) {
                $index = substr( $index, 0, strlen( $index ) - $suffix_len );
                if ( is_array( $_REQUEST[$index] ) || !array_key_exists( $index, $_REQUEST ) ) {
                    $_REQUEST[$index][] = array( 'operator' => $is_min ? 'minimum' : 'maximum', 'value' => $request );
                }
                # kill the original request
                $request = NULL;
            }
        }
        unset( $request );
        #error_log( '##### filter:posts_where:$_REQUEST=' . print_r( $_REQUEST, TRUE ) );
        #error_log( '##### filter:posts_where:$where=' . $where );
        # first do custom fields
        $non_field_keys = array( 'magic_fields_search_form', 'magic_fields_search_widget_option',
            'magic_fields_search_widget_number', 'magic-fields-search-and-or', 'magic-fields-show-using-macro', 'post_type' );
        $sql = '';
        foreach ( $_REQUEST as $key => $values ) {
            if ( in_array( $key, $non_field_keys ) ) { continue; }
            $prefix = substr( $key, 0, 8 );
            if ( $prefix == 'tax-cat-' || $prefix == 'tax-tag-' || $prefix == 'pst-std-' ) { continue; }
            if ( !is_array( $values) ) {
                if ( $values ) { $values = array( $values ); }
                else { $values = array(); }
            }
            if ( !$values || $values[0] === 'no-selection' ) { continue; }
            if ( $sql ) { $sql .= " $and_or "; }
            $sql .= " EXISTS ( SELECT * FROM $wpdb->postmeta w INNER JOIN " . MF_TABLE_POST_META
                . ' m ON w.meta_id = m.meta_id WHERE ( ';
            $sql3 = '';   # holds meta_value min/max sql
            foreach ( $values as $value ) {
                if ( is_array( $value ) ) {
                    # check for minimum/maximum operation
                    if ( ( $is_min = $value['operator'] == 'minimum' ) || $value['operator'] == 'maximum' ) {
                        if ( $sql3 ) { $sql3 .= ' AND '; }
                        if ( !is_numeric( $value['value'] ) ) { $value['value'] = "'$value[value]'"; }
                        if ( $is_min ) {
                            $sql3 .= "( w.meta_key = '$key' AND w.meta_value >= $value[value] )";
                        } else if ( $value['operator'] == 'maximum' ) {
                            $sql3 .= "( w.meta_key = '$key' AND w.meta_value <= $value[value] )";
                        }
                    }
                    continue;
                }
                 if ( $value !== $values[0] ) { $sql .= ' OR '; }
                $sql .= "( w.meta_key = '$key' AND w.meta_value LIKE '%$value%' )";
            }   # foreach ( $values as $value ) {
            if ( $sql3 ) {
                if ( substr_compare( $sql, 'WHERE ( ', -8, 8 ) == 0 ) { $sql .= $sql3; }
                else { $sql .= ' OR ( ' . $sql3 . ' ) '; }
            }
            $sql .= ' ) AND w.post_id = p.ID )';
        }   #  foreach ( $_REQUEST as $key => $values ) {
        if ( $sql ) {
            $sql = "SELECT p.ID FROM $wpdb->posts p WHERE p.post_type = '$_REQUEST[post_type]' AND p.post_status = 'publish' "
                . " AND ( $sql ) ";
            #error_log( '##### filter:posts_where:meta $sql=' . $sql );
            $ids0 = $wpdb->get_col( $sql );
            if ( $and_or == 'AND' && !$ids0 ) { return ' AND 1 = 2 '; }
        } else {
            $ids0 = FALSE;
        }
        # now do taxonomies
        $sql = '';
        foreach ( $_REQUEST as $key => $values ) {
            if ( in_array( $key, $non_field_keys ) ) { continue; }
            $prefix = substr( $key, 0, 8 );
            if ( $prefix != 'tax-cat-' && $prefix != 'tax-tag-' ) { continue; }
            if ( !is_array( $values) ) {
                if ( $values ) { $values = array( $values ); }
                else { $values = array(); }
            }
            if ( !$values || $values[0] === 'no-selection' ) { continue; }
            $sql2 = '';
            foreach ( $values as $value ) {
                if ( $sql2 ) { $sql2 .= ' OR '; }
                $sql2 .= "term_taxonomy_id = $value"; 
            }   # foreach ( $values as $value ) {
            if ( $sql ) { $sql .= " $and_or "; }
            $sql .= " EXISTS ( SELECT * FROM $wpdb->term_relationships WHERE ( $sql2 ) AND object_id = p.ID )";
        }   # foreach ( $_REQUEST as $key => $values ) {
        if ( $sql ) {
            $sql = "SELECT p.ID FROM $wpdb->posts p WHERE p.post_type = '$_REQUEST[post_type]' AND p.post_status = 'publish' "
            . " AND ( $sql ) ";
            #error_log( '##### filter:posts_where:tax $sql=' . $sql );
            $ids1 = $wpdb->get_col( $sql );
            if ( $and_or == 'AND' && !$ids1 ) { return ' AND 1 = 2 '; }
        } else {
            $ids1 = FALSE;
        }
        $ids = Search_Using_Magic_Fields_Widget::join_arrays( $and_or, $ids0, $ids1 );
        if ( array_key_exists( 'pst-std-post_content', $_REQUEST ) && $_REQUEST['pst-std-post_content'] ) {
            #&& $_REQUEST['pst-std-post_content'] != '*enter search value*' ) {
            $sql = <<<EOD
                SELECT ID FROM $wpdb->posts WHERE post_type = "$_REQUEST[post_type]" AND post_status = "publish"
                    AND ( post_content  LIKE "%{$_REQUEST['pst-std-post_content']}%"
                        OR post_title   LIKE "%{$_REQUEST['pst-std-post_content']}%"
                        OR post_excerpt LIKE "%{$_REQUEST['pst-std-post_content']}%" )
EOD;
            $ids2 = $wpdb->get_col( $sql );
            #error_log( '##### filter:posts_where:post_content $sql=' . $sql );
            if ( $and_or == 'AND' && !$ids2 ) { return ' AND 1 = 2 '; }
        } else {
            $ids2 = FALSE;
        }
        $ids = Search_Using_Magic_Fields_Widget::join_arrays( $and_or, $ids, $ids2 );
        if ( is_array ( $ids ) ) {
            if ( $ids ) {
                $where = ' AND ID IN ( ' . implode( ',', $ids  ) . ' ) ';
            } else {
                $where = ' AND 1 = 2 ';
            }
        } else {
            $where = " AND ( post_type = '$_REQUEST[post_type]' AND post_status = 'publish' ) ";
        }
        #error_log( '##### filter:posts_where:$where=' . $where );
        return $where;
    }, 10, 2 );   #	add_filter( 'posts_where', function( $where, $query ) {
    # add null 'query_string' filter to force parse_str() call in WP::build_query_string() - otherwise name gets set ? TODO: why?
    add_filter( 'query_string', function( $arg ) { return $arg; } );
    if ( isset( $_REQUEST['magic-fields-show-using-macro'] ) && $_REQUEST['magic-fields-show-using-macro'] === 'use macro' ) {
        add_action( 'wp_enqueue_scripts', function() {
            wp_enqueue_style( 'search_results_table', plugins_url( 'search-results-table.css', __FILE__ ) );
        } );
        add_action( 'template_redirect', function() {
            global $wp_query;
            # in this case a template is dynamically constructed and returned
            if ( !class_exists( 'Magic_Fields_2_Toolkit_Dumb_Shortcodes' ) ) {
                include_once( dirname( __FILE__ ) . '/magic-fields-2-dumb-shortcodes-kai.php' );
            }
            if ( !class_exists( 'Magic_Fields_2_Toolkit_Dumb_Macros' ) ) {
                include_once( dirname( __FILE__ ) . '/magic-fields-2-dumb-macros.php' );
            }
            #error_log( '##### action:template_redirect():$wp_query=' . print_r( $wp_query, true ) );
            # get the list of posts
            $posts = array_map( function( $post ) { return $post->ID; }, $wp_query->posts );
            #error_log( '##### action:template_redirect():$_REQUEST=' . print_r( $_REQUEST, true ) );
            $option = get_option( $_REQUEST['magic_fields_search_widget_option'] );
            #error_log( '##### action:template_redirect():$option=' . print_r( $option, true ) );
            $number = $_REQUEST['magic_fields_search_widget_number'];
            # get the applicable fields from the options for this widget
            $fields = $option[$number]['show-' . $_REQUEST['post_type']];
            if ( !$fields ) {
                $fields = $option[$number][$_REQUEST['post_type']];
            }
            # fix taxonomy names and remove pst-std- fields;
            $fields = array_filter( array_map( function( $field ) { 
                if ( substr_compare( $field, 'tax-cat-', 0, 8, false ) === 0
                    || substr_compare( $field, 'tax-tag-', 0, 8, false ) === 0 ) {
                    return substr( $field, 8 );
                } else if ( substr_compare( $field, 'pst-std-', 0, 8, false ) === 0 ) {
                    return false;
                } else {
                    return $field . '<*,*>';
                }
            }, $fields ) );
            $macro = $option[$number]['table_shortcode'];
            if ( empty( $macro ) ) { $macro = Search_Using_Magic_Fields_Widget::DEFAULT_CONTENT_MACRO; }
            $macro = htmlspecialchars_decode( $macro );
            if ( $table_width = $option[$number]['table_width'] ) { $table_width = " style='width:{$table_width}px;'"; }
            #error_log( '##### action:template_redirect():$macro=' . $macro );
            #error_log( '##### action:template_redirect():$posts='  . print_r( $posts,  true ) );
            #error_log( '##### action:template_redirect():$fields=' . print_r( $fields, true ) );
            #error_log( '##### action:template_redirect():$table_width=' . $table_width );
            $post    = $posts[0];
            $posts   = implode( ',', $posts );
            $fields  = implode( ';', $fields );
            # build the main content from the above parts
            # the macro has parameters: posts - a list of post ids, fields - a list of field names, a_post - any valid post id,
            # and post_type - the post type
            $content = <<<EOD
[show_macro posts="$posts" fields="$fields" a_post="$post" post_type="$_REQUEST[post_type]" table_width="$table_width"]
$macro
[/show_macro]
EOD;
            # error_log( '##### action:template_redirect():$content=' . print_r( $content,  true ) );
            # finally output all the HTML
            get_header();
            echo do_shortcode( $content );
            get_footer();
            exit();
        } );
    }
}
?>