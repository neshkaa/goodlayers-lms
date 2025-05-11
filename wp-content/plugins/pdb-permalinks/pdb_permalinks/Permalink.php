<?php

/*
 * establishes a permalink pattern in WordPress
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

namespace pdb_permalinks;

class Permalink {

  /**
   * @var string name of the custom query var
   */
  private $query_var_name;

  /**
   * @var string  permalink url identifier string
   */
  private $key;
  
  /**
   * @var string  destination page name
   */
  private $page_id;

  /**
   * 
   * @param string  $key  string used to identify a PDb permalink URL
   * @param string $query_var_name name of the custom query variable
   * @param int  $pageid id of the destination page
   */
  public function __construct( $key, $query_var_name, $pageid )
  {
    $this->key = $key;
    $this->query_var_name = $query_var_name;
    $this->page_id = $pageid;
    add_action( 'init', array( $this, 'setup_rewrite' ), 100 );
  }

  /**
   * sets up the rewrite in WordPress
   * 
   * @param bool $flush if true, flush the rewrite rules
   */
  public function setup_rewrite( $flush = false )
  {
    add_rewrite_rule( $this->rule_regex(), $this->replace_string(), 'top' );
    add_rewrite_tag( '%' . $this->query_var_name . '%', '([^/]+)' );
    if ( $flush || get_site_transient( Plugin::must_flush ) ) {
      flush_rewrite_rules();
      set_site_transient( Plugin::must_flush, false );
    }
  }

  /**
   * provides a permalink URL
   * 
   * @param string the record slug
   * @return string|bool  the permalink or bool false if no slug is defined for the record
   */
  public function get_permalink( $slug )
  {
    return $slug ? get_bloginfo( 'url' ) . '/' . $this->key . '/' . $slug . '/' : false;
  }

  /**
   * provides the regex for the rewrite rule
   * 
   * grabs everything following the first slash after the key
   * 
   * @return string
   */
  private function rule_regex()
  {
    return "^$this->key/(.*)$"; // [^/] 
  }

  /**
   * provides the replace string for the rewite rule
   * 
   * @return string
   */
  private function replace_string()
  {
    return 'index.php?page_id=' . $this->page_id . '&' . $this->query_var_name . '=$matches[1]';
  }

}
