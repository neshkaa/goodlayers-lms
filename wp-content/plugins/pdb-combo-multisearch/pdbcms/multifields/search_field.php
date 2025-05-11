<?php

/**
 * models a single search field
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2020  xnau webdesign
 * @license    GPL3
 * @version    0.2
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdbcms\multifields;

class search_field {

  /**
   * @var \stdClass holds the settings object
   */
  private $settings;

  /**
   * sets up the field object
   * 
   * @param \stdClass $config
   */
  public function __construct( $config )
  {
    $this->settings = $config;
  }

  /**
   * provides raw setting values
   * 
   * @string $name
   * @return mixed
   */
  public function __get( $name )
  {
    switch ( true ) {

      case method_exists( $this, $name ):
        return $this->{$name}();

      case isset( $this->settings->$name ):
        return $this->settings->$name;
    }
    return false;
  }

  /**
   * provides the field label
   * 
   * @return string
   */
  public function title()
  {
    return $this->settings->title;
  }

  /**
   * provides the field name
   * 
   * @return string
   */
  public function name()
  {
    return $this->settings->name;
  }

  /**
   * provides the search field label
   * 
   * @return string
   */
  public function label()
  {
    return \Participants_Db::string_static_translation( $this->settings->label );
  }

  /**
   * provides the search field's attributes
   * 
   * @return array
   */
  public function attributes()
  {
    return $this->settings->attributes;
  }
  
  /**
   * provides a single attribute value
   * 
   * @param string $name attribute name
   * @return string
   */
  public function get_attribute( $name )
  {
    return isset( $this->settings->attributes[$name] ) ? $this->settings->attributes[$name] : '';
  }

  /**
   * provides the attributes as a config string
   * 
   * @return string
   */
  public function attributes_string()
  {
    return \PDb_Manage_Fields_Updates::array_to_string_notation( $this->settings->attributes );
  }

  /**
   * provides the "any" option text
   * 
   * @return string
   */
  public function any_option_label()
  {
    return \Participants_Db::apply_filters( 'translate_string', $this->settings->any_option_title );
  }

  /**
   * provides the help text
   * 
   * @return string
   */
  public function help_text()
  {
    return $this->settings->help_text;
  }

  /**
   * provides the configured search form element type
   * 
   * @return string form element type slug
   */
  public function search_control_type()
  {
    return $this->settings->form_element;
  }

  /**
   * tells if the field name should be included in the feedback string
   * 
   * @return bool true if the "name in result" preference is selected
   */
  public function name_in_result()
  {
    return (bool) $this->settings->name_in_result;
  }

  /**
   * provides the search_field form element
   * 
   * the '_range' and '-or' special designators are removed so that the resulting 
   * string is a standard PDB form element name
   * 
   * @return string form element type slug
   */
  public function form_element()
  {
    $form_element = str_replace( ['db_','_range', '-or', 'select-other'], ['', '', '', 'radio'], $this->settings->form_element );
    return $form_element;
  }

  /**
   * tells if the field uses a ranged control
   * 
   * @return bool
   */
  public function uses_ranged_control()
  {
    return strpos( $this->settings->form_element, 'range' ) !== false;
  }

  /**
   * tells if the field has the "text as dropdown" setting
   * 
   * @return bool
   */
  public function uses_text_as_dropdown()
  {
    return strpos( $this->form_element(), 'dropdown') !== false;
  }
  
  /**
   * tells if the field is a "chosen" selector
   * 
   * @return bool
   */
  public function is_chosen()
  {
    return strpos( $this->form_element(), 'chosen' ) !== false;
  }

  /**
   * tells if the field search value should be processed as an "or mode" array
   * 
   * @return bool
   */
  public function multi_or_mode()
  {
    return (bool) $this->or_mode && $this->is_multiselect();
  }
  
  /**
   * tells if the search field is a multiselect
   * 
   * @return bool
   */
  public function is_multiselect()
  {
    return strpos( $this->form_element(), 'multi' ) !== false;
  }

  /**
   * provides the default value for the search field
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   * @return string
   */
  public function default_value()
  {
    $default = $this->get_attribute('default');
    
    if ( $default === '' ) {
      global $PDb_Combo_Multi_Search;

      if ( $PDb_Combo_Multi_Search->plugin_option( 'use_default', '1' ) ) {
        $field_def = \Participants_Db::get_field_def( $this->name() );
        $default = $field_def->default_value();
      }
    }

    return $default;
  }

}
