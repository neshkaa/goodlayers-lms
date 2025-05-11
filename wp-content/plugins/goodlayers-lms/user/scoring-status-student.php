<h3 class="gdlr-lms-admin-head" ><?php esc_html_e('Scoring status', 'gdlr-lms'); ?></h3>
<table class="gdlr-lms-table">
<tr>
	<th><?php esc_html_e('Student', 'gdlr-lms'); ?></th>
	<th align="center" ><?php esc_html_e('Manual Check', 'gdlr-lms'); ?></th>
	<th align="center" ><?php esc_html_e('Score', 'gdlr-lms'); ?></th>
</tr>
<?php 
	global $wpdb;
	
	$temp_sql  = "SELECT student_id, quiz_score, quiz_status, section_quiz FROM " . $wpdb->prefix . "gdlrquiz ";
	$temp_sql .= $wpdb->prepare("WHERE course_id = %d ", gdlr_lms_escape_sql_number($_GET['course_id']));

	$results = $wpdb->get_results($temp_sql);
	foreach($results as $result){
		$user_id = $result->student_id;

		$quiz_score = unserialize($result->quiz_score);
		$quiz_score = empty($quiz_score)? array(): $quiz_score;
		$score_summary = gdlr_lms_score_summary($quiz_score);
		
		echo '<tr>';
		echo '<td><a href="' . esc_url(add_query_arg(array('type'=>'scoring-status-part', 'course_id'=>$_GET['course_id'], 'quiz_id'=>$_GET['quiz_id'], 'student_id'=>$result->student_id))) . '" >';
		echo gdlr_lms_get_user_info($user_id);
		if( !empty($result->section_quiz) ){
			echo '<span class="gdlr-quiz-section-text" >(' . __('Quiz section', 'gdlr-lms') . ' ' .  $result->section_quiz . ')</span>';
		}
		echo '</a></td>';
		
		echo '<td>';
		echo ($result->quiz_status != 'complete')? __('Pending', 'gdlr-lms'): __('Complete', 'gdlr-lms');
		echo '</td>';
		
		echo '<td>' . $score_summary['score'] . '/' . $score_summary['from'] . '</td>';
		echo '</tr>';		
	}
?>
</table>