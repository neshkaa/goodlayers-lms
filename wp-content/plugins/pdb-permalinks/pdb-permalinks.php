<?php

/*
 * Plugin Name: Participants Database Permalinks
 * Version: 1.9.3
 * Description: Use human-readable and SEO-friendly permalinks for Participants Database records
 * Author: Roland Barker, xnau webdesign
 * Plugin URI: https://xnau.com/shop/pdb-permalinks/
 * Text Domain: pdb-permalinks
 * Domain Path: /languages
 * 
 */
spl_autoload_register( 'pdb_permalinks_autoload' );
if ( class_exists( 'Participants_Db' ) ) {
  pdb_permalinks_initialize();
} else {
  add_action( 'participants-database_activated', 'pdb_permalinks_initialize' );
}

function pdb_permalinks_initialize()
{
  global $PDb_Permalinks;
  if ( !is_object( $PDb_Permalinks ) && version_compare( Participants_Db::$plugin_version, '1.6.2.8', '>' ) ) {
    $PDb_Permalinks = new \pdb_permalinks\Plugin( __FILE__ );
  }
}

/**
 * namespace-aware autoload
 * 
 * @param string $class
 */
function pdb_permalinks_autoload( $class )
{

  $file = ltrim( str_replace( '\\', '/', $class ), '/' ) . '.php';

  if ( !class_exists( $class ) && is_file( trailingslashit( plugin_dir_path( __FILE__ ) ) . $file ) ) {
    include $file;
  }
}
