<h3 class="gdlr-lms-admin-head" ><?php esc_html_e('View quizes scores (only online course)', 'gdlr-lms'); ?></h3>
<table class="gdlr-lms-table">
<tr>
	<th><?php esc_html_e('Student', 'gdlr-lms'); ?></th>
	<th align="center" ><?php esc_html_e('Part', 'gdlr-lms'); ?></th>
	<th align="center" ><?php esc_html_e('Score', 'gdlr-lms'); ?></th>
</tr>
<?php
	global $wpdb, $current_user;
	
	$temp_sql  = "SELECT course_id, quiz_id, quiz_score, section_quiz FROM " . $wpdb->prefix . "gdlrquiz ";
	$temp_sql .= $wpdb->prepare("WHERE student_id = %d", $current_user->ID);
	
	$results = $wpdb->get_results($temp_sql);	
	foreach($results as $result){
		$course_val = gdlr_lms_decode_preventslashes(get_post_meta($result->course_id, 'gdlr-lms-course-settings', true));
		$course_options = empty($course_val)? array(): json_decode($course_val, true);		
		$course_options['author_id'] = get_post_field('post_author', $result->course_id);
		
		$quiz_val = gdlr_lms_decode_preventslashes(get_post_meta($result->quiz_id, 'gdlr-lms-content-settings', true));
		$quiz_options = empty($quiz_val)? array(): json_decode($quiz_val, true);		
	
		$quiz_score = unserialize($result->quiz_score);
		$quiz_score = empty($quiz_score)? array(): $quiz_score;
		$score_summary = gdlr_lms_score_part_summary($quiz_score);	
	
		echo '<tr class="with-divider">';
		echo '<td><a href="' . get_permalink($result->course_id) . '" >' . get_the_title($result->course_id) . '</a>';
		if( !empty($result->section_quiz) ){
			echo '<span class="gdlr-quiz-section-text" >(' . __('Quiz section', 'gdlr-lms') . ' ' .  $result->section_quiz . ')</span>';
		}
		
		gdlr_lms_print_course_info($course_options, array('instructor'));
		
		echo '<a data-title="' . esc_attr(__('After viewing an answer, you\'ll not be able to retake the quiz anymore.', 'gdlr-lms')) . '" ';
		echo 'data-sub-title="' . esc_attr(__('* only for retakeable quiz.', 'gdlr-lms')) . '" ';
		echo 'data-yes="' . esc_attr(__('Confirm', 'gdlr-lms')) . '" data-no="' . esc_attr(__('Cancel', 'gdlr-lms')) . '" ';
		echo 'href="' . esc_url(add_query_arg(array('type'=>'view-answer', 'quiz_id'=>$result->quiz_id, 'course_id'=>$result->course_id))) . '" ';
		echo 'class="gdlr-lms-view-correct-answer" >' . __('View Correct Answers', 'gdlr-lms') . '</a>';
		echo '<br>';
		echo '<a class="gdlr-leader-board-link" href="' . esc_url(add_query_arg(array('course_id'=> $result->course_id, 'type'=>'leader-board'))) . '" >' . __('View Leader Board', 'gdlr-lms') . '</a>';
		echo '</td>';
		
		echo '<td>';
		for($i=1; $i<=sizeof($quiz_options); $i++){ 
			echo '<div class="lms-part-line">' . $i . '</div>';
		}
		echo '<div class="lms-part-line">' . __('Total', 'gdlr-lms') . '</div>';
		echo '</td>';
		
		echo '<td>';
		for($i=0; $i<sizeof($quiz_options); $i++){ 
			echo '<div class="lms-part-line">';
			if( empty($score_summary[$i]) ){
				echo esc_html__('Pending' ,'gdlr-lms');
			}else{
				echo gdlr_lms_text_filter($score_summary[$i]['score'] . '/' . $score_summary[$i]['from']);	
			}
			echo '</div>';
		}
		$score_summary = gdlr_lms_score_summary($quiz_score);
		echo gdlr_lms_text_filter($score_summary['score'] . '/' . $score_summary['from']);	
		echo '</td>';		
		echo '</tr>';
	}
?>
</table>