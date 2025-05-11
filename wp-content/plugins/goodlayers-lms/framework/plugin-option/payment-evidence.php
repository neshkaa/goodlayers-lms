<?php
	/*	
	*	Goodlayers Payment Evidence File
	*/
	
	function gdlr_lms_payment_evidence_option(){
		global $wpdb;

		$temp_sql  = "SELECT id, course_id, student_id, payment_info, payment_date, attachment FROM " . $wpdb->prefix . "gdlrpayment ";
		$temp_sql .= "WHERE payment_status = 'submitted'";

		$results = $wpdb->get_results($temp_sql);	
?>
<div class="wrap">
<h2><?php esc_html_e('Evidence Of Payment', 'gdlr-lms'); ?></h2>
<?php
	if( empty($results) ){
		echo '<div style="margin-top: 20px;">' . esc_html__('No record found', 'gdlr-lms') . '</div>';
		return;
	}
?>
<table class="gdlr-lms-table">
<tr>
	<th><?php esc_html_e('Name', 'gdlr-lms'); ?></th>
	<th><?php esc_html_e('Course', 'gdlr-lms'); ?></th>
	<th><?php esc_html_e('Code', 'gdlr-lms'); ?></th>
	<th><?php esc_html_e('Submitted Date', 'gdlr-lms'); ?></th>
	<th><?php esc_html_e('Total Price', 'gdlr-lms'); ?></th>
	<th><?php esc_html_e('View Attachment', 'gdlr-lms'); ?></th>
</tr>
<?php 
	foreach($results as $result){
		$payment_info = unserialize($result->payment_info);
		$payment_info['code'] = empty($payment_info['code'])? '': $payment_info['code'];
		$student_info = get_userdata($result->student_id);

		echo '<tr>';
		echo '<td class="evidence-of-payment-name">';
		echo gdlr_lms_text_filter($student_info->first_name . ' ' . $student_info->last_name);
		echo '<div class="evidence-of-payment-name-hover" >';
		foreach($payment_info as $key => $value){
			echo '<div class="evidence-of-payment-info">';
			echo '<span class="head">' . $key . ' :</span>';
			if( $key == 'price' ){
				echo '<span class="tail">' . gdlr_lms_money_format($value) . '</span>';
			}else{
				echo '<span class="tail">' . $value . '</span>';
			}
			echo '</div>';
		}
		echo '</div>'; // evd-of-payment-name-hover
		echo '</td>'; // evd-of-payment-name
		
		echo '<td>' . $result->course_id . '</td>';
		echo '<td>' . $payment_info['code'] . '</td>';
		echo '<td>' . gdlr_lms_date_format($result->payment_date) . '</td>';
		echo '<td>' . gdlr_lms_money_format($payment_info['price']) . '</td>';
		
		echo '<td>';
		echo '<a data-rel="gdlr-lms-lightbox" data-lb-open="evidence-validation" >' . esc_html__('View Attachment', 'gdlr-lms') . '</a>';
		gdlr_lms_lightbox_evidence_form($result, $payment_info);
		echo '</td>';
		echo '</tr>';
	} 
?>
</table>
</div>
<?php
	}
	
	function gdlr_lms_lightbox_evidence_form($result, $payment_info){
		$attachment = unserialize($result->attachment);
?>
<div class="gdlr-lms-lightbox-container evidence-validation">		
	<div class="gdlr-lms-evidence">		
		<?php if( !empty($attachment['url']) ){ ?>
		<a href="<?php echo esc_attr($attachment['url']); ?>" target="_blank">
			<img src="<?php echo esc_attr($attachment['url']); ?>" alt="" />
		</a>
		<?php }else{
			echo '<strong style="font-size: 16px; color: #fff;">';
			esc_html_e('No image submitted', 'gdlr-lms');
			echo '</strong>';
		} ?>
	</div>
	<div class="gdlr-lms-evidence-confirmation">
		<div class="gdlr-lms-half-left">
			<?php esc_html_e('Correct! Send verification code to student\'s email', 'gdlr-lms'); ?>
			<div class="clear"></div>
			<a class="gdlr-lms-button blue" data-email="<?php echo esc_attr($payment_info['email']); ?>" data-code="<?php echo esc_attr($payment_info['code']); ?>" data-ajax="<?php echo admin_url('admin-ajax.php'); ?>" data-action="gdlr_lms_confirm_evidence" data-invoice="<?php echo esc_attr($result->id); ?>" data-value="true"><?php 
				esc_html_e('Do it!', 'gdlr-lms'); 
			?></a>
		</div>
		<div class="gdlr-lms-half-right">
			<?php esc_html_e('Wrong! Remove this record and warn student via email', 'gdlr-lms'); ?>
			<div class="clear"></div>
			<a class="gdlr-lms-button red" data-email="<?php echo esc_attr($payment_info['email']); ?>" data-code="<?php echo esc_attr($payment_info['code']); ?>" data-ajax="<?php echo admin_url('admin-ajax.php'); ?>" data-action="gdlr_lms_confirm_evidence" data-invoice="<?php echo esc_attr($result->id); ?>" data-value="false"><?php 
				esc_html_e('Do it!', 'gdlr-lms'); 
			?></a>
		</div>
		<div class="clear"></div>
	</div>
</div>
<?php
	}
	
	add_action( 'wp_ajax_gdlr_lms_confirm_evidence', 'gdlr_lms_confirm_evidence' );
	function gdlr_lms_confirm_evidence(){
		$ret = array();
		
		if( !empty($_POST['value']) ){
			global $wpdb, $lms_paypal;
			
			// remove attachment
			$sql = $wpdb->prepare('SELECT attachment FROM ' . $wpdb->prefix . 'gdlrpayment WHERE id=%d', $_POST['invoice']);
			$current_row = $wpdb->get_row($sql);
			$attachment = unserialize($current_row->attachment);
			if( !empty($attachment['file']) && file_exists($attachment['file']) ){
				unlink($attachment['file']);
			}
			
			// update value
			if( $_POST['value'] == "true" ){ 
				$wpdb->update( $wpdb->prefix . 'gdlrpayment', 
					array('payment_status'=>'paid'), array('id'=>$_POST['invoice']), 
					array('%s'), array('%d')
				);
				
				gdlr_lms_mail($_POST['email'], 
					esc_html__('Evidence Submission Accept', 'gdlr-lms'), 
					esc_html__('Your verification code is', 'gdlr-lms') . ' ' . $_POST['code']);
			}else{
				$wpdb->update($wpdb->prefix . 'gdlrpayment', 
					array('payment_status'=>'pending', 'payment_date'=>current_time('Y-m-d')), array('id'=>$_POST['invoice']), 
					array('%s', '%s'), array('%d')
				);		

				gdlr_lms_mail($_POST['email'], 
					esc_html__('Evidence Submission Reject', 'gdlr-lms'), 
					esc_html__('Please submit the payment evidence again. Thank you.', 'gdlr-lms'));
			}
			
			$ret['status'] = 'success';
		}else{
			$ret['status'] = 'failed';
			$ret['message'] = _('Submission Failed, please refresh the page and try again.', 'gdlr-lms');
		}
	
		die(json_encode($ret));
	}
?>