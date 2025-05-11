<?php get_header(); ?>
<div id="primary" class="content-area gdlr-lms-primary-wrapper">
<div id="content" class="site-content" role="main">
<?php
	if( function_exists('gdlr_lms_get_header') && !empty($gdlr_lms_option['show-header']) && $gdlr_lms_option['show-header'] == 'enable' ){
		gdlr_lms_get_header();
	}
?>
	<div class="gdlr-lms-content">
		<div class="gdlr-lms-container gdlr-lms-container">
		<?php 
			if( isset($_GET['payment_complete']) ){
				echo '<div class="gdlr-lms-notice success" >' . esc_html__('There might be some delay on the paypal payment process, please wait for a while before refreshing the page to try again.', 'goodlayers-lms') . '</div>';
			}

			while( have_posts() ){ the_post();
				$course_val = gdlr_lms_decode_preventslashes(get_post_meta(get_the_ID(), 'gdlr-lms-course-settings', true));
				$course_options = empty($course_val)? array(): json_decode($course_val, true);		
			
				echo '<div class="gdlr-lms-course-single">';
				echo '<div class="gdlr-lms-course-info-wrapper ' . (empty($gdlr_lms_option['course-info-style'])? 'gdlr-info-style-1': 'gdlr-info-' . $gdlr_lms_option['course-info-style']) . '">';
				echo '<div class="gdlr-lms-course-info-inner-wrapper">';
				echo '<div class="gdlr-lms-course-info-author-image">';
				echo gdlr_lms_get_author_image($post->post_author, 'thumbnail');
				echo '</div>';			
				gdlr_lms_print_course_info($course_options, array('instructor', 'type', 'date', 'time', 'place', 'seat', 'certificate', 'rating'), '', true);
				gdlr_lms_print_course_price($course_options);
				gdlr_lms_print_course_button($course_options, array('buy', 'book'));				
				echo '</div>'; // course-info-inner-wrapper
				
				if( !empty($course_options['right-sidebar']) && $course_options['right-sidebar'] != 'none' ){
					echo '<div class="gdlr-lms-course-info-sidebar">';
					dynamic_sidebar($course_options['right-sidebar']);
					echo '</div>';
				}
				echo '</div>'; // course-info-wrapper
				
				echo '<div class="gdlr-lms-course-content">';
				gdlr_lms_print_course_thumbnail();
				echo '<div class="gdlr-lms-course-excerpt">';
				the_content();
				echo '</div>'; // course-excerpt
				
				// course curriculum
				echo '<div class="gdlr-course-curriculum-wrapper" >';
				$t_count = 0;
				$l_count = 0;
				$gdlr_course_content = gdlr_lms_get_course_content_settings($post->ID);

				foreach($gdlr_course_content as $course_tab){ $t_count++;
					$lectures_settings = empty($course_tab['lecture-section'])? array(): json_decode($course_tab['lecture-section'], true);
					
					if( sizeOf($gdlr_course_content) <= 1 && sizeOf($lectures_settings) == 1 ) continue;
					
					echo '<div class="gdlr-course-curriculum-section" >';
					echo '<div class="gdlr-course-curriculum-section-head" >';
					echo '<span class="gdlr-head">' . sprintf(esc_html__('Section %d', 'gdlr-lms'), $t_count) . '</span>';
					echo '<span class="gdlr-tail">' . $course_tab['section-name'] . '</span>';

					// free preview for 1 lecture per section
					if( sizeOf( $lectures_settings ) == 1 && (empty($course_tab['section-quiz']) || $course_tab['section-quiz'] == 'none') ){
						$l_count++;
						if( !empty($lectures_settings[0]['allow-free-preview']) && $lectures_settings[0]['allow-free-preview'] == 'enable' ){
							$lightbox_class = 'gdlr-lecture-' . $t_count . '-' . $l_count;
							echo '<a class="gdlr-free-preview" data-rel="gdlr-lms-lightbox" data-lb-open="' . $lightbox_class . '" >';
							esc_html_e('Free Preview', 'gdlr-lms');
							echo '</a>';
							gdlr_lms_preview_lightbox_form(gdlr_lms_content_filter($lectures_settings[0]['lecture-content']), $lightbox_class);
						}						
					}
					echo '</div>';

					if( sizeOf( $lectures_settings ) > 1 || (!empty($course_tab['section-quiz']) && $course_tab['section-quiz'] != 'none')){
						foreach($lectures_settings as $lecture_tab){ $l_count++;
							echo '<div class="gdlr-course-curriculum-lecture">';
							if( !empty($lecture_tab['icon-class']) ){
								echo '<i class="fa ' . $lecture_tab['icon-class'] . '" ></i>';
							}
							echo '<span class="gdlr-head">' . sprintf(esc_html__('Lecture %d', 'gdlr-lms'), $l_count) . '</span>';
							if( !empty($lecture_tab['lecture-name']) ){
								echo '<span class="gdlr-tail">' . $lecture_tab['lecture-name'] . '</span>';
							}
							
							if( !empty($lecture_tab['allow-free-preview']) && $lecture_tab['allow-free-preview'] == 'enable' ){
								$lightbox_class = 'gdlr-lecture-' . $t_count . '-' . $l_count;
								echo '<a class="gdlr-free-preview" data-rel="gdlr-lms-lightbox" data-lb-open="' . $lightbox_class . '" >';
								esc_html_e('Free Preview', 'gdlr-lms');
								echo '</a>';
								gdlr_lms_preview_lightbox_form(gdlr_lms_content_filter($lecture_tab['lecture-content']), $lightbox_class);
							}
							echo '</div>'; // gdlr-course-curriculum-lecture
						}
					}
					
					if( !empty($course_tab['section-quiz']) && $course_tab['section-quiz'] != 'none' ){
						echo '<div class="gdlr-course-curriculum-lecture">';
						echo '<i class="fa fa-check icon-check"></i>';
						echo '<span class="gdlr-tail">' . esc_html__('Section Quiz', 'gdlr-lms') . '</span>';
						echo '</div>';
					}
					echo '</div>'; // gdlr-course-curriculum-section
				}
				
				if( !empty($course_options['quiz']) && $course_options['quiz'] != 'none' ){
					echo '<div class="gdlr-course-curriculum-quiz">';
					echo '<i class="fa fa-check icon-check"></i>';
					echo '<span class="gdlr-tail">' . esc_html__('Final Quiz', 'gdlr-lms') . '</span>';
					echo '</div>';
				}
				echo '</div>'; // course-curriculum-wrapper 
				
				echo '<div class="gdlr-lms-single-course-info">';
				$tag = get_the_term_list(get_the_ID(), 'course_tag', '', '<span class="sep">,</span> ' , '' );
				if( !empty($tag) ){
					echo '<div class="portfolio-info portfolio-tag"><i class="fa fa-tag icon-tag" ></i>' . $tag . '</div>';
				}
				
				gdlr_lms_get_social_shares();
				echo '</div>';	// single-course-info

				comments_template( '', true );	
				echo '</div>'; // course-content
				echo '<div class="clear"></div>';
				echo '</div>'; // course-single		
			}
		?>
		</div><!-- gdlr-lms-container -->
	</div><!-- gdlr-lms-content -->
</div>
</div>
<?php 
if( !empty($gdlr_lms_option['show-sidebar']) && $gdlr_lms_option['show-sidebar'] == 'enable' ){ 
	get_sidebar( 'content' );
	get_sidebar();
}

get_footer(); ?>