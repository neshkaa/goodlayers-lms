<?php

/**
 * provides information about the combo field list
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2021  xnau webdesign
 * @license    GPL3
 * @version    1.1
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdbcms\combofields;

use \Participants_Db;

class field_list {
  
  /**
   * provides the combo search field selector options
   * 
   * @return array
   */
  public static function control_options()
  {
    $field_list = new self();
    
    return $field_list->get_control_options();
  }
  
  /**
   * provides the selector for the setting control
   * 
   * @return array
   */
  public function get_control_options()
  {
    $options = array();
    $group = '';
    
    foreach( $this->all_fields() as $field ) {
      if ( $field->group() !== $group ) {
        $options[] = (object) array(
            'type' => 'optgroup', 
            'title' => $field->group_title()
                );
        $group = $field->group();
      }
      
      $attribute = '';
      if ( in_array( $field->name(), $this->multisearch_field_list() ) ) {
        $disabled = $this->multiple_search_modes_enabled() ? '' : ' disabled';
        $attribute = 'class="selected-multifield" ' . $disabled;
      }
      
      $options[] = (object) array(
          'type' => 'option',
          'name' => $field->name(),
          'title' => $field->title(),
          'attribute' => $attribute,
      );
    }
    
    return $options;
  }
  
  /**
   * tells if field multiple search modes is enabled
   * 
   * @global \Plugin $PDb_Combo_Multi_Search
   * @return bool
   */
  private function multiple_search_modes_enabled()
  {
    global $PDb_Combo_Multi_Search;
    
    return (bool) $PDb_Combo_Multi_Search->plugin_option( 'allow_multiple_search_modes', 0 );
  }
  
  /**
   * provides the list of configured combo fields
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   * @return array of field names
   */
  public function combo_field_list()
  {
    global $PDb_Combo_Multi_Search;
    
    return $PDb_Combo_Multi_Search->controls->combo_field_list();
  }
  
  /**
   * provides a list of all fields
   * 
   * @global \wpdb $wpdb
   * @return array of \PDb_Form_Field_Def objects indexed by field name
   */
  private function all_fields()
  {
    return \pdbcms\Plugin::all_search_fields();
  }
  
  /**
   * provides a list of all the configured Multi Search fields
   * 
   * @return array of field names
   */
  private function multisearch_field_list()
  {
    $field_list = array();
    $multifields = \pdbcms\multifields\field_store::getInstance()->field_name_array();
    
    return array_keys( $multifields );
  }
}
