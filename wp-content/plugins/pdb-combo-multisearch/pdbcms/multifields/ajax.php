<?php

/**
 * handles ajax submissions
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

namespace pdbcms\multifields;

class ajax {

  /**
   * handles the ajax submission
   */
  public function submission()
  {
    if ( !check_admin_referer( filter_input( INPUT_POST, 'action', FILTER_DEFAULT, \Participants_Db::string_sanitize() ), updates::nonce ) ) {
      return;
    }

    $task = filter_input( INPUT_POST, 'task', FILTER_DEFAULT, \Participants_Db::string_sanitize() );

    if ( method_exists( $this, $task ) ) {
      $this->{$task}();
    }
  }

  /**
   * adds a field to the multi field search interface
   */
  private function add_field()
  {
    $fieldname = filter_input( INPUT_POST, 'fieldname', FILTER_DEFAULT, \Participants_Db::string_sanitize() );

    $field_store = field_store::getInstance();

    $search_field = $field_store->add_field( $fieldname );

    wp_send_json( array(
        'html' => field_editor::field_editor_html( $search_field ),
        'fieldname' => $fieldname,
    ) );
  }

  /**
   * deletes a field from the multi field search interface
   */
  private function delete_field()
  {
    $fieldname = filter_input( INPUT_POST, 'fieldname', FILTER_DEFAULT, \Participants_Db::string_sanitize() );

    $field_store = field_store::getInstance();

    $success = $field_store->delete_field( $fieldname );

    if ( $success ) {

      $field = new \PDb_Form_Field_Def( $fieldname );
      
      $html = sprintf( '<option value="%1$s">%2$s (%1$s)</option>', $fieldname, $field->title() );

      wp_send_json( array(
          'status' => 'success',
          'fieldname' => $fieldname,
          'group' => $field->group(),
          'option' => $html,
      ) );
    }

    wp_send_json( array(
        'status' => false,
    ) );
  }
  
  /**
   * changes the order of the multi search fields
   */
  private function reorder_fields()
  {
    parse_str( filter_input(INPUT_POST, 'list', FILTER_DEFAULT, \Participants_Db::string_sanitize() ), $list );
    
    $field_store = field_store::getInstance();
    
    /** @var field_store $field_store */
    
    $field_store->reorder_fields($list);
  }

}
