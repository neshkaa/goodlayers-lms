<?php
	/*	
	*	Goodlayers Login Form File
	*/	
	
	if( !empty($_GET['login']) ){
		header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
		header("Pragma: no-cache"); // HTTP/1.0
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
	}
	
	add_filter( 'body_class', 'gdlr_login_body_class' );
	if( !function_exists('gdlr_login_body_class') ){
		function gdlr_login_body_class( $classes ) {
			if( !empty($_GET['login']) ){
				$classes[] = 'gdlr-custom-login-page';
			}else if( !empty($_GET['register']) ){
				$classes[] = 'gdlr-custom-register-page';
			}
			return $classes;
		}
	}
	
	// redirect to login page
	add_filter('template_include', 'gdlr_lms_register_login_template', 99999);
	function gdlr_lms_register_login_template($template){
		if( !is_user_logged_in() ){
			if( !empty($_GET['login']) ){
				$template = dirname(dirname( __FILE__ )) . '/login.php';
			}else if( !empty($_GET['register']) ){
				$template = dirname(dirname( __FILE__ )) . '/register.php';
			}
		}
		return $template;
	}
	
	// redirect to login page
	add_action('login_form_login', 'gdlr_lms_login_redirect');
	function gdlr_lms_login_redirect(){
		$args = array('login'=>'home');
		wp_redirect(add_query_arg($args, home_url()));
	}	
	
	// redirect to login failed page
	add_action('wp_login_failed', 'gdlr_lms_login_failed_redirect');
	function gdlr_lms_login_failed_redirect( $username = '' ){
		global $pagenow;
		if( 'wp-login.php' == $pagenow ){
			$args = array('login'=>'home');
			
			// check the post data
			if(!empty($username)){ 
				$args['status'] = 'login_incorrect';
			}
			
			if( !empty($_POST['home_url']) ){
				wp_redirect(add_query_arg($args, $_POST['home_url']));	
			}else{
				wp_redirect(add_query_arg($args, home_url()));	
			}
			exit();
		}
	}
	
	// redirect to lost password page
	add_action('login_form_lostpassword', 'gdlr_lms_login_lost_redirect');
	add_action('login_form_retrievepassword', 'gdlr_lms_login_lost_redirect');
	function gdlr_lms_login_lost_redirect( ){
		$args = array('login'=>'home', 'action'=>'lost_password');
		
		// check the post data
		if( !empty($_POST['user_login']) ){
			$errors = retrieve_password();
			
			if( !is_wp_error($errors) ){
				$args['status'] = 'forgot_password_confimation';
				unset($args['action']);
			}else{
				$args['status'] = $errors->get_error_code();
			} 
		}		
		
		wp_redirect(add_query_arg($args, home_url()));	
		exit();
	}
	
	// redirect to retrieve password page
	add_action('login_form_rp', 'gdlr_lms_login_resetpass_redirect');
	add_action('login_form_resetpass', 'gdlr_lms_login_resetpass_redirect');
	function gdlr_lms_login_resetpass_redirect( ){	
		$args = array('login'=>'home', 'action'=>'reset_pass');
		
		if( !empty($_GET['key']) ){
			$args['key'] = rawurlencode($_GET['key']);
		}
		if( !empty($_GET['login']) ){
			$args['login'] = rawurlencode($_GET['login']);
		}
		
		wp_redirect(add_query_arg($args, home_url()));	
		exit();
	}	
?>