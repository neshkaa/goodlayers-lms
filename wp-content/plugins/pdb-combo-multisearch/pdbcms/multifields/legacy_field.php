<?php

/**
 * provides the legacy multiseasrch settings
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

class legacy_field extends field_config {

  /**
   * provides the array of legacy multi field settings
   * 
   * @return array
   */
  public static function field_config_list()
  {
    $legacy = new self();

    return $legacy->get_field_configs();
  }

  /**
   * provides the configuration array as determined by the old settings
   * 
   * @return array of field configuration values
   */
  private function get_field_configs()
  {
    $config_list = array();
    
    $field_list = explode( ',', str_replace(' ', '', $this->plugin_setting( 'field_list' ) ) );
    
    if ( count( $field_list ) > 0 ) {

      foreach ( $field_list as $fieldname ) {
        
        if ( \PDb_Form_Field_Def::is_field( $fieldname ) ) {
        
          $field = new \PDb_Form_Field_Def( $fieldname );

          if ( $field ) {
            $config_list[$fieldname] = $this->field_config( $field );
          }
        }
      }
      
    }

    return $config_list;
  }

}
