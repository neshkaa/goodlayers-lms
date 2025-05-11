<?php

/**
 * models a field that is getting a filter applied to it
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2017  xnau webdesign
 * @license    GPL3
 * @version    0.3
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdbcms\Search;

class Search_Field {
  /**
   * @var \PDb_Form_Field_Def from the Participants Database $fields array
   */
  private $field_def;
  /**
   * @var string name of the field
   */
  private $name;
  /**
   * 
   * @param string $fieldname
   */
  public function __construct( $fieldname )
  {
    $this->name = $fieldname;
    $this->field_def = \Participants_Db::$fields[$fieldname];
  }
  /**
   * provides the search term
   * @param string  $term the search term
   * @return string
   */
  public function search_term( $term )
  {
    /**
     * @filter pdbcms-prepare_search_term
     * 
     * @param string the prepared term
     * @param string the fieldname
     * 
     * @return string
     */
    return apply_filters( 'pdbcms-prepare_search_term', $this->_prepare_term($term), $this->name );
  }
  
  /**
   * provides the operator to use
   * 
   * this is primarily so that "whole word" searches are not performed on multi- fields
   * 
   * @param string $operator the current operator
   * @return string
   */
  public function operator( $operator )
  {
    if ( $this->field_def->is_multi() && $operator === 'WORD' ) {
      $operator = 'LIKE';
    }
    return $operator;
  }
  
  
  /**
   * prepares a search term depending on the form element type
   * 
   * @param string $term the search term
   * @param string $operator
   * @return string the prepared search term
   */
  private function _prepare_term( $term, $operator = 'LIKE' )
  {
    if ( $this->field_def->is_value_set() && $this->field_def->is_multi() && array_key_exists( $term, $this->field_def->options()) ) {
      $term = sprintf( '"%s"', $term ); // ( $operator === 'LIKE' ? '"%%%s%%"' : '"%s"' )
    }
    
    return trim( $term );
  }
}
