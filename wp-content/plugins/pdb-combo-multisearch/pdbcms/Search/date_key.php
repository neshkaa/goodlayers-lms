<?php

/**
 * converts a single-word date into a range of dates
 * 
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2020  xnau webdesign
 * @license    GPL3
 * @version    1.1
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdbcms\Search;

class date_key {
  
  /**
   * @var string range start date
   */
  private $start_date;
  
  /**
   * @var string range end date
   */
  private $end_date;
  
  /**
   * provides the date range for a date key
   * 
   * @param string $date_key such as 'january' or '2012' or 'april 2013'
   * @return array of date strings as 'start' => $date, 'end' => $date
   */
  public static function get_date_range( $date_key )
  {
    $keyparse = new self( $date_key );
    
    $return = array();
    
    foreach( $keyparse->date_range() as $index => $ts ) {
      $return[$index] = date( get_option('date_format'), $ts );
    }
    
    return $return;
  }
  
  /**
   * provides a timestamp for the start value of a date range
   * 
   * @param string $date_key
   * @return int unix timestamp
   */
  public static function get_timestamp( $date_key )
  {
    $keyparse = new self( $date_key );
    
    return $keyparse->start_date;
  }
  
  /**
   * provides a timestamp for the end value of a date range
   * 
   * this is the nominal timestamp plus 1 day
   * 
   * @param string $date_key
   * @return int unix timestamp
   */
  public static function get_end_timestamp( $date_key )
  {
    $keyparse = new self( $date_key );
    
    return $keyparse->end_date;
  }


  /**
   * provides the date range value
   * 
   * @return array|bool false if not a date key
   */
  private function date_range()
  {
    return $this->start_date ? array( 'start' => $this->start_date, 'end' => $this->end_date ) : false;
  }
  
  /**
   * sets up the date key parse
   * 
   * @param string $date_key
   */
  private function __construct( $date_key )
  {
    $this->parse_date_key( urldecode( $date_key ) );
  }

  /**
   * parses a date key into a date range
   * 
   * date keys can be a the name of a month, a year, or both
   * 
   * @param string $date_key
   */
  private function parse_date_key( $date_key )
  {
    switch ($this->date_key_type($date_key)) {
      
      case 'month':
        if ( $ts = $this->_get_timestamp( $date_key . ' 1, ' . date('y') ) ) {
        
          $this->start_date = $ts;
          $this->end_date = strtotime( '+1 month', $ts );
        }
        break;
      
      case 'year':
        if ( $ts = $this->_get_timestamp( date('F', mktime(0, 0, 0, 1) ) .' 1, ' . $date_key ) ) {
          
          $this->start_date = $ts;
          $this->end_date = strtotime( '+1 year', $ts );
        }
        break;
      
      case 'month/year':
        list( $month, $year ) = $this->term_parts( $date_key );
        
        if ( $ts = $this->_get_timestamp( $month . ' 1, ' . $year ) ) {
          
          $this->start_date = $ts;
          $this->end_date = strtotime( '+1 month', $ts );
        }
        break;
        
      case 'date':
        
        $this->start_date = \PDb_Date_Parse::timestamp($date_key, array('zero_time' => true), __METHOD__ );
        
        $this->end_date = strtotime( '+1 day', $this->start_date );
        
        break;
        
      default:
        
        // not a recognized date key
        
    }
  }
  
  /**
   * tells the type of date key
   * 
   * @param string $date_key
   * @return string type value
   */
  private function date_key_type( $date_key )
  {
    if ( $this->is_full_date( $date_key ) ) {
      return 'date';
    }
    if ( $this->is_month_year_key( $date_key ) ) {
      return 'month/year';
    }
    if ( $this->is_year_key( $date_key ) ) {
      return 'year';
    }
    return 'month';
  }
  
  /**
   * tells if the string is a full date
   * 
   * @param string $term
   * @return bool true if the term is a full date
   */
  private function is_full_date( $term )
  {
    return count( $this->term_parts( $term ) ) === 3 && \PDb_Date_Parse::timestamp($term) !== false;
  }
  
  /**
   * tells if the keys is a month/year date key
   * 
   * this is expecting a key in this form: "January 2010"
   * 
   * @param string $date_key
   * @return bool
   */
  private function is_month_year_key( $date_key )
  {
    $terms = $this->term_parts( $date_key );
    if ( count( $terms ) === 2 && $this->is_year_key( array_pop( $terms ) ) ) {
      return true;
    }
    
    return false;
  }
  
  /**
   * tells if the key is a year
   * 
   * @param string $date_key
   * @return bool true if it is a year value
   */
  private function is_year_key( $date_key )
  {
    return preg_match( '/^[0-9]{4}$/', $date_key ) === 1;
  }
  
  /**
   * splits the search term
   * 
   * @param string $term
   * @return array
   */
  private function term_parts( $term )
  {
    return array_filter( preg_split( '#[\s,/\-\\\]+#', $term) );
  }
  
  /**
   * provides the base format for the date conversions
   * 
   * @return string date format string
   */
  private function date_format()
  {
    return apply_filters('pdbcms-date_key_parse_format', get_option( 'date_format' ) );
  }
  
  /**
   * provides a unix timestamp, given the date
   * 
   * @param string $date
   * @return int unix timestamp
   */
  private function _get_timestamp( $date )
  {
    $ts = \PDb_Date_Parse::timestamp( urldecode( $date ), array('zero_time' => true), __METHOD__ );
    
    //error_log(__METHOD__.' date: '.$date.' timestamp: '.$ts);
    
    return $ts;
  }
}
