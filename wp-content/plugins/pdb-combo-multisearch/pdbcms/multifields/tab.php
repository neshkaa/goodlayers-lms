<?php
/**
 * provides the tab content framework for the multi-fields setup interface
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

class tab {

  /**
   * @var \pdbcms\multifields\field_store instance
   * 
   * these are the field configurations selected for the multi search
   */
  private $field_store;

  /**
   * instantiates the tab content class
   */
  private function __construct()
  {
    $this->field_store = field_store::getInstance();
  }

  /**
   * prints the display html
   * 
   */
  public static function display()
  {
    $tab = new self;

    $tab->content();
  }

  /**
   * prints the tab content
   * 
   */
  public function content()
  {
    ?>
    <div id="multi-search-fields-selector-ui" class="ui-tabs-panel">
      <h2><?php _e( 'Multi Search Fields', 'pdb-combo-multisearch' ) ?></h2>
      <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
        <?php wp_nonce_field( updates::action_key, updates::nonce ) ?>
        <div class="ui-top-row add-field">
          <h3><?php _e( 'Add Field:', 'pdb-combo-multisearch' ) ?></h3><select name="new_field_name"><?php echo $this->new_field_name_options() ?></select><a id="add_multi_field" class="button dashicons dashicons-plus" ></a><?php echo \Participants_Db::get_loading_spinner(); ?>
        </div>
        <div class="multi-field-config-area">
          <?php
          $multisearch_fields = $this->field_store->multi_search_fields();
          if ( $this->field_store->has_multisearch_fields() ) {
            foreach ( $multisearch_fields as $search_field ) {
              /** @var \pdbcms\multifields\search_field $search_field */
              $editor = new field_editor( $search_field );
              echo $editor->html();
            }
          } else {
            ?>
            <p><?php _e( 'To add a field to the multi-search control, select a field above, then click the "plus" sign button.', 'pdb-combo-multisearch' ) ?></p>
            <?php
          }
          ?>
        </div>
        <div class="ui-bottom-row">
          <p class="submit">
            <button type="submit" class="button button-primary multi-fields-update" name="action" value="<?php echo updates::action_key ?>"><?php _e( 'Update Multi-Fields', 'pdb-combo-multisearch' ) ?></button>
          </p>
        </div>
        <div class="submit top-bar-submit">
          <button type="submit" class="button button-primary  multi-fields-update" name="action" value="<?php echo updates::action_key ?>"><?php _e( 'Update Multi-Fields', 'pdb-combo-multisearch' ) ?></button>
        </div>
      </form>
    </div>
    <div id="confirmation-dialog"></div>
    <?php
  }

  /**
   * provides the options for the new field selector
   * 
   * @return string
   */
  private function new_field_name_options()
  {
    $in_optgroup = false;
    $currentgroup = '';

    foreach ( $this->field_store->available_fields() as $field ) {

      if ( $field->group()!== $currentgroup ) {
        
        if ( $in_optgroup ) {
          
          echo '</optgroup>';
          $in_optgroup = false;
        }
        
        $currentgroup = $field->group();
        printf( '<optgroup label="%s" data-groupname="%s">', $field->group_title(), $field->group() );
        $in_optgroup = true;
      }
      
      $disabled = $field->get_attribute('disabled') ? 'disabled' : '';
      
      $class = '';
      $note = '';
      if ( isset( $field->attributes['class'] ) )
      {
        $class = 'class="' . $field->get_attribute('class') . '"';
        if ( strpos( $class, 'combofield') !== false )
        {
          $note = ' (' . __( 'Combo Search field', 'pdb-combo-multisearch' ) . ')';
        }
      }
      
      printf( '<option value="%1$s" %3$s %4$s >%2$s (%1$s)%5$s</option>', $field->name(), $field->title(), $disabled, $class, $note );      
      
    }
    
    if ( $in_optgroup ) {
          
      echo '</optgroup>';
    }
  }

}
