<?php

add_action( 'admin_enqueue_scripts', function( ) {
    wp_enqueue_script( 'jquery-ui-draggable' );
    wp_enqueue_script( 'jquery-ui-droppable' );
} );

class alt_table_field extends mf_custom_fields {

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
        $this->description = __( 'Table of All Magic Fields'
                                . '- automatically generated shortcode to display all Magic Fields in a table', $mf_domain);
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
        error_log( 'display_field():$post=' + print_r( $post, true ) );
        $post_type = $post->post_type;
        $MF_TABLE_CUSTOM_FIELDS = MF_TABLE_CUSTOM_FIELDS;
        $results = $wpdb->get_col(
            "SELECT name FROM $MF_TABLE_CUSTOM_FIELDS WHERE post_type = '$post_type' AND type != 'alt_table'" );
        $fields = implode( ';', $results );
    $output = <<<EOD
    <div class="mf2tk-field-input-optional">
        <h6>How to Use</h6>
        <div class="mf2tk-field_value_pane" style="clear:both;">
            <textarea class="mf2tk-how-to-use mf2tk-table-shortcode" rows="10" cols="80" readonly><!-- Edit below to your liking -->
&lt;div&gt;
&lt;style scoped&gt;
table.mf2tk-alt_table{border-collapse:collapse;}
table.mf2tk-alt_table,table.mf2tk-alt_table td{border:2px solid black;}
table.mf2tk-alt_table td{padding:7px 10px 3px 10px;}
&lt;/style&gt;
&lt;table class="mf2tk-alt_table"&gt;
[show_custom_field
&nbsp;&nbsp;&nbsp;&nbsp;field="$fields"
&nbsp;&nbsp;&nbsp;&nbsp;filter="tk_value_as_color;tk_value_as_checkbox;tk_value_as_audio;tk_value_as_image__h{$default_height};tk_value_as_video__h{$default_height};url_to_link2;"
    separator=", "
    field_before="&lt;tr&gt;&lt;td&gt;&lt;!--\$Field--&gt;&lt;/td&gt;&lt;td&gt;"
    field_after="&lt;/td&gt;&lt;/tr&gt;"
]
&lt/table&gt;
&lt/div&gt;
<!-- Edit above to your liking --></textarea>
- To display a table of all Magic Fields <button class="mf2tk-how-to-use">select,</button> copy and paste this into editor
above in <strong>Text</strong> mode
        </div>
    </div>
    <!-- optional configuration field -->
    <div class="mf2tk-field-input-optional">
        <button class="mf2tk-field_value_pane_button">Open</button>
        <h6>Re-Configure Table</h6>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <fieldset class="mf2tk-configure mf2tk-fields">
                <legend>Fields:</legend>
                <!-- before drop point -->
                <div><div class="mf2tk-dragable-field-after"></div></div>
EOD;
    foreach ( $results as $field ) {
    $output .= <<<EOD
                <div class="mf2tk-dragable-field">
                    <label class="mf2tk-configure">
                        <input type="checkbox" class="mf2tk-configure" value="$field" checked>$field
                    </label>
                    <br>
                    <!-- a drop point -->
                    <div class="mf2tk-dragable-field-after"></div>
                </div>
EOD;
    }
    $output .= <<<EOD
                <span>Use drag and drop to change field order</span>
            </fieldset>
            <fieldset class="mf2tk-configure mf2tk-filters">
                <legend>Filters:</legend>
                <div>
                    <label class="mf2tk-configure">
                        <input type="checkbox" class="mf2tk-configure" value="tk_value_as_color" checked>tk_value_as_color
                    </label>
                </div>
                <div>
                    <label class="mf2tk-configure">
                        <input type="checkbox" class="mf2tk-configure" value="tk_value_as_checkbox" checked>tk_value_as_checkbox
                    </label>
                </div>
                <div>
                    <label class="mf2tk-configure">
                        <input type="checkbox" class="mf2tk-configure" value="tk_value_as_audio" checked>tk_value_as_audio
                    </label>
                </div>
                <div>
                    <label class="mf2tk-configure">
                        <input type="checkbox" class="mf2tk-configure" value="tk_value_as_image__" checked>tk_value_as_image__
                    </label>
                    <label class="mf2tk-configure">
                        <input type="radio" class="mf2tk-configure" name="tk_value_as_image_{$post_type}" value="width">Width
                    </label>
                    <label class="mf2tk-configure">
                        <input type="radio" class="mf2tk-configure" name="tk_value_as_image_{$post_type}" value="height" checked>
                        Height
                    </label>
                    <label class="mf2tk-configure">
                        <input type="number" class="mf2tk-configure" max="9999" value="$default_height">
                     </label>
                </div>
                <div>
                    <label class="mf2tk-configure">
                        <input type="checkbox" class="mf2tk-configure" value="tk_value_as_video__" checked>tk_value_as_video__
                    </label>
                    <label class="mf2tk-configure">
                        <input type="radio" class="mf2tk-configure" name="tk_value_as_video_{$post_type}" value="width">Width
                    </label>
                    <label class="mf2tk-configure">
                        <input type="radio" class="mf2tk-configure" name="tk_value_as_video_{$post_type}" value="height" checked>
                        Height
                    </label>
                    <label class="mf2tk-configure">
                        <input type="number" class="mf2tk-configure" max="9999" value="$default_height">
                     </label>
                </div>
                <div>
                    <label class="mf2tk-configure">
                        <input type="checkbox" class="mf2tk-configure" value="url_to_link2" checked>url_to_link2
                    </label>
                </div>
            </fieldset>
            <button class="mf2tk-refresh-table-shortcode">Refresh Table Shortcode</button>
        </div>
    </div>
EOD;
    return $output;
    }

 }
