<?php
/**
 * combo multisearch responsive list template
 * 
 * @version 1.3
 *
 */
global $PDb_Combo_Multi_Search;
$PDb_Combo_Multi_Search->enable();
$combo_search = $PDb_Combo_Multi_Search->get_text_search_value();
$combo_search_label = empty($PDb_Combo_Multi_Search->plugin_options['combo_search_label']) ? false : $PDb_Combo_Multi_Search->plugin_options['combo_search_label'];
$placeholder = $PDb_Combo_Multi_Search->plugin_options['placeholder'];
$search_term = $PDb_Combo_Multi_Search->current_search_term();


?>
<div class="wrap <?php echo $this->wrap_class ?> pdb-list-responsive pdb-combo-multisearch" id="<?php echo $this->list_anchor ?>">
  <?php  if ($PDb_Combo_Multi_Search->combo_multi_search_is_active()): ?>
  <?php echo $this->search_error_style ?>
  <div class="pdb-searchform">
    <div class="pdb-error pdb-search-error"  style="display:none">
      <p class="value_error"><?php echo $PDb_Combo_Multi_Search->incomplete_search_error_message(); ?></p>
    </div>
      <?php
      $this->search_sort_form_top();
      $PDb_Combo_Multi_Search->print_hidden_fields(array('subsource'=>\pdbcms\Plugin::subsource));
      ?>
      <div class="combo-multi-search-controls">
          <?php if ($PDb_Combo_Multi_Search->combo_search_is_active()) : ?>
            <div class="combo-search-controls search-control-group">
              <span class="search-control pdb-combo_search combo-search">
                <?php if ($combo_search_label) : ?>
                <label for="pdb-combo_search-control"><?php echo $combo_search_label ?></label>
                <?php endif ?>
                <input name="combo_search" id="pdb-combo_search-control" placeholder="<?php echo $placeholder ?>" value="<?php echo $combo_search ?>" type="text">
              </span>
            <?php if ($PDb_Combo_Multi_Search->combo_search_modifiers_enabled()) : ?>
              <span class="search-control pdb-combo_search combo-search">
                <?php $PDb_Combo_Multi_Search->print_search_options(); ?>
              </span>
            <?php endif ?>
            </div>
          <?php endif ?>
          <?php if ($PDb_Combo_Multi_Search->multi_search_is_active()) : ?>
          <div class="multi-search-controls search-control-group">
          <?php foreach($PDb_Combo_Multi_Search->search_controls as $control) : if($control) : ?>
            <span class="search-control pdb-combo_search combo-search pdb-<?php echo $control->name . ' ' . $control->wrap_class ?>">
              <label for="<?php echo $control->id ?>"><?php echo $control->title ?></label>
              <span class="search-control-input">
                <?php echo $control->control ?>
                <?php if (!empty($control->help_text)) : ?>
                  <span class="helptext"><?php echo $control->help_text ?></span>
                <?php endif ?>
              </span>
            </span>
          <?php endif; endforeach; ?>
          </div>
          <?php endif ?>
          <div class="submit-controls search-control-group">
            <span class="search-control">
              <input type="submit" class="button-primary" name="multisearch-submit" data-submit="search" value="Търси" />
              <input type="submit" class="button-secondary" name="multisearch-submit" data-submit="clear" value="Изчисти" />
            </span>
          </div>
      </div>
    <?php if ( $filter_mode == 'sort' || $filter_mode == 'both' ) : ?>
    
    <fieldset class="widefat">
      <legend><?php _e('Sort by', 'participants-database' )?>:</legend>
      
      <?php
      /*
       * this function sets the fields in the sorting dropdown. It has two options:
       *    1. columns: an array of field names to show in the sorting dropdown. If 
       *       'false' shows default list of sortable fields as defined
       *    2. sorting: you can choose to sort the list by 'column' (the order they 
       *       appear in the table), 'alpha' (alphabetical order), or 'order' which 
       *       uses the defined group/field order
       */
      $this->set_sortables(false, 'column');
      ?>

      <?php $this->sort_form() ?>

    </fieldset>
    <?php endif ?>
    </form>
  </div>
  <?php endif ?>

<?php // this is an example of a way to style the records, delete this or edit as needed ?>
<style type="text/css">
  section {
    margin: 1.5em 0;
      border: .4em solid #fff570;
      padding: 1em;
  }
  .pdb-field-title {
    font-weight: bold;
    padding-right: 15px;
  }
  .pdb-field-title:after {
    content: ':';
  }
  .pdb-filed-title-free {font-weight: bold;}
  .pdb-list-responsive .submit-controls {
    margin-left: 0px;
  }
  .prestoi  { 
    position: absolute;
    bottom: -4em;
    left: 2em;
  }
</style>

<div class="pdb-list list-container" >

    
    <?php if ( $record_count > 0 ) : ?>


    <?php while ( $this->have_records() ) : $this->the_record(); // each record is one row ?>
      <section id="record-<?php echo $this->record->record_id ?>">
          
           <?php /*
        This is where your single record data is displayed
        */ ?>
        <?php $record = new PDb_Template( $this ); // instantiate the helper class ?>
				
<h3><?php if (!empty($record->get_value ('program'))) : $record->print_value('program') ?><?php elseif ( $record->print_value( 'clinic' )): endif ?></h3>
<ul>
<?php if ($record->get_value ('free')) : echo '<li><strong>Безплатни кастрации</strong></li>'; endif?>	
<li><?php echo 'Програмата включва ' ?> <strong> <?php $record->print_value('status') ?><?php echo '&nbsp;' ?><?php $record->print_value('animals') ?> </strong></li>

<?php if ($record->get_value ('price') !== 'безплатно' ):echo '<li><strong>Цени: </strong>', $record->print_value('price'),  '</li>'; endif ?>		



<li><?php if($record->get_value ('stay') == 'без' ) : echo ' Кастрацията <span>не включва престой</span> в клиниката и животните се изписват в деня на хирургията. Задължително предвидете място за престой след изписването. *';
				    elseif ($record->get_value ('stay') !== 'без') : echo 'Кастрацията включва <span>'?> <?php echo $record->get_value ('stay') . '</span> престой в клиниката. Предвидете място за престой на женските котки след изписването. *';endif ?></li></ul>
				
<h5><strong>Телефон: </strong><?php $record->print_value('phone') ?></h5>

<?php if (!empty($record->get_value ('time'))) : echo '<h5><strong>Работно време: </strong>' ?> <?php $record->print_value('time') . '</h5>'; endif?>

<?php if ((!empty($record->get_value ('clinic'))) || (!empty($record->get_value ('adress')))) : echo '<h5><strong>Адрес: </strong>' ; 
        if (!empty($record->get_value ('program'))) : $record->print_value ('clinic') ; echo '<br>'; endif?><?php $record->print_value('adress') ; echo '</h5>' ; endif ?>
    
<?php if (!empty($record->get_value('email'))) :  echo '<h5><strong>Email: </strong>' ?><?php $record->print_value ('email') . '</h5>' ; endif ?>

<?php if (!empty($record->get_value ('info'))) : echo '<h5><strong>Допълнителна информация: </strong>' ?><?php $record->print_value('info') . '</h5>' ; endif ?>


<?php if (!empty($record->get_value ('link')) || !empty($record->get_value ('facebook'))) : echo '<h5>' ; ?>
<?php if (!empty($record->get_value ('link'))) : $record->print_value('link') . '<span class="icon-globe"></span></h5>'  ; endif ?>

<?php if (!empty($record->get_value ('facebook'))) : $record->print_value('facebook')  ; endif ?><?php echo '</h5>'; endif ?>
        
       </section>
    <?php endwhile; // each record ?>
       <br><h4><?echo '* Много е важно да предвидите <strong>място за престой</strong> на котките, непосредствено след операцията. <u>Минимумът е 24 часа за мъжки и 2-3 дни за женски.</u> Никога не пускайте животни обратно навън в деня на хирургията! През пъртите 24 часа минават ефектите от упойката. Мъжките котки, които се изписват от клиниката в деня <u>след</u> операцията, са готови да бъдат пуснати веднага. За женските, при които операцията е значително по-тежка - с отворена рана и шевове - е най-сигурно да се уверите, че разрезът зараства добре (котката не го ближе и чопли), че има апетит и ходи до тоалетна, преди да я пуснете обратно навън. ';?></h4>

 
    <?php else : // if there are no records ?>
    <hr><br><h5>Списъкът се обновява постоянно. Ако имате информация, която искате да включим или актуализираме, моля да се свържете с нас през <a href=" /https://m.me/TNR.Bulgaria/" target="_blank"rel="noopener">Messenger</a> или на rescue@thelastcage.org.</h5><h5>
Ветеринарните клиники и амбулатории, включени в този списък са признати сред общността на доброволци и спасители с хуманност и добро отношение към бездомните животни. Клиники, позволяващи съмнителни пракитки и ветеринари, от които има много оплаквания и лоши отзиви няма да бъдат включвани в списъка.</h5><h5>

Последна актуализация на: 23.02.2023</h5>

    <h4><?php if ($this->is_search_result === true)  echo Participants_Db::$plugin_options['no_records_message'] ?></h4>

</h4>
    <?php endif; // $record_count > 0 ?>



  <?php
	// set up the bootstrap pagination classes and wrappers

  // set up the bootstrap-style pagination block
  // sets the indicator class for the pagination display
  $this->pagination->set_current_page_class( 'active' );
  // wrap the current page indicator with a dummy anchor
  $this->pagination->set_anchor_wrap( false );
  // set the wrap class and element
	$this->pagination->set_props(array('list_class' => '',));
	$this->pagination->show();
	?>
</div>