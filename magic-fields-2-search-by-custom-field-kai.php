<?php
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
 
if ( is_admin() ) {
    add_action( 'admin_enqueue_scripts', function() {
        wp_enqueue_script( 'jquery-ui-draggable' );
        wp_enqueue_script( 'jquery-ui-droppable' );
    } );
}

class Search_Using_Magic_Fields_Widget extends WP_Widget {
    const GET_FORM_FOR_POST_TYPE = 'get_form_for_post_type';         # the AJAX action to get the form for post type selected by user
    const SQL_LIMIT = '25';                                          # maximum number of items to show per custom field
    #const SQL_LIMIT = '2';                                          # TODO: this limit for testing only replace with above
    const OPTIONAL_TEXT_VALUE_SUFFIX = '-mf2tk-optional-text-value'; # suffix for additional text input for a custom field
    const OPTIONAL_MINIMUM_VALUE_SUFFIX = '-stcfw-minimum-value';    # suffix to append to optional minimum/maximum value text 
    const OPTIONAL_MAXIMUM_VALUE_SUFFIX = '-stcfw-maximum-value';    #     inputs for a numeric search field
    const DEFAULT_CONTENT_MACRO = <<<'EOD'
<div style="width:99%;overflow:auto;">
<div class="scpbcfw-result-container"$#table_width#>
<table class="scpbcfw-result-table tablesorter">
[show_custom_field post_id="$#a_post#" field="__post_title;$#fields#"
    before="<span style='display:none;'>"
    after="</span>"
    field_before="<th class='scpbcfw-result-table-head-<!--$field-->' style='padding:5px;'><!--$Field-->"
    field_after="</th>
    post_before="<thead><tr>"
    post_after="</tr></thead>"
]
<tbody>
[show_custom_field post_id="$#posts#" field="__post_title;$#fields#"
    separator=", "
    field_before="<td class='scpbcfw-result-table-detail-<!--$field-->' style='padding:5px;'>"
    field_after="</td>
    post_before="<tr>"
    post_after="</tr>"
    filter="url_to_link2"
]
</tbody>
</table>
</div>
</div>
EOD;
    const SEARCH_RESULTS_FIELDS_AND_FILTERS = 'magic_fields_2_toolkit_search_results_fields_and_filters';
    const SEARCH_RESULTS_TEMPLATE_PREFIX = 'search-result-template-for-';
    const TEMPLATE_NOT_FOUND = 'no template found - the generic will be used';
    
	public function __construct() {
		parent::__construct( 'search_magic_fields', __( 'Search using Magic Fields', 'mf2tk' ),
            [ 'classname' => 'search_magic_fields_widget',
                'description' => __( "Search for Custom Posts using Magic Fields" ), 'mf2tk' ]
        );
	}

    # widget() emits a form to select the post type; after user selects a post type
    # an AJAX request is sent back to retrieve the post type specific search form
    
	public function widget( $args, $instance ) {
        global $wpdb;
        extract( $args );
        # initially show only post type selection form after post type selected use AJAX to retrieve post specific form
?>
<form id="search-using-magic-fields-<?php echo $this->number; ?>" class="scpbcfw-search-fields-form" method="get"
    action="<?php echo esc_url( home_url( '/' ) ); ?>">
<input id="magic_fields_search_form" name="magic_fields_search_form" type="hidden" value="magic-fields-search">
<input id="magic_fields_search_widget_option" name="magic_fields_search_widget_option" type="hidden"
    value="<?php echo $this->option_name; ?>">
<input id="magic_fields_search_widget_number" name="magic_fields_search_widget_number" type="hidden"
    value="<?php echo $this->number; ?>">
<div id="scpbcfw-search-fields-help">
<a href="http://magicfields17.wordpress.com/magic-fields-2-search-0-4-1/#user" target="_blank">help</a>
</div>
<h2 class="scpbcfw-search-fields-title">Search:</h2>
<div class="magic-field-parameter">
<h4 class="scpbcfw-search-fields-post-type-title">Select post type:</h4>
<select id="post_type" name="post_type" class="scpbcfw-search-fields-post-type" required>
<option value="no-selection">--select post type--</option>
<?php
        # get data for the administrator selected post types
        $selected_types = '"' . implode( '", "', array_diff( array_keys( $instance ),
            array( 'maximum_number_of_items', 'set_is_search', 'enable_table_view_option', 'table_shortcode', 'table_width' ) ) )
            . '"';
        $SQL_LIMIT = self::SQL_LIMIT;
        $types = $wpdb->get_results( <<<EOD
            SELECT post_type, COUNT(*) count FROM $wpdb->posts
                WHERE post_type IN ( $selected_types ) AND post_status = "publish" 
                GROUP BY post_type ORDER BY count DESC LIMIT $SQL_LIMIT
EOD
            , OBJECT_K );
        foreach ( $types as $name => $type ) {
?>      
<option class="real_post_type" value="<?php echo $name; ?>"><?php echo "$name ($type->count)"; ?></option>
<?php
        }   # foreach ( $types as $name => $type ) {
?>
</select>
</div>
<div id="magic-fields-parameters"></div>
<div id="magic-fields-submit-box" style="display:none">
<div class="scpbcfw-search-fields-and-or-box">
<div>
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
<div class="scpbcfw-search-fields-alt-format">
<input type="checkbox" name="magic-fields-show-using-macro" class="scpbcfw-search-fields-alt-format" value="use macro">
Show search results in alternate format:
</div>
<?php
        }
?>
</div>
<div class="scpbcfw-search-fields-submit">
<input id="magic-fields-reset" type="button" value="Reset" disabled>
<input id="magic-fields-search" type="submit" value="Start Search" disabled>
</div>
</div>
</form>
<script type="text/javascript">
jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> select.scpbcfw-search-fields-post-type")
    .change(function(){
    var postType=jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> select#post_type option:selected").val();
    if(postType==="no-selection"){return;}
    jQuery.post(
        '<?php echo admin_url( 'admin-ajax.php' ); ?>',{
            action:'<?php echo Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE; ?>',
            mf2tk_get_form_nonce:'<?php echo wp_create_nonce( Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE ); ?>',
            post_type:postType,
            magic_fields_search_widget_option:
                jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> input#magic_fields_search_widget_option")
                    .val(),
            magic_fields_search_widget_number:
                jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> input#magic_fields_search_widget_number")
                    .val()
        },
        function(response){
            jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> div#magic-fields-parameters").html(response);
            jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> input#magic-fields-reset")
                .prop("disabled",false);
            jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> input#magic-fields-search")
                .prop("disabled",false);
            jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> div#magic-fields-submit-box")
                .css("display","block");
        }
    );
});
jQuery(document).ready(function(){
    if(jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> select.scpbcfw-search-fields-post-type \
        option.real_post_type").length===1){
        jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> select.scpbcfw-search-fields-post-type \
            option.real_post_type").prop("selected",true);
        jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> select.scpbcfw-search-fields-post-type").change();
        jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> select.scpbcfw-search-fields-post-type")
            .parent("div").css("display","none");
    }
    jQuery("form#search-using-magic-fields-<?php echo $this->number; ?> select.scpbcfw-search-fields-post-type")
        .mousedown(function(){jQuery(this).val("no-selection");});
});
</script>
<?php
	}   # public function widget( $args, $instance ) {
    
    public function update( $new, $old ) {
        if ( $new['select_post_type'] !== 'generic' ) {
            $new['table_shortcode'] = $old['table_shortcode'];
        }
        unset( $new['select_post_type'] );
        #return array_map( function( $values ) {
        #    return is_array( $values) ? array_map( strip_tags, $values ) : strip_tags( $values );
        #}, $new );
        return $new;
    }   # public function update( $new, $old ) {
    
    # form() emits a form for the administrator to select the post types and custom fields that the user will be allowed to search
    
    public function form( $instance ) {
        global $wpdb;
        # show the configuration form to select custom fields for the given post type
?>
<div class="scpbcfw-admin-search-fields-help">
    <a href="http://magicfields17.wordpress.com/magic-fields-2-search-0-4-1/#administrator" target="_blank">help</a>
</div>
<h4 class="scpbcfw-admin-search-fields-title">Select Search Fields and Content Macro Display Fields for:</h4>
<p style="clear:both;margin:0px;">
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
        $wp_taxonomies = get_taxonomies( '', 'objects' );
        # do all post types
        foreach ( $types as $name => $type ) {
            $selected      = array_key_exists( $name, $instance ) ? $instance[$name] : [];
            $show_selected = array_key_exists( 'show-' . $name, $instance ) ? $instance['show-' . $name] : [];
?>
<div class="scpbcfw-admin-search-fields">
<div class="scpbcfw-admin-search-fields-name"><?php echo "$name ($type->count)"; ?>:</div>
<div class="scpbcfw-admin-search-fields-display-button">Open</div>
<div style="clear:both;"></div>
<div class="scpbcfw-search-field-values" style="display:none;">
<!-- before drop point -->
<div><div class="mf2tk-selectable-field-after"></div></div>
<?php
            # setup taxonomies
            $the_taxonomies = array();
            foreach ( $db_taxonomies as &$db_taxonomy ) {
                if ( $db_taxonomy->post_type != $name ) { continue; }
                $wp_taxonomy =& $wp_taxonomies[$db_taxonomy->taxonomy];
                $tax_name = ( $wp_taxonomy->hierarchical ? 'tax-cat-' : 'tax-tag-' ) . $wp_taxonomy->name;
                $the_taxonomies[$tax_name] =& $db_taxonomy;
            }
            unset( $db_taxonomy, $wp_taxonomy );
            # setup custom fields, post content and post author
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
                                AND f.type != "alt_table" AND f.type != "alt_template"
                        GROUP BY f.name, m.post_id ) d
                    GROUP BY name ORDER BY count DESC LIMIT $SQL_LIMIT
EOD
                , OBJECT_K );
            # add the post_content field giving it a special name since it requires special handling
            $fields['pst-std-post_content'] 
                = (object) array( 'label' => 'Post Content', 'type' => 'multiline', 'count' => $type->count );
            $sql = <<<EOD
                SELECT COUNT(*) FROM $wpdb->posts p
                    WHERE p.post_type = "$name" AND p.post_status = "publish" AND p.post_author IS NOT NULL
EOD;
            # add the post_author field giving it a special name since it requires special handling
            $fields['pst-std-post_author']
                = (object) array( 'label' => 'Author', 'type' => 'author', 'count' => $wpdb->get_var( $sql ) );
            # handle deleted fields and newly created fields
            $previous = array_filter( array_merge(
                !empty( $instance['tax-order-' . $name] ) ? explode( ';', $instance['tax-order-' . $name] ) : array(),
                !empty( $instance['order-' . $name] ) ? explode( ';', $instance['order-' . $name] ) : array()
            ) );
            $current = array_merge( array_keys( $the_taxonomies ), array_keys( $fields ) );
            $previous = array_intersect( $previous, $current );
            $new = array_diff( $current, $previous );
            $current = array_merge( $previous, $new );
            foreach ( $current as $meta_key ) {
?>
<div class="mf2tk-selectable-field">
<?php
                if ( ( ( $prefix = substr( $meta_key, 0, 8 ) ) === 'tax-cat-' || $prefix === 'tax-tag-' )
                    && array_key_exists( $meta_key, $the_taxonomies ) ) {
                    $tax_name = $meta_key;
                    $db_taxonomy =& $the_taxonomies[$tax_name];
                    $wp_taxonomy = $wp_taxonomies[$db_taxonomy->taxonomy];
                    $tax_type = ( $wp_taxonomy->hierarchical ) ? 'tax-cat-' : 'tax-tag-';
                    $tax_label = ( $wp_taxonomy->hierarchical ) ? ' (category)' : ' (tag)';
?>
    <input type="checkbox"
        class="mf2tk-selectable-field" 
        id="<?php echo $this->get_field_id( $name ); ?>"
        name="<?php echo $this->get_field_name( $name ); ?>[]"
        value="<?php echo $tax_name; ?>"
        <?php if ( $selected && in_array( $tax_name, $selected ) ) { echo ' checked'; } ?>>
    <input type="checkbox"
        id="<?php echo $this->get_field_id( 'show-' . $name ); ?>"
        class="scpbcfw-select-content-macro-display-field"
        name="<?php echo $this->get_field_name( 'show-' . $name ); ?>[]"
        value="<?php echo $tax_name; ?>"
        <?php if ( $show_selected && in_array( $tax_name, $show_selected ) ) { echo ' checked'; } ?>
        <?php if ( $instance && !isset( $instance['enable_table_view_option'] ) ) { echo 'disabled'; } ?>>
        <?php echo "{$wp_taxonomy->label}{$tax_label} ($db_taxonomy->count)"; ?>
<?php
                }   # if ( ( ( $prefix = substr( $meta_key, 0, 8 ) ) === 'tax-cat-' || $prefix === 'tax-tag-' )
                else {
                    $field =& $fields[$meta_key];
                    if ( substr_compare( $meta_key, 'mf2tk_key', -9 ) === 0 ) { continue; }
?>
    <input type="checkbox" class="mf2tk-selectable-field" id="<?php echo $this->get_field_id( $name ); ?>"
        name="<?php echo $this->get_field_name( $name ); ?>[]" value="<?php echo $meta_key; ?>"
        <?php if ( $selected && in_array( $meta_key, $selected ) ) { echo ' checked'; } ?>>
    <input type="checkbox" id="<?php echo $this->get_field_id( 'show-' . $name ); ?>"
        name="<?php echo $this->get_field_name( 'show-' . $name ); ?>[]"
        <?php if ( $field->type !== 'multiline' && $field->type !== 'markdown_editor' ) {
            echo 'class="scpbcfw-select-content-macro-display-field"'; } ?>
        value="<?php echo $meta_key; ?>" <?php if ( $show_selected && in_array( $meta_key, $show_selected ) ) { echo ' checked'; } ?>
        <?php if ( ( $instance && !isset( $instance['enable_table_view_option'] ) ) || $field->type === 'multiline'
            || $field->type === 'markdown_editor' ) { echo 'disabled'; } ?>>
        <?php echo "$field->label (field) ($field->count)"; ?>
<?php
                }   # if ( true ) {
?>
    <!-- a drop point -->
    <div class="mf2tk-selectable-field-after"></div>
</div>
<?php
            }   # foreach ( $current as $meta_key ) {
?>
<!-- hidden field to hold field order for each post type -->
<input type="hidden" class="mf2tk-selectable-field-order" id="<?php echo $this->get_field_id( 'order-' . $name ); ?>"
    name="<?php echo $this->get_field_name( 'order-' . $name ); ?>"
    value="<?php echo isset( $instance['order-' . $name] ) ? $instance['order-' . $name] : ''; ?>">
</div>
</div>
<?php
        }   # foreach ( $types as $name => $type ) {
?>
<div class="scpbcfw-admin-search-fields-options-container">
<div class="scpbcfw-admin-search-fields-option">
<input type="number" min="0" max="1024" 
    id="<?php echo $this->get_field_id( 'maximum_number_of_items' ); ?>"
    name="<?php echo $this->get_field_name( 'maximum_number_of_items' ); ?>"
    class="scpbcfw-admin-search-fields-option-number"
    value="<?php echo isset( $instance['maximum_number_of_items'] ) ? $instance['maximum_number_of_items'] : 16; ?>"
    size="4">
Maximum number of items to display per custom field:
<div style="clear:both;"></div>
</div>
<div class="scpbcfw-admin-search-fields-option">
<input type="checkbox"
    id="<?php echo $this->get_field_id( 'set_is_search' ); ?>"
    name="<?php echo $this->get_field_name( 'set_is_search' ); ?>"
    class="scpbcfw-admin-search-fields-option-checkbox"
    value="is search"
    <?php if ( isset( $instance['set_is_search'] ) ) { echo 'checked'; } ?>>
Display search results using the same template as the default WordPress search:
<div style="clear:both;"></div>
</div>
<div class="scpbcfw-admin-search-fields-option">
<input type="checkbox"
    id="<?php echo $this->get_field_id( 'enable_table_view_option' ); ?>"
    name="<?php echo $this->get_field_name( 'enable_table_view_option' ); ?>"
    class="scpbcfw-admin-search-fields-option-checkbox"
    value="table view option enabled"
    <?php if ( !$instance || isset( $instance['enable_table_view_option'] ) ) { echo 'checked'; } ?>>
Enable option to display search results using a content template:
<div style="clear:both;"></div>
</div>
<div class="scpbcfw-admin-search-fields-option">
<input type="number" min="256" max="8192" 
    id="<?php echo $this->get_field_id( 'table_width' ); ?>"
    name="<?php echo $this->get_field_name( 'table_width' ); ?>"
    class="scpbcfw-admin-search-fields-option-number"
    <?php if ( !empty( $instance['table_width'] ) ) { echo "value=\"$instance[table_width]\""; } ?>
    <?php if ( $instance && !isset( $instance['enable_table_view_option'] ) ) { echo 'disabled'; } ?>
    placeholder="from css"
    size="5">
Width in pixels of the container used to display the search results:
<div style="clear:both;"></div>
</div>
<div class="scpbcfw-admin-search-fields-option">
The content template to use to display the search results:<br>
<button id="mf2tk-edit-search-result-template-button" style="float:right;visibility:hidden;" disabled>...</button>
Post Type:
<select id="mf2tk-select-search-result-post-type-select" name="<?php echo $this->get_field_name( 'select_post_type' ); ?>">
    <option value="generic" selected>Generic</option>
<?php
        foreach ( $types as $name => $type ) {
            echo "<option value=\"$name\">$name</option>\n";
        }
?>
</select>
<textarea
    id="<?php echo $this->get_field_id( 'table_shortcode' ); ?>"
    name="<?php echo $this->get_field_name( 'table_shortcode' ); ?>"
    class="scpbcfw-admin-search-fields-option-textarea"
    rows="8" <?php if ( $instance && !isset( $instance['enable_table_view_option'] ) ) { echo 'disabled'; } ?>
    placeholder="The default content macro will be used." style="clear:both">
<?php 
    if ( !empty( $instance['table_shortcode'] ) ) {
        $macro = $instance['table_shortcode'];
    } else {
        $macro = Search_Using_Magic_Fields_Widget::DEFAULT_CONTENT_MACRO;
    }
    echo htmlspecialchars( $macro );
?>
</textarea>
<!-- start create/edit search result template -->
<div id="mf2tk-edit-search-result-template-form" style="display:none;">
<h3 id="mf2tk-edit-search-result-template-h3">Loading ... please wait ...</h3>
</div>
<!-- end create/edit search result template -->
<div style="clear:both;"></div>
</div>
</div>
<script type="text/javascript">
var mf2tkGenericSearchTemplate
    ="<?php echo str_replace( "\n", "\\n\\\n", str_replace( "\r\n", "\n", str_replace( '"', '\"', $macro ) ) ); ?>";
jQuery("div.scpbcfw-admin-search-fields-display-button").click(function(event){
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
jQuery("select#mf2tk-select-search-result-post-type-select").change(function(){
    var button=jQuery(this.parentNode).find("button#mf2tk-edit-search-result-template-button");
    var textarea=jQuery(this.parentNode).find("textarea#<?php echo $this->get_field_id( 'table_shortcode' ); ?>");
    button.text("...").css("visibility","hidden").prop("disabled",true);
    var postType=this.value;
    if(postType==="generic"){
        textarea.val(mf2tkGenericSearchTemplate).prop("readonly",false);
    }else{
        textarea.val("Loading ... Please wait ...");
        jQuery.post(ajaxurl,{action:'mf2tk_get_search_result_template',post_type:postType},function(r){
            textarea.val(r).prop("readonly",true);
            button.text(r==="<?php echo self::TEMPLATE_NOT_FOUND; ?>"?"Create":"Re-Create").css("visibility","visible")
                .prop("disabled",false);
        });
    }
});
jQuery("button#mf2tk-edit-search-result-template-button").click(function(e){
    var windowWidth=jQuery(window).width();
    var windowHeight=jQuery(window).height();
    var background=document.createElement("div");
    background.id="mf2tk-background";
    var backgroundStyle=background.style;
    backgroundStyle.position="fixed";
    backgroundStyle.width=windowWidth+"px";
    backgroundStyle.height=windowHeight+"px";
    backgroundStyle.left=0;
    backgroundStyle.top=0;
    backgroundStyle.backgroundColor="black";
    backgroundStyle.zIndex=99999;
    backgroundStyle.opacity=0.7;
    document.body.appendChild(background);
    var offset=jQuery("div#wpadminbar").outerHeight(true);
    windowHeight-=offset;
    var width=windowWidth>800?800:Math.floor(windowWidth*9/10);
    var height=Math.floor(windowHeight*9/10);
    var form=jQuery(this.parentNode).find("div#mf2tk-edit-search-result-template-form");
    form.html("<h3 id='mf2tk-edit-search-result-template-h3'>Loading ... please wait ...</h3>");
    var style=form[0].style;
    style.position="fixed";
    style.width=width+"px";
    style.height=height+"px";
    style.overflow="auto";
    style.left=Math.floor((windowWidth-width)/2)+"px";
    style.top=offset+Math.floor((windowHeight-height)/2)+"px";
    style.backgroundColor="white";
    style.border="3px solid black";
    style.zIndex=100000;
    style.display="block";
    var postType=jQuery(this.parentNode).find("select#mf2tk-select-search-result-post-type-select").val();
    jQuery.post(ajaxurl,{action:"mf2tk_get_search_result_template_form",post_type:postType},function(r){
        form.empty().append(r);
    });
    window.mf2tkSearchTemplateTextarea=jQuery("textarea#<?php echo $this->get_field_id( 'table_shortcode' ); ?>");
    e.preventDefault();
    return false;
});
jQuery(document).ready(function(){
    jQuery("select#mf2tk-select-search-result-post-type-select").val("generic");
    jQuery("button#mf2tk-edit-search-result-template-button").text("...").css("visibility","hidden").prop("disabled",true);
    jQuery("textarea#<?php echo $this->get_field_id( 'table_shortcode' ); ?>").val(mf2tkGenericSearchTemplate);
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
        $arr = false;
        return $arr;
    }
}   # class Search_Using_Magic_Fields_Widget extends WP_Widget

add_action( 'widgets_init', function() {
        register_widget( 'Search_Using_Magic_Fields_Widget' );
} );

if ( is_admin() ) {
    add_action( 'admin_enqueue_scripts', function() {
        wp_enqueue_style( 'admin', plugins_url( 'admin.css', __FILE__ ) );
    } );
    add_action('admin_head', function() {
?>
<style>
div.mf2tk-selectable-field-after{height:2px;background-color:white;}
div.mf2tk-selectable-field-after.mf2tk-hover{background-color:black;}
</style>
<?php
    } );
    # Use the no privilege version also in privileged mode
    add_action( 'wp_ajax_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE, function() {
        do_action( 'wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE );
    } );
    # This ajax action will generate and return the search form for the given post type
    add_action( 'wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE, function() {
        global $wpdb;
        if ( !isset( $_POST['mf2tk_get_form_nonce'] ) || !wp_verify_nonce( $_POST['mf2tk_get_form_nonce'],
            Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE ) ) {
            error_log( '##### action:wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE . ':nonce:die' );
            die;
        }
        $option = get_option( $_REQUEST['magic_fields_search_widget_option'] );
        $number = $_REQUEST['magic_fields_search_widget_number'];
        $selected = $option[$number][$_REQUEST['post_type']];
        $SQL_LIMIT = (integer) $option[$number]['maximum_number_of_items'];  
?>
<h4 class="scpbcfw-search-fields-conditions-title">Specify search conditions:<h4>
<p style="clear:both;margin:0px;">
<?php    
        $taxonomies = array();
        $wp_taxonomies = get_taxonomies( '', 'objects' );
        foreach ( $wp_taxonomies as &$taxonomy ) {
            if ( !in_array( $_REQUEST['post_type'], $taxonomy->object_type ) ) { continue; }
            $tax_name = ( $taxonomy->hierarchical ? 'tax-cat-' : 'tax-tag-' ) . $taxonomy->name;
            if ( in_array( $tax_name, $selected ) ) { $taxonomies[$tax_name] =& $taxonomy; }
        }
        unset ( $taxonomy );
        $fields = $wpdb->get_results( 'SELECT name, type, label FROM ' . MF_TABLE_CUSTOM_FIELDS
            . " WHERE post_type = '$_REQUEST[post_type]' ORDER BY custom_group_id, display_order", OBJECT_K );
        $fields['pst-std-post_content'] = (object) array( 'type' => 'multiline', 'label' => 'Post Content' );
        $fields['pst-std-post_author' ] = (object) array( 'type' => 'author',    'label' => 'Author'       );
        foreach ( $selected as $selection ) {
            if ( array_key_exists( $selection, $taxonomies ) ) {
                # do a selected taxonomy
                $tax_name = $selection;
                $taxonomy =& $taxonomies[$tax_name];
                $tax_type = ( $taxonomy->hierarchical ) ? 'tax-cat-' : 'tax-tag-';
                $results = $wpdb->get_results( $wpdb->prepare( <<<EOD
                    SELECT x.term_taxonomy_id, t.name, COUNT(*) count
                        FROM $wpdb->term_relationships r, $wpdb->term_taxonomy x, $wpdb->terms t, $wpdb->posts p
                        WHERE r.term_taxonomy_id = x.term_taxonomy_id AND x.term_id = t.term_id AND r.object_id = p.ID
                            AND x.taxonomy = "$taxonomy->name" AND p.post_type = %s
                            GROUP BY x.term_taxonomy_id ORDER BY count DESC LIMIT $SQL_LIMIT
EOD
                    , $_REQUEST['post_type'] ), OBJECT );
?>
<div class="scpbcfw-search-fields">
<span class="scpbcfw-search-fields-field-label"><?php echo $taxonomy->name ?>:</span>
<div class="scpbcfw-display-button">Open</div>
<div style="clear:both;"></div>
<div class="scpbcfw-search-field-values" style="display:none;">
<?php
                foreach ( $results as $result ) {
?>
<input type="checkbox" id="<?php echo $meta_key; ?>" name="<?php echo $tax_type . $taxonomy->name; ?>[]"
    class="scpbcfw-search-fields-checkbox"
    value="<?php echo $result->term_taxonomy_id; ?>"><?php echo "$result->name ($result->count)"; ?><br>
<?php
                }   # foreach ( $results as $result ) {
                if ( count( $results ) === $SQL_LIMIT ) {
                    # too many distinct terms for this custom taxonomy so allow user to also manually enter search value
?>
<input id="<?php $meta_key . Search_Using_Magic_Fields_Widget::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    name="<?php echo "{$tax_type}{$taxonomy->name}" . Search_Using_Magic_Fields_Widget::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    class="scpbcfw-search-fields-for-input" type="text" placeholder="--Enter Search Value--">
<?php
                }
?>
</div>
</div>
<?php
                unset( $taxonomy );
            }   # if ( array_key_exists( $tax_name, $taxonomies ) ) {
            else if ( array_key_exists( $selection, $fields ) ) {
                # now do a selected field
                $meta_key = $selection;
                $field =& $fields[$meta_key];
?>
<div class="scpbcfw-search-fields">
<span class="scpbcfw-search-fields-field-label"><?php echo $field->label ?>:</span>
<div class="scpbcfw-display-button">Open</div>
<div style="clear:both;"></div>
<div class="scpbcfw-search-field-values" style="display:none;">
<?php
                if ( $field->type === 'multiline' || $field->type === 'markdown_editor' ) {
                    # values are multiline so just let user manually enter search values
?>
<input id="<?php echo $meta_key ?>" name="<?php echo $meta_key ?>" class="scpbcfw-search-fields-for-input" type="text"
    placeholder="--Enter Search Value--">
</div>
</div>
<?php
                    continue;
                }
                if ( $field->type === 'author' ) {
                    # use author display name in place of author id
                    $results = $wpdb->get_results( $wpdb->prepare( <<<EOD
                        SELECT p.post_author, u.display_name, COUNT(*) count FROM $wpdb->posts p, $wpdb->users u
                            WHERE p.post_author = u.ID AND p.post_type = %s AND p.post_status = "publish"
                                AND p.post_author IS NOT NULL GROUP BY p.post_author ORDER BY count
EOD
                        , $_REQUEST['post_type'] ), OBJECT );
                    $count = -1;
                    foreach ( $results as $result ) {
                        if ( ++$count === $SQL_LIMIT ) { break; }
?>
<input type="checkbox" id="<?php echo $meta_key ?>" name="<?php echo $meta_key ?>[]" class="scpbcfw-search-fields-checkbox"
    value="<?php echo $result->post_author; ?>"> <?php echo $result->display_name . " ($result->count)"; ?><br>
<?php
                    }
                    if ( $count === $SQL_LIMIT ) {
?>
<input type="text" id="pst-std-post_author<?php echo Search_Using_Magic_Fields_Widget::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    name="pst-std-post_author<?php echo Search_Using_Magic_Fields_Widget::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    class="scpbcfw-search-fields-for-input" placeholder="--Enter Search Value--">
<?php
                    }
?>
</div>
</div>
<?php
                    continue;
                }   # if ( $meta_key === 'pst-std-post_author' ) {
                $results = $wpdb->get_results( $wpdb->prepare( <<<EOD
                    SELECT meta_value, COUNT(*) count FROM
                        ( SELECT distinct m.meta_value, m.post_id FROM $wpdb->postmeta m, $wpdb->posts p
                            WHERE m.post_id = p.ID AND m.meta_key = %s AND p.post_type = %s
                                AND m.meta_value IS NOT NULL AND m.meta_value != '' ) d
                            GROUP BY meta_value ORDER BY count DESC LIMIT $SQL_LIMIT
EOD
                    , $meta_key, $_REQUEST['post_type'] ), OBJECT_K );
                $values = array();   # to be used by serialized fields
                $numeric = TRUE;
                foreach ( $results as $meta_value => $result ) {
                    if ( !$meta_value ) { continue; }
                    if ( $field->type === 'related_type' ) {
                        $value = get_the_title( $meta_value );
                    } else if ( $field->type === 'image_media' ) {
                        # must use _wp_attached_file from $wpdb->postmeta 
                        #$value = $wpdb->get_col( $wpdb->prepare( "SELECT guid FROM $wpdb->posts WHERE ID = %s", $meta_value ) );
                        $value = wp_get_attachment_url( $meta_value );
                        if ( $value ) { $value = substr( $value[0], strrpos( $value[0], '/' ) + 1 ); }
                        else { $value = ''; }
                    } else if ( $field->type === 'image' ) {
                        # for Magic Fields 2 porprietary image data strip time stamp prefix
                        $value = substr( $meta_value, 10 );
                    } else if ( $field->type === 'alt_related_type' || $field->type === 'checkbox_list' 
                        || $field->type === 'dropdown' || $field->type === 'alt_dropdown' ) {
                        # These are the serialized fields. Since individual values may be embedded in multiple rows
                        # two passes will be needed - one to accumulate the counts and another to display the counts
                        $entries = unserialize( $meta_value );
                        for ( $i = 0; $i < $result->count; $i++ ) { $values = array_merge( $values, $entries ); }
                        continue;   # skip display which will be done later on a second pass
                    } else {
                        $value = $meta_value;
                    }
?>
<input type="checkbox" id="<?php echo $meta_key; ?>" name="<?php echo $meta_key; ?>[]" class="scpbcfw-search-fields-checkbox"
    value="<?php echo $meta_value; ?>"><?php echo "$value ($result->count)"; ?><br>
<?php
                    if ( $field->type == 'textbox' ) {
                        if ( !is_numeric( $meta_value ) ) { $numeric = FALSE; }
                    }
                }   # foreach ( $results as $result ) {
                # now do second pass on the serialized fields
                if ( $values ) {
                    # get count of individual values
                    $values = array_count_values( $values );
                    arsort( $values, SORT_NUMERIC );
                    foreach ( $values as $key => $value ) {   # $key is value and $value is count
?>
<input type="checkbox" id="<?php echo $meta_key; ?>" name="<?php echo $meta_key; ?>[]" class="scpbcfw-search-fields-checkbox"
<?php
                        # "serialize" the value - this is what the value would look like in a serialized array
                        echo 'value=\';s:' . strlen( $key ) . ':"' . $key . '";\'>';
                        # for alt_related_type use post title instead of post id
                        if ( $field->type === 'alt_related_type' ) { echo get_the_title( $key ) . "($value)<br>"; }
                        else { echo "$key ($value)<br>"; }
                    }   # foreach ( $values as $key => $value) {
                }   # if ( $values ) {
                if ( count( $results ) === $SQL_LIMIT && ( $field->type !== 'related_type'
                    && $field->type !== 'alt_related_type' && $field->type !== 'image_media' ) ) {
                    # for these types also allow user to manually enter search values
?>
<input id="<?php echo $meta_key . Search_Using_Magic_Fields_Widget::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    name="<?php echo $meta_key . Search_Using_Magic_Fields_Widget::OPTIONAL_TEXT_VALUE_SUFFIX; ?>"
    class="scpbcfw-search-fields-for-input" type="text" placeholder="--Enter Search Value--">
<?php
                }
                if ( $field->type == 'slider' || $field->type == 'datepicker' || ( $field->type == 'textbox' && $numeric ) ) {
                    # only show minimum/maximum input textbox for numeric and date custom fields
?>
<h4>Range Search</h4>
<input id="<?php echo $meta_key . Search_Using_Magic_Fields_Widget::OPTIONAL_MINIMUM_VALUE_SUFFIX; ?>"
    name="<?php echo $meta_key . Search_Using_Magic_Fields_Widget::OPTIONAL_MINIMUM_VALUE_SUFFIX; ?>"
    class="scpbcfw-search-fields-for-input" type="text" placeholder="--Enter Minimum Value--">
<input id="<?php echo $meta_key . Search_Using_Magic_Fields_Widget::OPTIONAL_MAXIMUM_VALUE_SUFFIX; ?>"
    name="<?php echo $meta_key . Search_Using_Magic_Fields_Widget::OPTIONAL_MAXIMUM_VALUE_SUFFIX; ?>"
    class="scpbcfw-search-fields-for-input" type="text" placeholder="--Enter Maximum Value--">
<?php
                }
?>
</div>
</div>
<?php
                unset( $field );
            }   # else if ( array_key_exists( $meta_key, $fields ) ) {
        }   # foreach ( $selected as $selection ) {
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

/*
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
*/

jQuery("div.scpbcfw-display-button").click(function(event){
    if(jQuery(this).text()=="Open"){
        jQuery(this).text("Close");
        jQuery("div.scpbcfw-search-field-values",this.parentNode).css("display","block");
    }else{
        jQuery(this).text("Open");
        jQuery("div.scpbcfw-search-field-values",this.parentNode).css("display","none");
    }
    return false;
});
jQuery("div.scpbcfw-search-fields input.scpbcfw-search-fields-checkbox").change(function(){
    this.parentNode.parentNode.style.backgroundColor
        =jQuery("input.scpbcfw-search-fields-checkbox:checked",this.parentNode).length+
            jQuery("input.scpbcfw-search-fields-for-input",this.parentNode)
                .filter(function(){return jQuery(this).val();}).length
            ?"white":this.parentNode.parentNode.parentNode.style.backgroundColor;
});
jQuery("div.scpbcfw-search-fields input.scpbcfw-search-fields-for-input").change(function(){
    this.parentNode.parentNode.style.backgroundColor
        =jQuery("input.scpbcfw-search-fields-checkbox:checked",this.parentNode).length+
            jQuery("input.scpbcfw-search-fields-for-input",this.parentNode)
                .filter(function(){return jQuery(this).val();}).length
            ?"white":this.parentNode.parentNode.parentNode.style.backgroundColor;
});
jQuery("div.scpbcfw-search-fields-submit input[type='button']#magic-fields-reset").mousedown(function(){
    jQuery("div.scpbcfw-search-fields input.scpbcfw-search-fields-checkbox").attr("checked",false);
    jQuery("div.scpbcfw-search-fields input.scpbcfw-search-fields-for-input").val("");
    var div=jQuery("div.scpbcfw-search-fields");
    div.css("background-color",div.parent().css("background-color"));
});
</script>
<?php
        die();
    } );   # add_action( 'wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::GET_FORM_FOR_POST_TYPE,
    
    add_action( 'wp_ajax_mf2tk_get_search_result_template_form', function( ) {
        global $wpdb;
        $post_type = $_POST['post_type'];
?>
<!-- start create/edit search result template -->
<div id="mf2tk-edit-search-result-template-div">
    <button id="mf2tk-edit-search-result-template-close-button" style="float:right;">X</button>
    <h3 id="mf2tk-edit-search-result-template-h3">Create Search Result Template for Post Type: <?php echo $post_type; ?></h3>
    <div style="clear:both;">
<?php
        $field_and_filters = get_option( Search_Using_Magic_Fields_Widget::SEARCH_RESULTS_FIELDS_AND_FILTERS, [ ] );
        $input_value = '';
        $matches = [ ];
        if ( array_key_exists( $post_type, $field_and_filters ) ) {
            $input_value = $field_and_filters[$post_type];
            preg_match( '/field=([\w;]+)\|filter=([\w;]+)/', $input_value, $matches );
        }
        $fields   = array_key_exists( 1, $matches ) ? $matches[1] : '';
        $filters  = array_key_exists( 2, $matches ) ? $matches[2] : '';
        $fields0  = array_key_exists( 1, $matches ) && $matches[1] ? explode( ';', $matches[1] ) : [ ];
        $filters0 = [ ];
        if ( array_key_exists( 2, $matches ) && $matches[2] ) {
            foreach ( explode( ';', $matches[2] ) as $filter ) {
                if ( preg_match( '/^(\w+)__(w|h)(\d+)$/', $filter, $filter_matches ) === 1 ) {
                    $filters0[$filter_matches[1]] = [ $filter_matches[2], $filter_matches[3] ];
                } else {
                    $filters0[$filter] = null;
                }
            }
        }
        $default_height = 50;
        $MF_TABLE_CUSTOM_FIELDS = MF_TABLE_CUSTOM_FIELDS;
        $results = $wpdb->get_col( <<<EOD
SELECT name FROM $MF_TABLE_CUSTOM_FIELDS WHERE post_type = '$post_type' AND type != 'alt_table' AND type != 'alt_template'
EOD
        );
        array_unshift( $results, '__post_title', '__post_author' );
        $checked_fields = array_intersect( $fields0, $results );
        $unchecked_fields = array_diff( $results, $checked_fields );
        $all_fields = array_merge(
            array_map( function( $v ) { return true;  }, array_flip( $checked_fields   ) ),
            array_map( function( $v ) { return false; }, array_flip( $unchecked_fields ) )
        );
        foreach ( [ 'tk_value_as_color', 'tk_value_as_checkbox', 'tk_value_as_audio', 'tk_value_as_image',
            'tk_value_as_video', 'url_to_link2' ] as $filter ) {
            $filter_checked = $filter . '_checked';
            $$filter_checked = array_key_exists( $filter, $filters0 ) ? ' checked' : '';
        }
        $tk_value_as_image_height_checked = ' checked';
        $tk_value_as_image_width_checked  = '';
        $tk_value_as_image_size           = $default_height;
        if ( array_key_exists( 'tk_value_as_image', $filters0 ) ) {
            if ( $filters0['tk_value_as_image'][0] === 'h' ) {
                $tk_value_as_image_height_checked = ' checked';
                $tk_value_as_image_width_checked  = '';
            } else {
                $tk_value_as_image_height_checked = '';
                $tk_value_as_image_width_checked  = ' checked';
            }
            $tk_value_as_image_size = $filters0['tk_value_as_image'][1];
        }
        $tk_value_as_video_height_checked = ' checked';
        $tk_value_as_video_width_checked  = '';
        $tk_value_as_video_size           = $default_height;
        if ( array_key_exists( 'tk_value_as_video', $filters0 ) ) {
            if ( $filters0['tk_value_as_video'][0] === 'h' ) {
                $tk_value_as_video_height_checked = ' checked';
                $tk_value_as_video_width_checked  = '';
            } else {
                $tk_value_as_video_height_checked = '';
                $tk_value_as_video_width_checked  = ' checked';
            }
            $tk_value_as_video_size = $filters0['tk_value_as_video'][1];
        }
?>
    <div class="mf2tk-field-input-optional">
        <div class="mf2tk-field_value_pane" style="clear:both;">
            <textarea id="mf2tk-search-result-template-textarea" class="mf2tk-how-to-use" rows="10">
<!-- This was generated by the toolkit from the specified fields and filters.
     Use this as a base content template and edit it to your liking.          -->
&lt;div&gt;
&lt;style scoped&gt;
table.mf2tk-<?php echo $post_type; ?>-table{border-collapse:collapse;}
table.mf2tk-<?php echo $post_type; ?>-table,table.mf2tk-<?php echo $post_type; ?>-table td,
    table.mf2tk-<?php echo $post_type; ?>-table th{border:2px solid black;}
table.mf2tk-<?php echo $post_type; ?>-table td,table.mf2tk-<?php echo $post_type; ?>-table th{padding:7px 10px 3px 10px;}
table.mf2tk-<?php echo $post_type; ?>-table th{background-color:rgb(128,128,128);
    background-image:url(data:image/gif;base64,R0lGODlhFQAJAIAAACMtMP///yH5BAEAAAEALAAAAAAVAAkAAAIXjI+AywnaYnhUMoqt3gZXPmVg94yJVQAAOw==);
    background-position:100% 50%;background-repeat:no-repeat;color:black;text-align:center;}
&lt;/style&gt;
&lt;table class="mf2tk-<?php echo $post_type; ?>-table tablesorter"&gt;
[show_custom_field
    post_id="$#a_post#"
    field="<?php echo $fields; ?>"
    before="&lt;span style='display:none;'&gt;"
    after="&lt;/span&gt;"
    field_before="&lt;th class='mf2tk-&lt;!--$field--&gt;-th'&gt;&lt;!--$Field--&gt;"
    field_after="&lt;/th&gt;"
    post_before="&lt;thead&gt;&lt;tr&gt;"
    post_after="&lt;/tr&gt;&lt;/thead&gt;"
]
&lt;tbody&gt;
[show_custom_field
    post_id="$#posts#"
    field="<?php echo $fields; ?>"
    filter="<?php echo $filters; ?>"
    separator=", "
    field_before="&lt;td class='mf2tk-&lt;!--$field--&gt;-td'&gt;"
    field_after="&lt;/td&gt;"
    post_before="&lt;tr&gt;"
    post_after="&lt;/tr&gt;"
]
&lt;/tbody&gt;
&lt;/table&gt;
&lt;/div&gt;
</textarea>
<button id="mf2tk-save-search-result-template-button" style="float:right;">Save the Content Template</button>
        </div>
    </div>
    <!-- optional configuration field -->
    <div class="mf2tk-field-input-optional">
        <h6>Re-Configure the Content Template</h6>
        <div class="mf2tk-field_value_pane" style="clear:both;">
            <fieldset class="mf2tk-configure mf2tk-fields">
                <legend>Fields:</legend>
                <!-- before drop point -->
                <div><div class="mf2tk-dragable-field-after"></div></div>
<?php foreach ( $all_fields as $field => $is_checked ) { $checked = $is_checked ? ' checked' : ''; ?>
                <div class="mf2tk-dragable-field">
                    <label class="mf2tk-configure"><input type="checkbox" class="mf2tk-configure"
                        value="<?php echo $field; ?>"<?php echo $checked; ?>><?php echo $field; ?></label><br>
                    <!-- a drop point -->
                    <div class="mf2tk-dragable-field-after"></div>
                </div>
<?php } ?>
                <span>Use drag and drop to change field order</span>
            </fieldset>
            <fieldset class="mf2tk-configure mf2tk-filters">
                <legend>Filters:</legend>
                <div>
                    <label class="mf2tk-configure"><input type="checkbox" class="mf2tk-configure"
                        value="tk_value_as_color"<?php echo $tk_value_as_color_checked; ?>>tk_value_as_color</label>
                </div>
                <div>
                    <label class="mf2tk-configure"><input type="checkbox" class="mf2tk-configure"
                        value="tk_value_as_checkbox"<?php echo $tk_value_as_checkbox_checked; ?>>tk_value_as_checkbox</label>
                </div>
                <div>
                    <label class="mf2tk-configure"><input type="checkbox" class="mf2tk-configure"
                    value="tk_value_as_audio"<?php echo $tk_value_as_audio_checked; ?>>tk_value_as_audio</label>
                </div>
                <div>
                    <label class="mf2tk-configure"><input type="checkbox" class="mf2tk-configure"
                        value="tk_value_as_image__"<?php echo $tk_value_as_image_checked; ?>>tk_value_as_image__</label>
                    <label class="mf2tk-configure"><input type="radio" class="mf2tk-configure" name="tk_value_as_image_{$post_type}"
                        value="width"<?php echo $tk_value_as_image_width_checked; ?>>Width</label>
                    <label class="mf2tk-configure"><input type="radio" class="mf2tk-configure" name="tk_value_as_image_{$post_type}"
                        value="height"<?php echo $tk_value_as_image_height_checked; ?>>Height</label>
                    <label class="mf2tk-configure"><input type="number" class="mf2tk-configure" max="9999"
                        value="<?php echo $tk_value_as_image_size; ?>"></label>
                </div>
                <div>
                    <label class="mf2tk-configure"><input type="checkbox" class="mf2tk-configure"
                        value="tk_value_as_video__"<?php echo $tk_value_as_video_checked; ?>>tk_value_as_video__</label>
                    <label class="mf2tk-configure"><input type="radio" class="mf2tk-configure" name="tk_value_as_video_{$post_type}"
                        value="width"<?php echo $tk_value_as_video_width_checked; ?>>Width</label>
                    <label class="mf2tk-configure"><input type="radio" class="mf2tk-configure" name="tk_value_as_video_{$post_type}"
                        value="height"<?php echo $tk_value_as_video_height_checked; ?>>Height</label>
                    <label class="mf2tk-configure"><input type="number" class="mf2tk-configure" max="9999"
                        value="<?php echo $tk_value_as_video_size; ?>"></label>
                </div>
                <div>
                    <label class="mf2tk-configure"><input type="checkbox" class="mf2tk-configure"
                        value="url_to_link2"<?php echo $url_to_link2_checked; ?>>url_to_link2</label>
                </div>
            </fieldset>
            <button id="mf2tk-search-result-template-refresh-button" style="float:right;">Refresh the Content Template</button>
        </div>
    </div>
    <input type="hidden" id="mf2tk-search-result-template-fields-filters-input" value="<?php echo $input_value; ?>">
    </div>
</div>
<script type="text/javascript">
(function(){
    var postType="<?php echo $post_type; ?>";
    var div=jQuery("div#mf2tk-edit-search-result-template-div");
    div.find("button#mf2tk-edit-search-result-template-close-button").click(function(e){
        this.parentNode.parentNode.style.display="none";
        jQuery(this.parentNode.parentNode).empty();
        jQuery("div#mf2tk-background").remove();
        e.preventDefault();
        return false;
    });
    div.find("button#mf2tk-search-result-template-refresh-button").click(function(e){
        var fields="";
        var filters="";
        var parent=jQuery(this.parentNode);
        parent.find("fieldset.mf2tk-configure.mf2tk-fields input[type='checkbox']").each(function(){
            var input=jQuery(this);
            if(input.prop("checked")){
                if(fields){fields+=";";}
                fields+=input.prop("value");
            }
        });
        parent.find("fieldset.mf2tk-configure.mf2tk-filters input[type='checkbox']").each(function(){
            var input=jQuery(this);
            if(input.prop("checked")){
                var name=input.prop("value");
                if(name==="tk_value_as_image__"||name==="tk_value_as_video__"){
                    if(jQuery(this.parentNode.parentNode).find("input[type='radio'][value='width']").prop("checked")){
                        name+="w";
                    }else{
                        name+="h";
                    }
                    name+=jQuery(this.parentNode.parentNode).find("input[type='number']").val().trim();
                }
                if(filters){filters+=";";}
                filters+=name;
            }
        });
        var textarea=div.find("textarea#mf2tk-search-result-template-textarea");
        var text=textarea.val();
        text=text.replace(/field="([\w;]*)"/g,function(match,old){return 'field="'+fields+'"';});
        text=text.replace(/filter="([\w;]*)"/g,function(match,old){return 'filter="'+filters+'"';});
        textarea.val(text);
        jQuery("input#mf2tk-search-result-template-fields-filters-input").val("field="+fields+"|filter="+filters);
        e.preventDefault();
        return false;
    });
    div.find("div.mf2tk-dragable-field").draggable({cursor:"crosshair",revert:true});
    div.find("div.mf2tk-dragable-field-after").droppable({accept:"div.mf2tk-dragable-field",tolerance:"touch",
        hoverClass:"mf2tk-hover",drop:function(e,u){
            jQuery(this.parentNode).after(u.draggable);
    }});
    div.find("button#mf2tk-save-search-result-template-button").click(function(e){
        var text=div.find("textarea#mf2tk-search-result-template-textarea").val();
        var fieldsFilters=div.find("input#mf2tk-search-result-template-fields-filters-input").val();
        jQuery.post(ajaxurl,
            {action:'mf2tk_update_search_result_template',post_type:postType,text:text,fields_filters:fieldsFilters},
            function(r){
                alert(r);
                window.mf2tkSearchTemplateTextarea.val(text);
            });
        e.preventDefault();
        return false;
    });
}());
</script>
<!-- end create/edit search result template -->
<?php
        die;
    } );
    
    add_action( 'wp_ajax_mf2tk_get_search_result_template', function( ) {
        global $wpdb;
        $post_name = Search_Using_Magic_Fields_Widget::SEARCH_RESULTS_TEMPLATE_PREFIX . $_POST['post_type'];
        $template = $wpdb->get_col( <<<EOD
SELECT post_content FROM $wpdb->posts WHERE post_type = 'content_macro'
    AND post_name = '$post_name' AND post_status = 'publish'
EOD
        );
        if ( $template ) { die( $template[0] ); }
        else { die( Search_Using_Magic_Fields_Widget::TEMPLATE_NOT_FOUND ); }
    } );   # add_action( 'wp_ajax_mf2tk_get_search_result_template', function( ) {
    add_action( 'wp_ajax_mf2tk_update_search_result_template', function( ) {
        global $wpdb;
        $fields_filters = get_option( Search_Using_Magic_Fields_Widget::SEARCH_RESULTS_FIELDS_AND_FILTERS, [ ] );
        $fields_filters[$_POST['post_type']] = $_POST['fields_filters'];
        update_option( Search_Using_Magic_Fields_Widget::SEARCH_RESULTS_FIELDS_AND_FILTERS, $fields_filters );
        $post_name = Search_Using_Magic_Fields_Widget::SEARCH_RESULTS_TEMPLATE_PREFIX . $_POST['post_type'];
        $ids = $wpdb->get_col( <<<EOD
SELECT ID FROM $wpdb->posts WHERE post_type = 'content_macro'
    AND post_name = '$post_name' AND post_status = 'publish'
EOD
        );
        $post = [
            'post_type' => 'content_macro',
            'post_name' => $post_name,
            'post_title' => "Search Result Template for $_POST[post_type]",
            'post_status' => 'publish',
            'post_content' => $_POST['text']
        ];
        if ( $ids ) {
            $post['ID'] = $ids[0];
            $id0 = wp_update_post( $post );
        } else {
            $id1 = wp_insert_post( $post );
        }
        die( !empty ( $id0 ) ? "Content template $id0 updated."
            : ( !empty( $id1 ) ? "Content template $id1 created."
                : "Error: Content template not created/updated." ) );
    } );   # add_action( 'wp_ajax_mf2tk_update_search_result_template', function( ) {
}   # if ( is_admin() ) {
else {
    add_action( 'wp_enqueue_scripts', function() {
        wp_enqueue_style( 'search', plugins_url( 'search.css', __FILE__ ) );
        wp_enqueue_script( 'jquery' );
    } );
    add_action( 'parse_query', function( &$query ) {
        if ( !$query->is_main_query() || !array_key_exists( 'magic_fields_search_form', $_REQUEST ) ) { return; }
        $option = get_option( $_REQUEST['magic_fields_search_widget_option'] );
        $number = $_REQUEST['magic_fields_search_widget_number'];
        if ( isset( $option[$number]['set_is_search'] ) ) { $query->is_search = true; }
    } );
    add_filter( 'posts_where', function( $where, &$query ) {
        global $wpdb;
        if ( !$query->is_main_query() || !array_key_exists( 'magic_fields_search_form', $_REQUEST ) ) { return $where; }
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
        # first get author name to ID translation table in case we need the translations
        $results = $wpdb->get_results( $wpdb->prepare( <<<EOD
            SELECT u.display_name, u.ID FROM $wpdb->users u, $wpdb->posts p
                WHERE u.ID = p.post_author AND p.post_type = %s GROUP BY u.ID
EOD
            , $_REQUEST['post_type'] ), OBJECT );
        $author_ids = array();
        foreach ( $results as $result ) {
            $author_ids[strtolower( $result->display_name)] = $result->ID;
        }
        # merge optional text values into the checkboxes array
        $suffix_len = strlen( Search_Using_Magic_Fields_Widget::OPTIONAL_TEXT_VALUE_SUFFIX );
        foreach ( $_REQUEST as $index => &$request ) {
            if ( $request
                && substr_compare( $index, Search_Using_Magic_Fields_Widget::OPTIONAL_TEXT_VALUE_SUFFIX, -$suffix_len ) === 0 ) {
                $index = substr( $index, 0, strlen( $index ) - $suffix_len );
                if ( is_array( $_REQUEST[$index] ) || !array_key_exists( $index, $_REQUEST ) ) {
                    $lowercase_request = strtolower( $request );
                    if ( substr_compare( $index, 'tax-', 0, 4 ) === 0 ) {
                        # for taxonomy values must replace the value with the corresponding term_taxonomy_id
                        $tax_name = substr( $index, 8 );
                        if ( !array_key_exists( $tax_name, $term_taxonomy_ids )
                            || !array_key_exists( $lowercase_request, $term_taxonomy_ids[$tax_name] ) ) {
                            # kill the original request
                            $request = NULL;
                            continue;
                        }
                        $request = $term_taxonomy_ids[$tax_name][$lowercase_request];
                    } else if ( $index === 'pst-std-post_author' ) {
                        # for author names must replace the value with the corresponding author ID
                        if ( !array_key_exists( $lowercase_request, $author_ids ) ) {
                            # kill the original request
                            $request = NULL;
                            continue;
                        }
                        $request = $author_ids[$lowercase_request];
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
        # first do custom fields
        $non_field_keys = array( 'magic_fields_search_form', 'magic_fields_search_widget_option',
            'magic_fields_search_widget_number', 'magic-fields-search-and-or', 'magic-fields-show-using-macro', 'post_type',
            'paged' );
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
                            $sql3 .= $wpdb->prepare( '( w.meta_key = %s AND w.meta_value >= %d )', $key, $value[value] );
                        } else if ( $value['operator'] == 'maximum' ) {
                            $sql3 .= $wpdb->prepare( '( w.meta_key = %s AND w.meta_value <= %d )', $key, $value[value] );
                        }
                    }
                    continue;
                }
                 if ( $value !== $values[0] ) { $sql .= ' OR '; }
                $sql .= $wpdb->prepare( '( w.meta_key = %s AND w.meta_value LIKE %s )', $key, "%$value%" );
            }   # foreach ( $values as $value ) {
            if ( $sql3 ) {
                if ( substr_compare( $sql, 'WHERE ( ', -8, 8 ) == 0 ) { $sql .= $sql3; }
                else { $sql .= ' OR ( ' . $sql3 . ' ) '; }
            }
            $sql .= ' ) AND w.post_id = p.ID )';
        }   #  foreach ( $_REQUEST as $key => $values ) {
        if ( $sql ) {
            $sql = $wpdb->prepare( "SELECT p.ID FROM $wpdb->posts p WHERE p.post_type = %s AND p.post_status = 'publish' AND ",
                $_REQUEST['post_type'] ) . "( $sql )";
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
                $sql2 .= $wpdb->prepare( 'term_taxonomy_id = %d', $value ); 
            }   # foreach ( $values as $value ) {
            if ( $sql ) { $sql .= " $and_or "; }
            $sql .= " EXISTS ( SELECT * FROM $wpdb->term_relationships WHERE ( $sql2 ) AND object_id = p.ID )";
        }   # foreach ( $_REQUEST as $key => $values ) {
        if ( $sql ) {
            $sql = $wpdb->prepare(
                "SELECT p.ID FROM $wpdb->posts p WHERE p.post_type = %s AND p.post_status = 'publish' AND ( $sql )",
                $_REQUEST['post_type'] );
            $ids1 = $wpdb->get_col( $sql );
            if ( $and_or == 'AND' && !$ids1 ) { return ' AND 1 = 2 '; }
        } else {
            $ids1 = FALSE;
        }
        $ids = Search_Using_Magic_Fields_Widget::join_arrays( $and_or, $ids0, $ids1 );
        if ( array_key_exists( 'pst-std-post_content', $_REQUEST ) && $_REQUEST['pst-std-post_content'] ) {
            #&& $_REQUEST['pst-std-post_content'] != '*enter search value*' ) {
            $sql = $wpdb->prepare( <<<EOD
                SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status = "publish"
                    AND ( post_content LIKE %s OR post_title LIKE %s OR post_excerpt LIKE %s )
EOD
                , $_REQUEST['post_type'], "%{$_REQUEST['pst-std-post_content']}%", "%{$_REQUEST['pst-std-post_content']}%",
                "%{$_REQUEST['pst-std-post_content']}%" );
            $ids2 = $wpdb->get_col( $sql );
            if ( $and_or == 'AND' && !$ids2 ) { return ' AND 1 = 2 '; }
        } else {
            $ids2 = FALSE;
        }
        $ids = Search_Using_Magic_Fields_Widget::join_arrays( $and_or, $ids, $ids2 );
        # filter on post_author
        if ( array_key_exists( 'pst-std-post_author', $_REQUEST ) && $_REQUEST['pst-std-post_author'] ) {
            $authors = implode( ', ', array_map( function( $author ) {
                global $wpdb;
                return $wpdb->prepare( '%d', $author );
            }, $_REQUEST['pst-std-post_author'] ) );
            $sql = $wpdb->prepare(
                "SELECT ID FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish' AND post_author IN ( $authors )",
                $_REQUEST['post_type'] );
            $ids4 = $wpdb->get_col( $sql );
            if ( $and_or == 'AND' && !$ids4 ) { return ' AND 1 = 2 '; }
        } else {
            $ids4 = FALSE;
        }
        $ids = Search_Using_Magic_Fields_Widget::join_arrays( $and_or, $ids, $ids4 );
        if ( $and_or == 'AND' && $ids !== FALSE && !$ids ) { return ' AND 1 = 2 '; }        
        if ( is_array ( $ids ) ) {
            if ( $ids ) {
                $where = ' AND ID IN ( ' . implode( ',', $ids  ) . ' ) ';
            } else {
                $where = ' AND 1 = 2 ';
            }
        } else {
            #$where = " AND ( post_type = '$_REQUEST[post_type]' AND post_status = 'publish' ) ";
            $where = ' AND 1 = 2 ';
        }
        return $where;
    }, 10, 2 );   #	add_filter( 'posts_where', function( $where, $query ) {
    # add null 'query_string' filter to force parse_str() call in WP::build_query_string() - otherwise name gets set ? TODO: why?
    add_filter( 'query_string', function( $arg ) { return $arg; } );
    if ( isset( $_REQUEST['magic-fields-show-using-macro'] ) && $_REQUEST['magic-fields-show-using-macro'] === 'use macro' ) {
        # for alternate output format do not page output
        add_filter( 'post_limits', function( $limit, &$query ) {
            if ( !$query->is_main_query() ) { return $limit; }
            return ' ';
        }, 10, 2 );
        add_action( 'wp_enqueue_scripts', function() {
            # use post type specific css file if it exists otherwise use the default css file
            if ( file_exists( dirname( __FILE__ ) . "/search-results-table-$_REQUEST[post_type].css") ) {
                wp_enqueue_style( 'search_results_table', plugins_url( "search-results-table-$_REQUEST[post_type].css",
                  __FILE__ ) );
            } else {
                wp_enqueue_style( 'search_results_table', plugins_url( 'search-results-table.css',
                  __FILE__ ) );
            }
        } );
        add_action( 'template_redirect', function() {
            global $wp_query, $wpdb;
            # in this case a template is dynamically constructed and returned
            if ( !class_exists( 'Magic_Fields_2_Toolkit_Dumb_Shortcodes' ) ) {
                include_once( dirname( __FILE__ ) . '/magic-fields-2-dumb-shortcodes-kai.php' );
            }
            if ( !class_exists( 'Magic_Fields_2_Toolkit_Dumb_Macros' ) ) {
                include_once( dirname( __FILE__ ) . '/magic-fields-2-dumb-macros.php' );
            }
            # get the list of posts
            if ( !$wp_query->posts ) {
                get_header();
                echo __( 'Nothing found', 'mf2tk' );
                get_footer();
                exit();
            }
            $posts = array_map( function( $post ) { return $post->ID; }, $wp_query->posts );
            $option = get_option( $_REQUEST['magic_fields_search_widget_option'] );
            $number = $_REQUEST['magic_fields_search_widget_number'];
            # get the applicable fields from the options for this widget
            $show_fields = 'show-' . $_REQUEST['post_type'];
            $fields = ( is_array( $option[$number] ) && array_key_exists( $show_fields, $option[$number] ) )
                ? $option[$number][$show_fields] : [];
            if ( !$fields ) {
                $fields = $option[$number][$_REQUEST['post_type']];
            }
            # fix taxonomy names and remove pst-std- fields;
            $fields = array_filter( array_map( function( $field ) { 
                if ( substr_compare( $field, 'tax-cat-', 0, 8, false ) === 0
                    || substr_compare( $field, 'tax-tag-', 0, 8, false ) === 0 ) {
                    return substr( $field, 8 );
                } else if ( $field === 'pst-std-post_author' ) {
                    return '__post_author';
                } else if ( substr_compare( $field, 'pst-std-', 0, 8, false ) === 0 ) {
                    return false;
                } else {
                    return $field . '<*,*>';
                }
            }, $fields ) );
            $post_name = Search_Using_Magic_Fields_Widget::SEARCH_RESULTS_TEMPLATE_PREFIX . $_REQUEST['post_type'];
            $macro = $wpdb->get_col( <<<EOD
SELECT post_content FROM $wpdb->posts WHERE post_type = 'content_macro'
    AND post_name = '$post_name' AND post_status = 'publish'
EOD
            );
            if ( $macro ) { 
                $macro = $macro[0];
                $post_type_specific = true;
            } else {
                $macro = $option[$number]['table_shortcode'];
                if ( empty( $macro ) ) { $macro = Search_Using_Magic_Fields_Widget::DEFAULT_CONTENT_MACRO; }
                $macro = htmlspecialchars_decode( $macro );
            }
            if ( array_key_exists( 'table_width', $option[$number] ) and $table_width = $option[$number]['table_width'] ) {
                $table_width = " style='width:{$table_width}px;'";
            }
            $post    = $posts[0];
            $posts   = implode( ',', $posts );
            $fields  = implode( ';', $fields );
            # build the main content from the above parts
            # the macro has parameters: posts - a list of post ids, fields - a list of field names, a_post - any valid post id,
            # and post_type - the post type
            if ( !empty( $post_type_specific ) ) {
                $content = "[show_macro posts=\"$posts\" a_post=\"$post\"]{$macro}[/show_macro]";
            } else {
                $content = <<<EOD
[show_macro posts="$posts" fields="$fields" a_post="$post" post_type="$_REQUEST[post_type]" table_width="$table_width"]
$macro
[/show_macro]
EOD;
            }
            # finally output all the HTML
            # first do the header
            wp_enqueue_script( 'jquery' );
            wp_enqueue_script( 'jquery.tablesorter.min', plugins_url( 'jquery.tablesorter.min.js', __FILE__ ),
                array( 'jquery' ) );
            add_action( 'wp_head', function () {
?>
<script type="text/javascript">
    jQuery(document).ready(function(){jQuery("table.tablesorter").tablesorter();}); 
</script>
<?php
            });
            get_header();
            # then do the body content
            $content = do_shortcode( $content );
            echo $content;
            get_footer();
            exit();
        } );
    }
}
?>