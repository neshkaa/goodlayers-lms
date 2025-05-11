<?php

/*
 * handles a single record slug
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2016  xnau webdesign
 * @license    GPL2
 * @version    0.1
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdb_permalinks\database;

class Slug {

  /**
   * @var array of record values
   */
  private $data;

  /**
   * @var int record id
   */
  private $id;

  /**
   * @var string the term divider character used in the slug
   */
  private $div = '-';

  /**
   * @var the maximum number of characters in a slug
   */
  private $max = 128;

  /**
   * sets up the object
   * 
   * @param int $id the record id
   */
  public function __construct( $id )
  {
    $this->id = $id;
    $this->data = \Participants_Db::get_participant( $id );
    $this->div = apply_filters( 'pdb-permalinks_slug_term_divider', $this->div );
    $this->max = apply_filters( 'pdb-permalinks_slug_max_length', $this->max );
  }

  /**
   * provides a slug with an escalation value
   * 
   * the escalation value orders a larger slug so that a unique slug can be provided
   * 
   * @param int $level the escalation level of the slug
   * @return string the slug
   */
  public function get_slug( $level )
  {
    return $this->build_slug( $this->slug_composition( $level ) );
  }

  /**
   * provides a slug given a list of columns to make it out of
   * 
   * if a column name does not correspond to a column in the record the name is used literally in the slug
   * 
   * @param array $column_list list of column names to use to build the slug
   * @return string the slug
   */
  private function build_slug( $column_list )
  {
    $slug = array();
    $i = 1;
    foreach ( array_filter( $column_list ) as $column ) {
      $term = $this->data( $column );
      if ( empty( $term ) ) {
        if ($i > 2) {
          $slug[] = empty( $term ) ? ( is_int( $column ) ? $column : '1' ) : $term;
        }
      } else {
        $slug[] = $term;
      }
      $i++;
    }
    if ( empty( $slug ) ) {
      // if we still have no slug, get a unique id
      $slug = array( uniqid() );
    }
    return substr( $this->sanitize( implode( $this->div, $slug ) ), 0, $this->max );
  }

  /**
   * provides a list of columns names to use in building the slug
   * 
   * the "alt" allows us to escalate the slug if it matches another slug in the database: 
   *      0 = base slug, 
   *      1 = base slug + 3rd term, 
   *      > 1 adds a literal number to achieve uniqueness
   * 
   * @param int $level determins the level of slug to develop
   * @return array of columns names or literal
   */
  private function slug_composition( $level )
  {
    switch ( $level ) {
      case 0:
        return $this->base_slug();
      case 1:
        return array_merge( $this->base_slug(), array( $this->component( '3' ) ) );
      default:
        return array_merge( $this->base_slug(), array( $level ) );
    }
  }

  /**
   * provides the column names in the base slug
   * 
   * @return array of columns names
   */
  private function base_slug()
  {
    return $this->component( '2' ) == '1' ? array($this->component( '1' )) : array($this->component( '1' ), $this->component( '2' ));
  }

  /**
   * provdes the column setting that corresponds to the number
   * 
   * returns false if the corresponding column setting is not found
   * 
   * @param int $number
   * @return string|bool false if component not found
   */
  private function component( $number )
  {
    global $PDb_Permalinks;
    $component = $PDb_Permalinks->plugin_option( 'identifier_column_' . $number, false );
    return ( $component && !empty( $component ) ) ? $component : false;
  }

  /**
   * sanitizes the slug fo use in the permalink
   * 
   * @param string $slug
   * @return string the sanitized slug
   */
  private function sanitize( $slug )
  {
    return urldecode( sanitize_title( $slug, '', 'pdb-permalinks' ) );
  }

  /**
   * provides a value
   * 
   * @param string $name name of the field to get
   * @return mixed the value or bool false if not found
   */
  private function data( $name )
  {
    return isset( $this->data[$name] ) ? $this->data[$name] : false;
  }

}
