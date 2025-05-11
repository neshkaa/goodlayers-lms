<?php 

/*
 * handles the plugin assets
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2015  xnau webdesign
 * @license    GPL2
 * @version    1.0
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    pdbcms\Plugin
 */

namespace pdbcms;
use \Participants_Db;

class Assets {
  /**
   * @var object the main plugin object 
   */
  private $plugin;
  
  /**
   * @var string minified asset pre-extension
   */
  private $min;
  
  /**
   * 
   */
  function __construct( Plugin $plugin )
  {
    $this->plugin = $plugin;
    $this->min = Participants_Db::use_minified_assets() ? '.min' : '';
    
    add_action( 'pdb-shortcode_present', array( $this, 'frontend_enqueues' ) );
    
    add_action( 'pdbcms_enable', array( $this, 'enable' ) );
    
    add_action( 'admin_enqueue_scripts', array( $this, 'admin_includes' ), 30 );
  }
  
  /**
   * enqueues the frontend assets
   */
  public function frontend_enqueues()
  {
    add_action( 'wp_enqueue_scripts', array( $this, 'enqueues' ), 5 );
  }

  /**
   * enqueues scripts and CSS
   * 
   */
  public function enqueues()
  { 
    wp_register_script( 'pdb-cookie', plugins_url( 'js/jquery_cookie.js', $this->plugin->plugin_path ) );
    if ( ! wp_script_is( 'pdb-formsaver' ) ) {
      wp_register_script( 'pdb-formsaver', plugins_url( "js/jquery_formsaver$this->min.js", $this->plugin->plugin_path ), array('jquery'), '0.15' );
    }

    wp_register_style( 'jq-ui-autocomplete', plugins_url( 'css/jquery.ui.autocomplete.css', $this->plugin->plugin_path ) );
    wp_register_style( 'pdb-combo-multisearch-style', plugins_url( 'css/combo-multisearch.css', $this->plugin->plugin_path ), array( 'jq-ui-autocomplete' ), '1.2' );
    
    wp_register_script( 'pdb-combo-multisearch', plugins_url( "js/combo-multisearch$this->min.js", $this->plugin->plugin_path ), array('jquery-ui-autocomplete','pdb-list-filter'), '1.6', true );

    $values = array(
        'auto' => $this->plugin->plugin_option('autocomplete', '1'),
        'alpha_auto' => $this->plugin->plugin_option('alpha_autocomplete', '1'),
        'auto_search' => $this->plugin->plugin_option('search_on_click', '1'),
        'restore_search' => $this->plugin->plugin_option('restore_last_search', '1'),
        'remote_search' => $this->is_remote_search(), // true if data from a remote search exists
        'cache_check' => Plugin::cache_test_cookie,
        'autocomplete_terms' => $this->plugin->term_list,
        'require_all' => $this->plugin->plugin_option( 'multi_all_required', '0' ),
        'min_term_length' => $this->plugin->plugin_option( 'multi_min_term_length', '1' ) ? : '1',
        'autocomplete_min_length' => $this->plugin->plugin_option( 'autocomplete_min_length', '1' ) ? : '1',
    );
    
    wp_add_inline_script('pdb-combo-multisearch', Participants_Db::inline_js_data('PDbCMS', $values) );
  }
  
  /**
   * enqueues the admin side JS
   * 
   * @param string $hook the admin menu hook as provided by the WP filter
   */
  public function admin_includes( $hook )
  {
    if ( strpos( $hook, 'pdb-combo-multisearch_settings') !== false ) {
      wp_deregister_script(Participants_Db::$prefix . 'aux_plugin_settings_tabs');
      wp_register_script( Participants_Db::$prefix . 'aux_plugin_settings_tabs', plugins_url( "js/aux_plugin_settings$this->min.js", $this->plugin->plugin_path ), array( 'jquery-ui-tabs' ) );
      
      wp_register_script('pdbcms-multifields', plugins_url( "js/multifields$this->min.js", $this->plugin->plugin_path ), array(), '1.5' );
      wp_add_inline_script('pdbcms-multifields', Participants_Db::inline_js_data('PDbCMS', array(
          multifields\updates::nonce => wp_create_nonce(multifields\updates::action_key),
          'action' => multifields\updates::action_key,
          'delete_confirm' => _x('Remove the %s field?', 'substitution is the name of the field', 'pdb-combo-multisearch' ),
      )));
      
      wp_register_script( 'jquery-multiselect', plugins_url( "js/jquery.multi-select$this->min.js", $this->plugin->plugin_path ) );
      wp_add_inline_script('jquery-multiselect', $this->plugin->inline_comboselect_script() );
      
      wp_enqueue_script('jquery-multiselect');
      wp_enqueue_script('pdbcms-multifields');
      
      wp_enqueue_style('jquery-multiselect', plugins_url( "css/multi-select.css", $this->plugin->plugin_path ), array(), '1.6' );
      wp_enqueue_style('pdbcms-multifields', plugins_url( "css/multifields.css", $this->plugin->plugin_path ) );
    }
  }

  /**
   * enqueue the scripts
   * 
   * should be fired by the template to avoid loading javascript uneccesarily
   * 
   * 
   */
  public function enable()
  {
    if ( ! wp_script_is( 'pdb-combo-multisearch', 'registered' ) )
    {
      \Participants_Db::debug_log( __METHOD__.' re-enqueueing scripts', 2 );
      $this->enqueues();
    }
    
    wp_deregister_script( 'pdb-list-filter' );
    wp_register_script( 'pdb-list-filter', plugins_url( "js/multisearch-filter$this->min.js", $this->plugin->plugin_path ), array('jquery','pdb-formsaver'), '2.10', true );

    global $wp_query;

    $ajax_params = array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'postID' => ( isset( $wp_query->post ) ? $wp_query->post->ID : '' ),
        'prefix' => Participants_Db::$prefix,
        'loading_indicator' => Participants_Db::get_loading_spinner(),
    );

    wp_add_inline_script( 'pdb-list-filter', Participants_Db::inline_js_data('PDb_ajax', $ajax_params) );
    
    wp_enqueue_style('pdb-combo-multisearch-style');
    
    wp_enqueue_script('pdb-combo-multisearch');
  }
  
  /**
   * tells if the current page load is from a remote search
   * 
   * @return bool
   */
  private function is_remote_search()
  {
    return filter_input( INPUT_POST, 'subsource', FILTER_SANITIZE_SPECIAL_CHARS ) === 'pdb-multi-searchform' || ( Participants_Db::is_column( filter_input( INPUT_GET, 'search_field', FILTER_SANITIZE_SPECIAL_CHARS ) ) && strlen( filter_input( INPUT_GET, 'value', FILTER_SANITIZE_SPECIAL_CHARS ) ) > 0 );
  }
}