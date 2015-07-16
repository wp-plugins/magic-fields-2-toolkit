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
        return <<<EOD
<div class="mf2tk-field-input-optional">This field has been superseded by the "Insert Template" button.</div>';
EOD;
    }
}

?>

