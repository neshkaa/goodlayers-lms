<?php

/**
 * provides permalinks access to the pdb_record_member_payment shortcode
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2020  xnau webdesign
 * @license    GPL3
 * @version    0.1
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdb_permalinks\destinations;

class Record_Payment extends Record {
  
  /**
   * @var string name of the shortcode
   */
  protected $shortcode = 'pdb_record_member_payment';
  
}
