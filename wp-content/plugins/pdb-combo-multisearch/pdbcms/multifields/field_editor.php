<?php
/**
 * produces a single field editor display
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2020  xnau webdesign
 * @license    GPL3
 * @version    0.1
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdbcms\multifields;

class field_editor {

  /**
   * @var pdbcms\multifields\search_field the search field instance
   */
  private $search_field;

  /**
   * sets up the field editor
   * 
   * @param pdbcms\multifields\search_field $search_field
   */
  public function __construct( search_field $search_field )
  {
    $this->search_field = $search_field;
  }
  
  /**
   * provides the field editor html
   * 
   * @param pdbcms\multifields\search_field $search_field
   * @return string HTML
   */
  public static function field_editor_html( $search_field )
  {
    $editor = new self($search_field);
    return $editor->html();
  }

  /**
   * provides the field editor HTML
   * 
   * @return string HTML
   */
  public function html()
  {
    $field = $this->field_def();
    return sprintf(
            $this->field_editor_template(),
            $this->search_field->name(),
            $this->field_title(),
            $field->form_element_title(),
            $this->editor_inputs()
    );
  }

  /**
   * provides the field editor template
   * 
   * template values:
   *  1 - field name
   *  2 - field title
   *  3 - form element title
   *  4 - option inputs
   * 
   * @return string
   */
  private function field_editor_template()
  {
    ob_start();
    ?>
    <div class="field-editor" data-fieldname="%1$s" >
        <div class="editor-tools">
          <span class="delete-field dashicons dashicons-no"></span>
          <a class="dragger" href="#"><span class="dashicons dashicons-sort"></span></a>
        </div>
        <div class="field-title-group">
          <h3 class="field-title">%2$s</h3>
          <h3 class="field-form-element">%3$s</h3>
        </div>
        %4$s
    </div>
    <?php
    return ob_get_clean();
  }
  
  /**
   * provides the input fields for the editor
   * 
   * @return string HTML
   */
  private function editor_inputs()
  {
    $inputs = array();
    
    foreach ( $this->field_form_element()->editor_options() as $option => $value ) {
      
      if ( $value ) {
        $inputs[] = $this->{$option . '_input'}();
      }
    }
    
    return implode( PHP_EOL, $inputs );
  }

  /**
   * provides the field label input html
   * 
   * @return string
   */
  private function label_input()
  {
    return '
      <div class="input-group input-group-label">
        <input type="text" id="' . $this->id_att( 'label' ) . '" name="' . $this->input_name( 'label' ) . '" value="' . htmlspecialchars( $this->search_field->label() ) . '" />
        <label for="' . $this->id_att( 'label' ) . '">' . __( 'Label', 'pdb-combo-multisearch' ) . '</label>
      </div>';
  }

  /**
   * provides the input for the help text option
   * 
   * @return string
   */
  private function help_text_input()
  {
    return '
      <div class="input-group input-group-help_text">
        <textarea id="' . $this->id_att('help_text') . '" name="' . $this->input_name( 'help_text' ) . '" >' . htmlspecialchars( $this->search_field->help_text ) . '</textarea>
        <label for="' . $this->id_att('help_text') . '">' . __( 'Help Text', 'pdb-combo-multisearch' ) . '</label>
      </div>';
  }

  /**
   * provides the input for the help text option
   * 
   * @return string
   */
  private function attributes_input()
  {
    return '
      <div class="input-group input-group-attributes">
        <textarea id="' . $this->id_att('attributes') . '" name="' . $this->input_name( 'attributes' ) . '" >' . $this->search_field->attributes_string() . '</textarea>
        <label for="' . $this->id_att('attributes') . '">' . __( 'Attributes', 'pdb-combo-multisearch' ) . '</label>
      </div>';
  }

  /**
   * provides the form element selector group
   * 
   * @return string
   */
  private function form_element_input()
  {
    return '
      <div class="input-group input-group-form_element">
        <label for="' . $this->id_att('form_element') . '">' . __( 'Input Type', 'pdb-combo-multisearch' ) . '
          <select id="' . $this->id_att('form_element') . '" name="' . $this->input_name( 'form_element' ) . '" >' . $this->form_element_options() . '</select>
        </label>
      </div>';
  }

  /**
   * provides the HTML for the "any" option input
   * 
   * @return string HTML
   */
  private function any_option_input()
  {
    $checked = $this->search_field->any_option ? 'checked' : '';
    return '
      <div class="input-group input-group-any_option">
        <label >
          <input type="hidden" name="' . $this->input_name( 'any_option' ) . '" value="0" />
          <input type="checkbox" name="' . $this->input_name( 'any_option' ) . '" value="1" ' . $checked . ' />
            ' . __( 'Show "Any" Option', 'pdb-combo-multisearch' ) . '</label>
        </div>
      <div class="input-group input-group-any_option">
        <input type="text" id="' . $this->id_att('any_option_title') . '" name="' . $this->input_name( 'any_option_title' ) . '" value="' . $this->search_field->any_option_title . '" />
        <label for="' . $this->id_att('any_option_title') . '">' . __( '"Any" Option Label', 'pdb-combo-multisearch' ) . '</label>
      </div>';
  }

  /**
   * provides the db values in dropdown option input HTML
   * 
   * @return string HTML
   */
  private function db_values_input()
  {
    $checked = $this->search_field->db_values ? 'checked' : '';
    return '
      <div class="input-group input-group-db_values">
          <input type="hidden" name="' . $this->input_name( 'db_values' ) . '" value="0" />
        <label >
          <input type="checkbox" name="' . $this->input_name( 'db_values' ) . '" value="1" ' . $checked . ' />
            ' . __( 'Database Values in Selector', 'pdb-combo-multisearch' ) . '
        </label>
      </div>';
  }

  /**
   * provides the multiselect "or" mode option input HTML
   * 
   * @return string HTML
   */
  private function or_mode_input()
  {
    $checked = $this->search_field->or_mode ? 'checked' : '';
    return '
      <div class="input-group input-group-or_mode">
          <input type="hidden" name="' . $this->input_name( 'or_mode' ) . '" value="0" />
        <label>
          <input type="checkbox" name="' . $this->input_name( 'or_mode' ) . '" value="1" ' . $checked . ' />
            ' . __( 'Multiselect "Or" Mode', 'pdb-combo-multisearch' ) . '
        </label>
      </div>';
  }
  

  /**
   * provides the include name in result option input HTML
   * 
   * @return string HTML
   */
  private function name_in_result_input()
  {
    $checked = $this->search_field->name_in_result ? 'checked' : '';
    return '
      <div class="input-group input-group-or_mode">
          <input type="hidden" name="' . $this->input_name( 'name_in_result' ) . '" value="0" />
        <label>
          <input type="checkbox" name="' . $this->input_name( 'name_in_result' ) . '" value="1" ' . $checked . ' />
            ' . __( 'Include Field Label in Result Summary', 'pdb-combo-multisearch' ) . '
        </label>
      </div>';
  }
  
  /**
   * provides an id attribute value
   * 
   * @param string $input_name
   * @return string
   */
  private function id_att( $input_name )
  {
    return $this->search_field->name() . '-' . $input_name;
  }
  
  /**
   * provides an input name value
   * 
   * @param string $input_name
   * @return string
   */
  private function input_name( $input_name )
  {
    return $this->search_field->name() . '[' . $input_name . ']';
  }
  
  /**
   * provides the field definition object
   * 
   * @return \PDb_Form_Field_Def instance
   */
  private function field_def()
  {
    return new \PDb_Form_Field_Def($this->search_field->name());
  }

  /**
   * provides the form element options
   * 
   * @return string HTML
   */
  private function form_element_options()
  {
    $form_element_list = array();

    foreach ( $this->field_form_element()->form_element_options() as $form_element_slug ) {
      $form_element_list[$form_element_slug] = $this->form_element_title($form_element_slug);
    }

    return $this->option_set( $form_element_list );
  }

  /**
   * provides a set of HTML options, given an array
   * 
   * @param array $options as $value => $title
   * @return string
   */
  private function option_set( $options )
  {
    $template = '<option value="%s" %s >%s</option>';
    $return = array();

    foreach ( $options as $value => $title ) {
      
      $selected = $value === $this->search_field->search_control_type() ? 'selected' : '';
      
      $return[] = sprintf( $template, $value, $selected, $title );
    }

    return implode( PHP_EOL, $return );
  }
  
  /**
   * provides the field form element object
   * 
   * @return \pdbcms\multifields\form_element
   */
  private function field_form_element()
  {
    return new form_element( $this->field_def() );
  }
  
  /**
   * provides the field title display
   * 
   * @return string
   */
  private function field_title()
  {
    return sprintf( '%s (%s)', $this->search_field->title(), $this->search_field->name() );
  }

  /**
   * provides the title of a form element
   * 
   * @param string $form_element_name
   * @return string
   */
  private function form_element_title( $form_element_name )
  {
    $pdb_form_elements = \PDb_FormElement::get_types();

    if ( array_key_exists( $form_element_name, $pdb_form_elements ) ) {
      return $pdb_form_elements[$form_element_name];
    }

    // special form elements
    switch ( $form_element_name ) {

      case 'date_range':
      case 'timestamp_range':
      case 'numeric_range':
      case 'currency_range':
      case 'decimal_range':
        $basename = str_replace('_range', '', $form_element_name);
        return sprintf( _x( '%s Range', 'title for a numeric range input, the type name is the replaced text', 'pdb-combo-multisearch' ), $pdb_form_elements[$basename] );

      case 'db_dropdown':
        return __( 'Database Dropdown', 'pdb-combo-multisearch' );
        
      case 'multi-or-dropdown':
      case 'multi-or-checkbox':
        return $pdb_form_elements[ str_replace( '-or', '', $form_element_name ) ];
    }

    \Participants_Db::debug_log( __METHOD__ . ' unknown form element: ' . $form_element_name );
    return '';
  }

}
