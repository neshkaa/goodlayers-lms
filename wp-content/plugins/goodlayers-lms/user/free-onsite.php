<h3 class="gdlr-lms-admin-head" ><?php esc_html_e('Free Onsite Courses', 'gdlr-lms'); ?></h3>
<table class="gdlr-lms-table">
<tr>
	<th><?php esc_html_e('Course Name', 'gdlr-lms'); ?></th>
	<th align="center" ><?php esc_html_e('Status', 'gdlr-lms'); ?></th>
	<th align="center" ><?php esc_html_e('Action', 'gdlr-lms'); ?></th>
</tr>
<?php 
	global $wpdb, $gdlr_lms_option, $current_user;

	$temp_sql  = "SELECT id, course_id, payment_status, payment_info, attendance FROM " . $wpdb->prefix . "gdlrpayment ";
	$temp_sql .= $wpdb->prepare("WHERE student_id = %d ", $current_user->data->ID);
	$temp_sql .= "AND payment_status = 'reserved'";

	$results = $wpdb->get_results($temp_sql);
	foreach($results as $result){
		$course_val = gdlr_lms_decode_preventslashes(get_post_meta($result->course_id, 'gdlr-lms-course-settings', true));
		$course_options = empty($course_val)? array(): json_decode($course_val, true);	
		$fix_val = unserialize($result->payment_info);
		$fix_val['id'] =  $result->id;
		$fix_val['title'] =  get_the_title($result->course_id);
		
		echo '<tr>';
		echo '<td>';
		echo '<a href="' . get_permalink($result->course_id) . '" >' . $fix_val['title'] . '</a> ';
		echo '</td>';

		echo '<td class="gdlr-' . $result->payment_status . '">';
		if( strtotime($course_options['start-date']) <= strtotime(current_time('Y-m-d')) ){
			esc_html_e('attended', 'gdlr-hotel');
		}else if( $result->payment_status == 'reserved' ){
			esc_html_e('reserved', 'gdlr-hotel');
		}else{
			echo gdlr_lms_text_filter($result->payment_status);
		}
		echo '</td>';
		
		echo '<td>';
		echo '<a href="#" title="' . esc_attr(__('Cancel Booking', 'gdlr-lms')) . '" class="gdlr-lms-cancel-booking" ';
		echo 'data-title="' . esc_attr(__('Are you sure you want to cancel booking this course', 'gdlr-lms')) . '" ';
		echo 'data-yes="' . esc_attr(__('Confirm', 'gdlr-lms')) . '" data-no="' . esc_attr(__('Cancel', 'gdlr-lms')) . '" ';
		echo 'data-id="' . $result->id . '" data-ajax="' . admin_url('admin-ajax.php') . '" >';
		echo __('Cancel Course', 'gdlr-lms') . '</a>';
		echo '</td>';
		echo '</tr>';
	}
?>
</table>
