<?php

/*
 * manages the logging database initialization and uninstallation
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2016  xnau webdesign
 * @license    GPL2
 * @version    0.2
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdb_permalinks\database;

class Init {

  /**
   * initializes the database table
   */
  public static function activate()
  {
    /*
     * install the record slug field
     */
    if ( !isset( \Participants_Db::$fields[Db::slug_field] ) ) {
      
      $atts = array(
          'name' => Db::slug_field,
          'title' => __( 'Slug', 'pdb_permalinks' ),
          'group' => 'internal',
          'order' => count( \Participants_Db::$fields ) + 1,
          'validation' => 'no',
      );
      $result = \Participants_Db::add_blank_field( $atts );
      
      if ( ! self::slug_column_is_unique() ) {
        /*
         * here, we convert the slug column to a varchar and make it a unique index
         */
        global $wpdb;
        $sql = 'ALTER TABLE `' . \Participants_Db::$participants_table . '` MODIFY `' . Db::slug_field . '` VARCHAR(255)';
        $wpdb->query( $sql );

        $sql = 'ALTER TABLE `' . \Participants_Db::$participants_table . '` ADD UNIQUE (`' . Db::slug_field . "`) COMMENT 'Participants Database Permalinks';";
        $wpdb->query( $sql );
      }
    }
  }

  /**
   * deactivates the plugin
   */
  public static function deactivate()
  {
    global $wpdb;
    $wpdb->hide_errors();
    $result = $wpdb->query( $wpdb->prepare( '
      DELETE FROM ' . \Participants_Db::$fields_table . '
      WHERE `name` = "%s"', Db::slug_field )
    );
    if ( self::slug_column_is_unique() ) {
      $wpdb->query( 'ALTER TABLE ' . \Participants_Db::$participants_table .  ' DROP INDEX ' . Db::slug_field );
    }
  }

  /**
   * uninstall the tables/options
   */
  public static function uninstall()
  {
    // delete the slug field

    global $wpdb;
    $wpdb->hide_errors();

    $result = $wpdb->query( $wpdb->prepare( '
      DELETE FROM ' . \Participants_Db::$fields_table . '
      WHERE `name` = "%s"', Db::slug_field )
    );
    $result = $wpdb->query( '
      ALTER TABLE ' . \Participants_Db::$participants_table . '
      DROP `' . Db::slug_field . '`' );
  }

  /**
   *  determines if the slug column has had it's indexes setup already
   * 
   * @return bool true if the column has a unique index
   */
  private static function slug_column_is_unique()
  {
    global $wpdb;
    $columns = $wpdb->get_results( 'SHOW COLUMNS FROM ' . \Participants_Db::$participants_table );
    
    foreach ( $columns as $column ) {
      if ( $column->Field === Db::slug_field && $column->Key === 'UNI' ) {
        return true;
      }
    }
    return false;
  }

}
