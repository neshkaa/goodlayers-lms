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
			global $current_user, $post;

			while( have_posts() ){ the_post();
				global $gdlr_course_content, $gdlr_course_options, $gdlr_time_left, $lms_page, $lms_lecture, $payment_row;
				$lectures = empty($gdlr_course_content[$lms_page-1]['lecture-section'])? array(): json_decode($gdlr_course_content[$lms_page-1]['lecture-section'], true);
				
				$prev_lnum = 0;
				for( $i=0; $i<$lms_page-1; $i++ ){
					$prev_lnum += sizeOf(json_decode($gdlr_course_content[$i]['lecture-section'], true));
				}
				
				// assign certificate at last page when there're no quiz
				if( ($lms_page == sizeof($gdlr_course_content)) && $gdlr_course_options['quiz'] == 'none' &&
					(!empty($gdlr_course_options['enable-certificate']) && $gdlr_course_options['enable-certificate'] == 'enable') &&
					(empty($gdlr_course_content['allow-non-member']) || $gdlr_course_content['allow-non-member'] == 'disable') ){
					gdlr_lms_add_certificate(get_the_ID(), $gdlr_course_options['certificate-template']);
				}
				
				echo '<div class="gdlr-lms-course-single gdlr-lms-content-type">';
				echo '<div class="gdlr-lms-course-info-wrapper">';
                echo '<div class="gdlr-lms-course-info-title">' . esc_html(get_the_title()) . '</div>';
				echo '<div class="gdlr-lms-course-info">';
				for( $i=1; $i<=sizeof($gdlr_course_content); $i++ ){
					$part_class  = ($i == sizeof($gdlr_course_content))? 'gdlr-last ': '';
					if($i < $lms_page){ 
						$part_class .= 'gdlr-pass '; 
					}else if($i == $lms_page){ 
						$part_class .= 'gdlr-current ';
					}else{ 
						$part_class .= 'gdlr-next '; 
					}
					
					echo '<div class="gdlr-lms-course-part ' . $part_class . '">';
					echo '<div class="gdlr-lms-course-part-icon">';
					echo '<div class="gdlr-lms-course-part-bullet"></div>';
					echo '<div class="gdlr-lms-course-part-line"></div>';
					echo '</div>'; // part-icon
					
					echo '<div class="gdlr-lms-course-part-content">';
					echo '<a href="' . esc_url(add_query_arg(array('course_type'=>'content', 'course_page'=> $i, 'lecture'=>1))) . '" >';
					echo '<span class="part">' .  $i .'. '. $gdlr_course_content[$i-1]['section-name'] . '</span>';
					echo '</a>';

					// if is current section && ( at least 1 lecture || has quiz )
					if( strpos($part_class, 'gdlr-current') !== false && 
						( sizeOf( $lectures ) > 1 || (!empty($gdlr_course_content[$lms_page-1]['section-quiz']) && $gdlr_course_content[$lms_page-1]['section-quiz'] != 'none') ) ){
						
						echo '<div class="gdlr-lms-lecture-part-wrapper">';
						for( $j=1; $j<=sizeOf($lectures); $j++  ){
							if( $lms_lecture > $j ){
								$lecture_class = 'lecture-prev';
							}else if( $lms_lecture == $j ){
								$lecture_class = 'lecture-current';
							}else{
								$lecture_class = 'lecture-next';
							}

							echo '<div class="gdlr-lms-lecture-part ' . $lecture_class . '">';
							if( $lms_lecture > $j ){
								echo '<i class="fa fa-check icon-check"></i>';
							}else if( $lms_lecture == $j ){
								echo '<i class="fa fa-circle-o icon-circle-blank"></i>';
							}else{
								echo '<i></i>';
							}
							echo '<div class="gdlr-lms-lecture-part-content">';
							echo '<a href="' . esc_url(add_query_arg(array('course_type'=>'content', 'course_page'=> $i, 'lecture'=>$j))) . '" >';
							if( !empty($lectures[$j-1]['lecture-name']) ){
								echo '<span class="lecture-title">'. sprintf(esc_html__('%d', 'gdlr-lms'), ($prev_lnum + $j)) . '. ' . $lectures[$j-1]['lecture-name'] . '</span>';
							}
							echo '</a>';
							echo '</div>'; // gdlr-lms-lecture-part-content
							echo '</div>'; // gdlr-lms-lecture-part
						}

						if( !empty($gdlr_course_content[$lms_page-1]['section-quiz']) && $gdlr_course_content[$lms_page-1]['section-quiz'] != 'none' ){
							$quiz_status = gdlr_lms_quiz_status($gdlr_course_content[$lms_page-1]['section-quiz'], get_the_ID(), $current_user->ID, $lms_page);

							echo '<div class="gdlr-lms-lecture-part lecture-next">';
							echo '<i></i>';
							echo '<div class="gdlr-lms-lecture-part-content">';
							if( $quiz_status == 'new-quiz' ){
								echo '<a href="' . esc_url(add_query_arg(array('course_type'=>'section-quiz', 'course_page'=> $i, 'lecture'=>$j-1, 'section-quiz'=>1))) . '" >';
								echo '<span class="lecture-part">' . esc_html__('Section Quiz', 'gdlr-lms') . '</span>';
								echo '</a>';
							}else if( $quiz_status == 'old-quiz-retakable' ){
								echo '<a href="' . esc_url(add_query_arg(array('course_type'=>'section-quiz', 'course_page'=> $i, 'lecture'=>$j-1, 'section-quiz'=>1, 'retake'=>1))) . '" >';
								echo '<span class="lecture-part">' . esc_html__('Section Quiz', 'gdlr-lms') . '</span>';
								echo '<span class="lecture-title">' . esc_html__('Retake the quiz', 'gdlr-lms') . '</span>';
								echo '</a>';
							}else if( $quiz_status == 'please-login-first' ){
								echo '<span class="lecture-part">' . esc_html__('Section Quiz', 'gdlr-lms') . '</span>';
								echo '<span class="lecture-title">' . esc_html__('( Please login before taking a quiz )', 'gdlr-lms') . '</span>';
							}else if( $quiz_status == 'old-quiz-disable' ){
								echo '<span class="lecture-part">' . esc_html__('Section Quiz', 'gdlr-lms') . '</span>';
								echo '<span class="lecture-title">' . esc_html__('( Cannot retake a quiz )', 'gdlr-lms') . '</span>';
							}
							echo '</div>'; // gdlr-lms-lecture-part-content
							echo '</div>'; // gdlr-lms-lecture-part
						}
						
						echo '</div>';
					}
					echo '</div>'; // part-content
					echo '</div>'; // course-part
				}
				echo '</div>'; // course-info

				if( empty($payment_row) || ($payment_row->attendance_section >= sizeof($gdlr_course_content)) ){
					gdlr_lms_print_course_button($gdlr_course_options, array('quiz'));
				}
				
				if( empty($gdlr_time_left) ){
					echo '<div class="gdlr-lms-course-pdf">';
					for( $i=1; $i<=$lms_lecture; $i++ ){
						if( !empty($lectures[$i-1]['pdf-download-link']) ){
							echo '<div class="gdlr-lms-part-pdf">';
							echo '<a class="gdlr-lms-pdf-download" target="_blank" href="' . $lectures[$i-1]['pdf-download-link'] . '">';
							echo '<i class="fa fa-file-text icon-file-text"></i>';
							echo '</a>';
							
							echo '<div class="gdlr-lms-part-pdf-info">';
							echo '<div class="gdlr-lms-part-title">' . esc_html__('Lecture', 'gdlr-lms') . ' ' . $i . '</div>';
							echo '<div class="gdlr-lms-part-caption">' . $lectures[$i-1]['lecture-name'] . '</div>';
							echo '</div>';
							echo '</div>';
						}
					}
					echo '</div>'; // course-pdf		
				}				
				echo '</div>'; // course-info-wrapper
				
				
				echo '<div class="gdlr-lms-course-content">';
				$score_pass = true;
				if( $current_user->ID != $post->post_author && (!empty($gdlr_course_content[$lms_page-2]['section-quiz']) && $gdlr_course_content[$lms_page-2]['section-quiz'] != 'none') ){
					if( !empty($gdlr_course_content[$lms_page-2]['pass-mark']) ){
						$sql  = 'SELECT quiz_score, quiz_status FROM ' . $wpdb->prefix . 'gdlrquiz ';
						$sql .= $wpdb->prepare('WHERE quiz_id=%d AND student_id=%d AND course_id=%d AND section_quiz=%d ', $gdlr_course_content[$lms_page-2]['section-quiz'], $current_user->ID, get_the_ID(), ($lms_page-1));
						$current_row = $wpdb->get_row($sql);	
						
						if( $current_row->quiz_status == 'complete' ){
							$quiz_score = unserialize($current_row->quiz_score);
							$quiz_score = gdlr_lms_score_summary($quiz_score);
							
							$quiz_percent = floatval($quiz_score['score']) * 100 / floatval($quiz_score['from']);
							if( $quiz_percent < $gdlr_course_content[$lms_page-2]['pass-mark'] ){
								$score_pass = sprintf(esc_html__('You have to get at least %d%% from last section to continue to this section', 'gdlr-lms'), $gdlr_course_content[$lms_page-2]['pass-mark']);
							}
						}else if( $current_row->quiz_status == 'submitted' ){
							$score_pass = esc_html__('Please wait for your instructor scoring before continue to this section', 'gdlr-lms');
						}else{
							$score_pass = esc_html__('You have to complete last section quiz before continuing to this section.', 'gdlr-lms');
						}
					}
				}

				if( $score_pass === true ){
					if( empty($gdlr_time_left) ){
						echo gdlr_lms_content_filter($lectures[$lms_lecture-1]['lecture-content']);
					}else{
						$day_left = intval($gdlr_time_left / 86400);
						$gdlr_time_left = $gdlr_time_left % 86400;
						$gdlr_day_left  = empty($day_left)? '': $day_left . ' ' . esc_html__('days', 'gdlr-lms') . ' '; 
						
						$hours_left = intval($gdlr_time_left / 3600);
						$gdlr_time_left = $gdlr_time_left % 3600;
						$gdlr_day_left .= empty($hours_left)? '': $hours_left . ' ' . esc_html__('hours', 'gdlr-lms') . ' '; 
						
						$minute_left = intval($gdlr_time_left / 60);
						$gdlr_time_left = $gdlr_time_left % 60;
						$gdlr_day_left .= empty($minute_left)? '': $minute_left . ' ' . esc_html__('minutes', 'gdlr-lms') . ' '; 				
						$gdlr_day_left .= empty($gdlr_time_left)? '': $gdlr_time_left . ' ' . esc_html__('seconds', 'gdlr-lms') . ' '; 	
						
						echo '<div class="gdlr-lms-course-content-time-left">';
						echo '<i class="fa fa-clock icon-time" ></i>';
						echo sprintf(esc_html__('There\'re %s left before you can access to next part.', 'gdlr-lms'), $gdlr_day_left);
						echo '</div>';
					}					
				}else{
					echo '<div class="gdlr-lms-course-content-time-left">' . $score_pass . '</div>';
				}
				
				echo '<div class="gdlr-lms-course-pagination">';
				$lecture_num = sizeOf($lectures);
				if( $lms_page > 1 || ($lms_page == 1 && $lms_lecture > 1) ){
					if( $lms_lecture > 1 ){
						$lms_page_prev = $lms_page;
						$lms_lecture_prev = $lms_lecture - 1;
					}else{
						$prev_lecture = empty($gdlr_course_content[$lms_page-2]['lecture-section'])? array(): json_decode($gdlr_course_content[$lms_page-2]['lecture-section'], true);
						
						$lms_page_prev = $lms_page - 1;
						$lms_lecture_prev = sizeOf($prev_lecture);
					}
					
					echo '<a href="' . esc_url(add_query_arg(array('course_type'=>'content', 'course_page'=> $lms_page_prev, 'lecture'=> $lms_lecture_prev))) . '" class="gdlr-lms-button blue">';
					echo esc_html__('Previous Part', 'gdlr-lms');
					echo '</a>';
				}

				// (if has next section || at last section but there're next lecture) && this section available  
				if( ($lms_page < sizeof($gdlr_course_content) || ($lms_page == sizeof($gdlr_course_content) && $lecture_num > $lms_lecture )) && 
					empty($gdlr_time_left) && $score_pass === true ){
					
					$next_section_url = '';
					$next_section_text = esc_html__('Next Part', 'gdlr-lms');

					$course_type = 'content';
					if( $lms_lecture >= sizeOf($lectures) ){
						if( !empty($quiz_status) && $quiz_status == 'new-quiz' ){
							$next_section_url = add_query_arg(array(
								'course_type'=>'section-quiz', 
								'course_page'=> $lms_page, 
								'lecture'=> $lms_lecture, 
								'section-quiz'=>1
							));
							$next_section_text = esc_html__('Take a quiz', 'gdlr-lms');
						}else if( !empty($quiz_status) && $quiz_status == 'old-quiz-retakable' ){
							$next_section_url = add_query_arg(array(
								'course_type'=>'section-quiz', 
								'course_page'=> $lms_page, 
								'lecture'=> $lms_lecture, 
								'section-quiz'=>1,
								'retake'=>1
							));
							$next_section_text = esc_html__('Retake a quiz', 'gdlr-lms');
						}else{
							$next_section_url = add_query_arg(array(
								'course_type'=>'content', 
								'course_page'=> $lms_page + 1, 
								'lecture'=> 1, 
								'section-quiz'=>1
							));
						}
					}else{
						$next_section_url = add_query_arg(array(
							'course_type'=>'content', 
							'course_page'=> $lms_page, 
							'lecture'=> $lms_lecture + 1, 
							'section-quiz'=>1
						));
					}

					echo '<a href="' . esc_url($next_section_url) . '" class="gdlr-lms-button blue">' . $next_section_text . '</a>';
				}
				
				// final quiz button
				// if( empty($payment_row) || ($payment_row->attendance_section >= sizeof($gdlr_course_content)) ){
				// 	gdlr_lms_print_course_button($gdlr_course_options, array('quiz'));
				// }
				echo '</div>'; // pagination
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