<?php
/**
 * combo multisearch remote template
 *
 * @version 1.1
 *
 */
global $PDb_Combo_Multi_Search;
$PDb_Combo_Multi_Search->enable();
$combo_search = $PDb_Combo_Multi_Search->get_text_search_value();
$combo_search_label = empty( $PDb_Combo_Multi_Search->plugin_options['combo_search_label'] ) ? false : $PDb_Combo_Multi_Search->plugin_options['combo_search_label'];
$placeholder = $PDb_Combo_Multi_Search->plugin_options['placeholder'];
$field_list = array_merge( array('combo_search'), $PDb_Combo_Multi_Search->multi_search_field_list() );
$search_terms = array();
foreach ( $field_list as $field ) {
  $value = isset( $_POST[$field] ) ? $_POST[$field] : '';
  if ( is_array( $value ) ) {
    foreach ( $value as $subvalue ) {
      if ( $subvalue !== '' && $subvalue !== 'any' ) {
        $search_terms[] = trim( $subvalue, ', ' );
      }
    }
  } elseif ( $value !== '' && $value !== 'any' ) {
    $search_terms[] = trim( $value, ', ' );
  }
}
?>
<style type="text/css">
  .pdb-searchform label {
    /*    display:block;
        width:120px;
        text-align:left;
        font-weight:bold;*/
  }
  .pdb-searchform input[type=text] {
    width: 250px;
  }
  .pdb-searchform .date-search input[type=text],
  .pdb-searchform .timestamp-search input[type=text],
  .pdb-searchform .numeric-search input[type=number] {
    width: 30%;
  }
</style>
<div class="wrap <?php echo $this->wrap_class ?> pdb-combo-multisearch">

  <?php if ( $PDb_Combo_Multi_Search->combo_multi_search_is_active() ): ?>
    <?php echo $this->search_error_style ?>
    <div class="pdb-searchform">
      <div class="pdb-error pdb-search-error"  style="display:none">
        <p class="value_error"><?php echo $PDb_Combo_Multi_Search->incomplete_search_error_message(); ?></p>
      </div>
      <?php
      $this->search_sort_form_top();
      $PDb_Combo_Multi_Search->print_hidden_fields( array('subsource' => \pdbcms\Plugin::subsource) );
      ?>
      <div class="combo-multi-search-controls">
        <?php if ( $PDb_Combo_Multi_Search->combo_search_is_active() ) : ?>
          <div class="combo-search-controls search-control-group">
            <span class="search-control pdb-combo_search combo-search">
              <?php if ( $combo_search_label ) : ?>
                <label for="pdb-combo_search-control"><?php echo $combo_search_label ?></label>
              <?php endif ?>
              <input name="combo_search" id="pdb-combo_search-control" placeholder="<?php echo $placeholder ?>" value="<?php echo $combo_search ?>" type="text">
            </span>
            <?php if ( $PDb_Combo_Multi_Search->combo_search_modifiers_enabled() ) : ?>
              <span class="search-control pdb-combo_search combo-search">
                <?php $PDb_Combo_Multi_Search->print_search_options(); ?>
              </span>
            <?php endif ?>
          </div>
        <?php endif ?>
        <?php if ( $PDb_Combo_Multi_Search->multi_search_is_active() ) : ?>
          <div class="multi-search-controls search-control-group">
            <?php foreach ( $PDb_Combo_Multi_Search->search_controls as $control ) : if ( $control ) : ?>
                <span class="search-control pdb-combo_search combo-search pdb-<?php echo $control->name . ' ' . $control->wrap_class ?>">
                  <label for="<?php echo $control->id ?>"><?php echo $control->title ?></label>
                  <span class="search-control-input">
                    <?php echo $control->control ?>
                    <?php if ( !empty( $control->help_text ) ) : ?>
                      <span class="helptext"><?php echo $control->help_text ?></span>
                    <?php endif ?>
                  </span>
                </span>
              <?php endif;
            endforeach; ?>
          </div>
  <?php endif ?>
        
    <?php if ($filter_mode == 'sort' || $filter_mode == 'both') : ?>

      <div class="sort-controls search-control-group">
        <label><?php _e('Sort by', 'participants-database') ?>:</label>

        <?php $this->set_sortables(false, 'column'); ?>

        <?php $this->sort_form() ?>

      </div>
    <?php endif ?>
        <div class="submit-controls search-control-group">
          <span class="search-control">
            <input type="submit" class="button-primary" name="multisearch-submit" data-submit="search" value="<?php echo $PDb_Combo_Multi_Search->i18n['search'] ?>" />
            <input type="submit" class="button-secondary" name="multisearch-submit" data-submit="clear" value="<?php echo $PDb_Combo_Multi_Search->i18n['clear'] ?>" />
          </span>
        </div>
      </div>
      </form>
    </div>
<?php endif ?>
</div>