<?php
/*
 * handles slug creation and modification
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

class Manage_Slugs {
  
  /**
   * @var \pdb_permalinks\database\process instance
   */
  public $processor;
  
  /**
   * 
   */
  public function __construct()
  {
    add_action( 'pdb-after_submit_signup', array($this, 'update_slug') );
    add_action( 'pdb-after_submit_update', array($this, 'update_slug') );
    add_action( 'pdb-after_submit_add', array($this, 'update_slug') );
    add_filter( 'pdb-before_submit_add', array($this, 'handle_blank_slug') );
    /*
     * this is called when the "update all record slugs" button is pressed on the plugin settings page
     */
    add_action( 'admin_post_update_all_slugs', array($this, 'update_all_slugs') );
    // this is called when a record is getting imported via CSV
    add_filter( 'pdb-before_csv_store_record', array($this, 'handle_blank_slug') );
    add_filter( 'pdb-before_submit_update', array($this, 'handle_blank_slug') );

    add_action( 'pdb_permalinks_update_complete', function ( $tally ) {
      add_action( 'admin_notices', function () use ( $tally ) {
        ?>
        <div class="notice notice-success is-dismissible">
          <p><?php printf( __( '%s s% records have been updated with their permalink slugs.', 'pdb_permalinks' ), $tally, \Participants_Db::$plugin_title ); ?></p>
        </div>
        <?php
      } );
    } );
    
    $this->include_wpap();
    
    $this->processor = new database\process();
  }

  /**
   * fills in or updates all the slugs
   */
  public function update_all_slugs()
  {
    status_header( 200 );
    database\Db::populate_all_slugs($this->processor);
    wp_redirect( admin_url( filter_input( INPUT_POST, 'return', FILTER_SANITIZE_STRING ) ) );
    exit;
  }

  /**
   * updates a record slug
   * 
   * @param array $post the submitted data
   */
  public function update_slug( $post )
  {
    database\Db::update_slug( $post );
  }

  /**
   * removes the slug from the post array if it is empty
   * 
   * needed becuase if an empty string is saved it will match another empty string 
   * in the unique column, causing an error and aborting the record save
   * 
   * @param array $post the post data array
   * @return array $post
   */
  public function handle_blank_slug( $post )
  {
    if ( isset( $post[database\Db::slug_field] ) && strlen( $post[database\Db::slug_field] ) === 0 ) {
      unset( $post[database\Db::slug_field] );
    }
    return $post;
  }
  
  /**
   * include the background process scripts
   * 
   * this is for backward compatibility, as of PDB 1.9.7 the library is autoloaded
   */
  private function include_wpap()
  {
    if ( is_readable( \Participants_Db::$plugin_path . '/vendor/wp-background-process/wp-async-request.php' ) ) {
      require_once \Participants_Db::$plugin_path . '/vendor/wp-background-process/wp-async-request.php';
      require_once \Participants_Db::$plugin_path . '/vendor/wp-background-process/wp-background-process.php';
    }
  }

}
