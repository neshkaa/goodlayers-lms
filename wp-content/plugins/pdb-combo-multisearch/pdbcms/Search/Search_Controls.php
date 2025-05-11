<?php

/**
 * handles presentation of the search controls
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2020  xnau webdesign
 * @license    GPL3
 * @version    1.0
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdbcms\Search;

class Search_Controls {

  /**
   * @var string name of the autosuggest transient
   */
  const autosuggest_transient = 'pdb_combo_search_term_list';

  /**
   * @var array holds the list of multisearch terms as defined in the shortcode (if any)
   */
  private $shortcode_search_fields = false;
  
  /**
   * @var pdbcms\Search\Autosuggest the autosuggest object
   */
  private $autosuggest;

  /**
   * sets up the search processor instance
   */
  public function __construct()
  { 
    add_action( 'init', array($this, 'init') );

    // gets the search_field attribute of the shortcode
    add_filter( 'pdb-shortcode_call_pdb_list', array($this, 'setup_shortcode_search_list'), 1 );
    add_filter( 'pdb-shortcode_call_pdb_search', array($this, 'setup_shortcode_search_list'), 1 );

    // this is to grab the search_field attribute during an AJAX call
    $search = $this;
    add_action( 'pdb-before_include_shortcode_template', array( $this, 'handle_shortcode_atts' ) );
  }

  /**
   * 
   * class initialization
   * 
   * fired on init hook
   */
  function init()
  {
    $this->_setup_search_terms();
  }
  
  /**
   * prepares an array of multi search fields from the user setting
   * 
   * @return array of fieldnames
   */
  public function multi_search_field_list()
  {
    return array_keys( $this->_multi_search_field_list() );
  }

  /**
   * gets a list of fields to include in the combo search
   * 
   * removes any fields that are also present as an individual (multi) field
   * 
   * @return array of field names
   */
  public function combo_field_list()
  {
    return $this->_combo_field_list();
  }

  /**
   * gets a list of fields to include in the combo search autocomplete
   * 
   * @return array of field names
   */
  public function autocomplete_field_list()
  {
    return $this->autosuggest->field_list();
  }

  /**
   * indicates whether either combo search or multi search has been configured
   * 
   * @return bool true if either search type has been defined
   */
  public function combo_multi_search_is_active()
  {
    return $this->multi_search_is_active() || $this->combo_search_is_active();
  }

  /**
   * indicates whether a combo search has been configured
   * 
   * @return bool true if combo search fields have been defined
   */
  public function multi_search_is_active()
  {
    return count( $this->multi_search_field_list() ) > 0;
  }

  /**
   * indicates whether a combo search has been configured
   * 
   * @return bool true if combo search fields have been defined
   */
  public function combo_search_is_active()
  {
    return count( $this->combo_field_list() ) > 0;
  }

  // 'combo_search_modifiers'
  /**
   * indicates whether the search midifier option has been enabled
   * 
   * @return bool true if combo search modifiers are to be shown
   */
  public function combo_search_modifiers_enabled()
  {
    global $PDb_Combo_Multi_Search;
    return $PDb_Combo_Multi_Search->plugin_option( 'combo_search_modifiers' ) != '0';
  }
  
  /**
   * provides the list of multi search control objects
   * 
   * @retrun array of pdbcms\Control_Element objects
   */
  public function search_controls()
  {
    $search_controls = array();
    $search_fields = $this->_multi_search_field_list();

    if ( !empty( $search_fields ) )
    {  
      foreach ( $search_fields as $field )
      {
        /** @var \pdbcms\multifields\search_field $field */
        if ( !$search_control = wp_cache_get( $field->name(), 'pdbcms_search_controls' ) )
        {
          $control = new Control_Element( $field );
          
          do_action( \Participants_Db::$prefix . 'before_get_control', $control );
          $search_controls[] = $control->get_search_control();
          wp_cache_set( $field->name(), $search_control, 'pdbcms_search_controls' );
        }
      }
    }
    
    
    return $search_controls;
  }

  /**
   * provides a set of combo search option fields
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   * @param bool $print if false, return the control HTML
   * @param array $options array of label => value for each option (optional)
   * @return string|null HTML if $print is false
   */
  public function print_search_options( $print = true, $options = false )
  {
    global $PDb_Combo_Multi_Search;
    $value = filter_input( INPUT_POST, 'text_search_options', FILTER_DEFAULT, \Participants_Db::string_sanitize() );
    $default = $PDb_Combo_Multi_Search->plugin_option( 'default_combo_search_modifier', 'any' );
    if ( empty( $value ) ) {
      $value = $default;
    }
    $options = $options ? $options : array(__( 'Any Words', 'pdb-combo-multisearch' ) => 'any', __( 'All Words', 'pdb-combo-multisearch' ) => 'all', __( 'Exact Phrase', 'pdb-combo-multisearch' ) => 'phrase');
    $search_opton_fields = array(
        'type' => 'radio',
        'name' => 'text_search_options',
        'options' => $options,
        'value' => $value,
        'attributes' => array( 'data-default' => $default ),
    );
    $output = \PDb_FormElement::get_element( $search_opton_fields );
    if ( $print )
      echo $output;
    else
      return $output;
  }

  /**
   * sets up the array of search terms for autosuggest
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   * @return null
   */
  private function _setup_search_terms()
  {
    global $PDb_Combo_Multi_Search;
    if ( $PDb_Combo_Multi_Search->plugin_option( 'autocomplete', '0' ) == 1 ) {
      $list = $this->_field_list('combo_field_autocomplete_fields');
      $field_list = count( $list ) > 0 ? $list : $this->_combo_field_list();
      $this->autosuggest = new Autosuggest( $field_list );
    }
  }
  
  /**
   * provides the list of combo search fields
   * 
   * @return array of fieldnames
   */
  private function _combo_field_list()
  {
    return $this->_field_list('combo_field_list');
  }

  /**
   * prepares an array of search fields from the user setting
   * 
   * @global \pdbcms\Plugin $PDb_Combo_Multi_Search
   * @param string $list the name of the list of fields to use
   * @return array
   */
  private function  _field_list( $list )
  {
    $cachegroup = 'combo-multisearch';
    
    // check if the shortcode is configured to skip showing the combo search
    if ( ! $this->show_combo_search_control() ) {
      return array();
    }
    
    $field_list = wp_cache_get( $list, $cachegroup );

    if ( $field_list === false ) {
      global $PDb_Combo_Multi_Search;

      $field_list = $PDb_Combo_Multi_Search->plugin_option( $list, '' );
      
      if ( !empty( $field_list ) ) {
        $list_items = is_array( $field_list ) ? $field_list : explode( ',', str_replace( ' ', '', $field_list ) );
        
        $field_list = array();
        foreach ( $list_items as $fieldname ) {
          if ( \Participants_Db::is_column( $fieldname ) ) {
            $field_list[] = $fieldname;
          }
        }
        wp_cache_set( $list, $field_list, $cachegroup );
      } else {
        $field_list = array();
      }
    }

    return $field_list;
  }
  
  /**
   * tells if the combo search should be shown based on the shortcode
   * 
   * the way this works is if the shortcode has the search_fields attribute, it 
   * must also have 'combo_search' as one of the fields in that attribute value
   * 
   * @return bool true if the combo search control should be shown
   */
  private function show_combo_search_control()
  {
    if ( is_array( $this->shortcode_search_fields ) && ! in_array( 'combo_search', $this->shortcode_search_fields ) ) {
       return false;
    }
    return true;
  }
  
  /**
   * provides the list of multi search fields
   * 
   * @return array of search_field objects
   */
  private function _multi_search_field_list()
  {
    $field_store = \pdbcms\multifields\field_store::getInstance();
    $multisearch_fields = $field_store->multi_search_fields();
    
    if ( is_array( $this->shortcode_search_fields ) ) {
      
      /* if there are search fields configured in the shortcode, use that to 
       * determine the list of search fields
       */
      $search_fields = array();
      foreach ( $this->shortcode_search_fields as $fieldname ) {
        if ( isset( $multisearch_fields[$fieldname] ) ) {
          $search_fields[$fieldname] = $multisearch_fields[$fieldname];
        }
      }
      
    } else {
      
      $search_fields = $multisearch_fields;
    }
    
    return $search_fields;
  }

  /**
   * provides the current autocomplete term list
   * 
   * @return array
   */
  public function autocomplete_term_list()
  {
    if ( is_object( $this->autosuggest ) ) {
       return $this->autosuggest->term_list();
    } else {
      return array();
    }
  }
  
  /**
   * provides the autosuggest instance
   * 
   * @return pdbcms\Search\Autosuggest
   */
  public function autosuggest()
  {
    return $this->autosuggest;
  }
  
  /**
   * switches in the search fields if they are in the shortcode search_fields attribute
   * 
   * @param \PDb_List $shortcode
   */
  public function handle_shortcode_atts( $shortcode )
  {
    if ( isset( $shortcode->shortcode_atts['filter'] ) && ! empty( $shortcode->shortcode_atts['filter'] ) ) {
      $this->setup_preload_fields( $shortcode->shortcode_atts['filter'] );
    }
    if ( defined( 'DOING_AJAX' ) && DOING_AJAX && ( $shortcode->module === 'list' || $shortcode->module === 'search' ) ) {
      $this->setup_shortcode_search_list( $shortcode->shortcode_atts );
    }
  }
  
  /**
   * gets the multisearch field preloads from the shortcode
   * 
   * @param string $filter the shortcode filter string
   */
  private function setup_preload_fields( $filter )
  {
    new shortcode_filter( $filter );
  }

  /**
   * sets up the shortcode search field list
   * 
   * called on pdb-shortcode_call_pdb_list filter
   * 
   * @param array $attributes the shortcode attributes
   * @return array the attributes array
   */
  public function setup_shortcode_search_list( $attributes )
  {
    if ( isset( $attributes['template'] )  && strpos( $attributes['template'], 'multisearch' ) !== false ) {
      $attributes['class'] = ( isset( $attributes['class'] ) ? $attributes['class'] : '' ) . ' multisearch';
    }
    
    if ( isset( $attributes['search_fields'] ) ) {
      
      $this->shortcode_search_fields = array();
              
      foreach ( explode( ',', str_replace( ' ' , '', $attributes['search_fields'] ) ) as $fieldname ) {
        if ( array_key_exists( $fieldname, \Participants_Db::$fields ) ) {
          $this->shortcode_search_fields[] = $fieldname;
        }
        if ( $fieldname === 'combo_search' ) {
          $this->shortcode_search_fields[] = $fieldname;
        }
      }
    }
    return $attributes;
  }
  
}
