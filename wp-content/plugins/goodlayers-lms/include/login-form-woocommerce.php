<?php
	/*	
	*	Goodlayers Login Form File
	*/	
	
	add_filter( 'body_class', 'gdlr_login_body_class' );
	if( !function_exists('gdlr_login_body_class') ){
		function gdlr_login_body_class( $classes ) {
			if( !empty($_GET['register']) ){
				$classes[] = 'gdlr-custom-register-page';
			}
			return $classes;
		}
	}	
	
	// redirect to login page
	add_filter('template_include', 'gdlr_lms_register_login_template', 9999);
	function gdlr_lms_register_login_template($template){
		if( !is_user_logged_in() ){
			if( !empty($_GET['register']) ){
				$template = dirname(dirname( __FILE__ )) . '/register.php';
			}
		}
		return $template;
	}
	
?>