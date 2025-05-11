<?php

/*
 * Plugin Name: Participants Database Combination Multi Search
 * Version: 2.7.4
 * Description: add multiple-field search, plus text search to Participants Database List Display
 * Author: Roland Barker, xnau webdesign
 * Plugin URI: https://xnau.com/shop/combo-multisearch-plugin/
 * Text Domain: pdb-combo-multisearch
 * Domain Path: /languages
 */

spl_autoload_register('pdb_combo_multisearch_autoload');

if (class_exists('Participants_Db')) {
  pdb_combo_multisearch_initialize();
} else {
  add_action('participants-database_activated', 'pdb_combo_multisearch_initialize');
}

function pdb_combo_multisearch_initialize()
{
  global $PDb_Combo_Multi_Search;
  $PDb_Combo_Multi_Search = new \pdbcms\Plugin(__FILE__);
}

/**
 * namespace-aware autoload
 * 
 * @param string $class
 */
function pdb_combo_multisearch_autoload($class)
{
  $file = ltrim(str_replace('\\', '/', $class), '/') . '.php';
  
  if (!class_exists($class) && is_file(trailingslashit(plugin_dir_path(__FILE__)) . $file)) {
    include $file;
  }
}