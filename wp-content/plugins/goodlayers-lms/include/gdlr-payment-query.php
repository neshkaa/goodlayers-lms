<?php
	/*	
	*	Goodlayers Payment Table Query File
	*/		
	
	// redirect to payment page
	add_filter('template_include', 'gdlr_lms_register_payment_template', 99999);
	function gdlr_lms_register_payment_template($template){
		if( !empty($_GET['payment-method']) && $_GET['payment-method'] == 'stripe' ){
			$template = dirname(dirname( __FILE__ )) . '/single-stripe.php';
		}else if( !empty($_GET['payment-method']) && $_GET['payment-method'] == 'paymill' ){
			$template = dirname(dirname( __FILE__ )) . '/single-paymill.php';
		}else if( !empty($_GET['payment-method']) && $_GET['payment-method'] == 'authorize' ){
			$template = dirname(dirname( __FILE__ )) . '/single-authorize.php';
		}else if( !empty($_GET['payment-method']) && $_GET['payment-method'] == 'braintree' ){
			$template = dirname(dirname( __FILE__ )) . '/single-braintree.php';
		}
		return $template;
	}	
	
	// if payment or booked record exists
	function gdlr_lms_payment_row_exists($course_id, $student_id){
		if( empty($course_id) || empty($student_id) ) return;
		global $wpdb;
	
		$sql  = 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'gdlrpayment ';
		$sql .= $wpdb->prepare('WHERE course_id = %d AND student_id = %d', $course_id, $student_id);

		return $wpdb->get_var($sql);
	}
	
	// return the row of specific course payment
	function gdlr_lms_get_payment_row($course_id, $student_id, $column = '*'){
		if( empty($course_id) || empty($student_id) ) return;
		
		global $wpdb;
		
		$sql  = 'SELECT ' . $column . ' FROM ' . $wpdb->prefix . 'gdlrpayment ';
		$sql .= $wpdb->prepare('WHERE course_id = %d AND student_id = %d', $course_id, $student_id);
		
		return $wpdb->get_row($sql);	
	}
	
	// return the row of specific quiz
	function gdlr_lms_get_quiz_row($quiz_id, $course_id, $student_id, $section_id = '', $column = '*'){	
		if( empty($course_id) || empty($student_id) ) return;
	
		global $wpdb;		
		
		$sql  = 'SELECT ' . $column . ' FROM ' . $wpdb->prefix . 'gdlrquiz ';
		$sql .= $wpdb->prepare('WHERE quiz_id = %d AND student_id = %d AND course_id = %d ', $quiz_id, $student_id, $course_id);
		if( empty($section_id) ){
			$sql .= 'AND section_quiz IS NULL';
		}else{
			$sql .= $wpdb->prepare('AND section_quiz = %d ', $section_id);
		}

		return $wpdb->get_row($sql);	
	}
?>