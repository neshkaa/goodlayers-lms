<?php 
	global $wpdb, $current_user, $post;
	
	$authorization = true;
	$lms_page = (empty($_GET['course_page']))? 1: intval($_GET['course_page']);
	
	// for single quiz post_type
	if( $post->post_type == 'quiz' ){
		$quiz_val = gdlr_lms_decode_preventslashes(get_post_meta($post->ID, 'gdlr-lms-content-settings', true));
		$quiz_options = empty($quiz_val)? array(): json_decode($quiz_val, true);

	}else{
	
		// initialte the value
		$course_val = gdlr_lms_decode_preventslashes(get_post_meta(get_the_ID(), 'gdlr-lms-course-settings', true));
		$course_options = empty($course_val)? array(): json_decode($course_val, true);		

		$quiz_val = gdlr_lms_decode_preventslashes(get_post_meta($course_options['quiz'], 'gdlr-lms-content-settings', true));
		$quiz_options = empty($quiz_val)? array(): json_decode($quiz_val, true);	

		if( $current_user->ID != $post->post_author ){

			$quiz_status = gdlr_lms_quiz_status($course_options['quiz'], get_the_ID(), $current_user->ID);

			if( $quiz_status == 'old-quiz-disable' || $quiz_status == 'please-login-first' ){
				$authorization = false;
			}else if( $quiz_status == 'old-quiz-retakable' ){
				if( empty($_GET['retake']) ){
					$authorization = false;
				}else{
					// reset old quiz value
					$sql  = 'SELECT * FROM ' . $wpdb->prefix . 'gdlrquiz ';
					$sql .= $wpdb->prepare('WHERE quiz_id=%d AND student_id=%d AND course_id=%d ', $course_options['quiz'], $current_user->ID, get_the_ID());
					$sql .= 'AND (quiz_status=\'complete\' OR quiz_status=\'submitted\') AND section_quiz IS NULL';
					$old_quiz = $wpdb->get_row($sql);	

					if( !empty($old_quiz) ){
						$wpdb->update( $wpdb->prefix . 'gdlrquiz', 
							array('quiz_answer'=>'', 'quiz_score'=>'', 'quiz_status'=>'pending', 'retake_times'=>($old_quiz->retake_times + 1)), 
							array('id'=>$old_quiz->id), 
							array('%s', '%s', '%s', '%d'), 
							array('%d')
						);		
					}
				}
			}else if( $quiz_status == 'new-quiz' ){
			
				// get the old value 
				$sql  = 'SELECT * FROM ' . $wpdb->prefix . 'gdlrquiz ';
				$sql .= $wpdb->prepare('WHERE quiz_id=%d AND student_id=%d AND course_id=%d', $course_options['quiz'], $current_user->ID, get_the_ID());
				$current_row = $wpdb->get_row($sql);	
				$quiz_answer = empty($current_row)? array(): unserialize($current_row->quiz_answer);
				
				// save quiz answer action
				if( isset($_POST['action']) && $_POST['action'] == 'save_quiz_answer' ){
				
					$question_set = intval($_POST['lms_page']) - 1;
					if( isset($_POST['timeleft']) ){
						$quiz_answer[$question_set]['timeleft'] = $_POST['timeleft'];
					}

					for($i=0; $i<sizeof($_POST); $i++){
						if( isset($_POST['question' . $i]) ){
							if( is_array($_POST['question' . $i]) ){
								$quiz_answer[$question_set][$i] = $_POST['question' . $i];
							}else{
								$quiz_answer[$question_set][$i] = stripslashes($_POST['question' . $i]);
							}
						}
					}
					
					$quiz_score = array(); $quiz_status = 'pending';
					if( $_GET['course_page'] == 'finish' ){
						$quiz_score = gdlr_lms_calculating_score($quiz_options, $quiz_answer, $quiz_score);
						$quiz_status = (sizeof($quiz_score) == sizeof($quiz_options))? 'complete': 'submitted';
						
						if( $quiz_status == 'complete' && (!empty($course_options['enable-badge']) && $course_options['enable-badge'] == 'enable') ){
							gdlr_lms_add_badge(get_the_ID(), gdlr_lms_score_summary($quiz_score), $course_options['badge-percent'],
								$course_options['badge-title'], $course_options['badge-file']);
						}
						
						if( $quiz_status == 'complete' && (!empty($course_options['enable-certificate']) && $course_options['enable-certificate'] == 'enable')){
							gdlr_lms_add_certificate(get_the_ID(), $course_options['certificate-template'], 
								gdlr_lms_score_summary($quiz_score), $course_options['certificate-percent']);
						}
					}		
					
					if($current_row){
						$wpdb->update( $wpdb->prefix . 'gdlrquiz', 
							array('quiz_answer'=>serialize($quiz_answer), 'quiz_score'=>serialize($quiz_score), 'quiz_status'=>$quiz_status), 
							array('quiz_id'=>$course_options['quiz'], 'student_id'=>$current_user->ID, 'course_id'=>get_the_ID()), 
							array('%s', '%s', '%s'), 
							array('%d', '%d')
						);			
					}else{
						$wpdb->insert( $wpdb->prefix . 'gdlrquiz', 
							array('quiz_id'=>$course_options['quiz'], 'student_id'=>$current_user->ID, 'course_id'=>get_the_ID(),
								'quiz_answer'=>serialize($quiz_answer), 'quiz_score'=>serialize($quiz_score), 'quiz_status'=>$quiz_status), 
							array('%d', '%d', '%s', '%s', '%s', '%s') 
						);		
					}
				}
			}

		} // if is post author
	}
	
	if( isset($_POST['ajax_req']) || (isset($_GET['course_page']) && $_GET['course_page'] == 'finish') ){
		echo "0";
		exit();
	}
	
	
	get_header();
?>
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
			if( $authorization ){
				echo '<form class="gdlr-lms-course-single gdlr-lms-quiz-type" method="post" action="" >';
				echo '<input type="hidden" name="action" value="save_quiz_answer" />';
				echo '<input type="hidden" name="lms_page" value="' . $lms_page . '" />';
				
				echo '<div class="gdlr-lms-course-info-wrapper">';
				echo '<div class="gdlr-lms-course-info-title">' . esc_html__('Quiz Process', 'gdlr-lms') . '</div>';
				
				if( $quiz_options[$lms_page-1]['section-timer'] == 'enable' ){
					echo '<div class="gdlr-lms-quiz-timer">';
					echo '<i class="fa fa-calendar icon-time"></i>';

					$full_time = intval($quiz_options[$lms_page-1]['time-period'])*60;
					if( !isset($quiz_answer[$lms_page-1]['timeleft']) ){
						$timeleft = $full_time;
					}else{
						$timeleft = $quiz_answer[$lms_page-1]['timeleft'];
					}
					
					echo '<span class="timer" ></span>';
					echo '<input type="hidden" name="timeleft" ';
					echo 'data-full="' . $full_time . '" ';
					echo 'value="' . $timeleft . '" />';	
					
					// if not last page
					if( $lms_page < sizeof($quiz_options) ){
						gdlr_lms_quiz_timeout_form($lms_page+1);
					}else{
						gdlr_lms_quiz_timeout_form();
					}
					
					echo '</div>'; // quiz-timer
				}
				
				echo '<div class="gdlr-lms-course-info">';
				for( $i=1; $i<=sizeof($quiz_options); $i++ ){
					$part_class  = ($i == sizeof($quiz_options))? 'gdlr-last ': '';
					if($i < $lms_page){ $part_class .= 'gdlr-pass '; }
					else if($i == $lms_page){ $part_class .= 'gdlr-current '; }
					else{ $part_class .= 'gdlr-next '; }
					
					echo '<div class="gdlr-lms-course-part ' . $part_class . '">';
					echo '<div class="gdlr-lms-course-part-icon">';
					echo '<div class="gdlr-lms-course-part-bullet"></div>';
					echo '<div class="gdlr-lms-course-part-line"></div>';
					echo '</div>'; // part-icon
					
					echo '<div class="gdlr-lms-course-part-content">';
					echo '<span class="part">' . esc_html__('Part', 'gdlr-lms') . ' ' . $i . '</span>';
					echo '<span class="title">' . $quiz_options[$i-1]['section-name'] . '</span>';
					echo '</div>'; // part-content
					echo '</div>'; // course-part
				}
				echo '</div>'; // course-info
				gdlr_lms_print_course_button(array(), array('finish-quiz'));
				echo '</div>'; // course-info-wrapper
				
				echo '<div class="gdlr-lms-course-content">';
				echo '<h3>' . esc_html__('Final Quiz', 'gdlr-lms') . '</h3>';
				echo '<h3>' . esc_html__('Part', 'gdlr-lms') . ' ' . $lms_page . ' ' . $quiz_options[$lms_page-1]['section-name'] . '</h3>';
				echo '<div class="gdlr-lms-quiz-content-wrapper">';

				$count = 0;
				$quiz_options[$lms_page-1]['question'] = json_decode($quiz_options[$lms_page-1]['question'], true);
				if( !empty($quiz_options[$lms_page-1]['question']) ){
					foreach($quiz_options[$lms_page-1]['question'] as $question){ $count++;

						echo '<div class="gdlr-lms-quiz-question-wrapper">';
						echo '<div class="gdlr-lms-quiz-question">' . $count . '. ' . do_shortcode($question['question']) . '</div>';
						echo '<div class="gdlr-lms-quiz-answer">';
						switch($quiz_options[$lms_page-1]['question-type']){
							case 'single':
								$choice_count = 0;
								foreach($question['quiz-choice'] as $quiz_choice){ $choice_count++;
									echo '<div class="gdlr-lms-quiz-choice">';
									echo '<input type="radio" value="' . $choice_count . '" name="question' . ($count-1) . '" ';
									if( !empty($quiz_answer[$lms_page-1][$count-1]) && 
										$choice_count == $quiz_answer[$lms_page-1][$count-1] ){
										echo 'checked ';
									}
									echo '/>' . $quiz_choice;
									echo '</div>';
								}
								break;
							case 'multiple':
								$choice_count = 0;
								foreach($question['quiz-choice'] as $quiz_choice){ $choice_count++;
									echo '<div class="gdlr-lms-quiz-choice">';
									echo '<input type="checkbox" value="' . $choice_count . '" name="question' . ($count-1) . '[]" ';
									if( !empty($quiz_answer[$lms_page-1][$count-1]) && 
										in_array($choice_count, $quiz_answer[$lms_page-1][$count-1]) ){
										echo 'checked ';
									}
									echo '/>' . $quiz_choice;
									echo '</div>';
								}
								break;
							case 'large':
							case 'small':
								echo '<textarea name="question' . ($count-1) . '">';
								if( !empty($quiz_answer[$lms_page-1][$count-1]) ){
									echo gdlr_lms_text_filter($quiz_answer[$lms_page-1][$count-1]);
								}
								echo '</textarea>';
								break;
						}
						echo '<div class="gdlr-lms-question-score" >';
						echo '<span class="gdlr-head">'; 
						echo empty($question['score'])? 1: $question['score'];
						echo '</span>';
						echo '<span class="gdlr-tail">' . esc_html__('Point(s)', 'gdlr-lms') . '</span>'; 
						echo '</div>'; // question-score
						
						echo '</div>'; // quiz-answer
						echo '</div>'; // question-wrapper
					}
				}
				echo '</div>'; // quiz-content-wrapper

				echo '<div class="gdlr-lms-course-pagination">';
				if( $lms_page > 1 ){
					echo '<a href="' . esc_url(add_query_arg(array('course_type'=>'quiz', 'course_page'=> $lms_page-1), get_permalink())) . '" class="gdlr-lms-button blue submit-quiz-form">';
					echo esc_html__('Previous Part', 'gdlr-lms');
					echo '</a>';
				}
				if( $lms_page < sizeof($quiz_options) ){
					echo '<a href="' . esc_url(add_query_arg(array('course_type'=>'quiz', 'course_page'=> $lms_page+1), get_permalink())) . '" class="gdlr-lms-button blue submit-quiz-form">';
					echo esc_html__('Next Part', 'gdlr-lms');
					echo '</a>';
				}
				if( $lms_page == sizeof($quiz_options) ){
					gdlr_lms_print_course_button(array(), array('finish-quiz'));
				}
				echo '</div>'; // pagination
				echo '</div>'; // course-content
				
				echo '<div class="clear"></div>';
				echo '</form>'; // course-single	
			}else{
				echo '<div class="gdlr-lms-denied-quiz-permission" ><p>';
				esc_html_e('You do not have a permission to access or retake this quiz.', 'gdlr-lms');
				echo '</p><p><a class="gdlr-lms-button cyan" href="' . get_permalink() . '" >';
				esc_html_e('Back to course', 'gdlr-lms');
				echo '</a></p></div>';
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