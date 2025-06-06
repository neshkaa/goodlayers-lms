<h3 class="gdlr-lms-admin-head" ><?php esc_html_e('Booked Courses', 'gdlr-lms'); ?></h3>
<table class="gdlr-lms-table">
<tr>
	<th><?php esc_html_e('Course Name', 'gdlr-lms'); ?></th>
	<th align="center" ><?php esc_html_e('Status', 'gdlr-lms'); ?></th>
	<th align="center" ><?php esc_html_e('Action', 'gdlr-lms'); ?></th>
</tr>
<?php 
	global $wpdb, $gdlr_lms_option, $current_user;

	$temp_sql  = "SELECT id, course_id, payment_status, payment_info FROM " . $wpdb->prefix . "gdlrpayment ";
	$temp_sql .= $wpdb->prepare("WHERE student_id = %d ", $current_user->data->ID);
	$temp_sql .= "AND ( payment_status = 'pending' OR payment_status = 'submitted' )";

	$results = $wpdb->get_results($temp_sql);
	foreach($results as $result){
		$course_val = gdlr_lms_decode_preventslashes(get_post_meta($result->course_id, 'gdlr-lms-course-settings', true));
		$course_options = empty($course_val)? array(): json_decode($course_val, true);	
		$fix_val = unserialize($result->payment_info);
		$fix_val['id'] =  $result->id;
		$fix_val['title'] =  get_the_title($result->course_id);
		$fix_val['course-id'] = $result->course_id;
		
		echo '<tr>';
		echo '<td>';
		echo '<a href="' . get_permalink($result->course_id) . '" >' . $fix_val['title'] . '</a> ';
		echo '<a href="#" title="' . esc_attr(__('Cancel Booking', 'gdlr-lms')) . '" class="gdlr-lms-cancel-booking" ';
		echo 'data-title="' . esc_attr(__('Are you sure you want to cancel booking this course', 'gdlr-lms')) . '" ';
		echo 'data-yes="' . esc_attr(__('Confirm', 'gdlr-lms')) . '" data-no="' . esc_attr(__('Cancel', 'gdlr-lms')) . '" ';
		echo 'data-id="' . $result->id . '" data-ajax="' . admin_url('admin-ajax.php') . '" >';
		echo __('(Cancel)', 'gdlr-lms') . '</a>';
		echo '</td>';
		
		echo '<td class="gdlr-' . $result->payment_status . '">'; 
		if( $result->payment_status == 'pending' ){
			esc_html_e('Pending', 'gdlr-lms');
		}else if( $result->payment_status == 'submitted' ){
			esc_html_e('Submitted', 'gdlr-lms');
		}else if( $result->payment_status == 'paid' ){
			esc_html_e('Paid', 'gdlr-lms');
		}else{
			echo gdlr_lms_text_filter($result->payment_status);
		}
		echo '</td>';
		
		echo '<td>';
		if( $result->payment_status == 'pending' ){
			if(empty($gdlr_lms_option['payment-method']) || $gdlr_lms_option['payment-method'] == 'both'){
				echo '<a class="gdlr-submit-payment" data-rel="gdlr-lms-lightbox2" data-lb-open="payment-option-form" >';
				echo __('Submit Payment', 'gdlr-lms');
				echo '</a>';
				
				echo '<div class="gdlr-lms-lightbox-container-wrapper">';
				gdlr_lms_payment_option_form();
				gdlr_lms_purchase_lightbox_form($course_options, 'buy', $fix_val, 'payment-option-form');
				gdlr_lms_evidence_lightbox_form($fix_val, 'payment-option-form');
				echo '</div>';
			}else if($gdlr_lms_option['payment-method'] == 'paypal'){
				echo '<a class="gdlr-submit-payment" data-rel="gdlr-lms-lightbox" data-lb-open="buy-form" >';
				echo __('Submit Payment', 'gdlr-lms');
				echo '</a>';
				
				gdlr_lms_purchase_lightbox_form($course_options, 'buy', $fix_val);
			}else if($gdlr_lms_option['payment-method'] == 'receipt'){
				echo '<a class="gdlr-submit-payment" data-rel="gdlr-lms-lightbox" data-lb-open="evidence-form" >';
				echo __('Submit Payment', 'gdlr-lms');
				echo '</a>';

				gdlr_lms_evidence_lightbox_form($fix_val);		
			}
		}else{
			echo '-';
		}
		echo '</td>';
		echo '</tr>';
	}
?>
</table>
