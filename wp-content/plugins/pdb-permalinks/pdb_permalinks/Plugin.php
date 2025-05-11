<?php
/**
 * main class for handling using a permalink to get a pdb record
 * 
 * @category   Plugins
 * @package    WordPress
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2016 7th Veil, LLC
 * @license    http://www.gnu.org/licenses/gpl-2.0.txt GPL2
 * @version    Release: 0.5
 * @subpackage Participants Database 1.6
 */

namespace pdb_permalinks;

class Plugin extends \PDb_Aux_Plugin {

// plugin slug
  var $aux_plugin_name = 'pdb-permalinks';
// shortname for the plugin
  var $aux_plugin_shortname = 'pdbpermalinks';

  /**
   * @var string name of the custom query var
   */
  private $query_var = 'pdb-record-slug';

  /**
   * name of the "flush required" transient
   */
  const must_flush = 'pdb-transient_must_flush';

  /**
   * 
   * @param string $plugin_file
   */
  public function __construct( $plugin_file )
  {
    $this->plugin_data += array(
        'SupportURI' => 'https://xnau.com/product_support/participants-database-pretty-permalinks/',
    );
    parent::__construct( __CLASS__, $plugin_file );

    register_activation_hook( $plugin_file, array('pdb_permalinks\database\Init', 'activate') );
    register_uninstall_hook( $plugin_file, array('pdb_permalinks\database\Init', 'uninstall') );
    register_deactivation_hook( $plugin_file, array('pdb_permalinks\database\Init', 'deactivate') );

    // set up slug management
    new Manage_Slugs();

    // we need to do this here to get the option value at this point
    $this->plugin_options = get_option( $this->settings_name() );
    
    // set up the permalinks
    new destinations\Single();
    if ( isset( $this->plugin_options['pid_permalink_enable'] ) && $this->plugin_options['pid_permalink_enable'] ) {
      new destinations\Record();
      new destinations\Record_Payment();
    }

    add_action( 'plugins_loaded', array($this, 'initialize') );

    $this->settings_filters();
    
    add_action( 'admin_enqueue_scripts', function( $hook ) {
      wp_enqueue_script( 'pdb-permalinks-admin-notices', plugins_url( 'pdb_permalinks_admin.js', __FILE__ ), array('jquery') );
    } );
    
    add_action( 'wp_ajax_pdb-permalinks-process_notice_dismiss', function () {
      delete_transient(database\process::process_tally);
      wp_die();
    });
    
    add_action('admin_notices', array( $this, 'admin_feedback_notices' ) );
  }

  /**
   * called on the 'plugins_loaded' hook
   */
  public function initialize()
  {
    parent::set_plugin_options();
    $this->aux_plugin_title = __( 'Permalinks', 'pdb_permalinks' );

    add_filter( 'pdb-permalinks_query_var_name', array($this, 'query_var') );
    
    add_action( 'pdb_permalinks_update_complete', array( __CLASS__, 'clear_slug_cache' ) );
  }

  /**
   * provides the name of the custom query var
   * 
   * @return string
   */
  public function query_var()
  {
    return apply_filters( 'pdb-permalinks_query_var', $this->query_var );
  }

  /**
   * sets the update process feedback message
   */
  public function admin_feedback_notices()
  {
    $tally = get_transient( database\process::process_tally );
    
    if ( $tally === '0' ) {
      ?>
      <div class="notice notice-info pdb-permalinks-process">
        <h4><?php echo \Participants_Db::$plugin_title . ': ' . $this->aux_plugin_title ?></h4>
        <p><?php printf( __( ' processing permalinks in the background...', 'pdb_permalinks' ), $tally ) ?></p>
      </div>
      <?php
    }
    if ( $tally > 0 ) {
      ?>
      <div class="notice notice-success is-dismissible pdb-permalinks-process">
        <h4><?php echo \Participants_Db::$plugin_title . ': ' . $this->aux_plugin_title ?></h4>
        <p><?php printf( __( ' update complete: %d records processed.', 'pdb_permalinks' ), $tally ) ?></p>
      </div>
      <?php
    }
  }
  
  /**
   * clears the slug cache
   */
  public static function clear_slug_cache()
  {
    wp_cache_delete( database\Db::slugs);
  }

  /**
   * SETTINGS API
   */
  function settings_api_init()
  {
    register_setting( $this->aux_plugin_name . '_settings', $this->settings_name() );

// define settings sections
    $sections = array(
        array(
            'title' => __( 'General Settings', 'pdb_permalinks' ),
            'slug' => 'general_setting_section',
        ),
    );
    $this->_add_settings_sections( $sections );

    $this->add_setting( array(
        'name' => 'identifier_column_1',
        'title' => __( 'First Identifier Column', 'pdb_permalinks' ),
        'type' => 'dropdown',
        'options' => $this->id_columns(),
        'default' => 'first_name',
        'help' => __( 'The values of the field is the first one used to generate the record slug.', 'pdb_permalinks' ),
        'style' => 'width:100%',
        'section' => 'general_setting_section',
            )
    );
    $this->add_setting( array(
        'name' => 'identifier_column_2',
        'title' => __( 'Second Identifier Column', 'pdb_permalinks' ),
        'type' => 'dropdown',
        'options' => $this->id_columns( true ),
        'default' => 'last_name',
        'help' => __( 'The values of the field is the second one used to generate the record slug. This is optional.', 'pdb_permalinks' ),
        'style' => 'width:100%',
        'section' => 'general_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'identifier_column_3',
        'title' => __( 'Auxiliary Identifier Column', 'pdb_permalinks' ),
        'type' => 'dropdown',
        'options' => $this->id_columns( true ),
        'default' => 'none',
        'help' => __( 'The value in this column will only be used if using the above field or fields gives a duplicate slug. If "none" a number will be used instead.', 'pdb_permalinks' ),
        'style' => 'width:100%',
        'section' => 'general_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'key',
        'title' => __( 'Record URL Key', 'pdb_permalinks' ),
        'type' => 'text',
        'default' => 'record',
        'help' => __( 'This string is used to identify the URL as one to get a single record. See above for explanation.', 'pdb_permalinks' ),
        'style' => 'width:100%',
        'section' => 'general_setting_section',
            )
    );


    $this->add_setting( array(
        'name' => 'pid_permalink_enable',
        'title' => __( 'Enable Permalinks on Record Edit URLs', 'pdb_permalinks' ),
        'type' => 'checkbox',
        'default' => '0',
        'help' => __( 'Check this to use pretty permalinks with your private record edit links', 'pdb_permalinks' ),
        'section' => 'general_setting_section',
            )
    );

    $this->add_setting( array(
        'name' => 'pid_key',
        'title' => __( 'Record Edit URL Key', 'pdb_permalinks' ),
        'type' => 'text',
        'default' => 'edit',
        'help' => __( 'This string is used to identify the URL as one to open a record edit page.', 'pdb_permalinks' ),
        'style' => 'width:100%',
        'section' => 'general_setting_section',
            )
    );
  }

  /**
   * settings filters
   */
  private function settings_filters()
  {
    $settings = $this;
    add_filter( 'pdb-permalinks_key', function () use ( $settings ) {
      return $settings->plugin_option( 'key' );
    } );
    add_filter( 'pdb-permalinks_pid_key', function () use ( $settings ) {
      return $settings->plugin_option( 'pid_key' );
    } );
  }

  /**
   * renders the plugin settings page
   */
  function render_settings_page()
  {
    ?>
    <div class="wrap" style="max-width:670px;">

      <div id="icon-plugins" class="icon32"></div>  
      <h2><?php echo \Participants_Db::$plugin_title . ' ' . $this->aux_plugin_title ?> Setup</h2>
      <?php settings_errors(); ?>

      <p><?php _e( 'This plugin works very much like WordPress permalinks: each record in Participants Database is given a unique "slug" which identifies the record with a descriptive human-readible string. The slug gets it\'s value from the fields defined in the identifier column settings.', 'pdb_permalinks' ) ?></p>
      <p><?php _e( 'This slug is then used along with the "Record URL Key" to create the URL for accessing the record. For example, if the key word was "member" the the identifier columns were "first_name" and "last_name," a record for the name "John Smith" would be reached at: member/john-smith.', 'pdb_permalinks' ) ?></p>
      <p><?php _e( 'The "Auxiliary Identifier Column" is used if the first two columns don\'t result in a unique slug.', 'pdb_permalinks' ) ?></p>
      <h4><?php printf( __( 'Important: The %sWordPress Permalinks settings%s must be refreshed after any change here that changes the structure of the Participants Database permalink.', 'pdb_permalinks' ), '<a href="' . admin_url('options-permalink.php') . '">', '</a>' ) ?></h4>
      <p><?php _e( 'To refresh permalinks, go to the WordPress settings/permalinks page and save the permalinks.', 'pdb_permalinks' ) ?></p>
      <p><?php printf( __( 'Pretty permalinks can also be used with record edit links, so that your record edit link could look something like this: %s This is enabled in the settings below, you can also set the keyword to use.', 'pdb_permalinks' ), get_bloginfo( 'url' ) . '/edit/AX3R1' ) ?></p>
      <?php if ( $this->plain_permalinks_is_set() ) : ?>
      
      <h3 class="notice notice-error" style="padding:5px 10px"><span class="dashicons dashicons-warning" style="color:hsl(0, 70.8%, 52.9%)"></span><?php  _e( 'Your WordPress Permalinks are currently set to "Plain." You must have site-wide pretty permalinks enabled for this add-on to work.', 'pdb_permalinks' ) ?></h3>
      
      <?php endif ?>

      <form method="post" action="options.php">
        <?php
        settings_fields( $this->aux_plugin_name . '_settings' );
        do_settings_sections( $this->aux_plugin_name );
        submit_button();
        ?>
      </form>
      <h3><?php _e( 'Update All Record Slugs', 'pdb_permalinks' ) ?></h3>
      <p><?php _e( 'This provides all records with a "slug" based on the identifier columns settings above. Unless you change the above settings and want to re-build all the slugs, you only need to do this once when you first set up the plugin. Note: this may take a long time with very large databases.', 'pdb_permalinks' ) ?></p>
      <form method="post" action="<?php echo admin_url( 'admin-post.php' ) ?>">
        <input type="hidden" name="action" value="update_all_slugs" />
        <input type="hidden" name="return" value="<?php echo basename( $_SERVER['REQUEST_URI'] ) ?>" />
        <?php submit_button( __( 'Update All Slugs', 'pdb_permalinks' ), 'secondary' ) ?>
      </form>
    </div><!-- /.wrap -->  
    <aside class="attribution"><?php echo $this->attribution ?></aside>
    <?php
  }

  /**
   * refreshes the permalinks if the stting has changed
   * 
   * @param mixed $new_value
   * @param mixed $old_value
   * @return mixed
   */
  public function refresh_permalinks( $new_value, $old_value )
  {
    /*
     * if this value is changed, schedule a flush on the next refresh
     */
    if ( $new_value !== $old_value ) {
      set_site_transient( self::must_flush, true );
    }
    return $new_value;
  }
  
  /**
   * checks to see if plain permalinks are set on this site
   * 
   * @return bool true if plain permalinks are set
   */
  public function plain_permalinks_is_set()
  {
    $setting = get_option('permalink_structure', '' );
    
    // if the setting is empty, permalinks are disabled
    return empty( $setting );
  }

  /**
   * handles changing the key setting
   * 
   * @param mixed $new_value
   * @param mixed $old_value
   * @return mixed
   */
  public function setting_callback_for_key( $new_value, $old_value )
  {
    return $this->refresh_permalinks( $new_value, $old_value );
  }

  /**
   * handles changing the single record page setting
   * 
   * @param mixed $new_value
   * @param mixed $old_value
   * @return mixed
   */
  public function setting_callback_for_single_record_page( $new_value, $old_value )
  {
    return $this->refresh_permalinks( $new_value, $old_value );
  }

  /**
   * provides a list of pages in the site
   * 
   * @return array
   */
  private function _get_pagelist()
  {

    $key = 'meetmecr_pagelist';

    $pagelist = wp_cache_get( $key, 'get_pagelist' );

    if ( $pagelist === false ) {

      $pagelist['null_select'] = '';

      $pages = wp_cache_get( 'pagelist_posts' );
      if ( $pages === false ) {
        $pages = get_posts( array('post_type' => 'page', 'posts_per_page' => -1) );
        wp_cache_set( 'pagelist_posts', $pages );
      }

      foreach ( $pages as $page ) {
        $pagelist[$page->ID] = $page->post_title;
      }
      wp_cache_set( $key, $pagelist, 'get_pagelist' );
    }

    return $pagelist;
  }

  /**
   * supplies a list of database columns to select
   * 
   * @param bool  $null if true, include a blank selection
   * @retrun array
   */
  private function id_columns( $null = false )
  {
    $columns = \PDb_Settings::_get_identifier_columns( true );

    $columns = array_flip( array_filter( $columns ) );
    return $null ? array('' => __( 'none', 'pdb-permalinks' )) + $columns : $columns;
  }

}
