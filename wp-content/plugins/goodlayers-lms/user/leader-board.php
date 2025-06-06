<h3 class="gdlr-lms-admin-head" ><?php esc_html_e('Leader Board :', 'gdlr-lms'); ?> <?php echo get_the_title($_GET['course_id']); ?></h3>
<table class="gdlr-lms-table gdlr-lms-leaderboard-table">
<tr>
	<th align="center" ><?php esc_html_e('Rank', 'gdlr-lms'); ?></th>
	<th class="gdlr-lms-left-align" ><?php esc_html_e('Name', 'gdlr-lms'); ?></th>
	<th align="center" ><?php esc_html_e('Score', 'gdlr-lms'); ?></th>
</tr>
<?php
	global $wpdb;
	
	$temp_sql  = "SELECT student_id, quiz_score FROM " . $wpdb->prefix . "gdlrquiz ";
	$temp_sql .= $wpdb->prepare("WHERE course_id = %d AND quiz_status = 'complete'", $_GET['course_id']);
	$results = $wpdb->get_results($temp_sql);	
	
	// list the student score to array
	$score_list = array();
	foreach($results as $result){ 
		$quiz_score = unserialize($result->quiz_score);
		$quiz_score = empty($quiz_score)? array(): $quiz_score;
		$score_summary = gdlr_lms_score_summary($quiz_score);
		$score_summary['student_id'] = $result->student_id;
		
		$score_list[] = $score_summary;
	}
	
	// sort the score
	$order = false;
	while(!$order){ $order = true;
		for($i=0; $i<sizeOf($score_list)-1; $i++){
			if($score_list[$i]['score'] < $score_list[$i+1]['score']){
				$temp = $score_list[$i];
				$score_list[$i] = $score_list[$i+1];
				$score_list[$i+1] = $temp;
				$order = false;
			}
		}
	}
	
	// print the score
	$count = 0;
	foreach($score_list as $score){ $count++;
		$author_id = $score['student_id'];

		echo '<tr>';
		echo '<td align="center" >' . $count . '</td>';
		echo '<td class="gdlr-lms-left-align" >';
		echo get_avatar($score['student_id'], 70);
		echo gdlr_lms_get_user_info($author_id);
		echo '</td>';
		echo '<td>' . $score['score'] . '/' . $score['from'] . '</td>';
		echo '</tr>';	
	}
?>
</table>