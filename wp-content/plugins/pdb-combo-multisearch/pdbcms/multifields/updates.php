<?php

/**
 * handles updates to the multifields configuration
 *
 * @package    WordPress
 * @subpackage Participants Database Plugin
 * @author     Roland Barker <webdesign@xnau.com>
 * @copyright  2020  xnau webdesign
 * @license    GPL3
 * @version    0.3
 * @link       http://xnau.com/wordpress-plugins/
 * @depends    
 */

namespace pdbcms\multifields;

class updates {
  
  /**
   * @var string name of the action/nonce
   */
  const action_key = 'pdbcms_multifields';
  
  /**
   * @var string name of the nonce field
   */
  const nonce = '_pdbnonce';
  
  /**
   * processes the multi fields update submission
   */
  public static function process_submission()
  {
    $field_store = field_store::getInstance();
    
    $updates = new self();
    
    foreach( $updates->sanitized_post() as $fieldname => $attributes ) {
      $field_store->update_field($fieldname, $updates->field_config( $fieldname, $attributes ) );
    }
    
    global $PDb_Combo_Multi_Search;
    \PDb_Admin_Notices::post_info( __( 'Multisearch fields have been updated.', 'pdb-combo-multisearch' ), $PDb_Combo_Multi_Search->aux_plugin_title );
    
    $updates->return_to_the_combo_multisearch_settings_page();
  }
  
  /**
   * provides the sanitized post submission
   */
  private function sanitized_post()
  {
    $post = array();
    $field_store = field_store::getInstance();
    
    $filters = field_config::config_fields();
    // remove the name and title values, we don't change those
    unset( $filters['name'], $filters['title'] );
    
    foreach( $_POST as $field => $field_atts ) {
      $fieldname = filter_var($field, FILTER_DEFAULT, \Participants_Db::string_sanitize() );
      if ( $field_store->is_multi_search_field( $fieldname ) ) {
        $post[$fieldname] = filter_var_array( $field_atts, $filters );
      }
    }
    
    return $post;
  }
  
  /**
   * sanitizes a text field that allows some tags
   * 
   * @param string $raw_value
   * @return string sanitized value
   */
  public static function text_sanitize( $raw_value )
  {
    return stripslashes( wp_filter_post_kses( $raw_value ) );
  }
  
  

  /**
   * provides the config object for a field
   * 
   * @param string $fieldname
   * @param array $attributes the submitted values form the UI
   * 
   * @return \stdClass
   */
  protected function field_config( $fieldname, $attributes )
  {
    $field_store = field_store::getInstance();
    $config = $field_store->field_config( $fieldname );
    
    $field = new \PDb_Form_Field_Def($fieldname);
    
    foreach ( array_keys( field_config::config_fields() ) as $att ) {
      
      switch ($att) {
        case 'name':
          $config->{$att} = $field->name();
          break;
        case 'title':
          $config->{$att} = $field->title();
          break;
        case 'attributes':
          $config->{$att} = \PDb_Manage_Fields_Updates::string_notation_to_array( $attributes[$att] );
          break;
        default:
          if ( isset( $attributes[$att] ) ) {
            $config->{$att} = $attributes[$att];
          }
      }
      
    }

    return $config;
  }

  /**
   * redirects back to the manage database fields page after processing the submission
   * 
   * @link https://tommcfarlin.com/wordpress-admin-redirect/
   */
  private function return_to_the_combo_multisearch_settings_page()
  {
    if ( !isset( $_POST['_wp_http_referer'] ) ) {
      $_POST['_wp_http_referer'] = wp_login_url();
    }

    $url = sanitize_text_field(
            wp_unslash( $_POST['_wp_http_referer'] )
    );

    wp_safe_redirect( urldecode( $url ) );

    exit;
  }
}
