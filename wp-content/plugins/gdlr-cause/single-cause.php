<?php get_header(); ?>
	
<div class="gdlr-content">

	<?php 
		global $gdlr_sidebar, $theme_option, $gdlr_post_option, $gdlr_is_ajax;
		
		if( empty($gdlr_post_option['sidebar']) || $gdlr_post_option['sidebar'] == 'default-sidebar' ){
			$gdlr_sidebar = array(
				'type'=>$theme_option['cause-sidebar-template'],
				'left-sidebar'=>$theme_option['cause-sidebar-left'], 
				'right-sidebar'=>$theme_option['cause-sidebar-right']
			); 
		}else{
			$gdlr_sidebar = array(
				'type'=>$gdlr_post_option['sidebar'],
				'left-sidebar'=>$gdlr_post_option['left-sidebar'], 
				'right-sidebar'=>$gdlr_post_option['right-sidebar']
			); 				
		}
		$gdlr_sidebar = gdlr_get_sidebar_class($gdlr_sidebar);
	?>
	<div class="with-sidebar-wrapper">
		<div class="with-sidebar-container container gdlr-class-<?php echo $gdlr_sidebar['type']; ?>">
			<div class="with-sidebar-left <?php echo $gdlr_sidebar['outer']; ?> columns">
				<div class="with-sidebar-content <?php echo $gdlr_sidebar['center']; ?> columns">
					<div class="gdlr-item gdlr-item-start-content">
						<div id="cause-<?php the_ID(); ?>" <?php post_class(); ?>>
							<?php 
								while( have_posts() ){ the_post();
									echo gdlr_get_cause_thumbnail($theme_option['cause-thumbnail-size']);
									
									echo '<div class="gdlr-cause-info-wrapper">';
									echo gdlr_get_cause_info(array('date', 'category'));
									
									if( !empty($gdlr_post_option['goal-of-donation']) ){
										echo '<div class="gdlr-cause-donation-goal">';
										if (pll_current_language() == 'bg'){
					                	echo __('Вие дарявате :','gdlr_cause') . ' <span>' . get_the_title() . '</span>';
						            } else {
                        echo __('You are donating to :','cause') . ' <span>' . get_the_title() . '</span>';
										echo '<span class="goal">';
										echo gdlr_cause_money_format($gdlr_post_option['goal-of-donation']);
										echo '</span>';}
										echo '</div>';
									}
									
									echo gdlr_cause_donation_amount($gdlr_post_option['goal-of-donation'], $gdlr_post_option['current-funding']);
									echo gdlr_get_cause_info(array('pdf'), $gdlr_post_option, false);
									echo '<div class="clear"></div>';
									
									echo gdlr_cause_donation_button($gdlr_post_option);
									
									echo '<div class="clear"></div>';
									echo '</div>';
									
									echo '<div class="gdlr-cause-content" >';
									the_content();
									echo '</div>';
									
								}
							?>
						</div><!-- #cause -->	
					</div>
				</div>
				<?php get_sidebar('left'); ?>
				<div class="clear"></div>
			</div>
			<?php get_sidebar('right'); ?>
			<div class="clear"></div>
		</div>				
	</div>				
 
</div><!-- gdlr-content -->

<?php get_footer(); ?>