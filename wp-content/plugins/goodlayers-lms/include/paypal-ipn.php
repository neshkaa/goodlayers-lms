<?php
	if( isset($_GET['paypal']) ){
		global $lms_paypal;

		$debug = array();
		$debug['date'] = current_time('mysql');

		// STEP 1: read POST data
		$raw_post_data = file_get_contents('php://input');
		$raw_post_array = explode('&', $raw_post_data);
		$myPost = array();
		foreach ($raw_post_array as $keyval) {
		  $keyval = explode ('=', $keyval);
		  if (count($keyval) == 2)
			 $myPost[$keyval[0]] = urldecode($keyval[1]);
		}
		
		// read the IPN message sent from PayPal and prepend 'cmd=_notify-validate'
		$req = 'cmd=_notify-validate';
		if(function_exists('get_magic_quotes_gpc')) {
		   $get_magic_quotes_exists = true;
		} 
		foreach ($myPost as $key => $value) {        
		   if($get_magic_quotes_exists == true && get_magic_quotes_gpc() == 1) { 
				$value = urlencode(stripslashes($value)); 
		   } else {
				$value = urlencode($value);
		   }
		   $req .= "&$key=$value";
		}
		 
		$debug['action-url'] = $lms_paypal['url'];
		$debug['action-url'] = str_replace('www.paypal', 'ipnpb.paypal', $debug['action-url']);
		$debug['action-url'] = str_replace('sandbox.paypal', 'ipnpb.sandbox.paypal', $debug['action-url']);
		$debug['step'] = 'prestep'; 
		update_option('paypal_debug', $debug);
		 
		// Step 2: POST IPN data back to PayPal to validate
		$ch = curl_init($debug['action-url']);
		curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($ch, CURLOPT_FORBID_REUSE, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Connection: Close', 'User-Agent: goodlayers'));

		if( !($res = curl_exec($ch)) ) {	
			$debug['step'] = 'error';
			$debug['error'] = curl_error($ch);
			update_option('paypal_debug', $debug);
			curl_close($ch);
			exit;
		}
		curl_close($ch);

		$debug['step'] = 'verifying';
		$debug['res'] = $res;
		update_option('paypal_debug', $debug);

		// inspect IPN validation result and act accordingly
		if( strpos($res,'VERIFIED') !== false ) {
			$myPost['invoice'] = substr($myPost['invoice'], 8);
			$payment_info = array( 'payment-method' => 'paypal' );
			if( !empty($_POST['txn_id']) ){
				$payment_info['txn_id'] = $_POST['txn_id'];
			}
						
			global $wpdb;
			$wpdb->update( $wpdb->prefix . 'gdlrpayment', 
				array('payment_status'=>'paid', 'attachment'=>serialize($raw_post_array), 'payment_date'=>current_time('mysql')), 
				array('id'=>$myPost['invoice']), 
				array('%s', '%s', '%s'), 
				array('%d')
			);			
			
			$temp_sql  = "SELECT payment_info FROM " . $wpdb->prefix . "gdlrpayment ";
			$temp_sql .= $wpdb->prepare("WHERE id = %d", $myPost['invoice']);	
			$result = $wpdb->get_row($temp_sql);
			
			$payment_info = unserialize($result->payment_info);

			gdlr_lms_mail($payment_info['email'], 
				esc_html__('Paypal Payment Received', 'gdlr-lms'), 
				esc_html__('Your verification code is', 'gdlr-lms') . ' ' . $payment_info['code']);
		}
	}else if( isset($_GET['paypal_print']) ){
		print_r(get_option('gdlr_paypal', array()));
		die();
	}else if( isset($_GET['paypal_debug']) ){
		print_r(get_option('paypal_debug', 'nothing'));
		die();
	}else if( isset($_GET['paypal_clear']) ){
		delete_option('gdlr_paypal');
		echo 'Option Deleted';
		die();
	}

?>