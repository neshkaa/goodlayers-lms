<?php

/**
 * temporarily holds the list query
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2021  xnau webdesign
 * @license    GPL3
 * @version    0.1
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdbcms\Search;

class list_query_stash {
  
  /**
   * @var string name of the transient to use
   */
  const transient = 'pdbcms-list_query_object';
  
  /**
   * sets the transient value
   * 
   * @param \PDb_List_Query $list_query
   */
  public static function stash( $list_query )
  {
    set_transient( self::transient, $list_query, 10 );
  }
  
  /**
   * provides the stashed query
   * 
   * @return \PDb_List_Query
   */
  public static function get()
  {
    return get_transient( self::transient );
  }
  
  /**
   * clears the transient
   */
  public static function clear()
  {
    delete_transient(self::transient);
  }
}
