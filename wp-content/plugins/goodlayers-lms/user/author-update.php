<?php
	$current_user = wp_get_current_user();
	$error = array();
	$success = array(); // Added success array to store success messages

// Email verification function 
function gdlr_lms_verify_email($user_id, $token) {
    $stored_token = get_user_meta($user_id, 'email_verification_token', true);
    $expiry = get_user_meta($user_id, 'email_verification_expiry', true);
    $pending_email = get_user_meta($user_id, 'pending_email', true);
    
    if (empty($stored_token) || empty($expiry) || empty($pending_email)) {
        return array(
            'success' => false,
            'message' => __('Invalid verification request.', 'gdlr-lms')
        );
    }
    
    if (time() > $expiry) {
        return array(
            'success' => false,
            'message' => __('Verification link has expired. Please request a new one.', 'gdlr-lms')
        );
    }
    
    if ($token !== $stored_token) {
        return array(
            'success' => false,
            'message' => __('Invalid verification token.', 'gdlr-lms')
        );
    }
    
    // Update the user's email
    $result = wp_update_user(array(
        'ID' => $user_id,
        'user_email' => $pending_email
    ));
    
    if (!is_wp_error($result)) {
    // Email updated successfully, clean up meta data
    delete_user_meta($user_id, 'email_verification_token');
    delete_user_meta($user_id, 'email_verification_expiry');
    delete_user_meta($user_id, 'pending_email');
    
    return array(
        'success' => true,
        'message' => __('Your email has been successfully verified and updated!', 'gdlr-lms')
    );
} else {
    return array(
        'success' => false,
        'message' => __('Error updating email. Please try again or contact support.', 'gdlr-lms')
    );
}
}

	if( $_SERVER['REQUEST_METHOD'] == 'POST' && !empty($_POST['action']) ){
	
		// pasword changing page
		if($_POST['action'] == 'change-password'){
			if( empty($_POST['old-pass']) || empty($_POST['new-pass']) || empty($_POST['repeat-pass']) ){
				$error[] = __('Please enter all required fields.', 'gdlr-lms');
			}else if( $_POST['new-pass'] != $_POST['repeat-pass'] ){
				$error[] = __('New password and password confirmation do not match.', 'gdlr-lms');
			}else if( !wp_check_password($_POST['old-pass'], $current_user->data->user_pass, $current_user->data->ID) ){
				$error[] = __('The password you typed is incorrect.', 'gdlr-lms');
			}else{
				wp_update_user(array( 
					'ID' => $current_user->ID, 
					'user_pass' => esc_attr($_POST['new-pass']) 
				));
				
				$success[] = __('Password is changed', 'gdlr-lms');
			}
			
		// edit profile page
		}else if($_POST['action'] == 'edit-profile') {
			// Modified validation to match the fields in the form
			if( empty($_POST['email']) || empty($_POST['first-name']) || empty($_POST['last-name']) || empty($_POST['city']) ){
				$error[] = __('Please enter all required fields.', 'gdlr-lms');
			}
			
			if( $current_user->user_email != $_POST['email'] && email_exists($_POST['email']) ){
				$error[] = __('Email already exists, Please try again with new email address.', 'gdlr-lms');
			}
			
			if( empty($error) ){
				// Only update email if not in verification process
				if(!isset($_POST['email_verification_pending'])) {
					wp_update_user(array(
						'ID' => $current_user->ID, 
						'user_email' => esc_attr($_POST['email'])
					));
				}
				
				// Update user meta fields based on the form
				if( !empty($_POST['first-name']) ){
					update_user_meta($current_user->ID, 'first_name', esc_attr($_POST['first-name']));
				}
				if( !empty($_POST['last-name']) ){
					update_user_meta($current_user->ID, 'last_name', esc_attr($_POST['last-name']));
				}
				if( !empty($_POST['phone']) ){
					// Only update if not in verification process
					if(!isset($_POST['phone_verification_pending'])) {
						// Store country code with phone
						$country_code = !empty($_POST['country_code']) ? $_POST['country_code'] : '+359';
						update_user_meta($current_user->ID, 'phone', esc_attr($_POST['phone']));
						update_user_meta($current_user->ID, 'country_code', esc_attr($country_code));
					}
				}
				if( !empty($_POST['facebook']) ){
					update_user_meta($current_user->ID, 'facebook', esc_attr($_POST['facebook']));
				}
				if( !empty($_POST['city']) ){
					update_user_meta($current_user->ID, 'city', esc_attr($_POST['city']));
				}
				
				// instructor / admin section
				if( !empty($_POST['social-network']) ){
					update_user_meta($current_user->ID, 'social-network', $_POST['social-network']);
				}
				if( !empty($_POST['author-biography']) ){
					update_user_meta($current_user->ID, 'author-biography', $_POST['author-biography']);
				}
				if( !empty($_POST['location']) ){
					update_user_meta($current_user->ID, 'location', esc_attr($_POST['location']));
				}
				if( !empty($_POST['position']) ){
					update_user_meta($current_user->ID, 'position', esc_attr($_POST['position']));
				}
				if( !empty($_POST['current-work']) ){
					update_user_meta($current_user->ID, 'current-work', esc_attr($_POST['current-work']));
				}
				if( !empty($_POST['past-work']) ){
					update_user_meta($current_user->ID, 'past-work', esc_attr($_POST['past-work']));
				}
				if( !empty($_POST['specialist']) ){
					update_user_meta($current_user->ID, 'specialist', esc_attr($_POST['specialist']));
				}
				if( !empty($_POST['experience']) ){
					update_user_meta($current_user->ID, 'experience', esc_attr($_POST['experience']));
				}
				
				// Handle email/phone verification actions
				if(isset($_POST['verify_email']) && !empty($_POST['new_email'])) {
					// Code to send email verification
					// Store pending email in user meta
					update_user_meta($current_user->ID, 'pending_email', esc_attr($_POST['new_email']));
					update_user_meta($current_user->ID, 'email_verification_code', wp_generate_password(6, false, false));
					$success[] = __('Verification email sent. Please check your inbox.', 'gdlr-lms');
				}
				
				if(isset($_POST['verify_phone']) && !empty($_POST['new_phone'])) {
					// Code to send SMS verification
					$country_code = !empty($_POST['new_country_code']) ? $_POST['new_country_code'] : '+359';
					update_user_meta($current_user->ID, 'pending_phone', esc_attr($_POST['new_phone']));
					update_user_meta($current_user->ID, 'pending_country_code', esc_attr($country_code));
					update_user_meta($current_user->ID, 'phone_verification_code', wp_generate_password(6, false, false));
					$success[] = __('Verification SMS sent. Please check your phone.', 'gdlr-lms');
				}
				
				// Handle OTP verification
				if(isset($_POST['verify_otp']) && !empty($_POST['otp_code'])) {
					$stored_code = get_user_meta($current_user->ID, 'phone_verification_code', true);
					if($_POST['otp_code'] == $stored_code) {
						$pending_phone = get_user_meta($current_user->ID, 'pending_phone', true);
						$pending_country_code = get_user_meta($current_user->ID, 'pending_country_code', true);
						if($pending_phone) {
							update_user_meta($current_user->ID, 'phone', $pending_phone);
							update_user_meta($current_user->ID, 'country_code', $pending_country_code);
							update_user_meta($current_user->ID, 'phone_verified', 'yes');
							delete_user_meta($current_user->ID, 'pending_phone');
							delete_user_meta($current_user->ID, 'pending_country_code');
							delete_user_meta($current_user->ID, 'phone_verification_code');
							$success[] = __('Phone number verified and updated successfully.', 'gdlr-lms');
						}
					} else {
						$error[] = __('Invalid verification code. Please try again.', 'gdlr-lms');
					}
				}
				
				// image uploaded
				if( !empty($_FILES['attachment']['size']) ){
					if(!function_exists( 'media_handle_upload' )){
						require_once( ABSPATH . 'wp-admin/includes/image.php' );
						require_once( ABSPATH . 'wp-admin/includes/file.php' );
						require_once( ABSPATH . 'wp-admin/includes/media.php' );
					}
					$profile_image_id = media_handle_upload('attachment', 0);
					
					if( !empty($profile_image_id) ){
						update_user_meta($current_user->ID, 'author-image', $profile_image_id);
					}
					
					$new_url = esc_url(add_query_arg('type', 'profile'));
					wp_redirect($new_url, 303);
				}
				
				if(empty($error)) {
					$success[] = __('Profile is updated', 'gdlr-lms');
				}
			}
			
		// evidence submission page
		}else if($_POST['action'] == 'submit-evidence'){
			
			if( empty($_POST['invoice']) ){
				$error[] = __('Submission failed, please try again.', 'gdlr-lms');
			}else{
			
				if(!function_exists( 'wp_handle_upload' )) require_once(ABSPATH . 'wp-admin/includes/file.php');
				
				if( !empty($_FILES['attachment']['size']) ){
					$uploadedfile = $_FILES['attachment'];
					$movefile = wp_handle_upload($uploadedfile,  array('test_form' => false));
				}else{
					$movefile = '';
				}
				
				if( empty($_FILES['attachment']['size']) || $movefile){
					global $wpdb;
					
					$sql = $wpdb->prepare('SELECT payment_info FROM ' . $wpdb->prefix . 'gdlrpayment WHERE id = %d', $_POST['invoice']);
					$current_row = $wpdb->get_row($sql);
					$payment_info = unserialize($current_row->payment_info);
					$payment_info['additional_note'] = $_POST['additional-note'];
					
					$wpdb->update( $wpdb->prefix . 'gdlrpayment', 
						array('attachment'=>serialize($movefile), 'payment_status'=>'submitted', 
							  'payment_date'=>current_time('mysql'), 'payment_info'=>serialize($payment_info)), 
						array('id'=>$_POST['invoice']), 
						array('%s', '%s', '%s', '%s'), 
						array('%d')
					);
					
					$success[] = __('Evidence Submitted', 'gdlr-lms');
				}else{
					$error[] = __('Submission failed, please try again.', 'gdlr-lms');
				}
			}
			
		// scoring status page
		}else if($_POST['action'] == 'scoring-status-part'){
			$quiz_val = gdlr_lms_decode_preventslashes(get_post_meta($_GET['quiz_id'], 'gdlr-lms-content-settings', true));
			$quiz_options = empty($quiz_val)? array(): json_decode($quiz_val, true);	

			if( !empty($_POST) ){
				global $wpdb;

				$sql  = 'SELECT id, quiz_score, section_quiz FROM ' . $wpdb->prefix . 'gdlrquiz ';
				$sql .= $wpdb->prepare('WHERE quiz_id = %d AND student_id = %d AND course_id = %d ', $_GET['quiz_id'], $_GET['student_id'], $_GET['course_id']);
				$current_row = $wpdb->get_row($sql);
				
				$quiz_score = unserialize($current_row->quiz_score);
				$quiz_score = empty($quiz_score)? array(): $quiz_score;
				
				$quiz_score[$_POST['pnum']] = array();
				foreach($_POST['score'] as $key => $value){
					$quiz_score[$_POST['pnum']][$key] = array(
						'score' => $value,
						'from' => $_POST['from'][$key]
					);
				}
				$quiz_status = (sizeof($quiz_score) == sizeof($quiz_options))? 'complete': 'pending';
				
				if( $quiz_status == 'complete' && empty($current_row->section_quiz) ){
					$course_val = gdlr_lms_decode_preventslashes(get_post_meta($_GET['course_id'], 'gdlr-lms-course-settings', true));
					$course_settings = empty($course_val)? array(): json_decode($course_val, true);		
					
					if(!empty($course_settings['enable-badge']) && $course_settings['enable-badge'] == 'enable'){
						gdlr_lms_add_badge($_GET['course_id'], gdlr_lms_score_summary($quiz_score), $course_settings['badge-percent'],
							$course_settings['badge-title'], $course_settings['badge-file'], $_GET['student_id']);
					}
					
					if(!empty($course_settings['enable-certificate']) && $course_settings['enable-certificate'] == 'enable'){
						gdlr_lms_add_certificate($_GET['course_id'], $course_settings['certificate-template'], 
							gdlr_lms_score_summary($quiz_score), $course_settings['certificate-percent'], $_GET['student_id']);
					}
					
				}
				
				$wpdb->update( $wpdb->prefix . 'gdlrquiz', 
						array('quiz_score'=>serialize($quiz_score), 'quiz_status'=>$quiz_status), 
						array('id'=>$current_row->id), 
						array('%s', '%s'), 
						array('%d')
				);
			}		
		}
	}
?>