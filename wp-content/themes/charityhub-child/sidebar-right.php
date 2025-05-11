<?php
/**
 * A template for calling the right sidebar in everypage
 */

	global $gdlr_sidebar;
?>

<?php if( $gdlr_sidebar['type'] == 'right-sidebar' || $gdlr_sidebar['type'] == 'both-sidebar' ){ ?>
<div class="gdlr-sidebar gdlr-right-sidebar <?php echo $gdlr_sidebar['right']; ?> columns">
  <style scoped>
    @media (max-width: 767px) {
      #right-sidebar-content {
        display: none
      }
    }
  </style>
	<div class="gdlr-item-start-content sidebar-right-item" id="right-sidebar-content">
	<?php dynamic_sidebar($gdlr_sidebar['right-sidebar']); ?>
	</div>
</div>
<?php } ?>
