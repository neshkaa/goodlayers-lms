<?php 
	get_header();

	while( have_posts() ){ the_post();
?>
<div class="gdlr-content">

	<?php
		global $gdlr_sidebar, $theme_option, $gdlr_post_option, $gdlr_is_ajax;

		if( empty($gdlr_post_option['sidebar']) || $gdlr_post_option['sidebar'] == 'default-sidebar' ){
			$gdlr_sidebar = array(
				'type'=>$theme_option['post-sidebar-template'],
				'left-sidebar'=>$theme_option['post-sidebar-left'],
				'right-sidebar'=>$theme_option['post-sidebar-right']
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
					<div class="gdlr-item gdlr-kot-<?php echo $theme_option['kot-page-style']; ?> gdlr-item-start-content">
						<div id="kot-<?php the_ID(); ?>" <?php post_class(); ?>>

                            <div class="gdlr-kot-content">
								
								
		                    	<!-- CONTENT -->
		                    	
								<?php if(empty($gdlr_post_option['hide-kot-description']) || $gdlr_post_option['hide-kot-description'] == 'disable'){ ?>
								<div class="gdlr-kot-description">
									<div class="content">
									<?php
										the_content();
										wp_link_pages( array(
											'before' => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'gdlr-kot' ) . '</span>',
											'after' => '</div>',
											'link_before' => '<span>',
											'link_after' => '</span>' ));
									?>
									</div>
								</div>
								<?php } ?>
								</div>
								<div class="clear"></div>
								

                                
                                </div></div>

                                <!-- BIO -->
								<?php if(empty($gdlr_post_option['hide-kot-info']) || $gdlr_post_option['hide-kot-info'] == 'disable'){ ?>
								<div class="gdlr-kot-info">
									

                                    <!-- NAV -->
									<nav class="gdlr-single-nav">
										<?php
											if( !$gdlr_is_ajax ){
												previous_post_link('<div class="previous-nav">%link</div>', '<i class="icon-angle-left fa fa-angle-left"></i>');
												next_post_link('<div class="next-nav">%link</div>', '<i class="icon-angle-right fa fa-angle-right"></i>');
											}else{
												$prev_post = get_adjacent_post(false, '', true);
												if( !empty($prev_post) ){
													echo '<div class="previous-nav"><a href="#" data-lightbox="' . $prev_post->ID . '">';
													echo '<i class="icon-angle-left"></i>';
													echo '</a></div>';
												}

												$next_post = get_adjacent_post(false, '', false);
												if( !empty($next_post) ){
													echo '<div class="next-nav"><a href="#" data-lightbox="' . $next_post->ID . '">';
													echo '<i class="icon-angle-right"></i>';
													echo '</a></div>';
												}
											}
										?>
										<div class="clear"></div>
									</nav><!-- .nav-single -->

									<div class="content">
									<?php
										echo gdlr_get_kot_info(array('gender', 'age', 'color', 'coat', 'neutered', 'kids', 'cats', 'location', 'tag'), $gdlr_post_option, false);
									?>
									</div>
									
								</div>
								<?php } ?>
							<!-- PHOTOS -->
							<?php
								$thumbnail = gdlr_get_kot_thumbnail($gdlr_post_option, $theme_option['kot-thumbnail-size']);
								if(!empty($thumbnail)){
									$additional_class = '';
									if( (!empty($gdlr_post_option['hide-kot-info']) && $gdlr_post_option['hide-kot-info'] == 'enable') &&
										(!empty($gdlr_post_option['hide-kot-description']) && $gdlr_post_option['hide-kot-description'] == 'enable') ){
										$additional_class = ' full-content ';
									}
									echo '<div class="gdlr-kot-thumbnail ' . $additional_class . gdlr_get_kot_thumbnail_class($gdlr_post_option) . '">';
									echo $thumbnail;
									echo '</div>';
								}
							?>

								<div class="clear"></div>
							
													 <!-- Page Builder Content -->
                <?php if (!empty($with_sidebar_content)) {
                    gdlr_print_page_builder($with_sidebar_content, false);
                } ?>
						<?php if (pll_current_language() == 'bg') {
						echo ('* Котките се предлагат единствено за домашно отглеждане в предварително <strong>обезопасено за котки</strong> жилище, без възможност за свободен достъп извън имота. Достъп до двор на къща е допустим единствено ако е заграден с обезопасена за котки ограда, непозволяваща да излязат на улицата. <br>Всички котки се предават на осиновителите с европейски паспорт, микрочип и договор за осиновяване. .');
						}elseif (pll_current_language() == 'en') {
                        echo ('* All cats are indoor only cats. Cat-proofing windows and balconies is required. Outdoor access is possible only with cat-proofed fences keeping the cat from leaving the property. Every adoption is finalized with an adoption contract.');}
								 ?>
								 
								<div class="social-kot">
									    <?php echo gdlr_get_social_shares();
									    ?>
								</div>
							</div>
						</div><!-- #kot -->
						<?php //  ?>

						<div class="clear"></div>
						<?php
							// print kot comment
							if( $theme_option['kot-comment'] == 'enable' ){
								comments_template( '', true );
							}
						?>
					</div>

					<?php
						// print related kot
						if( !$gdlr_is_ajax && is_single() && $theme_option['kot-related'] == 'enable' ){
							global $gdlr_related_section; $gdlr_related_section = true;

							$args = array('post_type' => 'kot', 'suppress_filters' => false);
							$args['posts_per_page'] = (empty($theme_option['related-kot-num-fetch']))? '3': $theme_option['related-kot-num-fetch'];
							$args['post__not_in'] = array(get_the_ID());

							$kot_term = get_the_terms(get_the_ID(), 'kot_tag');
							$kot_tags = array();
							if( !empty($kot_term) ){
								foreach( $kot_term as $term ){
									$kot_tags[] = $term->term_id;
								}
								$args['tax_query'] = array(array('terms'=>$kot_tags, 'taxonomy'=>'kot_tag', 'field'=>'id'));
							}
							$query = new WP_Query( $args );

							if( !empty($query) ){
								echo '<div class="gdlr-related-kot kot-item-holder">';
								echo '<h4 class="head">' . pll__('Още котета, които търсят дом', 'gdlr-kot') . '</h4>';
								if( $theme_option['related-kot-style'] == 'classic-kot' ){
									echo gdlr_get_classic_kot($query, $theme_option['related-kot-size'],
										$theme_option['related-kot-thumbnail-size'], 'fitRows' );
								}else{
									echo gdlr_get_modern_kot($query, $theme_option['related-kot-size'],
										$theme_option['related-kot-thumbnail-size'], 'fitRows' );
								}
								echo '<div class="clear"></div>';
								echo '</div>';
							}
							$gdlr_related_section = false;
						}
					?>
				</div>
				<?php get_sidebar('left'); ?>
				<div class="clear"></div>
			</div>
			<?php get_sidebar('right'); ?>
			<div class="clear"></div>
		</div>
	</div>

</div><!-- gdlr-content -->
<?php
	}

	get_footer();
?>
