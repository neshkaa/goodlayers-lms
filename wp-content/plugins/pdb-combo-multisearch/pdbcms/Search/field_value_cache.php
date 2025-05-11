<?php

/**
 * provides the unique values array for a field
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2021  xnau webdesign
 * @license    GPL3
 * @version    0.2
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdbcms\Search;

class field_value_cache {
  
  /**
   * @var string base name of the transient
   */
  const transient = 'pdbcms-unique_values';
  
  /**
   * @var int number of seconds to hold the transient
   */
  private $expiration = 300; // 5 minutes
  
  /**
   * @var array of unique values
   */
  private $unique_values;
  
  /**
   * @var string name of the field
   */
  private $fieldname;
  
  /**
   * expires the transient
   */
  public static function expire_cache()
  {
    delete_transient(self::transient);
  }
  
  /**
   * sets up the object
   * 
   * @param string $fieldname
   */
  public function __construct( $fieldname )
  {
    $this->fieldname = $fieldname;
    $this->unique_values = self::all_field_values();
  }
  
  /**
   * provides the unique values list for a field
   * 
   * returns an associative array where the keys are the same as the values
   * 
   * @return array|bool false if no cached value is available
   */
  public function get_list()
  {
    if ( array_key_exists( $this->fieldname, $this->unique_values ) )
    {  
      return array_combine( $this->unique_values[$this->fieldname], $this->unique_values[$this->fieldname] );
    }
    
    return false;
  }
  
  /**
   * updates the transient for the named field
   * 
   * @param array $values
   */
  public function update_field_values( $values )
  {
    $this->unique_values[$this->fieldname] = array_keys( $values );
    $this->update();
  }
  
  /**
   * provides the expiration time for the transient
   * 
   * @return int
   */
  private function expiration()
  {
    return apply_filters( 'pdbcms-unique_values_cache_expiration_time', $this->expiration );
  }
  
  /**
   * stores the value in the transient
   */
  private function update()
  {
    set_transient( self::transient, $this->unique_values, $this->expiration() );
  }
  
  /**
   * provides the transient contents
   * 
   * @return array
   */
  private static function all_field_values()
  {
    $unique_values = get_transient( self::transient );
    
    return is_array( $unique_values ) ? $unique_values : array();
  }
}
