<?php

/**
 * handles providing the values from a shortcode filter
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2023  xnau webdesign
 * @license    GPL3
 * @version    0.1
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdbcms\Search;

class shortcode_filter {
  
  /**
   * @var array holds the parsed filter string statements
   */
  private $statements;
  
  /**
   * @param string $shortcode_filter
   * @return array
   */
  public static function get_statements( $filter_string )
  {
    $filter = new self( $filter_string );
    
    return $this->statements;
  }
  
  
  /**
   * @param string $shortcode_filter
   */
  public function __construct( $shortcode_filter )
  {
    $this->setup_statements( $shortcode_filter );
    
    add_filter( 'pdbcms-get_preload_value', array( $this, 'get_preload_value' ), 10, 2 );
  }
  
  /**
   * provides the preload value for the given field
   * 
   * @param string $value
   * @param string $fieldname
   * @string return string search term
   */
  public function get_preload_value( $value, $fieldname )
  {
    return isset( $this->statements[$fieldname] ) ? $this->statements[$fieldname] : '';
  }
  
  /**
   * sets up the statements array
   * 
   * @param string $filter_string
   */
  private function setup_statements( $filter_string )
  {
    $statements = preg_split( '#(?<!\\\\)(&|\\|)#', $this->prep_filter_string( $filter_string ), -1, PREG_SPLIT_DELIM_CAPTURE );
    
    foreach( $statements as $statement ) {
      $operator = preg_match( '#^(.+)(>=|<=|!=|!|>|<|=|!|~)(.*)$#U', $statement, $matches );

      if ( $operator === 0 ) {
        continue; // no valid operator; skip to the next statement
      }
          
      // get the parts
      list( $string, $column, $op_char, $search_term ) = $matches;
      
      $this->statements[$column] = $search_term;
    }
  }
  
  
  /**
   * prepares a filter string for processing
   * 
   * @param string $filter_string
   * @return string
   */
  private function prep_filter_string( $filter_string )
  {
    // unquote the string
    preg_match( '/^[\'"]?(.+?)[\'"]?$/', html_entity_decode( \PDb_List_Query::straighten_quotes( $filter_string ) ), $matches );
    
    return $matches[1];
  }
  
}
