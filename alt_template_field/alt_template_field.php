<?php

add_action( 'admin_enqueue_scripts', function( ) {
    wp_enqueue_script( 'jquery-ui-draggable' );
    wp_enqueue_script( 'jquery-ui-droppable' );
} );

class alt_template_field extends mf_custom_fields {

  //Properties
  public  $css_script = FALSE;
  public  $js_script = FALSE;
  public  $js_dependencies = array();
  public  $allow_multiple = FALSE;
  public  $has_properties = TRUE;
  
    public function get_properties() {
        $properties['css']              = $this->css_script;
        $properties['js_dependencies']  = $this->js_dependencies;
        $properties['js']               = $this->js_script;
        return $properties;
    }

    public function _update_description(){
        global $mf_domain;
        $this->description = __( 'Content Template Shortcodes'
            . ' - This is a pseudo field for generating shortcodes to display your content templates.', $mf_domain);
    }
    
    public function _options(){
        global $mf_domain;
        return [ 'option'  => [] ];
    }
    
    public function display_field( $field, $group_index = 1, $field_index = 1 ) {
        global $mf_domain;
        global $post;
        global $wpdb;
        $default_height = 50;
        $post_type = $post->post_type;
        $MF_TABLE_CUSTOM_FIELDS = MF_TABLE_CUSTOM_FIELDS;
        $results = $wpdb->get_results( <<<EOD
SELECT post_name, post_title, post_content FROM $wpdb->posts
    WHERE post_type = 'content_macro' AND post_status = 'publish' AND post_name NOT LIKE 'search-result-template-for-%';
EOD
            , OBJECT );
        $output  = '<div class="mf2tk-field-input-optional">';
        $output .= '<h6>How to Use</h6><div class="mf2tk-field_value_pane" style="clear:both;">';
        $output .= '<select id="mf2tk-alt_template-select" onchange="window.mf2tk_alt_template.select_onchange(this);">';
        foreach ( $results as $i => $result ) {
            $selected = !$i ? ' selected' : '';
            $output .= "<option value='$result->post_name'$selected>$result->post_title</option>";
        }
        $output .= '</select>';
        $output .= "<input id='mf2tk-alt_template-post_name' type='text' class='mf2tk-how-to-use'
            value='[show_macro macro=\"{$results[0]->post_name}\"]' readonly><br>";
        $output .= <<<EOD
- To display this content template <button class="mf2tk-how-to-use">select,</button> copy and paste this into editor
above in <strong>Text</strong> mode.<br>
EOD;
        $output .= '</div></div>';
        $output .= '<div class="mf2tk-field-input-optional">';
        $output .= '<button class="mf2tk-field_value_pane_button">Open</button><h6>Template Definition</h6>';
        $output .= '<div class="mf2tk-field_value_pane" style="display:none;clear:both;">';
        $output .= "<textarea id='mf2tk-alt_template-post_content' rows='10' cols='80' readonly>"
            . htmlentities( $results[0]->post_content, ENT_QUOTES, 'UTF-8' ) . "</textarea></div></div>";
        $output .= '<script type="text/javascript">';
        $output .= 'window.mf2tk_alt_template={templates:{}};' . "\n";
        $output .= implode( "\n\n", array_map( function( $result ) {
            return "window.mf2tk_alt_template.templates['$result->post_name']='" . str_replace( "\n", "\\n\\\n",
                str_replace( "\r", '', htmlentities( $result->post_content, ENT_QUOTES, 'UTF-8' ) ) ) . "';";
        }, $results ) );
        $output .= <<<EOD
window.mf2tk_alt_template.select_onchange=function(select){
    var template=window.mf2tk_alt_template.templates[
        select.parentNode.parentNode.parentNode.querySelector("select#mf2tk-alt_template-select").value];
    var matches=template.match(/\\$#(\w+)#/g);
    var parms={};
    if(matches){matches.forEach(function(v){parms[v]=true;});}
    var macro='[show_macro macro="'+select.parentNode.parentNode.parentNode
        .querySelector("select#mf2tk-alt_template-select").value+'"';
    for(parm in parms){macro+=" "+parm.slice(2,-1)+'=""';}
    macro+="]";
    select.parentNode.parentNode.parentNode.querySelector("input#mf2tk-alt_template-post_name").value=macro;
    select.parentNode.parentNode.parentNode.querySelector("textarea#mf2tk-alt_template-post_content").innerHTML=template;
};
jQuery("select#mf2tk-alt_template-select").change();
EOD;
        $output .= '</script>';
        return $output;
    }
 }
