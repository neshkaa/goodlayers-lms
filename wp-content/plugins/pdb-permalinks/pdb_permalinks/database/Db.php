<?php

/*
 * initializes and maintains the database
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2016  xnau webdesign
 * @license    GPL2
 * @version    0.4
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdb_permalinks\database;

class Db {

  /**
   * @var string name of the slug column
   */
  const slug_field = 'record_slug';
  
  /**
   * @var string name of the record slug cache
   */
  const slugs = 'pdb-permalinks-slugs';
  
  /**
   * @var int limits the number of levels the algorithm will use to avoid duplicates
   * 
   * this is primarily to keep it from running away if the setting generate all 
   * identical slugs; it will only append numbers up to this limit
   */
  const level_limit = 20;

  /**
   * fills the record slug column with the record slugs
   * 
   * @param process $process the process object
   * 
   * @global \wpdb $wpdb
   */
  public static function populate_all_slugs( process $process )
  {
    global $wpdb;
    self::clear_all_slugs();
    $id_list = $wpdb->get_col( 'SELECT `id` FROM ' . \Participants_Db::$participants_table );
    foreach ( $id_list as $id ) {
      $process->push_to_queue($id);
      //self::_update_slug( $id );
    }
    $process->save();
    
    $result = $process->dispatch();
  }
  
  /**
   * creates and stores a slug for a record
   * 
   * called on the 'pdb-before_submit_signup' and 'pdb-before_submit_update' filters
   * 
   * @param array $record the updated record data
   * 
   */
  public static function update_slug( $record )
  {
    if ( self::needs_slug( $record['id'] ) ) {
      self::_update_slug( $record['id'] );
    }
  }
  
  /**
   * provides the slug for the given record id
   * 
   * @global \wpdb $wpdb
   * @param int $id
   * @return string|bool the slug or bool false if no slug found
   */
  public static function record_slug( $id )
  {
    $all_slugs = wp_cache_get( self::slugs );
    
    if ( ! $all_slugs ) {
      
      global$wpdb;
      
      $slug_list = $wpdb->get_results( 'SELECT v.id, v.' . self::slug_field . ' FROM ' . \Participants_Db::$participants_table . ' v', OBJECT_K );
      
      wp_cache_set( self::slugs, $slug_list );
      
    }
    
    $slug = isset( $slug_list[$id] ) ? $slug_list[$id]->{self::slug_field} : false;
    
    return $slug;
  }
  
  /**
   * creates and stores a slug for a record
   * 
   * @global \wpdb $wpdb
   * @param int $id the record to update
   * 
   */
  public static function _update_slug( $id )
  {
    global $wpdb;
    $updated = false;
    $level = 0;
    $slug = new Slug($id);
    while ( $updated !== 1 && $level < self::level_limit ) {
      $updated = $wpdb->query( $wpdb->prepare( self::update_slug_query(), $slug->get_slug( $level ), $id ) );
      $level++;
    }
  }

  /**
   * provides the query for creating a slug for a record
   * 
   * @retrun string
   */
  private static function update_slug_query( )
  {
    return 'UPDATE IGNORE ' . \Participants_Db::$participants_table . ' 
      SET `' . self::slug_field . '` = "%s" WHERE `id` = "%d" LIMIT 1';
  }
  
  /**
   * clears all slugs
   * @global \wpdb $wpdb
   */
  private static function clear_all_slugs()
  {
    global $wpdb;
    $wpdb->query( "UPDATE `" . \Participants_Db::$participants_table . "` SET `" . self::slug_field . "` = NULL" );
  }
  
  /**
   * checks for an existing slug
   * 
   * @global \wpdb $wpdb
   * @param int $id the record to check
   * @return bool true if no slug has been defined
   */
  public static function needs_slug( $id )
  {
    global $wpdb;
    $slug = $wpdb->get_var( $wpdb->prepare( 'SELECT `' . self::slug_field . '` FROM ' . \Participants_Db::$participants_table . ' WHERE `id` = "%d" LIMIT 1', $id ) );
    return empty( $slug );
  }
  
}
