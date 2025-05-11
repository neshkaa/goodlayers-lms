<?php

/**
 * provides data storage services for the mutlifield selector
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2020  xnau webdesign
 * @license    GPL3
 * @version    1.1
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdbcms\multifields;

class field_store {

  /**
   * @var string name of the option where the field configs are stored
   */
  const option = 'pdbcms_multifields';

  /**
   * @var array of the defined search field objects
   */
  private $search_fields = array();

  /**
   * @var array of search field configs
   */
  private $config_array;

  /**
   * @var private class instance
   */
  private static $instance;

  /**
   * provides the current class instance
   * 
   * @return field_store instance
   */
  public static function getInstance()
  {
    if ( self::$instance === null ) {
      self::$instance = new field_store();
    }

    return self::$instance;
  }

  /**
   * sets up the instance
   */
  private function __construct()
  {
    $this->setup_configs();
    $this->build_field_array();
  }

  /**
   * provides an array of field names from the configured fields
   * 
   * @return array as $name => $title
   */
  public function field_name_array()
  {
    $return = array();
    foreach ( $this->search_fields as $field ) {
      $return[$field->name()] = $field->title();
    }
    return $return;
  }

  /**
   * provides an array of available fields
   * 
   * @return array of \PDb_Form_Field_Def objects
   */
  public function available_fields()
  {
    $available_fields = array();
    
    foreach( \pdbcms\Plugin::all_search_fields() as $field ) {
      /** @var \PDb_Form_Field_Def $field */
      
      if ( in_array( $field->name(), $this->unavailable_fields() ) ) {
        $field->attributes['disabled'] = true;
      }
      
      if ( $this->is_combo_search_field( $field->name() ) ) {
        $field->attributes['class'] = 'selected-combofield';
      }
      
      $available_fields[$field->name()] = $field;
    }

    return $available_fields;
  }
  
  /**
   * provides a list of fields to make unavailable
   * 
   * @global \Plugin $PDb_Combo_Multi_Search
   * @return array
   */
  private function unavailable_fields()
  {
    global $PDb_Combo_Multi_Search;
    
    if ( $PDb_Combo_Multi_Search->plugin_option( 'allow_multiple_search_modes', 0 ) ) {
      return array_keys( $this->search_fields );
    }
    
    return array_merge( $PDb_Combo_Multi_Search->combo_field_list(), array_keys( $this->search_fields ) );
  }
  
  /**
   * tells if the field is selected as a combo search field
   * 
   * @global \Plugin $PDb_Combo_Multi_Search
   * @return bool
   */
  private function is_combo_search_field( $fieldname )
  {
    global $PDb_Combo_Multi_Search;
    
    return in_array( $fieldname, $PDb_Combo_Multi_Search->combo_field_list() );
  }
  

  /**
   * provides the complete array of search field objects
   * 
   * @return array
   */
  public function multi_search_fields()
  {
    return $this->search_fields;
  }

  /**
   * tells if there are fields configured for the multisearch
   * 
   * @return bool true if there are fields configured
   */
  public function has_multisearch_fields()
  {
    return count( $this->field_name_array() ) > 0;
  }

  /**
   * tells if the field is a configured multi search field
   * 
   * @param strign $fieldname
   * @return bool true if the field is a configured field
   */
  public function is_multi_search_field( $fieldname )
  {
    return isset( $this->search_fields[$fieldname] );
  }

  /**
   * provides the search field object for a single search field
   * 
   * @param string $fieldname
   * @return \pdbcms\multifields\search_field|bool false if field name does not match any configured field
   */
  public function search_field( $fieldname )
  {
    return isset( $this->search_fields[$fieldname] ) ? $this->search_fields[$fieldname] : false;
  }

  /**
   * provides the config object for a single configured field
   * 
   * @param string $fieldname
   * @return \stdClass
   */
  public function field_config( $fieldname )
  {
    return $this->config_array[$fieldname];
  }

  /**
   * deletes the named field from the config array
   * 
   * @param string $fieldname
   * @return bool success
   */
  public function delete_field( $fieldname )
  {
    if ( isset( $this->config_array[$fieldname] ) ) {
      unset( $this->config_array[$fieldname] );
      $this->update();
      return true;
    }
    return false;
  }

  /**
   * adds a new field to the multi search fields
   * 
   * @param string $fieldname
   * @return \pdbcms\multifields\search_field
   */
  public function add_field( $fieldname )
  {
    $search_field_config = added_field::config( $fieldname );

    return $this->update_field( $fieldname, $search_field_config );
  }

  /**
   * updates or adds a single field config
   * 
   * @param string $fieldname
   * @param \stdClass $config
   * @return \pdbcms\multifields\search_field
   */
  public function update_field( $fieldname, \stdClass $config )
  {
    $this->config_array[$fieldname] = $config;
    $this->update();
    return $this->search_field( $fieldname );
  }

  /**
   * reorders the field list based on the order of the supplied array
   * 
   * @param array $list ordered list of fieldnames
   */
  public function reorder_fields( $list )
  {
    if ( count( $list ) === count( $this->config_array ) ) {

      $reordered = array();

      foreach ( $list as $fieldname ) {

        $reordered[$fieldname] = $this->config_array[$fieldname];
      }

      $this->config_array = $reordered;
      $this->update();
    }
  }

  /**
   * gets the option value
   * 
   * @return array
   */
  private function setup_configs()
  {
    $stored_configs = $this->config_option();

    // if the stored value is unavailable, convert the legacy values
    if ( !$stored_configs ) {
      $stored_configs = legacy_field::field_config_list();
      $this->store_config_array( $stored_configs );
    }

    $this->config_array = $stored_configs;
  }
  
  /**
   * provides a verified field configuration list from the stored field options
   * 
   * @return array|bool false if no configuration found
   */
  private function config_option()
  {
    $configs = get_option( self::option );
    
    if ( !is_array( $configs ) ) {
      return false;
    }
    
    return array_filter( $configs, '\PDb_Form_Field_Def::is_field', ARRAY_FILTER_USE_KEY );
  }

  /**
   * updates the current config array
   */
  private function update()
  {
    update_option( self::option, $this->config_array );
    $this->build_field_array();
  }

  /**
   * stores a config array
   * 
   * @param array $config_array array of configuration objects
   */
  private function store_config_array( $config_array )
  {
    update_option( self::option, $config_array );
  }

  /**
   * builds the fields array
   * 
   */
  private function build_field_array()
  {
    foreach ( $this->config_array as $config ) {
      $this->search_fields[$config->name] = new search_field( $config );
    }
  }

}
