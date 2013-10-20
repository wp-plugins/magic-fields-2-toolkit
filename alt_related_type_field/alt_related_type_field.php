<?php
/*
 * This code derived from "related_type_field.php" of Magic Fields 2 by Hunk and Gnuget
 * License: GPL2
 */

// initialisation
global $mf_domain;


// class with static properties encapsulating functions for the field type

class alt_related_type_field extends mf_custom_fields {

  public $allow_multiple = TRUE;
  public $has_properties = TRUE;
  
  public function _update_description(){
    global $mf_domain;
    $this->description = __("Checkbox list that lets a user select ONE or MORE related posts of a given post type.",$mf_domain);
  }
  
  public function _options(){
    global $mf_domain;
    
    $posttypes = $this->mf_get_post_types();
    $select = array();
    foreach($posttypes as $k => $v){
      #error_log('##### alt_related_type_field::_options():$v='.print_r($v,TRUE));
      if(in_array($v->name,array('revision','nav_menu_item','content_macro'))){continue;}
      $select[$k] = $v->label;
    }

    $data = array(
      'option'  => array(
        'post_type'  => array(
          'type'        =>  'select',
          'id'          =>  'post_type',
          'label'       =>  __('Related Type Panel (Post type)',$mf_domain),
          'name'        =>  'mf_field[option][post_type]',
          'default'     =>  '',
          'options'     => $select,
          'add_empty'   => false,
          'description' =>  '',
          'value'       =>  '',
          'div_class'   => '',
          'class'       => ''
        ),
        'field_order'  => array(
          'type'        =>  'select',
          'id'          =>  'field_order',
          'label'       =>  __('Field for order of Related type',$mf_domain),
          'name'        =>  'mf_field[option][field_order]',
          'default'     =>  '',
          'options'     => array('id' => 'ID','title' =>'Title'),
          'add_empty'   => false,
          'description' =>  '',
          'value'       =>  '',
          'div_class'   => '',
          'class'       => ''
        ),
        'order'  => array(
          'type'        =>  'select',
          'id'          =>  'order',
          'label'       =>  __('Order of Related type',$mf_domain),
          'name'        =>  'mf_field[option][order]',
          'default'     =>  '',
          'options'     => array('asc' => 'ASC','desc' =>'DESC'),
          'add_empty'   => false,
          'description' =>  '',
          'value'       =>  '',
          'div_class'   => '',
          'class'       => ''
        )
      )
    );
    
    return $data;
  }

  public function display_field($field, $group_index = 1, $field_index = 1){
    $output = '';
    $check_post_id = null;
    if( !empty($_REQUEST['post'])) {
      $check_post_id = $_REQUEST['post'];
    }

    $values = array();
    if($check_post_id){
      $values = ($field['input_value']) ? (is_serialized($field['input_value']))? unserialize($field['input_value']): (array)$field['input_value'] : array() ;
    }
	
    $type        = $field['options']['post_type'];
    $order       = $field['options']['order'];
    $field_order = $field['options']['field_order'];

    $options = get_posts( sprintf("post_type=%s&numberposts=-1&order=%s&orderby=%s&suppress_filters=0",$type,$order,$field_order) );

    $output = '<div class="mf-checkbox-list-box" >';
      
      foreach($values as &$val){
        $val = trim($val);
      }
      
    foreach($options as $option){
      $check = in_array($option->ID, $values) ? 'checked="checked"' : '';

      $output .= sprintf('<label for="%s_%s" class="selectit mf-checkbox-list">',$field['input_id'],$option->ID);
      $output .= sprintf('<input tabindex="3" class="checkbox_list_mf" id="%s_%s" name="%s[]" value="%s" type="checkbox" %s %s />',
					$field['input_id'],$option->ID,$field['input_name'],$option->ID,$check,$field['input_validate']);
      $output .= esc_attr($option->post_title);
      $output .= '</label>';
    }

    $output .= '</div>';
	#error_log( "##### alt_related_type_field::display_field() returns $output\n" );
    return $output;
  }
  
}
