<?php
/*
 * This code derived from "dropdown_field.php" of Magic Fields 2 by Hunk and Gnuget
 * License: GPL2
 */

 // initialisation
global $mf_domain;


// class with static properties encapsulating functions for the field type

class alt_dropdown_field extends mf_custom_fields {

  public $allow_multiple = TRUE;
  public $has_properties = TRUE;
  
  public function _update_description(){
    global $mf_domain;
    $this->description = __("Dropdown with optional textbox",$mf_domain);
  }
  
  public function _options(){
    global $mf_domain;
    
    $data = array(
      'option'  => array(
        'options'  => array(
          'type'        =>  'textarea',
          'id'          =>  'dropdown_options',
          'label'       =>  __('Options',$mf_domain),
          'name'        =>  'mf_field[option][options]',
          'default'     =>  '',
          'description' =>  __( 'Separate each option with a newline.', $mf_domain ),
          'value'       =>  '',
          'div_class'   => '',
          'class'       => ''
        ),
        'multiple' =>  array(
          'type'        =>  'checkbox',
          'id'          =>  'multiple_dropdown_options',
          'label'       =>  __('The dropdown can have multiple values', $mf_domain ),
          'name'        =>  'mf_field[option][multiple]',
          'default'     =>  '',
          'description' =>  '',
          'value'       =>  '',
          'div_class'   =>  '',
          'class'       =>  ''
        ),
        'default_value'  => array(
          'type'        =>  'text',
          'id'          =>  'dropdown_default_value',
          'label'       =>  __('Default value',$mf_domain),
          'name'        =>  'mf_field[option][default_value]',
          'default'     =>  '',
          'description' =>  __( 'Separate each value with a newline.', $mf_domain ),
          'value'       =>  '',
          'div_class'    => '',
          'class'       => ''
        )
      )
    );
    
    return $data;
  }
  
  public function display_field( $field, $group_index = 1, $field_index = 1 ) {
    $output = '';
    $div_id = sprintf( 'div-alt-dropdown-%d-%d-%d', $field['id'], $group_index, $field_index );
    $output .= '<div id="' . $div_id . '">';
    $is_multiple = ($field['options']['multiple']) ? true : false;

    $check_post_id = null;
    if(!empty($_REQUEST['post'])) {
      $check_post_id = $_REQUEST['post'];
    }

    $values = array();
    
    if($check_post_id) {
      $values = ($field['input_value']) ? (is_serialized($field['input_value']))? unserialize($field['input_value']): (array)$field['input_value'] : array() ;
    }else{
      $values[] = $field['options']['default_value'];
    }
 
    foreach($values as &$val){
      $val = trim($val);
    }

    $options = preg_split("/\\n/", $field['options']['options']);
    #error_log( '##### alt_dropdown_field.php:$options=' . print_r( $options, TRUE ) );
    $output .= '<div class="mf-dropdown-box">';

    $multiple = ($is_multiple) ? 'multiple="multiple"' : '';
    $output .= sprintf('<select class="dropdown_mf" id="%s" name="%s[]" %s >',$field['input_id'],$field['input_name'],$multiple);
    foreach($options as $option) {
      $option = trim($option);
      if ( !$option ) { continue; }
      $check = in_array($option,$values) ? 'selected="selected"' : '';

      $output .= sprintf('<option value="%s" %s >%s</option>',
        esc_attr($option),
        $check,
        esc_attr($option)
      );
    }
    $output .= '<option value="add-new">--Enter New Value--</option>';
    $output .= '</select>';
    $output .= '</div>';
    $output .= '<div class="text_field_mf" >';
    $output .= sprintf('<input type="text" placeholder="%s" style="display:none;" />', $field['label'] );
    $output .= '</div></div><script type="text/javascript">';
    $output .= 'jQuery("div#' . $div_id . ' select").change(function(){';
    $output .= <<<'EOD'
    //console.log(jQuery("option:selected",this).text());
    if(jQuery("option:selected:last",this).text()=="--Enter New Value--"){
        jQuery(this).css("display","none");
        var input=jQuery("input",this.parentNode.parentNode).css("display","inline").val("").get(0);
        input.focus();
        input.select();
    }
});
EOD;
    $output .= 'jQuery("div#' . $div_id . ' input").change(function(){';
    $output .= <<<'EOD'
    //console.log(jQuery(this).val());
    //console.log(jQuery(this).text());
    var value=jQuery(this).val();
    var select=jQuery("select",this.parentNode.parentNode);
    jQuery("option:last",select).prop("selected",false);
    if(value){select.prepend('<option value="'+value+'" selected>'+value+'</option>');}
    select.css("display","inline");
    jQuery(this).val("").css("display","none");
});
EOD;
    $output .= 'jQuery("div#' . $div_id . ' input").keydown(function(e){';
    $output .= <<<'EOD'
    if(e.keyCode==13){
        //console.log('keydown:e.keyCode='+e.keyCode);
        jQuery(this).blur();
        return false;
    }
});
EOD;
    $output .= 'jQuery("div#' . $div_id . ' input").blur(function(){jQuery(this).change();});';
#//jQuery("form#search-using-magic-fields").on('keypress',function(e){if(e.which==13){e.preventDefault();return false;}});
    $output .= '</script>';
    return $output;
  }
}
