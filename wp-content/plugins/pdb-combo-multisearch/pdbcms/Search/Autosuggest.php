<?php
/**
 * provides the autosuggest terms
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2020  xnau webdesign
 * @license    GPL3
 * @version    1.3
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdbcms\Search;

class Autosuggest {

  /**
   * @var string name of the autosuggest transient
   */
  const autosuggest_transient = 'pdbcms_term_list_';

  /**
   * an array of autocomplete terms for the combo search field
   * 
   * @var array auto complete term list
   */
  private $term_list = array();

  /**
   * @var array of autosuggest field names
   */
  private $field_list;

  /**
   * @var int the term list cache persistence duration
   */
  private $expiration;

  /**
   * @param array $field_list list of fieldnames to use in the autosuggest
   */
  public function __construct( $field_list )
  {
    $this->expiration = apply_filters( 'pdbcms-autosuggest_term_list_expiration', WEEK_IN_SECONDS );

    $this->_setup_field_list( $field_list );

    $this->_set_up_term_list();

    add_action( 'pdb-after_submit_update', array($this, 'expire_autosuggest_transient') );
    add_action( 'pdb-after_submit_signup', array($this, 'expire_autosuggest_transient') );

    /*
     * before PDB version 1.9.6, there is no proper "after import CSV" action, 
     * so this will do it because this is the last thing the importer does
     */
    $action = version_compare( '1.9.6', \Participants_Db::$plugin_version ) === 1 ? 'pdb-delete_file' : 'pdb-after_import_csv';
    add_action( $action, array($this, 'expire_autosuggest_transient') );

    // clear the cache when certain settings are saved
    foreach ( array('combo_field_list', 'combo_field_autocomplete_fields') as $hook )
    {
      add_action( 'update_option_' . $hook, array($this, 'expire_autosuggest_transient') );
    }
  }

  /**
   * provides the list of autocomplete fields
   * 
   * @return array
   */
  public function field_list()
  {
    return $this->field_list;
  }

  /**
   * provides the list of autosuggest terms
   * 
   * @return array
   */
  public function term_list()
  {
    return $this->term_list;
  }

  /**
   * provides the current autocomplete term list
   * 
   * @return array
   */
  public function autocomplete_term_list()
  {
    return $this->term_list;
  }

  /**
   * expires the autosuggest transient
   */
  public function expire_autosuggest_transient()
  {
    self::clear_cache();
  }

  /**
   * generate the term list, given an array of records
   * 
   * this is to set up the inline term list
   * 
   * @param array $record_list array of record objects
   */
  public function term_list_from_records( $record_list )
  {
    $key = $this->inline_term_list_key( $record_list );

    if ( defined( 'PDB_DEBUG' ) && PDB_DEBUG > 1 )
    {
      delete_transient( $key );
    }

    $term_list = get_transient( $key );

    if ( !$term_list )
    {

      // rebuild the term list
      $term_list = array();

      foreach ( $record_list as $record )
      {
        foreach ( $record as $field => $value )
        {
          if ( in_array( $field, $this->field_list ) )
          {
            $term = \Participants_Db::unserialize_array( $value, false );

            if ( is_array( $term ) )
            {
              $field_def = new \PDb_Form_Field_Def( $field );
              foreach ( $term as $word )
              {
                $wordtitle = $field_def->value_title( $word );
                if ( !in_array( $wordtitle, $term_list ) )
                {
                  $term_list[] = $wordtitle;
                }
              }
            } else
            {
              $term = \Participants_Db::apply_filters( 'translate_string', $value );
              if ( !in_array( $term, $term_list ) )
              {
                $term_list[] = $term;
              }
            }
          }
        }
      }

      set_transient( $key, $term_list, $this->expiration );

      \Participants_Db::debug_log( __METHOD__ . ' generating term list, ' . count( $term_list ) . ' terms generated', 2 );
    }

    $this->term_list = array_values( array_filter( $term_list ) );
  }

  /**
   * provides the inline term list transient key
   * 
   * @param array $record_list
   * @return string
   */
  private function inline_term_list_key( $record_list )
  {
    return self::autosuggest_transient . md5( implode( '', array_keys( $record_list ) ) );
  }

  /**
   * prints the term list inline
   * 
   * @param \PDb_Shortcode $shortcode
   */
  public function print_inline_term_list( $shortcode )
  {
    if ( strpos( $shortcode->template_basename(), 'multisearch' ) !== false ) :
    ?>
    <script>
      jQuery(function () {
        if (typeof PDbCMS !== "undefined") {
          PDbCMS.autocomplete_terms = <?php echo json_encode( $this->term_list ) ?>;
        }
      });
    </script>
    <?php
    endif;
  }

  /**
   * compiles a list of search terms
   * 
   * this is for populating the autocomplete
   * 
   */
  private function _set_up_term_list()
  {
    if ( defined( 'PDB_DEBUG' ) && PDB_DEBUG > 1 )
    {
      delete_transient( self::autosuggest_transient );
    }

    $this->term_list = get_transient( self::autosuggest_transient );

    if ( $this->term_list === false )
    {
      $this->_generate_term_list();
      set_transient( self::autosuggest_transient, $this->term_list, $this->expiration );
    }
  }

  /**
   * generates the term list
   */
  private function _generate_term_list()
  {
    $terms = array();
    foreach ( $this->field_list as $fieldname )
    {
      $control = Control_Element::combo_search_control( $fieldname );
      $field_terms = $control->get_unique_recorded_values( false, false );

      foreach ( $field_terms as $title => $value )
      {
        $title = \Participants_Db::apply_filters( 'translate_string', $title );
        $terms[$title] = html_entity_decode( (string) $title );
      }
    }

    $this->term_list = array_values( $terms );
  }

  /**
   * clears the autocomplete transient
   */
  public static function clear_cache()
  {
    if ( defined( 'PDB_DEBUG' ) && PDB_DEBUG > 1 )
    {
      \Participants_Db::debug_log( __METHOD__ );
    }
    self::clean_up();
  }

  /**
   * cleans up all transient options for the class
   * 
   * @global \wpdb $wpdb
   */
  public static function clean_up()
  {
    global $wpdb;

    $class_transients = $wpdb->get_results( "SELECT option_name FROM $wpdb->options WHERE option_name LIKE '%" . self::autosuggest_transient . "%'" );

    foreach ( $class_transients as $option )
    {
      delete_transient( str_replace( '_transient_', '', $option->option_name ) );
    }
  }

  /**
   * sets up the field list property
   * 
   * @param array $field_list the raw list from the configuration
   */
  private function _setup_field_list( $field_list )
  {
    $this->field_list = array_filter( $field_list, array($this, 'is_valid_field') );
  }

  /**
   * checks the field to determine if it should be an autosuggest field
   * 
   * @param string $fieldname
   * @retun bool true if it should be included in the autosuggest
   */
  private function is_valid_field( $fieldname )
  {
    if ( !\PDb_Form_Field_Def::is_field( $fieldname ) )
    {
      return false;
    }
    $field = new \PDb_Form_Field_Def( $fieldname );
    if ( in_array( $field->form_element(), $this->disallowed_form_elements() ) )
    {
      return false;
    }
    return true;
  }

  /**
   * provides a list of form elements that are not allowed to contribute to the autosuggest
   * 
   * this by default includes fields that would not have searchable content and 
   * fields that would have too much searchable content
   * 
   * @return array
   */
  private function disallowed_form_elements()
  {
    $disallowed = wp_cache_get( __CLASS__ . '-disallowed' );

    if ( $disallowed === false )
    {
      $disallowed = apply_filters( 'pdbcms-autosuggest_disallowed_form_elements', array('text-area', 'rich-text', 'participant-log', 'timestamp', 'captcha', 'placeholder', 'contact-form', 'star-rating', 'overall-rating', 'favorite', 'staticmap', 'log-table') );
      wp_cache_set( __CLASS__ . '-disallowed', $disallowed );
    }

    return $disallowed;
  }
}
