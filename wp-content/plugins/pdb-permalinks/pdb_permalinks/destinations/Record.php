<?php

/**
 * models a record edit page permalink destination
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

class Record extends Destination {
  
  /**
   * @var string name of the shortcode
   */
  protected $shortcode = 'pdb_record';
  /**
   * sets the filters
   */
  protected function set_filters()
  {
    add_filter( 'pdb-record_edit_url', array( $this, 'place_permalink' ), 10, 2 );
  }
  
  /**
   * provides the key used in the rewritten urls
   * 
   * @return string
   */
  protected function key() {
    return apply_filters('pdb-permalinks_pid_key', $this->shortcode );
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
      $id = \Participants_Db::get_participant_id( $this->slug );
    }
    return $id;
  }
  
  /**
   * provides the slug for a record ID
   * 
   * @param string $pid the private ID
   * @return string slug
   */
  protected function get_slug( $pid )
  {
    return $pid;
  }

  /**
   * provides the query var name
   * 
   * @return string
   */
  protected function query_var()
  {
    return 'pdb-record-edit-slug';
  }
  

  /**
   * supplies the page ID for the record edit form
   * 
   * @return  int
   */
  protected function page_id()
  {
    return \Participants_Db::plugin_setting('registration_page', false);
  }
}
