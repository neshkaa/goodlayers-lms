<?php

/**
 * provides the configuration for a new field
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

class added_field extends field_config {

  /**
   * provides the array of legacy multi field settings
   * 
   * @param string $fieldname
   * @return stdClass
   */
  public static function config( $fieldname )
  {
    $added_field = new self();

    return $added_field->get_field_config( $fieldname );
  }

  /**
   * provides the configuration array as determined by the old settings
   * 
   * @param string $fieldname
   * @return stcClass
   */
  private function get_field_config( $fieldname )
  {
    $config = false;
    
    $field = new \PDb_Form_Field_Def( $fieldname );

    if ( $field ) {
      $config = $this->field_config( $field );
    }

    return $config;
  }

  /**
   * provides the config object for a field
   * 
   * @param \PDb_Form_Field_Def $field
   * 
   * @return \stdClass
   */
  protected function field_config( $field )
  {
    $config = parent::field_config($field);
    $config->help_text = '';

    return $config;
  }

}
