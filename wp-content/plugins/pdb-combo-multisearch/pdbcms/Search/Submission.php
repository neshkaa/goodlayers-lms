<?php

namespace pdbcms\Search;

/*
 * processes the search submission
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2015  xnau webdesign
 * @license    GPL2
 * @version    2.6
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

class Submission {
  /*
   * holds the current $_POST array
   * 
   * @var array
   */

  var $post = array();

  /*
   * unneeded elements in the post array
   * 
   * @var array
   */
  var $skip_elements;

  /**
   * true if a search is being processed
   * 
   * @var bool
   */
  var $is_search_result = false;

  /**
   * holds the current query object
   * 
   * @var \PDb_List_Query
   */
  var $Query;

  /**
   * if 'AND', all search paramters must match, if 'OR', any paramater must match
   * 
   * @var string filter mode
   */
  var $filter_mode;

  /**
   * sets up the search processor instance
   */
  function __construct()
  {
    add_action( 'pdb-list_query_object', array($this, 'modify_query_object'), 100 );

    add_action( 'init', array($this, 'init') );
  }

  /**
   * alters the list query object to include the search parameters
   * 
   * @param \PDb_List_Query $query the list query object
   * @return null
   */
  public function modify_query_object( \PDb_List_Query $query )
  {
    if ( \pdbcms\Plugin::is_search_submission() ) {
      
      $this->Query = &$query;
      $this->_add_combo_search_filters();
      $this->_add_field_filters();
      $this->is_search_result = $this->Query->is_search_result();

      // store the query
      if ( $this->is_search_result && \pdbcms\Plugin::is_search_submission() ) {
        $this->Query->save_query_session();
        add_action( 'pdb-before_include_shortcode_template', array($this, 'clear_search_error') );
      }
    }
  }

  /**
   * 
   * class initialization
   * 
   * fired on init hook
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   */
  function init()
  {
    global $PDb_Combo_Multi_Search;

    $this->skip_elements = array('subsource', $PDb_Combo_Multi_Search->i18n['search'], 'submit');

    // the submit button could have one of several names...
    foreach ( array('submit_button', 'multisearch-submit') as $name ) {
      if ( isset( $_POST[$name] ) ) {
        $_POST['submit'] = $_POST[$name];
        unset( $_POST[$name] );
      }
    }

    $this->_setup_submission_values();
  }

  /**
   * gets the combo search control value
   * 
   * @param string $name name of the text search control
   * @return string the last-submitted value
   */
  public function get_text_search_value( $name = 'combo_search' )
  {
    if ( isset( $_POST[$name] ) ) {
      
      return filter_var( $_POST[$name], FILTER_DEFAULT, \Participants_Db::string_sanitize() );
    }

    if ( is_a( $this->Query, '\PDb_List_Query' ) ) {

      $text_search_filter = $this->Query->current_filter();

      if ( $this->combo_search_is_active() && !empty( $text_search_filter['search_values'] ) ) {

        $values = array_combine( $text_search_filter['search_fields'], $text_search_filter['search_values'] );

        $combo_fields = $this->combo_field_list();

        return isset( $values[current( $combo_fields )] ) ? $values[current( $combo_fields )] : '';
      }
    }

    if ( \pdbcms\Plugin::is_get_search() ) {

      $get_values = \pdbcms\Plugin::sanitize_array( array_combine( (array) $_GET['search_field'], (array) $_GET['value'] ) );

      return isset( $get_values[$name] ) ? $get_values[$name] : '';
    }

    return '';
  }

  /**
   * gets an array of search fields/terms from the current filter
   * 
   * @return array $field_name => $search_term
   */
  public function get_search_field_terms()
  {
    $text_search_filter = $this->Query->current_filter();

    $all_field_terms = array();
    reset( $text_search_filter['search_values'] );
    for ( $i = 0; $i < count( $text_search_filter['search_fields'] ); $i++ ) {
      $field = $text_search_filter['search_fields'][$i];
      if ( isset( $all_field_terms[$field] ) ) {
        $all_field_terms[$field] .= ', ' . \PDb_FormElement::get_value_title( $text_search_filter['search_values'][$i], $field );
      } else {
        $all_field_terms[$field] = \PDb_FormElement::get_value_title( $text_search_filter['search_values'][$i], $field );
      }
    }

//    error_log(__METHOD__.' all terms: '.print_r($all_field_terms,1));

    $search_fields = $this->multi_search_field_list();
    $field_terms = array();
    foreach ( $search_fields as $field ) {
      $term = isset( $all_field_terms[$field] ) ? $all_field_terms[$field] : '';
      if ( !empty( $term ) && $term != '0' ) {
        $field_terms[$field] = $term;
      }
    }

    return $field_terms;
  }

  /**
   * splits a combo search term into logical words according to the mode selection
   * 
   * @param string $term the combo search term
   * @return array of terms
   */
  public function split_combo_search_terms( $term )
  {
    return $this->combo_search_mode() === 'phrase' ? (array) $term : $this->_capture_search_terms( $term );
  }

  /**
   * provides the current combo search mode
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search;
   * @return string the mode: all, any, phrase
   */
  public function combo_search_mode()
  {
    global $PDb_Combo_Multi_Search;

    $default_mode = $PDb_Combo_Multi_Search->plugin_option( 'default_combo_search_modifier', 'any' );
    
    return isset( $this->post['text_search_options'] ) ? $this->post['text_search_options'] : $default_mode;
  }

  /**
   * clears the search error if the multiseasrch was successful
   * 
   * @param \PDb_List $list
   */
  public function clear_search_error( $list )
  {
    $list->search_error_style = '';
  }

  /**
   * provides the minimum term length for search term
   * 
   * @filter pdbcms-min_search_term_length
   * 
   * @return int
   */
  private function min_term_length()
  {
    return apply_filters( 'pdbcms-min_search_term_length', 1 );
  }

  /**
   * adds the combo search filters grouped by the search terms
   * 
   * @retun null
   */
  private function _add_combo_search_filters()
  {
    add_action( 'pdb-list_query_object', array($this, 'handle_combo_search_on_multi_field'), 50 );
    
    if ( !isset( $this->post['combo_search'] ) ) {
      return;
    }

    $text_search_option = $this->combo_search_mode();

    $combo_fields = $this->combo_field_list();
    end( $combo_fields );
    $fields_end_key = key( $combo_fields );

    $is_search = false;
    
    global $PDb_Combo_Multi_Search;
      
    // set up the strict search functionality #358
    if ( $PDb_Combo_Multi_Search->plugin_option( 'combo_strict_search', 0 ) )
    {
      add_filter( 'pdb-database_query_word_boundary_tags', [$this, 'add_strict_search_regex'] );
    }

    /**
     * break the search term up into an array of separate terms, keeping quoted 
     * phrases together
     * 
     * @version 0.5
     * don't break the phrase up if the mode is phrase!
     */
    $combo_search_terms = $text_search_option === 'phrase' ? array($this->combo_search_term()) : $this->_capture_search_terms( $this->combo_search_term() );
    end( $combo_search_terms );
    $terms_end_key = key( $combo_search_terms );

    $operator = $this->is_combo_whole_word_match() ? 'WORD' : 'LIKE';

    // this is the operator used to join the multi and combo searches if both are used
    $combo_multi_join = apply_filters( 'pdbcms_combo_multi_join', $this->filter_mode() );

    foreach ( $combo_search_terms as $termindex => $term ) {

      if ( $this->is_combo_whole_word_match() ) {
        // remove any wildcards
        $term = str_replace( ['?', '*', '_', '%'], '', $term );
      }

      // don't process short terms
      if ( !$this->is_valid_term( $term ) ) {
        continue;
      }

      /*
       * each search term is tested against each combo search field, with the search 
       * fields grouped together; then each group of fields is chained together 
       * according to the filter mode
       * 
       */

      switch ( $text_search_option )
      {
        case 'all':
          
          foreach ( $combo_fields as $i => $fieldname )
          {
            $search_field = new Search_Field( $fieldname );
            
            if ( $i === $fields_end_key ) {
              if ( $termindex === $terms_end_key ) {
                if ( $this->filter_mode() === 'OR' ) {
                  /*
                   * this closes the parens for the last set of fields
                   * 
                   * needed because the next clause will be joined by an OR, so 
                   * we need it to be a separate group in the query
                   */
                  add_filter( 'pdb-list_query_parens_logic', function ($logic) {
                    return false;
                  } );
                }
                $logic = $combo_multi_join;
              } else {
                $logic = 'AND';
              }
            } else {
              $logic = $this->filter_mode( $fieldname );
            }

            // clear out any preset search terms matching the field
            //$this->Query->clear_background_clauses( $fieldname );
            
            // add the filter statement to the query object
            $this->Query->add_filter( $fieldname, $search_field->operator( $operator ), $search_field->search_term( $term, $operator ), $logic );
            $is_search = true;
            unset( $this->post[$fieldname] );
          }
          break;
          
        case 'any':

          foreach ( $combo_fields as $i => $fieldname )
          {
            $search_field = new Search_Field( $fieldname );
            $logic = $termindex === $terms_end_key && $i === $fields_end_key ? $combo_multi_join : 'OR';

            // clear out any preset search terms matching the field
            //$this->Query->clear_background_clauses( $fieldname );
            
            // add the filter statement to the query object
            $this->Query->add_filter( $fieldname, $search_field->operator( $operator ), $search_field->search_term( $term, $operator ), $logic );
            $is_search = true;
            unset( $this->post[$fieldname] );
          }
          break;
          
        case 'phrase':
          
          foreach ( $combo_fields as $fieldname )
          {
            $search_field = new Search_Field( $fieldname );
            $search_term = trim( $search_field->search_term( $this->combo_search_term(), $operator ), '"' );
            $this->Query->add_filter( $fieldname, $search_field->operator( $operator ), $search_term, $combo_multi_join );
            $is_search = true;
          }
          break;
      }
    }
    // set the search flag
    $this->Query->is_search_result( $is_search );

    remove_filter( 'pdb-min_title_match_length', array($this, 'bypass_pdb_min_lingth') );
  }

  /**
   * sanitizes the submission
   * 
   * @param array $submission $_POST or $_GET array
   * @return array of submitted values
   */
  private function sanitize_submission( $submission )
  {
    return \pdbcms\Plugin::sanitize_array( $submission );
  }

  /**
   * provides the combo search term
   * 
   * @return string
   */
  private function combo_search_term()
  {
    $term = stripslashes( urldecode( $this->post['combo_search'] ) );
    // this prevents wildcard- or quote-only terms from being processed as such
    if ( !$this->is_valid_term( $term ) ) {
      $term = '';
    }
    return $term;
  }

  /**
   * tells if the combo whole word match preference is enabled
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   * @return bool
   */
  public function is_combo_whole_word_match()
  {
    global $PDb_Combo_Multi_Search;
    return $PDb_Combo_Multi_Search->plugin_option( 'combo_whole_word_match', '0' ) == '1';
  }

  /**
   * checks a term for the minimum term length
   * 
   * trims out quotes, wildcards so that terms that consist of only these characters 
   * are not used
   * 
   * @param  string|array  $term
   * @return bool true if the term is lengthy enough after trimming
   */
  private function is_valid_term( $term )
  {
    $valid = true;
    foreach ( (array) $term as $t ) {
      $valid = $valid && $this->valid_term_length( $t );
    }
    return $valid;
  }

  /**
   * checks an individual term for validity
   * @param string $term
   * @return bool
   */
  private function valid_term_length( $term )
  {
    return strlen( trim( $term, ' "\'%?*_' ) ) >= $this->min_term_length();
  }

  /**
   * tests an individual search term
   * 
   * @param string $term the term value
   * @return bool true if not an empty or placeholder value
   */
  private function _is_search_term( $term )
  {
    return $this->is_valid_term( $term ) && $term !== 'any';
  }

  /**
   * tells if the search contains ranged searches
   * 
   * @return bool true if ranged searches are included
   */
  private function has_ranged_searches()
  {
    foreach ( $this->post as $name => $value ) {

      if ( $this->is_ranged_search( $value ) ) {
        return true;
      }
    }
    return false;
  }

  /**
   * tells if a specific search term is a ranged search
   * 
   * @param string|array $value the search value to test
   * @return bool true if it is a ranged search
   */
  private function is_ranged_search( $value )
  {
    if ( !is_array( $value ) ) {
      return false;
    }
    $value = array_filter( $value );
    return array_key_exists( 'start', $value ) && array_key_exists( 'end', $value );
  }

  /**
   * adds the individual field filters
   * 
   * @return null
   */
  private function _add_field_filters()
  {
    if ( !is_array( $this->post ) ) {
      return;
    }
    $group = 0;

    if ( $this->has_ranged_searches() && $this->filter_mode() === 'OR' ) {
      // when using ranged searches, we neeed to parenthesize ANDs
      add_filter( 'pdb-list_query_parens_logic', array($this, 'parenthesize_and_statements') );
    }

    foreach ( $this->post as $fieldname => $value ) {

      if ( $this->filter_should_be_added( $fieldname, $value ) ) {

        if ( is_array( $value ) ) {

          /*
           * if we have inverted values, swap them
           */
          if ( $this->is_ranged_search( $value ) && ( (float) $value['start'] > (float) $value['end'] ) ) {
            $temp = $value['start'];
            $value['start'] = $value['end'];
            $value['end'] = $temp;
          }
        } else {
          /**
           * if string, remove slashes
           * 
           * @see #17
           * @version 1.9
           * 
           */
          $value = stripslashes( $value );
        }

        $search_field = \Participants_Db::$fields[$fieldname];
        /* @var $search_field \PDb_Form_Field_Def */

//        $this->Query->clear_background_clauses( $name );

        if ( is_array( $value ) ) {

          foreach ( $value as $i => $v ) {
            if ( !$this->_is_search_term( $v ) ) {
              unset( $value[$i] );
            }
          }

          if ( !empty( $value ) ) {
            if ( !apply_filters( 'pdb-cms-filter_mode', false ) ) {
              //add_filter( 'pdb-list_query_parens_logic', array($this, 'parenthesize_and_statements') );
            }
            $this->_add_filter( $value, $search_field, $group );
            $group++; // each field is one group
          }
        } elseif ( $this->_is_search_term( $value ) ) {
          //remove_filter( 'pdb-list_query_parens_logic', array($this, 'parenthesize_and_statements') );
          
          $this->_add_filter( $value, $search_field, $group );
          $group++; // each field is one group
        }
      }
    }
    // seat the search flag
    $this->Query->is_search_result( $group > 0 || $this->Query->is_search_result() );
  }

  /**
   * sets the list query parenthesization to group AND statements
   * 
   * @param bool  $mode
   * @return bool true to parenthesize OR statements
   */
  public function parenthesize_and_statements( $mode )
  {
    return false;
  }
  
  /**
   * tells if the filter values should be added to the query
   * 
   * @param string $fieldname the name of the field
   * @param string $value the filter term
   * @return bool true to add the field filter
   */
  private function filter_should_be_added( $fieldname, $value )
  {
    if ( ! \pdbcms\multifields\field_store::getInstance()->is_multi_search_field( $fieldname ) || $this->_empty( $value ) ) {
      return false;
    }
    
    $query_filters = $this->Query->get_field_filters();
    
    $new_filter = true;
    if ( array_key_exists( $fieldname, $query_filters ) ) {
      
      $filter_terms = array();
      foreach( $query_filters[$fieldname] as $query_filter ) {
        /** @var \PDb_List_Query_Filter $query_filter */
        $filter_terms[] = $query_filter->get_term();
      }
      
      $matching_terms = array_intersect( $filter_terms, (array) $value );
      
      $new_filter = count( $matching_terms ) === 0;
    }
    
    return $new_filter; 
  }

  /**
   * adds the plugin search filters
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   * 
   * @param string|array $value the search term to use for the field
   * @param \PDb_Form_Field_Def $field_def the search field
   * @param int $group the group index for the field
   * @return null
   */
  private function _add_filter( $value, $field_def, $group )
  {
    global $PDb_Combo_Multi_Search;

    $search_field = \pdbcms\multifields\field_store::getInstance()->search_field( $field_def->name() );

    /*
     * pdbmps-field-filter
     * 
     * allows custom filtering, passes in the form element type, the search field 
     * object, the query object and the submitted value for the search control
     * 
     * expects a form element string in return. If the filter returns bool false, no 
     * filter will be added to the query object here.
     * 
     * filter tag prefix should be 'pdbcms' I originally has 'pdbmps' for some reason
     */
    $form_element = apply_filters( 'pdbcms-field-filter', $search_field->form_element(), $field_def, $this->Query, $value );

    switch ( $this->element_add_to_query_mode( $form_element, $search_field ) ) {

      case 'skip':
        break;

      case 'date':
        
        if ( $search_field->uses_ranged_control() ) {
          
          // fill in the blank date for ranged searches
          foreach( array('start','end') as $value_part ) {
            
            if ( empty( $value[$value_part] ) ) {
              // assume the current date
              $value[$value_part] = \PDb_Date_Parse::timestamp( time(), array('zero_time' => true), __METHOD__ );
              // update the post array with the assumed value
              $_POST[$field_def->name()] = array_merge( $value, array( $value_part => date( get_option( 'date_format', $value[$value_part] ) ) ) );
              break;
            }
          }
        } else {
          
          // set up the date range for a single input
          $date_range = date_key::get_date_range( $value );
          
          if ( $date_range ) {
            $value = $date_range;
          }
        }
        
        if ( is_array( $value ) ) {
          
          $this->add_null_exclusion( $field_def->name() );
          
          $this->Query->add_filter( $field_def->name(), '>=', date_key::get_timestamp( $value['start'] ), 'AND', $group );
          if ( $search_field->uses_ranged_control() ) {
            $this->Query->add_filter( $field_def->name(), '<=', date_key::get_end_timestamp( $value['end'] ), $this->filter_mode(), $group );
          } else {
            $this->Query->add_filter( $field_def->name(), '<=', date_key::get_timestamp( $value['end'] ), $this->filter_mode(), $group );
          }
        } else {
          $this->Query->add_filter( $field_def->name(), '=', date_key::get_timestamp( $value ), $this->filter_mode(), $group );
        }
        
        break;

      case 'numeric':
        if ( is_array( $value ) ) {
          $this->Query->add_filter( $field_def->name(), '>=', urldecode( $value['start'] ), 'AND', $group );
          $this->Query->add_filter( $field_def->name(), '<=', urldecode( $value['end'] ), $this->filter_mode(), $group );
        } else {
          $this->Query->add_filter( $field_def->name(), '=', urldecode( $value ), $this->filter_mode(), $group );
        }
        break;

      case 'timestamp':
        if ( is_array( $value ) ) {
          $this->Query->add_filter( $field_def->name(), '>=', date( 'Y-m-d H:i:s', \PDb_Date_Parse::timestamp( urldecode( $value['start'] ), array('zero_time' => false), __METHOD__ ) ), 'AND', $group );
          $this->Query->add_filter( $field_def->name(), '<=', date( 'Y-m-d H:i:s', \PDb_Date_Parse::timestamp( urldecode( $value['end'] ), array('zero_time' => false), __METHOD__ ) ), $this->filter_mode(), $group );
        } else {
          $this->Query->add_filter( $field_def->name(), '=', date( 'Y-m-d H:i:s', \PDb_Date_Parse::timestamp( urldecode( $value ), array('zero_time' => true), __METHOD__ ) ), $this->filter_mode(), $group );
        }
        break;

      // for fields that are stored as arrays
      case 'array':

        if ( is_array( $this->post[$field_def->name()] ) ) {
          $this->post[$field_def->name()] = implode( ',', $this->post[$field_def->name()] );
        }
        $value = (array) $value;
        foreach ( $value as $term ) {
          if ( $this->_is_search_term( $term ) ) {
            $logic = $this->filter_mode( $field_def->name() );
            if ( $search_field->multi_or_mode() ) {
              $logic = $term === end( $value ) ? $logic : 'OR';
            }
            $operator = $this->multi_whole_word_match() ? '=' : 'LIKE';
            $term = $this->multi_whole_word_match() ? $this->strip_wildcards($term) : $term;
            $this->Query->add_filter( $field_def->name(), $operator, $term, $logic, $group );
          }
        }
        break;

      // fields that always use whole-word matches
      case 'word':
        if ( is_array( $value ) ) {
          $value = current( $value );
        }
        if ( $this->_is_search_term( $value ) ) {
          // whole word matches don't use wildcards
          $term = $this->strip_wildcards( $this->extract_word( $value ) );
          
          $this->Query->add_filter( $field_def->name(), 'WORD', $term, $this->filter_mode( $field_def->name() ), $group );
        }
        break;

      // fields that use predefined search terms
      case 'selector':
        if ( is_array( $value ) ) {
          $value = current( $value );
        }
        if ( $this->_is_search_term( $value ) ) {
          $operator = $this->is_other_term($value, $field_def) ? 'LIKE' : '=';
          $this->Query->add_filter( $field_def->name(), $operator, $value, $this->filter_mode( $field_def->name() ), $group );
        }
        break;

      case 'default':
      default:
        if ( is_array( $value ) ) {
          $value = current( $value );
        }
        if ( $this->_is_search_term( $value ) ) {
          $operator = $this->value_set_operator( $field_def );
          // whole word matches don't use wildcards
          $term = $this->multi_whole_word_match() && $operator === 'WORD' ? $this->strip_wildcards( $this->extract_word( $value) ) : $value;
          
          $this->Query->add_filter( $field_def->name(), $operator, $term, $this->filter_mode( $field_def->name() ), $group );
        }
    }
  }
  
  /**
   * provides the operator for use on a value set field
   * 
   * @param \PDb_Form_Field_Def $field_def
   * @return string operator
   */
  private function value_set_operator( $field_def )
  {
    $operator = $this->multi_whole_word_match() ? 'WORD' : 'LIKE';
    if ( $field_def->is_value_set() && $this->multi_whole_word_match() ) {
      $operator = $field_def->is_multi() ? 'WORD' : '=';
    }
    return $operator;
  }
  
  /**
   * adds a null exclusion clause for the named field
   * 
   * this is only for date fields
   * 
   * @param string $fieldname
   */
  private function add_null_exclusion( $fieldname )
  {
    add_filter( 'pdb-list_query', function ( $query ) use ( $fieldname ) {
      
      if ( preg_match( "/p.{$fieldname}[<>= ]+CAST/", $query, $matches ) === 1 && strpos( $query, 'p.' . $fieldname . ' IS NOT NULL' ) === false ) {
        
        $replace = 'p.' . $fieldname . ' IS NOT NULL AND ' . $matches[0]; // AND p.' . $fieldname . ' <> 0
        
        $query = str_replace( $matches[0], $replace, $query );
      }
      
      return $query;
    } );
  }
  
  /**
   * tells if the term is an "other" term
   * 
   * @param string $term
   * @param \PDb_Form_Field_Def $field_def
   * @return bool true of the term is not from the list of options
   */
  private function is_other_term( $term, $field_def )
  {
    return ! in_array( $term, $field_def->option_values() );
  }
  
  /**
   * extracts a word from a search term
   * 
   * this is used when whole-word match is enabled and the search term includes 
   * a word boundary character. We extract the largest word from the term and use 
   * that instead of the whole term.
   * 
   * this is temporary until we have more control over the word boundary in the 
   * query regex
   * 
   * @param string $term
   * @return string term with the largest word extracted
   */
  private function extract_word( $term )
  {
    $array = preg_split( '/[ ,\/.]/', $term, -1, PREG_SPLIT_NO_EMPTY );

    array_multisort(array_map('strlen', $array), $array);

    return end($array);
  }
  
  /**
   * removes wildcards from the term
   * 
   * @param string $term
   * @return string
   */
  private function strip_wildcards( $term )
  { 
    return str_replace( array('?', '%', '*', '_'), '', urldecode( $term ) );
  }

  /**
   * selects the add-to-query-filter method based on the form element
   * 
   * @param string  $form_element
   * @param \pdbcms\multifields\search_field $search_field
   * @return string add-to-query-filter mode
   */
  public function element_add_to_query_mode( $form_element, $search_field )
  {
    $field_def = \Participants_Db::get_field_def($search_field->name());
    switch ( $form_element ) {
      
      case false:
        $mode = 'skip';
        break;
      
      case 'date':
      case 'timestamp':
        $mode = $form_element;
        break;

      // these fields are all stored as arrays
      case 'multi-checkbox':
      case 'multi-select-other':
      case 'multiselect-dropdown':
      case 'multi-dropdown':
      case 'link':
        $mode = 'array';
        break;

      case 'numeric':
      case 'decimal':
      case 'currency':
        $mode = 'numeric';
        break;
      
      case 'dropdown-other':
      case 'radio-other':
      case 'radio':
      case 'dropdown':
        $mode = 'selector';
        break;

      default:
        $mode = 'default';
    }

    return apply_filters( 'pdbcms-add_to_query_mode', $mode, $form_element, $search_field );
  }

  /**
   * set logical to standard value
   * 
   * this is needed because when the query is parsed, things like parentheses are 
   * captured. We just want to end up with AND or OR
   * 
   * @param string $logical
   * @return string translated logical
   */
  private function _set_logical( $logical )
  {
    return strpos( $logical, 'OR' ) !== false ? 'OR' : 'AND';
  }

  /**
   * tests for an empty string or array
   * 
   * @var mixed $input
   * @return bool true if empty
   */
  public function _empty( $input )
  {

    if ( is_array( $input ) ) {
      $input = implode( '', $input );
    }
    return strlen( $input ) === 0;
  }

  /**
   * provides the filter mode logic value
   * 
   * if filter mode is selected, this will return 'AND'; if not, 'OR'
   * 
   * @param string $fieldname name of the search field the logic is applied to
   * @return string MySQL logic term
   */
  public function filter_mode( $fieldname = '' )
  {
    global $PDb_Combo_Multi_Search;

    return apply_filters( 'pdb-multisearch_filter_mode', $PDb_Combo_Multi_Search->plugin_option( 'filter_mode', '1' ) == '1' ? 'AND' : 'OR', $fieldname );
  }

  /**
   * set up the submission values
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   */
  private function _setup_submission_values()
  {
    global $PDb_Combo_Multi_Search;
    // if this is our submission, store it
    if ( \pdbcms\Plugin::is_post_search() ) {

      if ( filter_input( INPUT_POST, 'submit', FILTER_DEFAULT, \Participants_Db::string_sanitize() ) === $PDb_Combo_Multi_Search->i18n['clear'] ) {

        foreach ( $this->multi_search_field_list() as $field ) {
          unset( $_POST[$field] );
        }
        unset( $_POST['combo_search'] );
        \Participants_Db::$session->clear( Control_Element::list_query_session_name( filter_input( INPUT_POST, 'instance_index', FILTER_SANITIZE_NUMBER_INT ) ) );

        $_POST['text_search_options'] = $PDb_Combo_Multi_Search->plugin_option( 'default_combo_search_modifier', 'any' );
      } else {
        $this->post = $this->sanitize_submission( $_POST );
        
        $this->is_search_result = true;
      }
    } elseif ( \pdbcms\Plugin::is_get_search() ) {
      $this->post = \pdbcms\Plugin::sanitize_array( array_combine( (array) $_GET['search_field'], (array) $_GET['value'] ) );
    }

    // take out unneeded elements
    foreach ( $this->skip_elements as $element ) {
      unset( $this->post[$element] );
    }
  }

  /**
   * captures the search terms from the combo input
   * 
   * @paeam string  $combo_search input
   * @return array of captured search terms
   */
  private function _capture_search_terms( $combo_search )
  {
    /*
     * split the term into words or quoted phrases
     */
    $splits = preg_split( '/(([\'"]).+?\2| )/', $combo_search, -1, PREG_SPLIT_DELIM_CAPTURE );
    
    /*
     * clear out splits that consist of spaces or quotes only
     */
    $terms = array();
    foreach ( $splits as $term ) {
      if ( $this->valid_term_length( $term ) ) {
        $terms[] = trim( $term, "'\" " );
      }
    }

    return $terms;
  }

  /**
   * provides a text search control
   * 
   * TODO: not used
   * 
   * @return object
   */
  private function _get_text_search_control()
  {
    global $PDb_Combo_Multi_Search;
    $params = array(
        'name' => 'combo_search',
        'title' => '',
        'id' => 'pdb-combo_search-control',
        'wrap_class' => 'combo-search',
        'value' => isset( $_POST['combo_search'] ) ? $_POST['combo_search'] : '',
    );
    $attributes = array('id' => $params['id']);
    $placeholder = $PDb_Combo_Multi_Search->plugin_option( 'placeholder' );
    if ( !empty( $placeholder ) ) {
      $attributes['placeholder'] = $placeholder;
    }
    $html = \PDb_FormElement::get_element( array(
                'type' => 'text-line',
                'name' => $params['name'],
                'attributes' => $attributes,
                'value' => $params['value'],
            ) );

    return (object) (array(
        'control' => $html,
            ) + $params);
  }

  /**
   * tests a post array for search terms
   * 
   * @var array $post the submitted post array
   * @return bool true if a valid search has been submitted
   */
  public function has_search( $post )
  {
    if ( $this->is_search_result ) {
      foreach ( $post as $postvalue ) {
        if ( is_array( $postvalue ) ) {
          foreach ( $postvalue as $subvalue ) {
            if ( $this->_is_search_term( $subvalue ) ) {
              return true;
            }
          }
        } else {
          if ( $this->_is_search_term( $postvalue ) ) {
            return true;
          }
        }
      }
    }
    return false;
  }

  /**
   * tells if the multisearch whole word match is active
   * 
   * @global Plugin $PDb_Combo_Multi_Search
   * @return bool
   */
  private function multi_whole_word_match()
  {
    global $PDb_Combo_Multi_Search;
    return $PDb_Combo_Multi_Search->plugin_option( 'multi_whole_word_match', '0' ) == '1';
  }

  /**
   * provides the combo search field list
   * 
   * @global Plugin $PDb_Combo_Multi_Search
   * @return array of fieldnames
   */
  private function combo_field_list()
  {
    global $PDb_Combo_Multi_Search;

    return $PDb_Combo_Multi_Search->combo_field_list();
  }

  /**
   * tells if the combo search is active
   */
  private function combo_search_is_active()
  {
    return count( $this->combo_field_list() ) > 0;
  }

  /**
   * provides the multi search field list
   * 
   * @global Plugin $PDb_Combo_Multi_Search
   * @return array of fieldnames
   */
  private function multi_search_field_list()
  {
    global $PDb_Combo_Multi_Search;

    return $PDb_Combo_Multi_Search->multi_search_field_list();
  }

  /**
   * alters the query to accomodoate combo searches on multiselect fields
   * 
   * @param \PDb_List_Query
   */
  public function handle_combo_search_on_multi_field( $query )
  {
    foreach ( $this->combo_field_list() as $fieldname ) {

      $field = new \PDb_Form_Field_Def( $fieldname );

      if ( $field && $field->is_multi() ) {
        $field_filters = $query->get_field_filters( $fieldname );
        
        foreach( $field_filters as $filter ) {

          $handler = new combo_multiselect_handler( $filter->get_term() );

          add_filter( 'pdb-list_query', array($handler, 'alter_query') );
          add_action( 'pdb-before_include_shortcode_template', function () use ($handler) {
            remove_filter( 'pdb-list_query', array($handler, 'alter_query') );
          } );      
        }
      }
    }
  }
  
  /**
   * adds the strict search regex characters
   * 
   * this forces the search term to match the entire contents of the db field
   * 
   * @param array $boundaries
   * @return array
   */
  public function add_strict_search_regex( $boundaries )
  {
    if ( ! empty( $boundaries ) )
    {
      $boundaries[0] = str_replace( '"', '"^',$boundaries[0] );
      $boundaries[1] = str_replace( '"', '$"',$boundaries[1] );
    }
    
    return $boundaries;
  }

}

/**
 * handles altering the list query for combo searches on multiselect fields
 */
class combo_multiselect_handler {

  /**
   * @var string the search term
   */
  private $search_term;

  /**
   * @param string $search_term
   */
  public function __construct( $search_term )
  {
    $this->search_term = trim( $search_term, '"\\' );
  }

  /**
   * alters the query
   * 
   * @param string $query
   * @return string query
   */
  public function alter_query( $query )
  {
    $replace = array('LIKE "%', '%"');
    return str_replace( 'LIKE "%\"' . $this->search_term . '\"%"', $replace[0] . $this->search_term . $replace[1], $query );
  }

}
