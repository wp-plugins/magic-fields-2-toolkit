<?php

/*
 * Description:   Widget for searching by Magic Fields 2 custom fields and custom taxonomies.
 * Documentation: http://magicfields17.wordpress.com/magic-fields-2-toolkit-0-2/#search
 * Author:        Magenta Cuda
 * License:       GPL2
 */

class Search_Using_Magic_Fields_Widget extends WP_Widget {

    public static $get_form_for_post_type = 'get_form_for_post_type';
    public static $sql_limit = '16';

	public function __construct() {
		parent::__construct(
            'search_magic_fields',
            __( 'Search using Magic Fields' ),
            [
                'classname' => 'search_magic_fields_widget',
                'description' => __( "Search for Custom Posts using Magic Fields" )
            ]
        );
	}

	public function widget( $args, $instance ) {
        global $wpdb;
        extract( $args );
?>
<form id="search-using-magic-fields" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
<input id="magic_fields_form" name="magic_fields_form" type="hidden" value="magic-fields-search">
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
            if ( $name === 'attachment' || $name === 'revision' || $name === 'nav_menu_item' ) { continue; }
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
jQuery("form#search-using-magic-fields select#post_type").change(function(){
    //console.log("select#post_type change");
    jQuery.post(
        '<?php echo admin_url( 'admin-ajax.php' ); ?>',
        {
            action : '<?php echo Search_Using_Magic_Fields_Widget::$get_form_for_post_type; ?>',
            post_type: jQuery("form#search-using-magic-fields select#post_type option:selected").val()
        },
        function(response){
            //console.log(response);
            jQuery("form#search-using-magic-fields div#magic-fields-parameters").html(response);
        }
    );
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
    add_action(
        'wp_ajax_' . Search_Using_Magic_Fields_Widget::$get_form_for_post_type,
        function() {
            do_action( 'wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::$get_form_for_post_type );
        }
    );
    add_action(
        'wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::$get_form_for_post_type,
        function() {
            global $wpdb;
            #error_log( '##### wp_ajax_nopriv_' . Search_Using_Magic_Fields_Widget::$get_form_for_post_type . ' called.' );
            foreach ( get_taxonomies( '', 'objects' ) as $taxonomy ) {
                #error_log( '##### $taxonomy=' . print_r( $taxonomy, TRUE ) );
                if ( in_array( $_REQUEST['post_type'], $taxonomy->object_type ) ) {
                    $tax_type = ( $taxonomy->hierarchical ) ? 'tax-cat-' : 'tax-tag-';
                    $cloud = wp_tag_cloud( [ 'format' => 'array', 'taxonomy' => $taxonomy->name ] );
                    #error_log( '##### $cloud=' . print_r( $cloud, TRUE ) );
                    $terms = [];
                    foreach ( $cloud as $term ) {
                        $terms[substr( $term, strpos( $term, '>' ) + 1, -4 )]
                            = sscanf( substr( $term, strpos( $term, 'title=\'' ) + 7 ), '%d' )[0];
                    }
                    arsort( $terms, SORT_NUMERIC );
                    #error_log( '##### $terms=' . print_r( $terms, TRUE ) );
?>
<div class="magic-field-parameter" style="padding:5px 10px;border:2px solid black;margin:5px;">
<h3><?php echo $taxonomy->name ?>:</h3>
<select id="<?php echo $tax_type . $taxonomy->name ?>" name="<?php echo $tax_type . $taxonomy->name ?>[]" multiple size="4"
    style="width:100%;">
<option value="no-selection" selected>--no <?php echo $taxonomy->name ?> selected--</option>
<?php
                    $count = 0;
                    foreach ( $terms as $term => $value ) {
?>
<option value="<?php echo $term ?>"><?php echo $term . ' (' . $value . ')'; ?></option>
<?php
                        if ( ++$count == Search_Using_Magic_Fields_Widget::$sql_limit ) { break; }
                    }
?>
<option value="add-new">--Enter New Search Value--</option>
</select>
<input id="<?php ?>" name="<?php ?>" class="for-select" type="text" style="width:90%;display:none;"
    placeholder="--Enter Search Value--">
</div>
<?php
                }
            }
            #$meta_keys = $wpdb->get_col( 'SELECT DISTINCT m.meta_key FROM ' . $wpdb->postmeta . ' m, ' . $wpdb->posts . ' p '
            #    . 'WHERE m.post_id = p.ID AND p.post_type = "' . $_REQUEST['post_type'] .'"' );
            $fields = $wpdb->get_results( 'SELECT name, type, label FROM ' . MF_TABLE_CUSTOM_FIELDS
                . ' WHERE post_type = "' . $_REQUEST['post_type'] . '" ORDER BY custom_group_id, display_order', OBJECT_K );
            $fields['pst-std-post_content'] = (object) [ 'type' => 'multiline', 'label' => 'Post Content' ];
            foreach ( $fields as $meta_key => $field ) {
                #if ( $meta_key === '_edit_last' || $meta_key === '_edit_lock' ) { continue; }
                #error_log( '##### $meta_key=' . $meta_key );
?>
<div class="magic-field-parameter" style="padding:5px 10px;border:2px solid black;margin:5px;">
<h3><?php echo $field->label ?>:</h3>
<?php
                if ( $field->type === 'multiline' || $field->type === 'markdown_editor' ) {
?>
<input id="<?php echo $meta_key ?>" name="<?php echo $meta_key ?>" class="for-input" type="text" style="width:90%;"
    placeholder="--Enter Search Value--">
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
<select id="<?php echo $meta_key ?>" name="<?php echo $meta_key ?>[]" multiple size="4" style="width:100%;<?php
if ( $field->type === 'file' || $field->type === 'image' || $field->type === 'audio' ) {
    echo 'text-align:right;';
}
?>">
<option value="no-selection" selected>--no <?php echo $field->label ?> selected--</option>
<?php
                $values = [];
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
                        || $field->type === 'dropdown' ) {
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
<option value="<?php echo $result['meta_value'] ?>"<?php
if ( $field->type === 'file' || $field->type === 'image' || $field->type === 'audio' ) {
    echo ' style="text-align:right;"';
}
?>><?php echo $value . ' (' . $result['count(*)'] . ')'; ?></option>
<?php
                }
                if ( $field->type === 'alt_related_type' || $field->type === 'checkbox_list'
                    || $field->type === 'dropdown' ) {
                    $values = array_count_values( $values );
                    arsort( $values, SORT_NUMERIC );
                    #error_log( '##### $type === \'alt_related_type\' $values=' . print_r( $values, TRUE ) );
                    foreach ( $values as $key => $value) {
                        if ( $field->type === 'alt_related_type' ) {
?>
<option value="<?php echo $key ?>"><?php echo get_the_title( $key ) . ' (' . $value . ')'; ?></option>
<?php
                        } else {
?>
<option value="<?php echo $key ?>"><?php echo $key . ' (' . $value . ')'; ?></option>
<?php
                        }
                    }
                }
                if ( $field->type !== 'related_type' && $field->type !== 'alt_related_type'
                    && $field->type !== 'image_media' ) {
?>
<option value="add-new">--Enter New Search Value--</option>
<?php
                }
?>
</select>
<input id="<?php ?>" name="<?php ?>" class="for-select" type="text" style="width:90%;display:none;"
    placeholder="--Enter Search Value--">
</div>
<?php
            }
?>
<script type="text/javascript">
jQuery("form#search-using-magic-fields div.magic-field-parameter select").change(function(){
    //console.log(jQuery("option:selected",this).text());
    if(jQuery("option:selected:last",this).text()=="--Enter New Search Value--"){
        jQuery(this).css("display","none");
        var input=jQuery("input",this.parentNode).css("display","inline").val("").get(0);
        input.focus();
        input.select();
    }
});
jQuery("form#search-using-magic-fields div.magic-field-parameter input.for-select").change(function(){
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
jQuery("form#search-using-magic-fields div.magic-field-parameter input.for-select").blur(function(){
    jQuery(this).change();
});
jQuery("form#search-using-magic-fields div.magic-field-parameter input.for-input").focus(function(){this.select();});
jQuery("form#search-using-magic-fields").on('keypress',function(e){if(e.which==13){e.preventDefault();return false;}});
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
            if ( $query->is_main_query() && array_key_exists( 'magic_fields_form', $_REQUEST ) ) {
                #error_log( '##### posts_where:$_REQUEST=' . print_r( $_REQUEST, TRUE ) );
                #error_log( 'posts_where:$where=' . $where );
                $sql = 'SELECT p.ID FROM ' . $wpdb->posts . ' p WHERE p.post_type = "' . $_REQUEST['post_type'] . '"';
                foreach ( $_REQUEST as $key => $values ) {
                    $prefix = substr( $key, 0, 8 );
                    if ( !is_array( $values) ) {
                        if ( $values ) { $values = [ $values ]; }
                        else { $values = []; }
                    }
                    if ( $key === 'magic_fields_form' || $key === 'post_type' || !$values || $values[0] === 'no-selection'
                        || $prefix === 'tax-cat-' || $prefix === 'tax-tag-' || $prefix === 'pst-std-' ) {
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
                    $prefix = substr( $key, 0, 8 );
                    if ( !is_array( $values) ) {
                        if ( $values ) { $values = [ $values ]; }
                        else { $values = []; }
                    }
                    if ( ( $prefix !== 'tax-cat-' && $prefix !== 'tax-tag-' ) || !$values || $values[0] === 'no-selection' ) {
                        continue;
                    }
                    $taxonomy = substr( $key, 8 );
                    $sql .= ' AND EXISTS ( SELECT * FROM ' . $wpdb->term_relationships . ' WHERE (';
                    foreach ( $values as $value ) {
                        if ( $value !== $values[0] ) { $sql .= ' OR '; }
                        $term = get_term_by( 'name', $value, $taxonomy, OBJECT );
                        $sql .= 'term_taxonomy_id = ' . $term->term_taxonomy_id; 
                    }
                    $sql .= ') AND object_id = p.ID )';
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