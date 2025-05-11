<?php

/**
 * models a single record page permalink destination
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2016  xnau webdesign
 * @license    GPL3
 * @version    0.1
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdb_permalinks\destinations;

class Single extends Destination {
  
  /**
   * @var string name of the shortcode
   */
  protected $shortcode = 'pdb_single';
  
  /**
   * sets the filters
   */
  protected function set_filters()
  {
    add_filter( 'pdb-single_record_url', array( $this, 'place_permalink' ), 10, 2 );
  }
  
  /**
   * provides the key used in the rewritten urls
   * 
   * @return string
   */
  protected function key() {
    return apply_filters('pdb-permalinks_key', $this->shortcode );
  }
  
  /**
   * obtains the record ID
   * 
   * @return int record id
   */
  public function get_record_id()
  {
    $id = false;
    if ( $this->slug ) {
      global $wpdb;
      $sql = 'SELECT `id` FROM ' . \Participants_Db::$participants_table . ' WHERE ' . \pdb_permalinks\database\Db::slug_field . ' = "%s"';
      $id = $wpdb->get_var( $wpdb->prepare( $sql, $this->slug ) );
    }
    return $id;
  }
  
  /**
   * provides the slug for a record ID
   * 
   * @param int $id tecord id
   * @return string slug
   */
  protected function get_slug( $id )
  {
    return \pdb_permalinks\database\Db::record_slug($id);
  }

  /**
   * provides the query var name
   * 
   * @return string
   */
  protected function query_var()
  {
    return apply_filters('pdb-permalinks_query_var_name', 'pdb-record-slug' );
  }
  

  /**
   * supplies the page ID for the single record display
   * 
   * @return  int
   */
  protected function page_id()
  {
    return \Participants_Db::plugin_setting('single_record_page', false);
  }
}
