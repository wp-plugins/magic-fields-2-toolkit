<?php

class alt_numeric_field extends mf_custom_fields {

    public function _update_description( ) {
        $this->description = __( "Numeric field with currency prefix and/or unit suffix", 'mf2tk' );
    }
  
    public function _options(){
        return [
            'option' => [
                'precision' => [
                    'type'        => 'text',
                    'id'          => 'numeric_precision',
                    'label'       => __( 'Precision', 'mf2tk' ),
                    'name'        => 'mf_field[option][precision]',
                    'description' => __( 'number of decimal places; 0 for integers - second parameter to number_format()',
                                         'mf2tk' ),
                    'value'       => '2',
                    'div_class'   => '',
                    'class'       => ''
                ],
                'unit'=> [
                    'type'        => 'text',
                    'id'          => 'numeric_unit',
                    'label'       => __( 'Unit of Measurement', 'mf2tk' ),
                    'name'        => 'mf_field[option][unit]',
                    'description' => esc_attr__( 'unit of measurement, e.g. "in", "sq mi", " fl oz", ... or "%" ' .
                                         'or counter e.g. "item:items", " man: men" given as singular:plural pair ' .
                                         '- really just a suffix to append to the value', 'mf2tk' ),
                    'value'       => '',
                    'div_class'   => '',
                    'class'       => ''
                ],
                'currency'=> [
                    'type'        => 'text',
                    'id'          => 'numeric_currency',
                    'label'       => __( 'Currency', 'mf2tk' ),
                    'name'        => 'mf_field[option][currency]',
                    'description' => __( 'currency code e.g. $, &amp;euro;, &amp;#128;, &amp;#x80; ... ' .
                                         '- really just a prefix to prepend to the value', 'mf2tk' ),
                    'value'       => '$',
                    'div_class'   => '',
                    'class'       => ''
                ],
                'min' => [
                    'type'        => 'text',
                    'id'          => 'numeric_min',
                    'label'       => __( 'Minimum', 'mf2tk' ),
                    'name'        => 'mf_field[option][min]',
                    'description' => __( 'minimum', 'mf2tk' ),
                    'value'       => '0',
                    'div_class'   => '',
                    'class'       => ''
                ],
                'max' => [
                    'type'        => 'text',
                    'id'          => 'numeric_max',
                    'label'       => __( 'Maximum', 'mf2tk' ),
                    'name'        => 'mf_field[option][max]',
                    'description' => __( 'maximum', 'mf2tk' ),
                    'value'       => '',
                    'div_class'   => '',
                    'class'       => ''
                ],
                'step' => [
                    'type'        => 'text',
                    'id'          => 'numeric_step',
                    'label'       => __( 'Step Size', 'mf2tk' ),
                    'name'        => 'mf_field[option][step]',
                    'description' => __( 'step size', 'mf2tk' ),
                    'value'       => '1',
                    'div_class'   => '',
                    'class'       => ''
                ],
                'decimal_point' => [
                    'type'        => 'text',
                    'id'          => 'numeric_decimal_point',
                    'label'       => __( 'Decimal Point', 'mf2tk' ),
                    'name'        => 'mf_field[option][decimal_point]',
                    'description' => __( 'The separator for the decimal point - third parameter to number_format()', 'mf2tk' ),
                    'value'       => '.',
                    'div_class'   => '',
                    'class'       => ''
                ],
                'thousands_separator' => [
                    'type'        => 'text',
                    'id'          => 'numeric_thousands_separator',
                    'label'       => __( 'Thousands Separator', 'mf2tk' ),
                    'name'        => 'mf_field[option][thousands_separator]',
                    'description' => __( 'The thousands separator - fourth parameter to number_format()', 'mf2tk' ),
                    'value'       => ',',
                    'div_class'   => '',
                    'class'       => ''
                ],
            ]
        ];
    }
  
    public function display_field( $field, $group_index = 1, $field_index = 1 ) {
        error_log( 'alt_numeric_field::display_field():$field=' . print_r( $field, true ) );
        $options =& $field['options'];
        $min  = ( array_key_exists( 'min',  $options ) && is_numeric ( $options['min']  ) )
                    ? " min  = \"$options[min]\""  : '';
        $max  = ( array_key_exists( 'max',  $options ) && is_numeric ( $options['max']  )
                        && $options['max'] > $options['min'] )
                    ? " max  = \"$options[max]\""  : '';
        $step = ( array_key_exists( 'step', $options ) && is_numeric ( $options['step'] ) && $options['step'] > 0 )
                    ? " step = \"$options[step]\"" : '';
        $currency = ( array_key_exists( 'currency',    $options ) ) ? $options['currency']  : '';
        $unit     = ( array_key_exists( 'unit',        $options ) ) ? $options['unit']      : '';
        $value    = ( array_key_exists( 'input_value', $field   ) ) ? $field['input_value'] : '';
        if ( strpos( $unit, ':' ) ) {
            $unit = explode( ':', $unit );
            $unit = $value == 1 ? $unit[0] : $unit[1];
        }
        $index = $group_index === 1 && $field_index === 1 ? '' : "<$group_index,$field_index>";
        return <<<EOT
<div class="text_field_mf">
    <div class="mf2tk-field-input-main">
        <div class="mf2tk-field_value_pane">
            <div><span>$currency</span><input type="number" name="$field[input_name]" placeholder="$field[label]"
                value="$value" $min$max$step style="display:inline-block;text-align:right;width:16em;" /><span>$unit</span>
            </div>
        </div>
        <div style="font-size:75%;margin:5px 50px;">Value has $options[precision] decimal places.</div>
    </div>
    <!-- usage instructions -->
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
                    alt_numeric_field::get_numeric( "$field[name]", $group_index, $field_index, \$post_id )
            </ul>
        </div>
    </div>
</div>
<!-- $field[input_validate] -->
EOT;
    }
    
    public static function get_numeric( $field_name, $group_index = 1, $field_index = 1, $post_id = null ) {
        global $post;
        if ( $post_id === null ) { $post_id = $post->ID; }
        error_log( 'alt_numeric_field::get_numeric():$field_name=' . $field_name );
        $data = get_data( $field_name, $group_index, $field_index, $post_id );
        error_log( 'alt_numeric_field::get_numeric():$data=' . print_r( $data, true ) );
        $value   = $data['meta_value'];
        $options = $data['options'];
        $unit    = $options['unit'];
        if ( strpos( $unit, ':' ) ) {
            $unit = explode( ':', $unit ); 
            if ( $value == 1 ) { $unit = $unit[0]; }
            else { $unit = $unit[1]; }
        }        
        return $options['currency']. number_format( $value, $options['precision'], $options['decimal_point'],
            $options['thousands_separator'] ) . $unit;
    }

}
