<?php

class alt_url_field extends mf_custom_fields {

    public static $suffix_label = '_mf2tk_label';

    public function _update_description( ) { $this->description = __( "URL field", 'mf2tk' ); }
  
    public function _options( ) {
        return [
            'option' => [
                'target' => [
                    'type'        => 'select',
                    'id'          => 'target',
                    'label'       => __( 'target', 'mf2tk' ),
                    'name'        => 'mf_field[option][target]',
                    'default'     => 'blank',
                    'options'     => [
                                         'blank'  => '_blank',
                                         'self'   => '_self',
                                         'top'    => '_top',
                                         'parent' => '_parent'
                                     ],
                    'add_empty'   => false,
                    'description' => 'where to open the linked document',
                    'value'       => '',
                    'div_class'   => '',
                    'class'       => ''
                ]
            ]
        ];
    }
  
    public function display_field( $field, $group_index = 1, $field_index = 1 ) {
        global $mf_post_values;
        $value = array_key_exists( 'input_value', $field ) ? $field['input_value'] : '';
        $label_field_name = $field['name'] . self::$suffix_label;
        $label_field_id = "mf2tk-$label_field_name-$group_index-$field_index";
        $label_input_name = "magicfields[$label_field_name][$group_index][$field_index]";
        $label_input_value = ( !empty( $mf_post_values[$label_field_name][$group_index][$field_index] ) )
            ? $mf_post_values[$label_field_name][$group_index][$field_index] : '';
        $index = $group_index === 1 && $field_index === 1 ? '' : "<$group_index,$field_index>";
        return <<<EOT
<div class="text_field_mf">
    <div class="mf2tk-field-input-main">
        <div class="mf2tk-field_value_pane">
            <div>URL:
                <button class="mf2tk-test-load-button" style="float:right;" onclick="event.preventDefault();
                    window.open(this.parentNode.querySelector('input[type=\'url\']').value,'_blank');">Test Load</button>
                <input type="url" name="$field[input_name]" placeholder="$field[label]"
                    value="$value" maxlength="2048" style="clear:both;display:block;" />
            </div>
            <div>label: (e.g. &lt;a href="..."&gt;the label is displayed here&lt;/a&gt;)
                <input type="text" name="$label_input_name" placeholder="display label for $field[label]"
                    value="$label_input_value" style="clear:both;display:block;" />
            </div>
        </div>
    </div>
    <!-- usage instructions -->
    <div class="mf2tk-field-input-optional">
        <button class="mf2tk-field_value_pane_button">Open</button>
        <h6>How to Use</h6>
        <div class="mf2tk-field_value_pane" style="display:none;clear:both;">
            <ul>
                <li style="list-style:square inside">Use with the Toolkit's shortcode:<br>
                    <input type="text" class="mf2tk-how-to-use" size="50" readonly
                        value='[show_custom_field field="$field[name]$index" filter="url_to_link2"]'>
                    - <button class="mf2tk-how-to-use">select,</button> copy and paste this into editor above in
                        <strong>Text</strong> mode
                <li style="list-style:square inside">Call the PHP function:<br>
                    alt_url_field::get_url( "$field[name]", $group_index, $field_index, \$post_id )
            </ul>
        </div>
    </div>
</div>
<!-- $field[input_validate] -->
EOT;
    }
    
    public static function get_url( $field_name, $group_index = 1, $field_index = 1, $post_id = null ) {
        global $post;
        if ( $post_id === null ) { $post_id = $post->ID; }
        $data = get_data2( $field_name, $group_index, $field_index, $post_id );
        $value = $data['meta_value'];
        $target = '_' . $data['options']['target'];
        $label = _mf2tk_get_optional_field( $field_name, $group_index, $field_index, $post_id, self::$suffix_label );
        return "<a href=\"$value\" target=\"$target\">$label</a>";
    }

}
