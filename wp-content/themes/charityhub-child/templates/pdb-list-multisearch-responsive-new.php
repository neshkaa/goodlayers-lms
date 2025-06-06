<?php
/*
template for participants list shortcode output
this template demonstrates a "table-less" reponsive layout for the list of records 
using the PDb_Template class for each record
*/
?>
<div class="wrap <?php echo $this->wrap_class ?>" id="<?php echo $this->list_anchor ?>">
<?php /* SEARCH/SORT FORM */ ?>
  <?php if ( $filter_mode != 'none' ) : ?>
  <div class="pdb-searchform">
  
    <div class="alert alert-block" style="display:none">
    	<a class="close" data-dismiss="alert" href="#">X</a>
      <p class="search_field_error"><?php _e( 'Please select a column to search in.', 'participants-database' )?></p>
      <p class="value_error"><?php _e( 'Please type in something to search for.', 'participants-database' )?></p>
    </div>

    <?php $this->search_sort_form_top( false, 'form-horizontal' ); ?>

    <?php if ( $filter_mode == 'filter' || $filter_mode == 'both' ) : ?>
    
      <div class="control-group">
      	<label class="control-label"><?php _e('Search', 'participants-database' )?>:</label>
      	<div class="controls">
        
				<?php
          /* 
           * you can replace "false" with your own text for the "all columns" value
           * for more info on using the column_selector method, see pdb-list-detailed.php
           */
          $this->column_selector( false );
        ?>
      		<?php $this->search_form() ?>
        </div>
        
      </div>
    <?php endif ?>
    <?php if ( $filter_mode == 'sort' || $filter_mode == 'both' ) : ?>
      
			
      <div class="control-group">
      	<label class="control-label"><?php _e('Sort by', 'participants-database' )?>:</label>
      	<div class="controls">

      		<?php $this->sort_form() ?>
          
        </div>
      </div>
    <?php endif ?>

  </div>
  <?php endif ?>


<?php // this is an example of a way to style the records, delete this or edit as needed ?>
<style type="text/css">
  section {
    margin: 1.5em 0;
  }
</style>

<div class="pdb-list list-container" >
		<?php 
		/* print the count if enabled in the shortcode
		 * 
		 * the tag wrapping the count statment can be supplied in the function argument, example here
		 */
		$this->print_list_count('<h5>'); 
		?>  
    
    <?php if ( $record_count > 0 ) : ?>

    <?php while ( $this->have_records() ) : $this->the_record(); // each record is one row ?>
  
      <section id="record-<?php echo $this->record->record_id ?>">
        
        <?php /*
        This is where your single record data is displayed
        */ ?>
        <?php $record = new PDb_Template( $this ); // instantiate the helper class ?>
				
        <h3><?php $record->print_value( 'city' ) ?> <?php $record->print_value( 'last_name' ) ?></h3>
        <h5><?php $record->print_value( 'animals' ) ?></h5>
        <p><?php $record->print_value( 'free' ) ?>, <?php $record->print_value( 'state' ) ?></p>
        <p><em>Email: <?php $record->print_value( 'clinic' ) ?><br />
          Phone: <?php $record->print_value( 'phone' ) ?></em></p>
        
      </section>
  
    <?php endwhile; // each record ?>
    
    <?php else : // if there are no records ?>

    <h4><?php if ($this->is_search_result === true)  echo Participants_Db::$plugin_options['no_records_message'] ?></h4>

    <?php endif; // $record_count > 0 ?>
	</div>
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