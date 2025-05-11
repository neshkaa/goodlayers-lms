<?php

/**
 * models a permalink destination
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2016  xnau webdesign
 * @license    GPL3
 * @version    0.3
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdb_permalinks\destinations;

abstract class Destination {
  
  /**
   * @var string name of the shortcode
   */
  protected $shortcode;
  
  /**
   * @var the slug value
   */
  protected $slug;
 
 /**
  * @var object the permalink class instance
  */
 protected $Permalink;
  
  /**
   * instantiates the destination
   */
  public function __construct()
  {
    add_action( 'plugins_loaded', array( $this, 'initialize' ) );
    
    // add the record ID to the shortcode
    add_filter( 'pdb-shortcode_call_' . $this->shortcode, array( $this, 'find_record' ) );
    
    // get the slug from the rewritten URL
    add_filter( 'wp', array( $this, 'get_query_var' ) );
  }
  
  /**
   * sets up the permalink
   * 
   */
  public function initialize()
  {
    // set up the permalinks
    $this->Permalink = new \pdb_permalinks\Permalink( $this->key(), $this->query_var(), $this->page_id() );
    $this->set_filters();
  }
  
  /**
   * sets the filters
   */
  abstract protected function set_filters();

  /**
   * provides the key used in the reqritten urls
   * 
   * @return string
   */
  abstract protected function key();

  
  /**
   * gets the query var value and sets the property
   */
  public function get_query_var()
  {
    $slug = get_query_var( $this->query_var() );
    if ( ! empty( $slug ) ) {
      $this->slug = urldecode( $slug );
    }
  }
  
  /**
   * filter in the permalink
   * 
   * @param string $url the normal url
   * @param int $id the record id
   * @return string the permalink
   */
  public function place_permalink( $url, $id )
  {
    $permalink = $this->Permalink->get_permalink( $this->get_slug( $id ) );
    
    return $permalink ? $permalink : $url;
  }
  
  /**
   * provides the slug for a record ID
   * 
   * @param int $id tecord id
   * @return string slug
   */
  abstract protected function get_slug( $id );


  /**
   * provides the query var name
   * 
   * @return string
   */
  abstract protected function query_var();
  
  /**
   * called on the 'pdb-shortcode_call_{$shortcode}' filter
   * 
   * @param array $params the shortcode parameters
   * @return array paramter array
   */
  public function find_record( $params )
  {
    if ( $record_id = $this->get_record_id() ) {
      $params['record_id'] = $record_id;
    }
    return $params;
  }
  
  /**
   * obtains the record ID
   * 
   * @return int record id
   */
  abstract protected function get_record_id();
  
  
  /**
   * supplies the page ID for the single record display
   * 
   * @return  int
   */
  abstract protected function page_id();
  
}
