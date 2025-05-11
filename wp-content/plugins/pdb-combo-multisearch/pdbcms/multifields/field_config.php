<?php

/**
 * provides the legacy multisearch settings
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

class field_config {
  
  /**
   * provides a list of all the config fields with validation filter
   * 
   * @return array as $name => validation
   */
  public static function config_fields()
  {
    return array(
        'name' => self::string_sanitize(),
        'title' => self::string_sanitize(),
        'label' => self::text_sanitize(),
        'form_element' => self::string_sanitize(),
        'help_text' => self::text_sanitize(),
        'attributes' => self::string_sanitize(),
        'any_option' => FILTER_VALIDATE_BOOLEAN,
        'any_option_title' => self::string_sanitize(),
        'or_mode' => FILTER_VALIDATE_BOOLEAN,
        'db_values' => FILTER_VALIDATE_BOOLEAN,
        'name_in_result' => FILTER_VALIDATE_BOOLEAN,
    );
  }
  
  /**
   * provides the string sanitize method
   * 
   * @return array
   */
  protected static function string_sanitize()
  {
    return array_merge( \Participants_Db::string_sanitize(), array( 'filter' => FILTER_DEFAULT ) );
  }
  
  /**
   * provides the custom text filter
   * 
   * this one allows some tags
   */
  protected static function text_sanitize()
  {
    return array( 'filter' => FILTER_CALLBACK, 'options' => 'pdbcms\multifields\updates::text_sanitize' ); 
  }

  /**
   * provides the config object for a field based on the legacy settings
   * 
   * @param \PDb_Form_Field_Def $field
   * 
   * @return \stdClass
   */
  protected function field_config( $field )
  {
    $config = new \stdClass();

    $config->name = $field->name();
    $config->title = $field->title();
    $config->label = $field->title();
    $config->form_element = $this->form_element_setting( $field->form_element() );
    $config->help_text = $this->legacy_help_text( $field->name() );
    $config->attributes = $field->attributes();
    $config->any_option = $this->inherit_any_option( $field );
    $config->any_option_title = $this->inherit_any_option_label( $field );
    $config->or_mode = (bool) $this->plugin_setting( 'multiselect_or' );
    $config->db_values = false;
    $config->name_in_result = false;

    return $config;
  }

  /**
   * provides the form element setting for the field
   * 
   * @param string $form_element the base form element of the field
   * @return the form element setting for the input field
   */
  protected function form_element_setting( $form_element )
  {
    switch ( $form_element ) {

      case 'text-line':
      case 'hidden':
      case 'image-upload':
      case 'file-upload':
        $form_element = $this->plugin_setting( 'text_as_dropdown' ) ? 'db_dropdown' : 'text-line';
        break;

      case 'date':
      case 'timestamp':
      case 'numeric':
      case 'currency':
      case 'decimal':
        $form_element = $this->plugin_setting( 'use_ranged_search' ) ? $form_element . '_range' : $form_element;
        break;
    }

    return $form_element;
  }

  /**
   * provides the plugin setting value
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   * @param string $name of the setting to get
   * @return mixed
   */
  protected function plugin_setting( $name )
  {
    global $PDb_Combo_Multi_Search;
    return $PDb_Combo_Multi_Search->plugin_option( $name );
  }

  /**
   * provides the legacy help text for a field
   * 
   * @param string $fieldname
   * @return string the help text for the field
   */
  protected function legacy_help_text( $fieldname )
  {
    $help_text_list = array();
    $field_help = $this->plugin_setting( 'field_help' );

    if ( !empty( $field_help ) ) {

      $lines = explode( ';', $field_help );
      foreach ( $lines as $line ) {
        if ( strpos( $line, ':' ) !== false ) {
          list($field, $text) = explode( ':', $line, 2 );
          $help_text_list[trim( $field )] = $text;
        }
      }
    }

    return isset( $help_text_list[$fieldname] ) ? $help_text_list[$fieldname] : '';
  }
  
  /**
   * provides the "any" option setting from the field def
   * 
   * @param \PDb_Form_Field_Def $field
   * @return bool
   */
  protected function inherit_any_option( $field )
  {
    return in_array( \PDb_FormElement::null_select_key(), $field->options() ) ? true : (bool) $this->plugin_setting( 'any_option' );
  }
  
  /**
   * provides the any option label from the field settings if available
   * 
   * @param \PDb_Form_Field_Def $field
   * @return bool
   */
  protected function inherit_any_option_label ( $field )
  {
    $label = $this->plugin_setting( 'any_option_title' );
    if ( $index = array_search( \PDb_FormElement::null_select_key(), $field->options() ) ) {
      $label = $index;
    }
    return $label;
  }

}
