<?php
/**
 * class for creating a multiple-field search for the Participants Database WordPress Plugin
 * 
 * @category   Plugins
 * @package    WordPress
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2011 - 2024 7th Veil, LLC
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GPL2
 * @version    2.3
 * @subpackage Participants Database 1.6
 */

namespace pdbcms;

use \Participants_Db;

class Plugin extends \PDb_Aux_Plugin {

// plugin slug
  public $aux_plugin_name = 'pdb-combo-multisearch';
// shortname for the plugin
  public $aux_plugin_shortname = 'pdbcms';

  /**
   * @var string  the subsource ID for search form submissions
   */
  const subsource = 'pdb-multi-searchform';

  /**
   * @var string name of the cache test cookie
   */
  const cache_test_cookie = 'pdb-cache_test';

  /**
   * @var string element id of the combo fields selector
   */
  const comboselect = 'combo_field_select';

  /**
   * @var array holds translation strings
   * 
   */
  public $i18n;

  /**
   * @var Search\Submission holds the search processor class instance
   */
  public $search;

  /**
   * @var Search\Search_Controls holds the search control presentation class instance
   */
  public $controls;

  /**
   * @var int holds the current instance index value
   */
  public $instance_index;

  /**
   * 
   * @param string $plugin_file
   */
  public function __construct( $plugin_file )
  {
    $this->plugin_data += array(
        'SupportURI' => 'https://xnau.com/product_support/participants-database-combo-multisearch/',
    );
    parent::__construct( __CLASS__, $plugin_file );

    add_action( 'init', array($this, 'init_plugin') );
    add_action( 'pdb-template_select', array($this, 'set_template') );

    new Assets( $this );

    $this->search = new Search\Submission();
    $this->controls = new Search\Search_Controls();

    add_filter( 'pdb-cms-filter_mode', array($this, 'filter_mode') );

    add_action( 'pdb-list_query_object', function ( $list_query ) {
      // stash the list query object for later use 
      Search\list_query_stash::stash( $list_query );
    } );

    $ajax = new multifields\ajax();
    add_action( 'wp_ajax_' . multifields\updates::action_key, array($ajax, 'submission') );

    add_action( 'admin_post_' . multifields\updates::action_key, '\pdbcms\multifields\updates::process_submission' );

    add_action( 'plugins_loaded', array($this, 'set_inline_autosuggest') );

    register_uninstall_hook( $plugin_file, '\pdbcms\Plugin::uninstall' );
  }

  /**
   * sets up the xnau plugin updates
   * 
   * @param string $plugin_file absolute path
   */
  protected function setup_updates( $plugin_file )
  {
    if ( ! method_exists( 'xnau_plugin_updates', 'setup' ) )
    {
      \PDb_Aux_Plugin::missing_updater_plugin_notice( $this->aux_plugin_title );
    } 
    else
    {
      \xnau_plugin_updates::setup( $plugin_file, $this->aux_plugin_name );
    }
  }

  /**
   * intializes the plugin
   * 
   */
  public function init_plugin()
  {
    $this->aux_plugin_title = __( 'Combo Multi Search', 'pdb-combo-multisearch' );

    $this->i18n = array(
        'clear' => __( 'Clear', 'pdb-combo-multisearch' ),
        'search' => __( 'Search', 'pdb-combo-multisearch' ),
        'any' => __( 'Any', 'pdb-combo-multisearch' ),
        'available fields' => __( 'Available Fields', 'pdb-combo-multisearch' ),
        'combo fields' => __( 'Combo Search Fields', 'pdb-combo-multisearch' ),
    );

    $this->check_version();
    
    $this->setup_cache_hooks();
  }

  /**
   * enable the plugin and set it up
   * 
   * should be fired by the template to avoid loading javascript uneccesarily
   * 
   */
  public function enable()
  {
    do_action( 'pdbcms_enable' );
    add_filter( 'pdb-html5_add_required_attribute', function () {
      return false;
    } );
    $this->instance_index = \Participants_Db::$instance_index - 1;
  }
  
  /**
   * sets up the autocomplete cache clearing hooks
   */
  private function setup_cache_hooks()
  {
    if ( $this->plugin_option( 'autocomplete', 1 ) )
    {
      add_action( 'pdb-after_submit_signup', [ '\pdbcms\Search\field_value_cache', 'expire_cache' ] );
      add_action( 'pdb-after_submit_add', [ '\pdbcms\Search\field_value_cache', 'expire_cache' ] );
      add_action( 'pdb-after_submit_update', [ '\pdbcms\Search\field_value_cache', 'expire_cache' ] );
    }
  }

  /**
   * sets the plugin template
   * 
   * use this plugin's default template if a template named "multisearch" has been 
   * named in the shortcode and a custom override is not present
   * 
   * @var string $template name of the currently selected template
   * @return string template path
   */
  public function set_template( $template )
  {
    $path = empty( $this->parent_path ) ? plugin_dir_path( $this->plugin_path ) : $this->parent_path;
    /*
     * check if it's this plugin's template being called for and it doesn't exist as a custom template 
     */
    if ( strpos( $template, 'multisearch' ) !== false && !is_file( get_stylesheet_directory() . '/templates/' . $template ) )
    {
      $template = $path . 'templates/' . $template;
    }
    return $template;
  }

  /**
   * tells if the current result is a combo-multisearch result
   * 
   * @return bool
   */
  public function is_search_result()
  {
    return $this->search->is_search_result;
  }

  /**
   * sets up the inline autosuggest
   */
  public function set_inline_autosuggest()
  {
    if ( $this->combo_search_is_active() && $this->plugin_option( 'autocomplete', 0 ) != 0 && $this->plugin_option( 'inline_autosuggest', 0 ) != 0 )
    {
      add_action( 'pdb-list_query_object', array($this, 'inline_autosuggest'), 100 );
    }
  }

  /**
   * generates the inline autosuggest
   * 
   * called on the pdb-list_query filter
   * 
   * @global \wpdb $wpdb
   * @param \PDb_List_Query $query
   */
  public function inline_autosuggest( $query )
  {
    if ( defined('DOING_AJAX') && DOING_AJAX && $query->is_search_result() )
    {
      return;
    }
    
    global $wpdb;

    $term_query = clone $query;

    $term_query->suppress = false;

    $term_query->set_query_session( 1 );

    if ( method_exists( $term_query, 'clear_foreground_clauses' ) )
    {
      // generate a query that does not include the user search
      $term_query->clear_foreground_clauses();
    }

    $list_query = \Participants_Db::apply_filters( 'list_query', $term_query->get_list_query() );

    $this->controls->autosuggest()->term_list_from_records( $wpdb->get_results( $list_query ) );

    add_action( 'pdb-before_include_shortcode_template', array($this->controls->autosuggest(), 'print_inline_term_list') );
  }

  /**
   * provides the default set of hidden fields
   * 
   * @depends PDb_FormElement class
   * @param array $hidden_fields hidden fields to add as $name => $value
   * @param bool $print if false, returns the HTML string
   * @return string|null string if $print is false
   */
  public function print_hidden_fields( $hidden_fields, $print = true )
  {
    \PDb_FormElement::print_hidden_fields( $hidden_fields, $print );
  }

  /**
   * prepares an array of multi search fields from the user setting
   * 
   * @return array
   */
  public function multi_search_field_list()
  {
    return $this->controls->multi_search_field_list();
  }

  /**
   * prepares an array of combo search fields from the user setting
   * 
   * @return array
   */
  public function combo_field_list()
  {
    return $this->controls->combo_field_list();
  }

  /**
   * gets the combo search control value
   * 
   * @param string $name name of the text search control
   * @return string the last-submitted value
   */
  public function get_text_search_value( $name = 'combo_search' )
  {
    return $this->search->get_text_search_value( $name );
  }

  /**
   * provides a search term feedback display string
   * 
   * @return string
   */
  public function search_term_feedback()
  {
    return implode( ', ', $this->search->get_search_field_terms() );
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
   * indicates whether a multi search has been configured
   * 
   * @return bool true if multi search fields have been defined
   */
  public function multi_search_is_active()
  {
    return count( $this->controls->multi_search_field_list() ) > 0;
  }

  /**
   * indicates whether a combo search has been configured
   * 
   * @return bool true if combo search fields have been defined
   */
  public function combo_search_is_active()
  {
    return count( $this->controls->combo_field_list() ) > 0;
  }

  // 'combo_search_modifiers'

  /**
   * indicates whether the search modifier option has been enabled
   * 
   * @return bool true if combo search modifiers are to be shown
   */
  public function combo_search_modifiers_enabled()
  {
    return $this->plugin_option( 'combo_search_modifiers' ) != '0';
  }

  /**
   * supplies the last search term or terms as a display string
   * 
   * @return string
   */
  public function current_search_term()
  {
    $feedback = new Search\Result_Feedback( $this->multi_search_field_list(), $this->is_search_result() );
    return $feedback->search_feedback_message();
  }

  /**
   * preps a search term for display
   * 
   * @param string $term the raw term
   * @param string $field the field name (optional, needed for finding value titles)
   * @return string
   */
  public static function prep_term_for_display( $term, $field = '' )
  {
    return Search\Result_Feedback::prep_term_for_display( $term, $field );
  }

  /**
   * supply the feedback message for an incomplete search submission
   * 
   * @param string  $message message text from the template
   * @return string
   */
  public function incomplete_search_error_message( $message = '' )
  {
    if ( empty( $message ) )
    {

      $min_length_message = $this->plugin_option( 'multi_min_term_length', '1' ) > 1 ? sprintf( __( 'Search words must be at least %s characters in length.', 'pdb-combo-multisearch' ), $this->plugin_option( 'multi_min_term_length' ) ) : '';

      if ( $this->plugin_option( 'multi_all_required', '0' ) == '0' )
      {

        if ( !empty( $min_length_message ) )
        {
          $message = $min_length_message;
        } else
        {
          $message = __( 'Please select something to search for.', 'pdb-combo-multisearch' );
        }
      } else
      {
        $message = __( 'Please complete all search fields.', 'pdb-combo-multisearch' ) . ' ' . $min_length_message;
      }
    }

    return $message;
  }

  /**
   * supply some property values
   */
  public function __get( $name )
  {
    switch ( $name ) {
      case 'search_controls':
        return $this->controls->search_controls();
        break;
      case 'term_list':
        return $this->controls->autocomplete_term_list();
        break;
      case 'is_multisearch':
        return $this->is_search_result();
    }
  }

  /**
   * prints the combo search options
   */
  public function print_search_options()
  {
    $this->controls->print_search_options();
  }

  /**
   * provides a list of all fields
   * 
   * @global \wpdb $wpdb
   * @return array of \PDb_Form_Field_Def objects indexed by field name
   */
  public static function all_search_fields()
  {
    $cachekey = 'pdbcms-all_search_fields';
    $fieldlist = wp_cache_get( $cachekey );

    if ( !$fieldlist )
    {

      global $wpdb;

      $fieldlist = array();

      $sql = 'SELECT f.* 
              FROM ' . Participants_Db::$fields_table . ' f 
                JOIN ' . Participants_Db::$groups_table . ' g ON f.group = g.name 
              WHERE f.name IN ("' . implode( '","', self::db_columns() ) . '") AND
                f.form_element NOT IN ( "' . implode( '","', self::invalid_form_elements() ) . '" ) 
              ORDER BY g.order, f.order';

      foreach ( $wpdb->get_results( $sql ) as $column )
      {

        if ( \PDb_Form_Field_Def::is_field( $column->name ) )
        {
          $fieldlist[$column->name] = new \PDb_Form_Field_Def( $column->name );
        }
      }

      wp_cache_set( $cachekey, $fieldlist, '', 30 );
    }

    return $fieldlist;
  }

  /**
   * provides a list of all main db columns
   * 
   * @global \wpdb $wpdb
   * @return array
   */
  private static function db_columns()
  {
    $db_columns = array();

    global $wpdb;

    foreach ( $wpdb->get_results( 'SHOW COLUMNS FROM ' . Participants_Db::$participants_table ) as $column )
    {

      $db_columns[] = $column->Field;
    }

    return $db_columns;
  }

  /**
   * provides a list of form element types that should not be available as combo multi search fields
   * 
   * @return array of form element names
   */
  public static function invalid_form_elements()
  {
    return apply_filters( 'pdbcms-invalid_search_form_elements', array(
        'captcha',
        'placeholder',
        'staticmap',
        'heading',
        'shortcode',
            ) );
  }

  /**
   * performs a deep sanitize of a user input
   * 
   * @param string $value
   * @return string|array
   */
  public static function sanitize( $value )
  {
    if ( is_array( $value ) )
    {
      return self::sanitize_array( $value );
    }
    return self::sanitize_string( $value );
  }

  /**
   * provides a general function for sanitizing an array
   * 
   * @param array $array the unsanitized array
   * @return array
   */
  public static function sanitize_array( $array )
  {
    array_walk_recursive( $array, function ( &$value, $key ) {
      $value = Plugin::sanitize( $value );
      $key = sanitize_key( $key );
    } );

    return $array;
  }

  /**
   * performs a deep sanitize of a user input string
   * 
   * @param string $string
   * @return string
   */
  private static function sanitize_string( $string )
  {
    /*
     * decoding: this is so values that have been encoded can be sanitized in unencoded form
     */
    $decoded_value = trim( stripslashes( htmlspecialchars_decode( urldecode( $string ) ) ) );

    return $decoded_value;
  }

  /**
   * determines if the current submission is a search
   * 
   * @return bool true if there are search terms in the submission
   */
  public static function is_search_submission()
  {
    return self::is_post_search() || self::is_get_search();
  }

  /**
   * determines if the post data is a search
   * 
   * @return bool
   */
  public static function is_post_search()
  {
    return filter_input( INPUT_POST, 'subsource', FILTER_DEFAULT, \Participants_Db::string_sanitize() ) === self::subsource;
  }

  /**
   * determines if the get data is a search
   * 
   * checks the pdb-allow_get_searches filter also
   * 
   * @return bool
   */
  public static function is_get_search()
  {
    if ( !\Participants_Db::apply_filters( 'allow_get_searches', true ) )
    {
      return false;
    }

    if ( self::all_search_fields_required() )
    {
      $is = self::get_has_all_search_terms();
    } else
    {
      $is = array_key_exists( 'search_field', $_GET ) && array_key_exists( 'value', $_GET );
    }

    return $is;
  }

  /**
   * tells if the "all required" setting is set
   * 
   * @global Plugin $PDb_Combo_Multi_Search
   * @return bool
   */
  public static function all_search_fields_required()
  {
    global $PDb_Combo_Multi_Search;

    return $PDb_Combo_Multi_Search->plugin_option( 'multi_all_required', '0' ) != '0';
  }

  /**
   * checks the _GET array for all search fields
   * 
   * @return bool
   */
  private static function get_has_all_search_terms()
  {
    $has_all = count( self::configured_search_fields() ) > 0 ? true : false;

    $terms = self::url_search_terms();
    foreach ( self::configured_search_fields() as $fieldname )
    {
      if ( !array_key_exists( $fieldname, $terms ) )
      {
        $has_all = false;
      }
    }

    return $has_all;
  }

  /**
   * provides the submitted terms from the GET array
   * 
   * @return array as $fieldname => $value
   */
  public static function url_search_terms()
  {
    $terms = array();
    if ( array_key_exists( 'search_field', $_GET ) && is_array( $_GET['search_field'] ) && is_array( $_GET['value'] ) )
    {
      foreach ( $_GET['search_field'] as $fieldname )
      {
        $terms[filter_var( $fieldname, FILTER_SANITIZE_SPECIAL_CHARS )] = filter_var( $fieldname, FILTER_SANITIZE_SPECIAL_CHARS );
      }
    } else
    {
      $terms[filter_input( INPUT_GET, 'search_field', FILTER_SANITIZE_SPECIAL_CHARS )] = filter_input( INPUT_GET, 'value', FILTER_SANITIZE_SPECIAL_CHARS );
    }

    return $terms;
  }

  /**
   * provides a list of all currently configured search fields
   * 
   * @global Plugin $PDb_Combo_Multi_Search
   * @return array of field names
   */
  private static function configured_search_fields()
  {
    global $PDb_Combo_Multi_Search;
    $field_store = \pdbcms\multifields\field_store::getInstance();
    $field_list = array_keys( $field_store->field_name_array() );

    if ( $PDb_Combo_Multi_Search->combo_search_is_active() )
    {
      $field_list = array_merge( $field_list, array('combo_search') );
    }

    return $field_list;
  }

  /**
   * provides the null select key string
   * 
   * @return string
   */
  public static function null_select_key()
  {
    return method_exists( '\PDb_FormElement', 'null_select_key' ) ? \PDb_FormElement::null_select_key() : 'null_select ';
  }
  
  /**
   * tells if the pdb chosen plugin is active
   * 
   * @global \pdbcdl\Plugin $PDb_Chosen_Dropdown
   * @return bool
   */
  public static function chosen_active()
  {
    global $PDb_Chosen_Dropdown;
    return is_object( $PDb_Chosen_Dropdown );
  }

  /**
   * SETTINGS API
   */
  function settings_api_init()
  {
    register_setting( $this->aux_plugin_name . '_settings', $this->settings_name() );

    // define settings sections
    $this->settings_sections = array(
        array(
            'title' => __( 'General Settings', 'pdb-combo-multisearch' ),
            'slug' => 'general_setting_section',
        ),
        array(
            'title' => __( 'Combination Search Settings', 'pdb-combo-multisearch' ),
            'slug' => 'combo_search_setting_section',
        ),
        array(
            'title' => __( 'Multi Search Settings', 'pdb-combo-multisearch' ),
            'slug' => 'multi_search_setting_section',
        ),
    );
    $this->_add_settings_sections();

    /*     * * GLOBAL SETTINGS */

    $this->add_setting( array(
        'name' => 'filter_mode',
        'title' => __( 'Enable Filter Mode', 'pdb-combo-multisearch' ),
        'type' => 'checkbox',
        'default' => '1',
        'help' => __( 'If checked, only records which match <strong>all</strong> search selectors will be shown. If unchecked, records which match <strong>any</strong> of the search selectors will be shown', 'pdb-combo-multisearch' ),
        'section' => 'general_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'restore_last_search',
        'title' => __( 'Remember the Last Search', 'pdb-combo-multisearch' ),
        'type' => 'checkbox',
        'default' => '1',
        'help' => __( 'If checked, the search page will restore the search and results when returning to the page.', 'pdb-combo-multisearch' ),
        'section' => 'general_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'multi_all_required',
        'title' => __( 'Require all Search Fields', 'pdb-combo-multisearch' ),
        'type' => 'checkbox',
        'default' => '0',
        'help' => __( 'If checked, all search fields must have a search value.', 'pdb-combo-multisearch' ),
        'section' => 'general_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'multi_min_term_length',
        'title' => __( 'Minimum Search Term Length', 'pdb-combo-multisearch' ),
        'type' => 'number',
        'default' => '1',
        'help' => __( 'Sets the global minimum length for a valid search term in a text input.', 'pdb-combo-multisearch' ),
        'section' => 'general_setting_section',
        'attributes' => array('min' => '1', 'step' => '1'),
        'style' => 'width: 4em',
            )
    );

    $this->add_setting( array(
        'name' => 'allow_multiple_search_modes',
        'title' => __( 'Enable Field Multiple Search Modes', 'pdb-combo-multisearch' ),
        'type' => 'checkbox',
        'default' => '0',
        'help' => __( 'If checked, it will be possible to configure fields to appear in both Multi-Search and Combo Search. Note that this can lead to unexpected results if not configured correctly.', 'pdb-combo-multisearch' ),
        'section' => 'general_setting_section',
            )
    );

    /*     * * COMBO SEARCH SETTINGS */

    $this->add_setting( array(
        'name' => 'combo_field_list',
        'title' => __( 'Combination Search Fields', 'pdb-combo-multisearch' ),
        'type' => 'combofield',
        'options' => combofields\field_list::control_options(),
        'attributes' => array('id' => self::comboselect),
        'help' => __( 'Select fields on the left to include in the Combination Search.', 'pdb-combo-multisearch' ) . '<br/>' . __( 'Select fields on the right to remove from Combo Search.', 'pdb-combo-multisearch' ) . '<br/>' . __( 'Greyed-out items are assigned to Multi Search.', 'pdb-combo-multisearch' ),
        'section' => 'combo_search_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'combo_search_label',
        'title' => __( 'Combination Search Field Label', 'pdb-combo-multisearch' ),
        'type' => 'text',
        'default' => '',
        'help' => __( 'Leave blank to not use a label.', 'pdb-combo-multisearch' ),
        'style' => 'width:100%',
        'section' => 'combo_search_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'placeholder',
        'title' => __( 'Combination Search Field Placeholder Text', 'pdb-combo-multisearch' ),
        'type' => 'text',
        'default' => __( 'Search', 'pdb-combo-multisearch' ) . '&hellip;',
        'help' => __( 'Text to place in the text search field as a placeholder. Leave blank for no placeholder.', 'pdb-combo-multisearch' ),
        'style' => 'width:100%',
        'section' => 'combo_search_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'combo_search_modifiers',
        'title' => __( 'Show the Combo Search Modifiers', 'pdb-combo-multisearch' ),
        'type' => 'checkbox',
        'default' => '1',
        'help' => __( 'If checked, the user can choose the type of search to use ("any," "all," or "phrase"). If unchecked, the selection is not shown and the search will use the Default Search Modifier setting.', 'pdb-combo-multisearch' ),
        'section' => 'combo_search_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'default_combo_search_modifier',
        'title' => __( 'Default Search Modifier', 'pdb-combo-multisearch' ),
        'type' => 'radio',
        'default' => 'any',
        'options' => array('any', 'all', 'phrase'),
        'help' => __( 'If user-selectable search modifiers are not shown, this is the modifier that will be used on combo searches.', 'pdb-combo-multisearch' ),
        'section' => 'combo_search_setting_section',
        'style' => 'display: inline-block;margin-right:13px;',
            )
    );

    $this->add_setting( array(
        'name' => 'combo_whole_word_match',
        'title' => __( 'Whole Word Match Only', 'pdb-combo-multisearch' ),
        'type' => 'checkbox',
        'default' => '0',
        'help' => __( 'If checked, only whole words matching the search term will be included in the result.', 'pdb-combo-multisearch' ),
        'section' => 'combo_search_setting_section',
            )
    );
    

    $this->add_setting( array(
        'name' => 'combo_strict_search',
        'title' => __( 'Whole Word Strict Matching', 'pdb-combo-multisearch' ),
        'type' => 'checkbox',
        'default' => '0',
        'help' => __( 'When "Whole Word Match Only" is enabled and this is checked, the combo search term must match the full content of the database field.', 'pdb-combo-multisearch' ),
        'section' => 'combo_search_setting_section',
            )
    );

    // AUTOCOMPLETE

    $this->add_setting( array(
        'name' => '',
        'title' => __( 'Autocomplete Settings', 'pdb-combo-multisearch' ),
        'type' => 'subsection',
        'default' => '1',
        'help' => '',
        'section' => 'combo_search_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'autocomplete',
        'title' => __( 'Use Autocomplete for Combo Search', 'pdb-combo-multisearch' ),
        'type' => 'checkbox',
        'default' => '1',
        'help' => __( 'If checked, search term suggestions (drawn from the fields included in the combo search) will be shown to the user as they type.', 'pdb-combo-multisearch' ),
        'section' => 'combo_search_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'inline_autosuggest',
        'title' => __( 'Inline Autocomplete', 'pdb-combo-multisearch' ),
        'type' => 'checkbox',
        'default' => '0',
        'help' => __( 'If checked, search term suggestions will be generated inline from the list shortcode. This may be needed if you are using the list shortcode filter. (This setting may impact performance.)', 'pdb-combo-multisearch' ),
        'section' => 'combo_search_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'combo_field_autocomplete_fields',
        'title' => __( 'Combo Search Autocomplete Fields', 'pdb-combo-multisearch' ),
        'type' => 'textarea',
        'default' => '',
        'help' => __( 'Enter a comma-separated list of field names to index for the combo search autocomplete feature. Leave blank to use all the combo search fields.', 'pdb-combo-multisearch' ),
        'section' => 'combo_search_setting_section',
        'style' => 'width:100%',
            )
    );

    $this->add_setting( array(
        'name' => 'alpha_autocomplete',
        'title' => __( 'First-Letter Autocomplete', 'pdb-combo-multisearch' ),
        'type' => 'checkbox',
        'default' => '1',
        'help' => __( 'If checked, the first letter of the autocomplete suggestions will match the first letter typed, and continue matching each letter typed.', 'pdb-combo-multisearch' ),
        'section' => 'combo_search_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'search_on_click',
        'title' => __( 'Autocomplete Auto-Search', 'pdb-combo-multisearch' ),
        'type' => 'checkbox',
        'default' => '1',
        'help' => __( 'If checked, the search will be performed immediately if the user clicks on an autocomplete item.', 'pdb-combo-multisearch' ),
        'section' => 'combo_search_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'autocomplete_min_length',
        'title' => __( 'Autocomplete Minimum Search Length', 'pdb-combo-multisearch' ),
        'type' => 'number',
        'default' => '1',
        'help' => __( 'Sets the minimum number of characters that need to be typed in to begin showing the autocomplete selector.', 'pdb-combo-multisearch' ),
        'section' => 'combo_search_setting_section',
        'style' => 'width:4em',
        'attributes' => array('min' => '1', 'max' => '9', 'step' => '1'),
            )
    );

    // MULTISEARCH SETTINGS

    $this->add_setting( array(
        'name' => 'alpha_sort',
        'title' => __( 'Alphabetical Value Sorting', 'pdb-combo-multisearch' ),
        'type' => 'checkbox',
        'default' => '1',
        'help' => __( 'If checked, the search selectors will be sorted alphabetically, if not, the sort will be determined by the order of the terms as found or defined.', 'pdb-combo-multisearch' ),
        'section' => 'multi_search_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'multi_whole_word_match',
        'title' => __( 'Whole Word Match Only', 'pdb-combo-multisearch' ),
        'type' => 'checkbox',
        'default' => '0',
        'help' => __( 'If checked, only whole words matching the search term will be included in the result.', 'pdb-combo-multisearch' ),
        'section' => 'multi_search_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'use_default',
        'title' => __( 'Use Field Default Value', 'pdb-combo-multisearch' ),
        'type' => 'checkbox',
        'default' => '1',
        'help' => __( 'If checked, the default value in the field definition will be used in the multisearch control input. Individual field defaults can be set using the "default" attribute.', 'pdb-combo-multisearch' ),
        'section' => 'multi_search_setting_section',
            )
    );
  }

  /**
   * resets the term list transient whenever the settings are saved
   * 
   * @param type $value
   * @param type $prev_value
   */
  function setting_callback_for_combo_field_autocomplete_fields( $value, $prev_value )
  {
    Search\Autosuggest::clear_cache();
    return $value;
  }

  /**
   * resets the term list transient whenever the settings are saved
   * 
   * @param type $value
   * @param type $prev_value
   */
  function setting_callback_for_multi_min_term_length( $value, $prev_value )
  {
    return intval( $value ) < 1 ? '1' : $value;
  }

  /**
   * resets the term list transient whenever the settings are saved
   * 
   * @param type $value
   * @param type $prev_value
   */
  function setting_callback_for_combo_field_list( $value, $prev_value )
  {
    return $value;
  }

  /**
   * renders a section heading
   * 
   * @param array $section information about the section
   */
  function setting_section_callback_function( $section )
  {
    printf( '<a name="%s"></a>', $section['id'] );
    switch ( $section['id'] ) :
      case 'general_setting_section':
        break;
      case 'combo_search_setting_section':
        ?>
        <p><?php _e( 'Combination Search is a single search input that searches on multiple columns for a match. You must tell the plugin which fields the Combo Search control will search in using the "Combination Search Fields" setting. If you leave this setting blank, the Combo Search control will not be shown.', 'pdb-combo-multisearch' ) ?></p>
        <p><?php _e( 'The search text will be used in one of three ways: "ALL Words," meaning all of the words must be present in any one field in order for the record to be shown. "ANY words," meaning at least one of the words must be found in the field. "Exact Phrase" means the whole contents of the search must be found as entered. Note that all three of these modes are the same if only one word is entered.', 'pdb-combo-multisearch' ) ?></p>
        <p><?php _e( 'This chart will help illustrate how the two settings that affect Combo Searches work. The "result level" scale goes from the strictest result set "1" to the loosest "4". The current setting is highlighted.', 'pdb-combo-multisearch' ) ?></p>
        <style>.filter-chart th, .filter-chart td {
            padding: 3px 7px;
            text-align: left;
            vertical-align: middle;
          }
          .rlevel<?php echo $this->result_level() ?> {
            background-color: #E0EDF9;
          }
        </style>
        <table class="filter-chart">
          <tr>
            <th><?php _e( 'Result Level', 'pdb-combo-multisearch' ) ?></th><th><?php _e( 'Global Filter Mode', 'pdb-combo-multisearch' ) ?></th><th><?php _e( 'Search Modifier', 'pdb-combo-multisearch' ) ?></th><th><?php _e( 'search result will be:', 'pdb-combo-multisearch' ) ?></th>
          </tr>
          <tr class="rlevel1">
            <td>1</td><td><?php _e( 'ON', 'pdb-combo-multisearch' ) ?></td><td><?php _e( 'ALL', 'pdb-combo-multisearch' ) ?></td><td><?php _e( 'all of the words in the search must be found in each combo field.', 'pdb-combo-multisearch' ) ?></td>
          </tr>
          <tr class="rlevel2">
            <td>2</td><td><?php _e( 'OFF', 'pdb-combo-multisearch' ) ?></td><td><?php _e( 'ALL', 'pdb-combo-multisearch' ) ?></td><td><?php _e( 'all of the words in the search must be found among the combo fields.', 'pdb-combo-multisearch' ) ?></td>
          </tr>
          <tr class="rlevel3">
            <td>3</td><td><?php _e( 'ON', 'pdb-combo-multisearch' ) ?></td><td><?php _e( 'ANY', 'pdb-combo-multisearch' ) ?></td><td><?php _e( 'at least one of the words in the search must be found in each combo field.', 'pdb-combo-multisearch' ) ?></td>
          </tr>
          <tr  class="rlevel4">
            <td>4</td><td><?php _e( 'OFF', 'pdb-combo-multisearch' ) ?></td><td><?php _e( 'ANY', 'pdb-combo-multisearch' ) ?></td><td><?php _e( 'at least one of the words in the search must be found in at least one of the combo fields.', 'pdb-combo-multisearch' ) ?></td>
          </tr>
        </table>
        <?php
        break;
      case 'multi_search_setting_section':
        ?>
        <p><?php _e( 'For the fields named in the "Multi Search Fields" setting, each type of field will use a control that is appropriate to that field: text fields use a text input, radio fields use a radio input, dropdown fields are filtered with a dropdown, and so forth. Only those fields named in the list of Multi Search fields will have a search control. If "Filter Mode" is ON <em>all</em> search parameters must match for a record to be shown, if filter mode is OFF, records which match <em>any</em> search field will be shown.', 'pdb-combo-multisearch' ) ?></p>
        <?php
        break;
    endswitch;
  }

  /**
   * supplies the current result level according to the settings
   * 
   * @return int the level number
   */
  public function result_level()
  {
    $filter = $this->plugin_option( 'filter_mode', 1 );
    $modifier = $this->plugin_option( 'default_combo_search_modifier', 'any' );
    $mod = $modifier === 'any' ? 4 : 2;
    return $mod - $filter;
  }

  /**
   * renders the plugin settings page
   */
  function render_settings_page()
  {
    ?>
    <div class="wrap pdb-aux-settings-tabs participants_db" style="max-width:670px;">

      <div id="icon-plugins" class="icon32"></div>  
      <h2><?php echo \Participants_Db::$plugin_title . ' ' . $this->aux_plugin_title ?> <?php _e( 'Setup', 'pdb-combo-multisearch' ) ?></h2>
          <?php settings_errors(); ?>
      <p><?php _e( 'The Combo Multi Search Plugin provides multiple-mode search capability to Participants Database.', 'pdb-combo-multisearch' ) ?></p>
      <p><?php _e( 'To use this plugin, you must enable the plugin template in your list shortcode like this:', 'pdb-combo-multisearch' ) ?></p>
      <pre>[pdb_list template=multisearch]</pre>
      <p><?php _e( 'You can combine this with any other shortcode attributes you want to use. For custom templates, see note below.', 'pdb-combo-multisearch' ) ?></p>
      <h4><?php _e( 'Multi Search', 'pdb-combo-multisearch' ) ?></h4>
      <p><?php _e( 'Provides a single control for each field named in the "Multi Search Fields" setting.', 'pdb-combo-multisearch' ) ?></p>
      <h4><?php _e( 'Combination Search', 'pdb-combo-multisearch' ) ?></h4>
      <p><?php _e( 'Combination (or Combo) Search is a single text search field that is applied to several fields. The text entered into this field will be searched for in all of the fields named in the "Combo Search Fields" setting.', 'pdb-combo-multisearch' ) ?></p>
      <p><?php _e( 'Either search type may be disabled by leaving the search fields setting blank.', 'pdb-combo-multisearch' ) ?></p>
      <div class="ui-tabs">
        <form method="post" action="options.php">
    <?php settings_fields( $this->aux_plugin_name . '_settings' ); ?>
    <?php $this->print_settings_tab_control() ?>
    <?php do_settings_sections( $this->aux_plugin_name ); ?>
    <?php submit_button(); ?>
        </form>
    <?php \pdbcms\multifields\tab::display() ?>
      </div>
      <h2><?php _e( 'Custom Templates', 'pdb-combo-multisearch' ) ?></h2>
      <p><?php _e( '<strong>This plugin requires a special template.</strong> There are templates for both the list and search shortcodes. To customize the template, use one of the default templates provided by this plugin as a starting point.', 'pdb-combo-multisearch' ) ?></p>
      <p><?php _e( 'If you are already using a custom template, compare your custom template with the template provided by the plugin. You will need to set up the search control on your custom template the same way it is set up in the Multisearch template.', 'pdb-combo-multisearch' ) ?></p>
    </div><!-- /.wrap -->  
    <aside class="attribution"><?php echo $this->attribution ?></aside>
      <?php
    }

    /**
     * renders a tabs control for a tabbed interface
     * 
     */
    protected function print_settings_tab_control()
    {
      $tabs = array_merge( $this->settings_sections, array(array('slug' => 'multi-search-fields-selector-ui', 'title' => __( 'Multi Search Fields', 'pdb-combo-multisearch' ))) );
      ?>
    <ul class="ui-tabs-nav">
    <?php
    foreach ( $tabs as $section )
      printf( '<li><a href="#%s">%s</a></li>', \Participants_Db::make_anchor( $section['slug'] ), $section['title'] );
    ?>
    </ul>
    <?php
  }

  /**
   * provides a plugin option value or default if no option set
   * 
   * uses a filter of the form pdbcms-{$option_name}
   * 
   * @param string $option_name
   * @param mixed $default
   * 
   * @return mixed
   */
  public function plugin_option( $option_name, $default = false )
  {
    return apply_filters( $this->aux_plugin_shortname . '-' . $option_name, isset( $this->plugin_options[$option_name] ) ? $this->plugin_options[$option_name] : $default );
  }

  /**
   * provides the online script for the combo field selector
   * 
   * @return string
   */
  public function inline_comboselect_script()
  {
    ob_start();
    ?>
    <script>
      jQuery(function ($) {
        $('#<?php echo self::comboselect ?>').multiSelect({
          selectableHeader : '<span class="multi-column-header" ><?php echo $this->i18n['available fields'] ?></span>',
          selectionHeader : '<span class="multi-column-header" ><?php echo $this->i18n['combo fields'] ?></span>'
        });
      });
    </script>
    <?php
    return str_replace( array('<script>', '</script>'), '', ob_get_clean() );
  }

  /**
   * builds a dropdown setting element
   * 
   * @param array $values array of setting values
   * 
   * @return string HTML
   */
  protected function _build_combofield( $values )
  {
    $selectstring = $this->set_selectstring( 'multiselect' );
    $html = array();
    $option_pattern = "\n" . '<option value="%4$s" %9$s %10$s ><span>%5$s</span></option>';

    $html[] = "\n" . '<div class="dropdown-group ' . $values[1] . ' ' . $values[4] . '" ><select name="' . $this->settings_name() . '[' . $values[0] . '][]" multiple ' . $values[9] . ' >';

    $in_optgroup = false;

    foreach ( $values[7] as $field )
    {

      if ( $field->type === 'optgroup' )
      {

        if ( $in_optgroup )
        {
          $html[] = '</optgroup>';
          $in_optgroup = false;
        }

        if ( !$in_optgroup )
        {
          $html[] = '<optgroup label="' . esc_attr( $field->title ) . '">';
          $in_optgroup = true;
        }
      } else
      {
        $values[8] = in_array( $field->name, $this->convert_list_to_array( $values[2] ) ) ? $selectstring : '';
        $values[3] = esc_attr( $field->name );
        $values[4] = $field->title;
        $values[9] = $field->attribute;
        $html[] = vsprintf( $option_pattern, $values );
      }
    }

    if ( $in_optgroup )
    {
      $html[] = '</optgroup>';
      $in_optgroup = false;
    }

    $html[] = '</select>';
    $html[] = '</div>';

    if ( !empty( $values[6] ) )
    {
      $html[] = "\n" . '<p class="description">' . $values[6] . '</p>';
    }

    return implode( PHP_EOL, $html );
  }

  /**
   * makes an array out of a comma-separated string
   * 
   * @param string $list
   * @return array
   */
  private function convert_list_to_array( $list )
  {
    if ( is_array( $list ) )
    {
      return $list;
    }

    return explode( ',', str_replace( ' ', '', $list ) );
  }

  /**
   * settings filter for the filter_mode setting
   * 
   * @param bool  $mode default setting
   * @return bool the current setting
   */
  public function filter_mode( $mode = false )
  {
    return $this->plugin_option( 'filter_mode', $mode ) == '1';
  }

  /**
   * checks for a version change and takes actions accordingly
   */
  private function check_version()
  {
    $current_version = get_option( $this->aux_plugin_shortname . '-version', '1.4' );

//    if ( version_compare( '2.0', $current_version ) === 1 ) {
//
//      if ( $this->updating_plugin() ) {
//        \PDb_Admin_Notices::post_admin_notice( sprintf( __( "Combo Multisearch has a new Multi Fields configuration interface. Your multi field settings should work the same as they did before, but it's recommended you test it. Visit this page to learn about the new interface: %sConfiguring the Multi Search.%s", 'pdb-combo-multisearch' ), '<a href="https://xnau.com/product_support/participants-database-combo-multisearch/#configuring-the-multi-search" target="_blank">', '</a>' ), array(
//            'context' => $this->aux_plugin_title,
//            'persistent' => true,
//        ) );
//      }
//    }
  }

  /**
   * checks if the plugin was previously installed
   * 
   * @return bool true if the plugin was previously installed
   */
  private function updating_plugin()
  {
    return get_option( $this->settings_name() ) !== false;
  }

  /**
   * handles uninstalling the plugin
   */
  public static function uninstall()
  {
    delete_option( 'pdbcms_settings' );
    delete_option( 'pdbcms_multifields' );
    Search\Autosuggest::clean_up();
  }

}
