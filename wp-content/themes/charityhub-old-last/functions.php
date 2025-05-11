<?php
	/*	
	*	Goodlayers Function File
	*	---------------------------------------------------------------------
	*	This file include all of important function and features of the theme
	*	---------------------------------------------------------------------
	*/
	
	////// DO NOT REMOVE OR MODIFY THIS /////
	define('WP_THEME_KEY', 'goodlayers');  //
	/////////////////////////////////////////
	
	define('THEME_FULL_NAME', 'Charity Hub');
	define('THEME_SHORT_NAME', 'crth');
	define('THEME_SLUG', 'charityhub');
	
	define('AJAX_URL', admin_url('admin-ajax.php'));
	define('GDLR_PATH', get_template_directory_uri());
	define('GDLR_LOCAL_PATH', get_template_directory());
	
	if ( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ) {
		define('GDLR_HTTP', 'https://');
	}else{
		define('GDLR_HTTP', 'http://');
	}
	
	global $gdlr_gallery_id, $gdlr_lightbox_id, $gdlr_crop_video, $gdlr_excerpt_length, $gdlr_excerpt_read_more, $gdlr_spaces;
	$gdlr_gallery_id = 0;
	$gdlr_lightbox_id = 0;
	$gdlr_crop_video = false;
	$gdlr_excerpt_length = 55;
	$gdlr_excerpt_read_more = true;

	$gdlr_spaces = array(
		'top-wrapper' => '60px', 
		'bottom-wrapper'=>'40px', 
		'top-full-wrapper' => '0px', 
		'bottom-item' => '20px',
		'bottom-blog-item' => '0px',
		'bottom-divider-item' => '50px'
	);
	
	global $theme_option;
	$theme_option = get_option(THEME_SHORT_NAME . '_admin_option', array());
	$theme_option['content-width'] = 960;
	
	// include goodlayers framework
	include_once( 'framework/gdlr-framework.php' );
	
	//-------------------------- theme section ---------------------------//

	// create sidebar controller
	global $gdlr_sidebar_controller;
	$gdlr_sidebar_controller = new gdlr_sidebar_generator();	
	
	// create font controller
	if( empty($theme_option['upload-font']) ){ $theme_option['upload-font'] = ''; }
	global $gdlr_font_controller;
	$gdlr_font_controller = new gdlr_font_loader( json_decode($theme_option['upload-font'], true) );	
	
	// create navigation controller
	if( empty($theme_option['enable-goodlayers-navigation']) || $theme_option['enable-goodlayers-navigation'] != 'disable'){
		include_once( 'include/gdlr-navigation-menu.php');
	}	
	if( empty($theme_option['enable-goodlayers-mobile-navigation']) || $theme_option['enable-goodlayers-mobile-navigation'] != 'disable'){
		include_once( 'include/gdlr-responsive-menu.php');
	}
	
	// utility function
	include_once( 'include/function/gdlr-media.php');
	include_once( 'include/function/gdlr-utility.php');		

	// register function / filter / action
	include_once( 'functions-size.php');	
	include_once( 'include/gdlr-include-script.php');	
	include_once( 'include/function/gdlr-function-regist.php');	
	
	// create admin option
	include_once( 'include/gdlr-admin-option.php');
	include_once( 'include/gdlr-plugin-option.php');
	include_once( 'include/gdlr-font-controls.php');
	include_once( 'include/gdlr-social-icon.php');

	// create page options
	include_once( 'include/gdlr-page-options.php');
	include_once( 'include/gdlr-demo-page.php');
	include_once( 'include/gdlr-post-options.php');
	
	// create page builder
	include_once( 'include/gdlr-page-builder-option.php');
	include_once( 'include/function/gdlr-page-builder.php');
	
	include_once( 'include/function/gdlr-page-item.php');
	include_once( 'include/function/gdlr-blog-item.php');
	
	// widget
	include_once( 'include/widget/recent-comment.php');
	include_once( 'include/widget/recent-post-widget.php');
	include_once( 'include/widget/recent-cause-widget.php');	
	include_once( 'include/widget/popular-post-widget.php');
	include_once( 'include/widget/post-slider-widget.php');	
	include_once( 'include/widget/recent-port-widget.php');
	include_once( 'include/widget/recent-port-widget-2.php');
	include_once( 'include/widget/port-slider-widget.php');
	include_once( 'include/widget/twitter-widget.php');
	include_once( 'include/widget/flickr-widget.php');
	include_once( 'include/widget/video-widget.php');
	
	// plugin support
	include_once( 'plugins/paypal.php');
	if( !class_exists('Stripe\Stripe') ){
		include_once( 'plugins/stripe-payment.php');
		include_once( 'plugins/stripe/init.php');
	}
	include_once( 'plugins/wpml.php');
	include_once( 'plugins/layerslider.php' );
	include_once( 'plugins/woocommerce.php' );
	include_once( 'plugins/twitteroauth.php' );
	include_once( 'plugins/goodlayers-importer.php' );
	
	if( empty($theme_option['enable-plugin-recommendation']) || $theme_option['enable-plugin-recommendation'] == 'enable' ){
		include_once( 'include/plugin/gdlr-plugin-activation.php');
	}

	// init include script class
	if( !is_admin() ){ new gdlr_include_script(); }	
	
	// revision
	include_once(get_template_directory() . '/gdlr-revision.php');
	
	function gdlr_replace_oembed($content){
		return preg_replace_callback( '|("?https?://[^\s"<]+)|im', 'gdlr_content_oembed', $content );
	}
	function gdlr_content_oembed( $link ){
		if( substr($link[1], 0, 1) == '"' ){ 
			return $link[1]; 
		}

		if( preg_match('/youtube|youtu\.be|vimeo|spotify/', $link[1]) ){
			$html = wp_oembed_get($link[1]);

			if( $html ) return $html;
		}
		return $link[1];
	}
?>