<?php

/**
 * handles updating the slugs for the whole database
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2018  xnau webdesign
 * @license    GPL3
 * @version    0.1
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdb_permalinks\database;

class process extends \WP_Background_Process {

  /**
   * @var string
   */
  protected $action = 'pdb_permalinks_process';

  /**
   * @var string name of the transient holding the process tally
   */
  const process_tally = 'pdb_permalinks_process_tally';

  /**
   * @var int record update count
   */
  protected $tally = 0;

  /**
   * Dispatch
   *
   * @access public
   * @return void
   */
  public function dispatch()
  {
    set_transient( self::process_tally, 0 );
    return parent::dispatch();
  }

  /**
   * creates the slug for a record
   * 
   * @param int $record_id
   */
  protected function task( $record_id )
  {
    Db::_update_slug( $record_id );
    $this->tally++;
    return false;
  }

  /**
   * Complete
   *
   * Override if applicable, but ensure that the below actions are
   * performed, or, call parent::complete().
   */
  protected function complete()
  {
    parent::complete();

    set_transient( self::process_tally, $this->tally );

    do_action( 'pdb_permalinks_update_complete', $this->tally );
  }

}
