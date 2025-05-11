<?php

/**
 * provides results feedback messaging for the combo multisearch
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2018  xnau webdesign
 * @license    GPL3
 * @version    1.3
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdbcms\Search;

class Result_Feedback {

  /**
   * @var string name of the cache
   */
  const cache = 'pdbmps-term_string';

  /**
   * @var array list of active search fields
   */
  private $field_list;

  /**
   * instantiates the object
   * 
   * @param array $field_list the list of multi search fields
   * @param bool $is_search_result if true, the current request is for a new search result
   */
  public function __construct( $field_list, $is_search_result )
  {
    $this->field_list = array_merge( array('combo_search'), $field_list );
    $this->set_up_result_string( $is_search_result );
  }

  /**
   * provides the search result string
   * 
   * @return string
   */
  public function search_feedback_message()
  {
    return $this->get_stash();
  }

  /**
   * prepares a new search result string
   * 
   * @param bool $is_search_result true if the submission is a fresh result
   */
  private function set_up_result_string( $is_search_result )
  {
    if ( $this->is_clearing() ) {

      $this->clear_search();
    } else /* if ( $is_search_result ) */ {

      $this->build_fresh_result_string();
    }
  }

  /**
   * builds a fresh result string
   */
  private function build_fresh_result_string()
  {
    $search_terms = array();

    foreach ( $this->field_list as $field ) {
      $search_terms = array_merge( $search_terms, $this->post_term( $field ) );
    }

    $this->update_stash( implode( ', ', $search_terms ) );
  }

  /**
   * gets a value from the $_POST array
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   * @param string $field name of the field
   * 
   * @return array
   */
  private function post_term( $field )
  {
    $value = '';
    if ( $this->field_has_search_term( $field ) ) {

      $value = trim( \pdbcms\Plugin::sanitize( $_POST[$field] ), '"' );

      /*
       * combo search term has its own special split function #356
       */
      if ( $field !== 'combo_search' && !is_array( $value ) && strpos( $value, ',' ) !== false ) {

        $value = array_filter( explode( ',', $value ), function($v) {
          return $v !== '';
        } );
      }
    }

    return $this->term_set( $value, $field );
  }

  /**
   * builds a term value set
   * 
   * this could be a set of min/max values or a single value
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   * @param mixed $value the search term or terms
   * @param string $field name of the field
   * @return array a set of search terms
   */
  private function term_set( $value, $field )
  {
    global $PDb_Combo_Multi_Search;
    $term = array();

    /**
     * @filter pdbcms-search_feedback_term_pattern
     * 
     * @param string the sprintf pattern to use when showing a single term
     * @param string name of the field
     * @return string the pattern
     */
    $term_pattern = apply_filters( 'pdbcms-search_feedback_term_pattern', '%s', $field );

    /**
     * @filter pdbcms-search_feedback_term_set_pattern
     * 
     * @param string the sprintf pattern to use when showing a term set
     * @param string name of the field
     * @return string the pattern
     */
    $term_set_pattern = apply_filters( 'pdbcms-search_feedback_term_set_pattern', '%s &ndash; %s', $field );

    /**
     * @filter pdbcms-search_feedback_term_phrase_pattern
     * 
     * @param string the sprintf pattern to use when showing a term phrase
     * @param string name of the field
     * @return string the pattern
     */
    $term_phrase_pattern = apply_filters( 'pdbcms-search_feedback_term_phrase_pattern', '"%s"', $field );

    if ( $field === 'combo_search' ) 
    {
      // split the term if not searching for a phrase
      $value = $PDb_Combo_Multi_Search->search->split_combo_search_terms( $value );
    }

    foreach ( (array) $value as $subvalue ) {

      if ( $field === 'combo_search' && $subvalue !== '' ) {

        if ( $PDb_Combo_Multi_Search->search->is_combo_whole_word_match() ) {
          $subvalue = str_replace( array('?', '*', '_', '%'), '', $subvalue );
        }

        $pattern = $term_pattern;
        if ( $PDb_Combo_Multi_Search->search->combo_search_mode() === 'phrase' && strpos( $subvalue, ' ' ) !== false ) {
          $pattern = $term_phrase_pattern;
        }

        $term[] = sprintf( $pattern, self::prep_term_for_display( $subvalue, $field, false ) );
        
      } elseif ( isset( $value['start'] ) && isset( $value['end'] ) ) {

        $term[] = sprintf( $term_set_pattern, self::prep_term_for_display( $value['start'], $field ), self::prep_term_for_display( $value['end'], $field, false ) );
        break; // skip the second term
        
      } elseif ( $subvalue !== '' && $subvalue !== 'any' ) {

        $term[] = sprintf( $term_pattern, self::prep_term_for_display( $subvalue, $field ) );
      }
    }
    return $term;
  }

  /**
   * performs a search term "clear"
   */
  private function clear_search()
  {
    $this->stash( '' );
  }

  /**
   * stores the search string in a transient if it is not empty
   * 
   * @param string $value to store
   */
  private function update_stash( $value )
  {
    if ( $value !== '' ) {
      $this->stash( $value );
    }
  }

  /**
   * stores the search string in a transient
   * 
   * @param string $value to store
   */
  private function stash( $value )
  {
    set_transient( self::cache, $value );
  }

  /**
   * provides the stashed value
   * 
   * @return string
   */
  private function get_stash()
  {
    return get_transient( self::cache ) ?: '';
  }

  /**
   * tests the current submission for being a "clear" operation or null search
   * 
   * @return bool true if the search is cleared
   */
  private function is_clearing()
  {
    return $this->no_search_terms() || filter_input( INPUT_POST, 'submit', FILTER_DEFAULT, \Participants_Db::string_sanitize() ) === 'clear';
  }

  /**
   * tells if the post array has no search terms in it
   * 
   * @return bool true if there are no search terms
   */
  private function no_search_terms()
  {
    $empty = true;
    foreach ( $this->field_list as $field ) {
      if ( $this->field_has_search_term( $field ) ) {
        $empty = false;
        break;
      }
    }
    return $empty;
  }

  /**
   * tells if the named field has a search term in the submission
   * 
   * @param string $fieldname
   * @return bool
   */
  private function field_has_search_term( $fieldname )
  {
    return !$this->field_search_is_empty( $fieldname );
  }

  /**
   * tells if the search term for a field is empty or reset
   * 
   * @param string $fieldname
   * @return bool true if the field's search terms are empty
   */
  private function field_search_is_empty( $fieldname )
  {
    if ( $fieldname !== 'combo_search' && !\PDb_Form_Field_Def::is_field( $fieldname ) ) {
      return true;
    }

    switch ( true ) {

      case!array_key_exists( $fieldname, $_POST ):
        return true;

      case $_POST[$fieldname] === '':
        return true;

      case is_array( $_POST[$fieldname] ):

        $check = array_filter( filter_var_array( $_POST[$fieldname], FILTER_DEFAULT ), function ($value) {
          return $value !== '' && strtolower( $value ) !== 'any';
        } );
        return count( $check ) === 0;

      case strtolower( filter_input( INPUT_POST, $fieldname, FILTER_DEFAULT, \Participants_Db::string_sanitize() ) ) === 'any':
        return true;
    }

    return false;
  }

  /**
   * preps a search term for display
   * 
   * @param string $term the raw term
   * @param string $fieldname the field name (optional, needed for finding value titles)
   * @param bool $show_label if true, show the field label with the value
   * @return string
   */
  public static function prep_term_for_display( $term, $fieldname = '', $show_label = true )
  {
    $value = urldecode( stripslashes( trim( esc_attr( $term ), ', ' ) ) );

    if ( \PDb_Form_Field_Def::is_field( $fieldname ) ) {

      $field_def = new \PDb_Form_Field_Def( $fieldname );
      $search_field = self::search_field($fieldname);
      
      if ( $show_label === true && $search_field->name_in_result() ) {
        /**
         * @filter pdbcms-result_label_template
         * @param string the printf template
         * @param \pdbcms\multifields\search_field
         * @return string template
         */
        $label_template = apply_filters( 'pdbcms-result_label_template', '%s: %s', $search_field );
        $value = sprintf( $label_template, $search_field->label(), $field_def->value_title( $value ) );
      } else {
        $value = $field_def->value_title( $value );
      }
      
      $value = self::add_pre_post_content( $value, $field_def );
    
    }

    /**
     * @filter pdbcms-search_feedback_term
     * 
     * @param string the display term
     * @param string the name of the field
     * @return string the display string for the field search term
     */
    return apply_filters( 'pdbcms-search_feedback_term', $value, $fieldname );
  }

  /**
   * adds post/pre content if defined
   * 
   * @param string $value raw value
   * @param string $field_def \PDb_Form_Field_Def object
   * 
   * @return string
   */
  private static function add_pre_post_content( $value, $field_def )
  {
    $field_atts = $field_def->attributes();

    if ( isset( $field_atts['data-before'] ) ) {
      return $field_atts['data-before'] . $value;
    }
    if ( isset( $field_atts['data-after'] ) ) {
      return $value . $field_atts['data-after'];
    }
    return $value;
  }

  /**
   * provides a list of values that should not be matched to a title
   * 
   * @return array of values
   */
  private static function exempt_values()
  {
    return apply_filters( 'pdbcms-value_title_exempt_values', array(
        'min',
        'max',
        'step',
            ) );
  }
  
  /**
   * provides the search_field object for the field
   * 
   * @param string $fieldname
   * @return \pdbcms\multifields\search_field
   */
  private static function search_field( $fieldname )
  {
    $field_store = \pdbcms\multifields\field_store::getInstance();
    return $field_store->search_field($fieldname);
  }

}
