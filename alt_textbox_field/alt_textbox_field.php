<?php
/*
 * This code derived from "textbox_field.php" of Magic Fields 2 by Hunk and Gnuget
 * License: GPL2
 */
 
// initialisation
global $mf_domain;


// class with static properties encapsulating functions for the field type

class alt_textbox_field extends mf_custom_fields {

  //Properties
  public  $css_script = FALSE;
  public  $js_script = FALSE;
  public  $js_dependencies = array();
  public  $allow_multiple = TRUE;
  public  $has_properties = TRUE;
  
  public function get_properties() {
    $properties['css']              = $this->css_script;
    $properties['js_dependencies']  = $this->js_dependencies;
    $properties['js']               = $this->js_script;

    return $properties;
  }

  public function _update_description(){
    global $mf_domain;
    $this->description = __('Textbox with Dropdown - useful for text fields where the same value is used in multiple posts',
        $mf_domain);
  }
  
  public function _options(){
    global $mf_domain;
    
    $data = array(
      'option'  => array(
        'evalueate'  => array(
          'type'        =>  'checkbox',
          'id'          =>  'textbox_evaluate',
          'label'       =>  __('Evaluate Max Length',$mf_domain),
          'name'        =>  'mf_field[option][evalueate]',
          'description' =>  '',
          'default'     => true,
          'value'       =>  1,
          'div_class'    => '',
          'class'       => ''
        ),
        'size'  => array(
          'type'        =>  'text',
          'id'          =>  'textbox_size',
          'label'       =>  __('Max Length',$mf_domain),
          'name'        =>  'mf_field[option][size]',
          'description' =>  'Only if evaluate max length is checked',
          'value'       =>  '25',
          'div_class'    => '',
          'class'       => ''
        )
      )
    );
    return $data;
  }
  
  public function display_field( $field, $group_index = 1, $field_index = 1 ) {
    global $mf_domain;
    global $wpdb;
    #####
    #error_log( '$$$$$ textbox_field::display_field():backtrace='
    #    . print_r( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ), TRUE ) );
    #error_log( '$$$$$ textbox_field::display_field():$field=' . print_r( $field, TRUE ) );
    #####
    $output = '';
    $output .= '<div class="text_field_mf" >';
    $output .= '<div class="mf2tk-field-input-main">';
    $max = '';
    if( $field['options']['evalueate'] && ($field['options']['size'] > 0) ){
      $max = sprintf('maxlength="%s"',$field['options']['size']);
    }
    $index = $group_index === 1 && $field_index === 1 ? '' : "<$group_index,$field_index>";
    #####
    $div_id = sprintf( 'div-alt-textbox-%d-%d-%d', $field['id'], $group_index, $field_index );
    $output .= '<div id="' . $div_id . '" class="mf2tk-field_value_pane">';
    $sql = 'select m.meta_value, count(*) from ' . $wpdb->postmeta . ' m inner join ' . $wpdb->posts
        . ' p on m.post_id = p.ID where m.meta_key = "' . $field['name'] . '" AND p.post_type = "' . $field['post_type']
        . '" GROUP BY m.meta_value ORDER BY count(*) DESC LIMIT 32';
    #error_log( '$$$$$ $sql=' . $sql );
    $values = $wpdb->get_col( $sql );
    #error_log( '$$$$$ $values=' . print_r( $values, TRUE ) );
    if ( $values ) {
        $output .= '<div class="mf-dropdown-box"><select class="dropdown_mf valid" style="width:50%;">';
        $output .= '<option>--Select ' . $field['label'] . ' or Enter New Value--';
        foreach ( $values as $value ) {
            if ( !$value ) { continue; }
            $output .= '<option>' . $value . '</option>';
        }
        $output .= '<option>--Enter New Value--</option>';
        $output .= '</select></div>';
    }
    #####
    $output .= '<div>';
    $output .= sprintf('<input %s type="text" name="%s" placeholder="%s" value="%s" %s />',$field['input_validate'], $field['input_name'], $field['label'], str_replace('"', '&quot;', $field['input_value']), $max );
    $output .= '</div>';
    $output .= '</div>';
    $output .= '<div style="font-size:75%;margin:5px 50px;">';
    $output .= 'To select from previously entered values for this field change the value of this field to a blank field';
    $output .= ' and temporarily change the focus away from this field by clicking anywhere outside this field.';
    $output .= '</div>';
    $output .= '</div>';
    $output .= <<<EOD
    <div class="mf2tk-field-input-optional">
        <button class="mf2tk-field_value_pane_button">Open</button>
        <h6>How to Use</h6>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <ul>
                <li style="list-style:square inside">Use with the Toolkit's shortcode:<br>
                    <input type="text" class="mf2tk-how-to-use" size="50" readonly
                        value='[show_custom_field field="$field[name]$index"]'>
                    - <button class="mf2tk-how-to-use">select,</button> copy and paste this into editor above in
                        <strong>Text</strong> mode
                <li style="list-style:square inside">Call the PHP function:<br>
                    get_data( "$field[name]", $group_index, $field_index, \$post_id )
            </ul>
        </div>
    </div>
EOD;
    $output .= '</div><script type="text/javascript">';
    $output .= 'jQuery("div#' . $div_id . ' select").change(function(){';
    $output .= <<<'EOD'
    var value=jQuery("option:selected",this).text();
    jQuery("option:selected",this).prop('selected',false);
    jQuery(this).css("display","none");
    if(value==="--Enter New Value--"){value="";}
    var input=jQuery("input",this.parentNode.parentNode).css("display","block").val(value).get(0);
    input.focus();
    input.select();
});
EOD;
    $output .= 'jQuery("div#' . $div_id . ' input").change(function(){';
    $output .= <<<'EOD'
    if(!jQuery(this).val()){
        jQuery(this).css("display","none");
        jQuery("select",this.parentNode.parentNode).css("display","inline-block");
    }
});
EOD;
    $output .= 'jQuery("div#' . $div_id . ' input").keydown(function(e){';
    $output .= <<<'EOD'
    if(e.keyCode==13){
        jQuery(this).blur();
        return false;
    }
});
EOD;
    #error_log( '$$$$$ textbox_field::display_field():$field[\'input_value\']="'
    #    . print_r( $field['input_value'], TRUE ) . '"' );
    if ( $field['input_value'] || !$values ){
        $output .= 'jQuery("div#' . $div_id . ' select").css("display","none")';
    } else {
        $output .= 'jQuery("div#' . $div_id . ' input").css("display","none")';
    }
    $output .= '</script>';
    return $output;
  }

 }
