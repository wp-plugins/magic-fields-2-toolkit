<?php

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
            . ' - This is a pseudo field for generating shortcodes to display your content templates.'
            . ' - This field has been superseded by the "Insert Template" button and exists only for'
            . ' backwards compatibility and should no longer be used.', $mf_domain);
    }
    
    public function _options(){
        global $mf_domain;
        return [ 'option'  => [] ];
    }
    
    public function display_field( $field, $group_index = 1, $field_index = 1 ) {
        global $mf_domain;
        $output  = '<div class="mf2tk-field-input-optional">';
        $output .= '<h6>How to Use</h6><div class="mf2tk-field_value_pane" style="clear:both;">';
        $output .= '<select id="mf2tk-alt_template-select">';
        $output .= '</select>';
        $output .= "<input id='mf2tk-alt_template-post_name' type='text' class='mf2tk-how-to-use'
            value='' readonly><br>";
        $output .= <<<EOD
- To display this content template <button class="mf2tk-how-to-use">select,</button> copy and paste this into editor
above in <strong>Text</strong> mode.<br>
EOD;
        $output .= '</div></div>';
        $output .= '<div class="mf2tk-field-input-optional">';
        $output .= '<button class="mf2tk-field_value_pane_button">Open</button><h6>Template Definition</h6>';
        $output .= '<div class="mf2tk-field_value_pane" style="display:none;clear:both;">';
        $output .= "<textarea id='mf2tk-alt_template-post_content' rows='8' readonly></textarea></div></div>";
        return $output;
    }
}

?>

