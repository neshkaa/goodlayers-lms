<?php

/**
 * manages properties of search fields according to the form element
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2020  xnau webdesign
 * @license    GPL3
 * @version    1.0
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdbcms\multifields;

class form_element {

  /**
   * @var string name of the base form element
   */
  private $form_element;
  
  /**
   * @var \PDb_Form_Field_Def instance
   */
  private $field_def;

  /**
   * sets up the instance
   * 
   * @param \PDb_Form_Field_Def $field_def
   */
  public function __construct( $field_def )
  {
    $this->field_def = $field_def;
    $this->form_element = $field_def->form_element();
  }

  /**
   * provides the list of configuration editor options for the form element
   * 
   * @return array of options for the search field editor
   */
  public function editor_options()
  {
    switch ( $this->element_option_set() ) {

      case 'text':

        $options = array(
            'form_element' => array( 'text-line', 'db_dropdown' )
            );
        break;
      
      case 'hidden':

        $options = array(
            'form_element' => array( 'text-line', 'db_dropdown' )
            );
        break;
      
      case 'numeric':

        $options = array(
            'form_element' => array( 'numeric', 'numeric_range' )
            );
        break;

      case 'select':

        $options = array(
            'form_element' => array( 'dropdown', 'multi-or-dropdown', 'multi-or-checkbox' ),
            'db_values' => true,
            'any_option' => true,
            'or_mode' => true,
        );
        break;

      case 'radio-other':

        $options = array(
            'form_element' => array( 'radio', 'dropdown', 'multi-or-checkbox' ),
            'db_values' => true,
            'any_option' => true,
            'or_mode' => true,
        );
        break;
      
      case 'multiselect':
        
        $options = array(
            'form_element' => array( 'dropdown', 'multi-or-dropdown', 'multi-or-checkbox' ),
            'any_option' => true,
            'db_values' => true,
            'or_mode' => true,
        );
        break;
      
      case 'radio':
        
        $options = [
            'form_element' => ['radio','dropdown'],
            'any_option' => true,
        ];
        break;
      
      case 'checkbox':
        
        $options = array(
            'form_element' => ['multi-or-checkbox','dropdown'],
            'any_option' => $this->multi_option_checkbox() ? true : false,
        );
        
        break;
      
      case 'chosen':
        
        $options = array(
            'form_element' => array( str_replace( '-other', '', $this->form_element ), 'text-line' ),
            'any_option' => true,
            'db_values' => true,
            'or_mode' => true,
        );
        break;
        
      
      case 'default':
      default:
        
        $options = array(
            'form_element' => array( $this->form_element, 'text-line' )
            );
    }
    
    if ( \pdbcms\Plugin::chosen_active() )
    {
      $options['form_element'] = array_merge( $options['form_element'], $this->chosen_selector() );
    }
    
    /**
     * @filter pdbcms-search_field_options
     * @param array of search field options for the form element
     * @param string name of the base form element
     * @return array of options
     */
    return apply_filters( 'pdbcms-search_field_options', array_merge( $this->default_options(), $options ), $this->form_element );
  }
  
  /**
   * provides the option set name for the form element
   * 
   * what this does is define several different option sets that are used to configure 
   * the multi search field according to the base form element
   * 
   * @return string option set name
   */
  public function element_option_set()
  {
    switch ( $this->form_element ) {

      case 'text-line':
      case 'hidden':
      case 'image-upload':
      case 'file-upload':
      case 'link':
      case 'string-combine':
      case 'date-calc':

        $option_set = 'text';
        break;

      case 'timestamp':
      case 'date':
      case 'numeric':
      case 'currency':
      case 'decimal':
      case 'numeric-calc':

        $option_set = 'numeric';
        break;

      case 'dropdown':
      case 'dropdown-other':

        $option_set = 'select';
        break;
      
      case 'multi-dropdown':
      case 'multi-checkbox':
      case 'multi-select-other':
        
        $option_set = 'multiselect';
        break;
      
      case 'checkbox':
        
        $option_set = 'checkbox';
        break;
      
      case 'radio':
        
        $option_set = 'radio';
        break;
      
      case 'select-other':
        
        $option_set = 'radio-other';
        break;
      
      case 'chosen-dropdown':
      case 'chosen-dropdown-other':
      case 'chosen-multi-dropdown':
      case 'chosen-multi-dropdown-other':
        
        $option_set = 'chosen';
        break;
      
      default:
        $option_set = 'default';
    }
    
    /**
     * @filter pdbcms-editor_options_set
     * @param string the option set
     * @param string form element name
     * @return string option set
     */
    return apply_filters( 'pdbcms-editor_options_set', $option_set, $this->form_element );
  }
  
  /**
   * provides the search form element options
   * 
   * @return array
   */
  public function form_element_options()
  {
    $options = $this->editor_options();
    return $options['form_element'];
  }

  /**
   * provides the default option set
   * 
   * @return array
   */
  private function default_options()
  {
    return array(
        'label' => true,
        'help_text' => true,
        'attributes' => true,
        'form_element' => false,
        'db_values' => false,
        'any_option' => false,
        'or_mode' => false,
        'name_in_result' => true,
    );
  }
  
  /**
   * tells if a field has more than 1 option
   * 
   * @return bool
   */
  private function multi_option_checkbox()
  {
    $null_select_key = \PDb_FormElement::null_select_key();
    $clean_options = $this->field_def->options();
    
    if ( array_key_exists( $null_select_key, $clean_options ) ) {
      unset( $clean_options[$null_select_key] );
    }
    
    return count( $clean_options ) > 1;
  }
  
  /**
   * provides an array of chosen selector options
   * 
   * @return array
   */
  private function chosen_selector()
  {
    $chosen_options = [];
    
    switch ( $this->element_option_set() ) {

      case 'text':
      case 'hidden':
        
        $chosen_options = ['chosen-dropdown'];
        break;

      case 'select':
      case 'radio-other':
      case 'multiselect':

        $chosen_options = ['chosen-dropdown','chosen-multi-dropdown'];
        break;
        
      
      case 'numeric':
      case 'checkbox':
      case 'chosen':
      case 'default':
      default:
    }
    
    return $chosen_options;
  }

}
