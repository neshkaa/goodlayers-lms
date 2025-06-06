<?php
	/*	
	*	Goodlayers Plugin Option File
	*/
	include_once('theme-option/gdlr-admin-panel.php');
	include_once('theme-option/gdlr-admin-panel-html.php');
	
	// create admin menu
	add_action('admin_menu', 'gdlr_lms_add_admin_menu');
	function gdlr_lms_add_admin_menu(){
		// for admin option
		$page = add_menu_page( esc_html__('LMS Option', 'gdlr-lms'), esc_html__('LMS Option', 'gdlr-lms'), 
			'edit_theme_options', 'lms-main-option', 'gdlr_lms_main_option');
		add_action('admin_print_styles-' . $page, 'gdlr_lms_register_main_admin_option_style');	
		add_action('admin_print_scripts-' . $page, 'gdlr_lms_register_main_admin_option_script');
			
		// for sub menu
		add_submenu_page('lms-main-option', esc_html__('Evidence Of Payment', 'gdlr-lms'), esc_html__('Evidence Of Payment', 'gdlr-lms'), 
			'edit_theme_options', 'lms-evidence-of-payment' , 'gdlr_lms_payment_evidence_option');
		add_submenu_page('lms-main-option', esc_html__('Statement', 'gdlr-lms'), esc_html__('Statement', 'gdlr-lms'), 
			'edit_theme_options', 'lms-statement' , 'gdlr_lms_statement_option');		
		add_submenu_page('lms-main-option', esc_html__('Commission', 'gdlr-lms'), esc_html__('Commission', 'gdlr-lms'), 
			'edit_theme_options', 'lms-commission' , 'gdlr_lms_commission_option');						
		add_submenu_page('lms-main-option', esc_html__('Transaction', 'gdlr-lms'), esc_html__('Transaction', 'gdlr-lms'), 
			'edit_theme_options', 'lms-transaction' , 'gdlr_lms_transaction_option');			
		add_action('admin_print_styles', 'gdlr_lms_register_admin_option_style');	
		add_action('admin_print_scripts', 'gdlr_lms_register_admin_option_script');			
	}
	
	// add style and script
	function gdlr_lms_register_main_admin_option_style(){
		gdlr_lms_include_jquery_ui_style();
		wp_enqueue_style('wp-color-picker');
		wp_enqueue_style('gdlr-alert-box', plugins_url('theme-option/gdlr-alert-box.css', __FILE__));						
		wp_enqueue_style('gdlr-admin-panel', plugins_url('theme-option/gdlr-admin-panel.css', __FILE__));						
		wp_enqueue_style('gdlr-admin-panel-html', plugins_url('theme-option/gdlr-admin-panel-html.css', __FILE__));
	}
	function gdlr_lms_register_main_admin_option_script(){
		if(function_exists( 'wp_enqueue_media' )){
			wp_enqueue_media();
		}		
		
		wp_enqueue_script('jquery-ui-datepicker');	
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-slider');
		wp_enqueue_script('wp-color-picker');			
		wp_enqueue_script('gdlr-alert-box', plugins_url('theme-option/gdlr-alert-box.js', __FILE__));
		wp_enqueue_script('gdlr-admin-panel', plugins_url('theme-option/gdlr-admin-panel.js', __FILE__));
		wp_enqueue_script('gdlr-admin-panel-html', plugins_url('theme-option/gdlr-admin-panel-html.js', __FILE__));
	}	
	function gdlr_lms_register_admin_option_style(){
		gdlr_lms_include_jquery_ui_style();
		wp_enqueue_style('font-awesome', plugins_url('font-awesome/css/font-awesome.min.css', dirname(__FILE__)) );
		wp_enqueue_style('admin-option', plugins_url('/stylesheet/plugin-option.css', __FILE__));
	}
	function gdlr_lms_register_admin_option_script(){
		wp_enqueue_script('jquery-ui-datepicker');	
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('admin-option', plugins_url('/javascript/plugin-option.js', __FILE__));
	}

	// create an option
	add_action('init', 'init_gdlr_lms_admin_option');
	function init_gdlr_lms_admin_option(){
		global $gdlr_lms_option, $lms_admin_option; $lms_admin_option = new gdlr_lms_admin_option();
		$lms_admin_option->set_option(array(
			'general' => array(
				'title' => esc_html__('General', 'gdlr-lms'),
				'icon' => plugins_url('theme-option/images/icon-general.png', __FILE__),
				'options' => array(
					
					'plugin-style' => array(
						'title' => esc_html__('Plugin Style', 'gdlr-lms'),
						'options' => array(
							'container-width' => array(
								'title' => esc_html__('Container Width', 'gdlr-lms'),
								'type' => 'text',	
								'default' => '1140', 
								'data-type' => 'pixel',
								'selector' => '.gdlr-lms-container, .gdlr-lms-title-wrapper{ max-width: #gdlr#; }'
							),
							'course-info-style' => array(
								'title' => esc_html__('Single Course Info Style', 'gdlr-lms'),
								'type' => 'combobox',	
								'default' => 'style-1',
								'options' => array(
									'style-1' => esc_html__('Style 1', 'gdlr-lms'),
									'style-2' => esc_html__('Style 2', 'gdlr-lms'),
								)
							),
							'date-format' => array(
								'title' => esc_html__('Date Format', 'gdlr-lms'),
								'type' => 'text',	
								'default' => 'M j, Y'
							),
							'money-format' => array(
								'title' => esc_html__('Money Format', 'gdlr-lms'),
								'type' => 'text',	
								'default' => '$NUMBER'
							),
							'instructor-thumbnail-size' => array(
								'title' => esc_html__('Instructor Thumbnail Size', 'gdlr-lms'),
								'type' => 'combobox',	
								'options' => gdlr_lms_get_thumbnail_list()
							),
							'transaction-record' => array(
								'title' => esc_html__('Transaction Record Per Page', 'gdlr-lms'),
								'type' => 'text',	
								'default' => 30
							),
							'show-header' => array(
								'title' => esc_html__('Display Page Title', 'gdlr-lms'),
								'type' => 'checkbox',	
								'default' => 'disable'
							),
							'show-sidebar' => array(
								'title' => esc_html__('Show Default Sidebar', 'gdlr-lms'),
								'type' => 'checkbox',	
								'default' => 'disable',
								'description' => esc_html__('To support twenty fourteen sidebar structure.', 'gdlr-lms')
							),	
							'jqueryui-style' => array(
								'title' => esc_html__('Include jQuery UI Style', 'gdlr-lms'),
								'type' => 'checkbox',	
								'default' => 'enable',
								'description' => esc_html__('Disable this if there\'re any conflicted with the theme or plugins.', 'gdlr-lms')
							),	
							'new-font-awesome' => array(
								'title' => esc_html__('Use New Font Awesome Version', 'gdlr-lms'),
								'type' => 'checkbox',	
								'default' => 'disable',
							),							
						)
					),
					'payment-option' => array(
						'title' => esc_html__('Payment Option', 'gdlr-lms'),
						'options' => array(
							'default-instructor-commission' => array(
								'title' => esc_html__('Default Instructor Commission (Percent)', 'gdlr-lms'),
								'type' => 'text',	
								'default' => '100',
								'description' => esc_html__('Please only fill number without special character here', 'gdlr-lms')
							),
							'payment-method' => array(
								'title' => esc_html__('Payment Method', 'gdlr-lms'),
								'type' => 'combobox',	
								'options' => array(
									'both' => esc_html__('Instant Payment and Receipt Confirmation', 'gdlr-lms'),
									'paypal' => esc_html__('Instant Payment', 'gdlr-lms'),
									'receipt' => esc_html__('Receipt Confirmation', 'gdlr-lms'),
								)
							),
							'instant-payment-method' => array(
								'title' => esc_html__('Instant Payment Method', 'gdlr-lms'),
								'type' => 'multi-combobox',	
								'options' => array(
									'paypal' =>  esc_html__('Paypal', 'gdlr-lms'),
									'stripe' =>  esc_html__('Stripe', 'gdlr-lms'),
									// 'paymill' =>  esc_html__('Paymill', 'gdlr-lms'),
									'authorize' =>  esc_html__('Authorize.Net', 'gdlr-lms'),
									'cloud' =>  esc_html__('Cloud Payment', 'gdlr-lms'),
									'braintree' =>  esc_html__('Braintree Payment', 'gdlr-lms'),
								),
								'description' => esc_html__('Leaving this field blank will display all available payment method.', 'gdlr-lms')
							),
						)
					),
					'paypal-payment-info' => array(
						'title' => esc_html__('Paypal Info', 'gdlr-lms'),
						'options' => array(	
							'paypal-recipient' => array(
								'title' => esc_html__('Paypal Recipient Name', 'gdlr-lms'),
								'type' => 'text',	
								'default' => 'LMS System'
							),
							'paypal-recipient-email' => array(
								'title' => esc_html__('Paypal Recipient Email', 'gdlr-lms'),
								'type' => 'text',	
								'default' => 'testmail@test.com'
							),
							'paypal-action-url' => array(
								'title' => esc_html__('Paypal Action URL', 'gdlr-lms'),
								'type' => 'text',	
								'default' => 'https://www.paypal.com/cgi-bin/webscr'
							),
							'paypal-currency-code' => array(
								'title' => esc_html__('Paypal Currency Code', 'gdlr-lms'),
								'type' => 'text',	
								'default' => 'USD'
							),						
						)
					),
					'stripe-payment-info' => array(
						'title' => esc_html__('Stripe Info', 'gdlr-lms'),
						'options' => array(	
							'stripe-secret-key' => array(
								'title' => esc_html__('Stripe Secret Key', 'gdlr-lms'),
								'type' => 'text'
							),
							'stripe-publishable-key' => array(
								'title' => esc_html__('Stripe Publishable Key', 'gdlr-lms'),
								'type' => 'text'
							),	
							'stripe-currency-code' => array(
								'title' => esc_html__('Stripe Currency Code', 'gdlr-lms'),
								'type' => 'text',	
								'default' => 'usd'
							),	
						)
					),
					/*
					'paymill-payment-info' => array(
						'title' => esc_html__('Paymill Info', 'gdlr-lms'),
						'options' => array(	
							'paymill-private-key' => array(
								'title' => esc_html__('Paymill Private Key', 'gdlr-lms'),
								'type' => 'text'
							),
							'paymill-public-key' => array(
								'title' => esc_html__('Paymill Public Key', 'gdlr-lms'),
								'type' => 'text'
							),	
							'paymill-currency-code' => array(
								'title' => esc_html__('Paymill Currency Code', 'gdlr-lms'),
								'type' => 'text',	
								'default' => 'usd'
							),
						)
					),
					*/
					'authorize-payment-info' => array(
						'title' => esc_html__('Authorize Info', 'gdlr-lms'),
						'options' => array(	
							'authorize-live-mode' => array(
								'title' => esc_html__('Live Mode ', 'gdlr-lms'),
								'type' => 'checkbox',
								'default' => 'disable',
								'description' => esc_html__('Please turn this option off when you\'re on test mode.','gdlr-hotel')
							),
							'authorize-api-id' => array(
								'title' => esc_html__('Authorize API Login ID ', 'gdlr-lms'),
								'type' => 'text'
							),
							'authorize-transaction-key' => array(
								'title' => esc_html__('Authorize Transaction Key', 'gdlr-lms'),
								'type' => 'text'
							),
							// 'authorize-md5-hash' => array(
							// 	'title' => esc_html__('Authorize MD5 Hash', 'gdlr-lms'),
							// 	'type' => 'text'
							// ),
						)
					),
					'cloud-payment-info' => array(
						'title' => esc_html__('Cloud Payment Info', 'gdlr-lms'),
						'options' => array(	
							'cloud-public-id' => array(
								'title' => esc_html__('Public ID', 'gdlr-lms'),
								'type' => 'text',
								'default' => 'disable',
							),
							'cloud-currency-code' => array(
								'title' => esc_html__('Currency Code', 'gdlr-lms'),
								'type' => 'text',	
								'default' => 'USD'
							),
						)
					),	
					'braintree-payment-info' => array(
						'title' => esc_html__('Braintree Payment Info', 'gdlr-lms'),
						'options' => array(	
							'braintree-live-mode' => array(
								'title' => esc_html__('Live Mode ', 'gdlr-lms'),
								'type' => 'checkbox',
								'default' => 'disable',
								'description' => esc_html__('Please turn this option off when you\'re on test mode.','gdlr-hotel')
							),
							'braintree-merchant-id' => array(
								'title' => esc_html__('Braintree Merchant ID ', 'gdlr-lms'),
								'type' => 'text'
							),
							'braintree-public-key' => array(
								'title' => esc_html__('Braintree Public Key', 'gdlr-lms'),
								'type' => 'text'
							),
							'braintree-private-key' => array(
								'title' => esc_html__('Braintree Private Key', 'gdlr-lms'),
								'type' => 'text'
							),
						)
					),					
					'course-style' => array(
						'title' => esc_html__('Course Style', 'gdlr-lms'),
						'options' => array(						
							'archive-course-style' => array(
								'title' => esc_html__('Archive Course Style', 'gdlr-lms'),
								'type' => 'combobox',	
								'options' => array(
									'grid' => esc_html__('Grid Style', 'gdlr-lms'),
									'grid-2' => esc_html__('Grid 2nd Style', 'gdlr-lms'),
									'medium' => esc_html__('Medium Style', 'gdlr-lms')
								)
							),
							'archive-course-size' => array(
								'title' => esc_html__('Archive Course Size', 'gdlr-lms'),
								'type' => 'combobox',	
								'options'=> array(
									'4'=>'1/4',
									'3'=>'1/3',
									'2'=>'1/2',
									'1'=>'1/1'
								),
								'default' => '3'
							),	
							'archive-course-thumbnail-size' => array(
								'title' => esc_html__('Archive Course Thumbnail Size', 'gdlr-lms'),
								'type' => 'combobox',	
								'options' => gdlr_lms_get_thumbnail_list()
							),	
						)
					)
				)
			),
			'elements-color' => array(
				'title' => esc_html__('Elements Color', 'gdlr-lms'),
				'icon' => plugins_url('theme-option/images/icon-elements-color.png', __FILE__),
				'options' => array(						
					'element-color' => array(
						'title' => esc_html__('Element Color', 'gdlr-lms'),
						'options' => array(		
							'button1-color' => array(
								'title' => esc_html__('Button 1 Color (Cyan)', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#ffffff',
								'selector'=> '.gdlr-lms-course-grid2 .gdlr-lms-course-price span, ' .
									'input[type="submit"].gdlr-lms-button, input[type="submit"].gdlr-lms-button:focus, ' . 
									'input[type="submit"].gdlr-lms-button:hover, input[type="submit"].gdlr-lms-button:active, ' .
									'.gdlr-lms-button.cyan{ color: #gdlr#; }'
							),
							'button1-background-color' => array(
								'title' => esc_html__('Button 1 Background (Cyan)', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#72d5cd',
								'selector'=> '.gdlr-lms-course-grid2 .gdlr-lms-course-price span, ' .
									'input[type="submit"].gdlr-lms-button, input[type="submit"].gdlr-lms-button:focus, ' . 
									'input[type="submit"].gdlr-lms-button:hover, input[type="submit"].gdlr-lms-button:active, ' .
									'.gdlr-lms-button.cyan{ background-color: #gdlr#; }'
							),
							'button1-border-color' => array(
								'title' => esc_html__('Button 1 Border Color (Cyan)', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#65b4ad',
								'selector'=> '.gdlr-lms-course-grid2 .gdlr-lms-course-price span, ' .
									'input[type="submit"].gdlr-lms-button, input[type="submit"].gdlr-lms-button:focus, ' . 
									'input[type="submit"].gdlr-lms-button:hover, input[type="submit"].gdlr-lms-button:active, ' .
									'.gdlr-lms-button.cyan{ border-color: #gdlr#; }'
							),
							'button2-color' => array(
								'title' => esc_html__('Button 2 Color (Blue)', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#ffffff',
								'selector'=> '.gdlr-lms-course-grid2 .gdlr-lms-course-price span.blue, ' . 
									'.gdlr-lms-button.blue{ color: #gdlr#; }'
							),
							'button2-background-color' => array(
								'title' => esc_html__('Button 2 Background (Blue)', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#71cbde',
								'selector'=> '.gdlr-lms-course-grid2 .gdlr-lms-course-price span.blue, ' . 
									'.gdlr-lms-button.blue{ background-color: #gdlr#; } '  .
									'.gdlr-lms-item.gdlr-lms-free{ border-bottom-color: #gdlr# !important; }'
							),
							'button2-border-color' => array(
								'title' => esc_html__('Button 2 Border Color (Blue)', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#4aacc0',
								'selector'=> '.gdlr-lms-course-grid2 .gdlr-lms-course-price span.blue, ' . 
									'.gdlr-lms-button.blue{ border-color: #gdlr#; }'
							),
							'button3-color' => array(
								'title' => esc_html__('Button 3 Color (Black)', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#ffffff',
								'selector'=> '.gdlr-lms-button.black{ color: #gdlr#; }'
							),
							'button3-background-color' => array(
								'title' => esc_html__('Button 3 Background (Black)', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#646464',
								'selector'=> '.gdlr-lms-button.black{ background-color: #gdlr#; }'
							),
							'button3-border-color' => array(
								'title' => esc_html__('Button 3 Border Color (Black)', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#000000',
								'selector'=> '.gdlr-lms-button.black{ border-color: #gdlr#; }'
							),
							'button4-color' => array(
								'title' => esc_html__('Button 4 Color (Red)', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#ffffff',
								'selector'=> '.gdlr-lms-button.red{ color: #gdlr#; }'
							),
							'button4-background-color' => array(
								'title' => esc_html__('Button 4 Background (Red)', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#d57272',
								'selector'=> '.gdlr-lms-button.red{ background-color: #gdlr#; }'
							),
							'button4-border-color' => array(
								'title' => esc_html__('Button 4 Border Color (Red)', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#b06464',
								'selector'=> '.gdlr-lms-button.red{ border-color: #gdlr#; }'
							),
							'table-head-background' => array(
								'title' => esc_html__('Table Head Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#72d5cd',
								'selector'=> '.gdlr-lms-table th{ background-color: #gdlr#; }'
							),
							'table-head-text' => array(
								'title' => esc_html__('Table Head Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#ffffff',
								'selector'=> '.gdlr-lms-table th{ color: #gdlr#; }'
							),
							'table-body-background' => array(
								'title' => esc_html__('Table Body Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#f7f7f7',
								'selector'=> '.gdlr-lms-table td{ background-color: #gdlr#; }'
							),
							'table-body-text' => array(
								'title' => esc_html__('Table Body Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#4e4e4e',
								'selector'=> '.gdlr-lms-table td{ color: #gdlr#; }'
							),
							'table-body-border' => array(
								'title' => esc_html__('Table Body Border', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#e5e5e5',
								'selector'=> '.gdlr-lms-table td{ border-color: #gdlr#; }'
							),
						)
					),
					'course-color' => array(
						'title' => esc_html__('Course Item Color', 'gdlr-lms'),
						'options' => array(						
							'course-title-color' => array(
								'title' => esc_html__('Course Title Color', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#212121',
								'selector'=> '.gdlr-lms-course-title, .gdlr-lms-course-title a{ color: #gdlr#; }'
							),
							'course-title-hover-color' => array(
								'title' => esc_html__('Course Title Hover Color', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#212121',
								'selector'=> '.gdlr-lms-course-title:hover, .gdlr-lms-course-title a:hover{ color: #gdlr#; }'
							),
							'course-info-color' => array(
								'title' => esc_html__('Course Info Color', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#aeaeae',
								'selector'=> '.gdlr-lms-info .tail, .gdlr-lms-info .tail a{ color: #gdlr#; }'
							),
							'course-info-head-color' => array(
								'title' => esc_html__('Course Info Head Color', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#575757',
								'selector'=> '.gdlr-lms-info .head{ color: #gdlr#; }'
							),
							'course-price-text' => array(
								'title' => esc_html__('Course Price Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#373737',
								'selector'=> '.gdlr-lms-course-price .head{ color: #gdlr#; }'
							),
							'course-price-color' => array(
								'title' => esc_html__('Course Price Color', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#72d5cd',
								'selector'=> '.gdlr-lms-course-price .price, .gdlr-lms-course-price .discount-price{ color: #gdlr#; }'
							),
							'course-grid2-background' => array(
								'title' => esc_html__('Course Grid2 Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#f5f5f5',
								'selector'=> '.gdlr-lms-course-grid2 .gdlr-lms-item, .gdlr-lms-course-grid2.gdlr-lms-item{ background-color: #gdlr#; }'
							),
							'course-grid2-bottom-border' => array(
								'title' => esc_html__('Course Grid2 Bottom Border', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#95e0da',
								'selector'=> '.gdlr-lms-course-grid2 .gdlr-lms-item, .gdlr-lms-course-grid2.gdlr-lms-item{ border-bottom-color: #gdlr#; }'
							),
							'course-rating-star-color' => array(
								'title' => esc_html__('Course Rating Star Color', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#f1c40f',
								'selector'=> '.gdlr-lms-rating-wrapper i, .gdlr-lms-lightbox-container.rating-form .gdlr-rating-input{ color: #gdlr#; }'
							),
							'course-rating-number-text' => array(
								'title' => esc_html__('Course Rating Number', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#c5c5c5',
								'selector'=> '.gdlr-lms-rating-wrapper .gdlr-lms-rating-amount{ color: #gdlr#; }'
							),
						)
					),
					'user-color' => array(
						'title' => esc_html__('Current User Color', 'gdlr-lms'),
						'options' => array(	
							'current-user-bar-background' => array(
								'title' => esc_html__('Current User Bar Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#f3f3f3',
								'selector'=> '.gdlr-lms-admin-bar{ background-color: #gdlr#; }'
							),
							'current-user-welcome-text' => array(
								'title' => esc_html__('Current User Welcome Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#4e4e4e',
								'selector'=> '.gdlr-lms-admin-head-content .gdlr-lms-welcome{ color: #gdlr#; }'
							),
							'current-user-name' => array(
								'title' => esc_html__('Current User Name Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#72d5cd',
								'selector'=> '.gdlr-lms-admin-head-content .gdlr-lms-name{ color: #gdlr#; }'
							),
							'current-user-role-text' => array(
								'title' => esc_html__('Current User Welcome Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#b0b0b0',
								'selector'=> '.gdlr-lms-admin-head-content .gdlr-lms-role{ color: #gdlr#; }'
							),
							'current-user-nav-background' => array(
								'title' => esc_html__('Current User Nav Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#fafafa',
								'selector'=> '.gdlr-lms-admin-list { background-color: #gdlr#; }'
							),
							'current-user-nav-border' => array(
								'title' => esc_html__('Current User Nav Border', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#ebebeb',
								'selector'=> '.gdlr-lms-admin-list li{ border-top-color: #gdlr#; }'
							),
							'current-user-nav-text' => array(
								'title' => esc_html__('Current User Nav Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#72d5cd',
								'selector'=> '.gdlr-lms-admin-list li a{ color: #gdlr#; }'
							),
							'user-notification-text' => array(
								'title' => esc_html__('Instructor Notification Text (Manual Check)', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#ffffff',
								'selector'=> '.gdlr-lms-admin-list li .gdlr-lms-notification{ color: #gdlr#; }'
							),
							'user-notification-background' => array(
								'title' => esc_html__('Instructor Notification Background (Manual Check)', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#f48484',
								'selector'=> '.gdlr-lms-admin-list li .gdlr-lms-notification{ background-color: #gdlr#; }'
							),
							'user-form-label' => array(
								'title' => esc_html__('User Form Label', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#8f8f8f',
								'selector'=> '.gdlr-lms-form label{ color: #gdlr#; }'
							),
							'user-form-text' => array(
								'title' => esc_html__('User Form Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#8f8f8f',
								'selector'=> '.gdlr-lms-form input[type="text"], .gdlr-lms-form input[type="email"], .gdlr-lms-form input[type="password"], ' .
									'.gdlr-lms-form textarea, .gdlr-lms-quiz-answer textarea, ' . 
									'.gdlr-lms-form .gdlr-lms-combobox:after, .gdlr-lms-form .gdlr-lms-combobox select{ color: #gdlr#; } ' . 
									'.gdlr-lms-form input::-webkit-input-placeholder{ color: #gdlr# } .gdlr-lms-form input:-moz-placeholder{ color: #gdlr# } ' . 
									'.gdlr-lms-form input::-moz-placeholder{ color: #gdlr# } .gdlr-lms-form input:-ms-input-placeholder{ color: #gdlr# }'  .
									'.gdlr-lms-authorize-payment input.text{ color: #gdlr# !important; }'
							),
							'user-form-border' => array(
								'title' => esc_html__('User Form Border', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#e3e3e3',
								'selector'=> '.gdlr-lms-form input[type="text"], .gdlr-lms-form input[type="email"], .gdlr-lms-form input[type="password"], ' .
									'.gdlr-lms-form textarea, .gdlr-lms-quiz-answer textarea, ' . 
									'.gdlr-lms-form .gdlr-lms-combobox, .gdlr-lms-form .gdlr-lms-combobox:after{ border-color: #gdlr#; }' .
									'.gdlr-lms-authorize-payment input.text{ border-color: #gdlr# !important; }'
							),
							'user-form-background' => array(
								'title' => esc_html__('User Form Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#ffffff',
								'selector'=> '.gdlr-lms-form input[type="text"], .gdlr-lms-form input[type="email"], .gdlr-lms-form input[type="password"], ' .
									'.gdlr-lms-form textarea, .gdlr-lms-quiz-answer textarea, .gdlr-lms-form .gdlr-lms-combobox, ' . 
									'.gdlr-lms-form .gdlr-lms-combobox:after{ background-color: #gdlr#; }'  .
									'.gdlr-lms-authorize-payment input.text{ background-color: #gdlr# !important; }'
							),
							'cancel-booking-text-color' => array(
								'title' => esc_html__('Cancel Booking Text Color', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#f48484',
								'selector'=> '.gdlr-lms-table .gdlr-lms-cancel-booking, table.gdlr-lms-table .gdlr-lms-delete-student{ color: #gdlr#; }'
							),
							'profile-info-wrapper' => array(
								'title' => esc_html__('Profile Info Wrapper Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#f3f3f3',
								'selector'=> '.gdlr-lms-profile-info-wrapper{ background-color: #gdlr#; }'
							),
							'profile-info-head-text' => array(
								'title' => esc_html__('Profile Info Head text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#b6b6b6',
								'selector'=> '.gdlr-lms-profile-info .gdlr-lms-head{ color: #gdlr#; }'
							),
							'profile-info-text' => array(
								'title' => esc_html__('Profile Info Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#737373',
								'selector'=> '.gdlr-lms-profile-info .gdlr-lms-tail{ color: #gdlr#; }'
							),
						)
					),
					'course-content-color' => array(
						'title' => esc_html__('Course Content Color', 'gdlr-lms'),
						'options' => array(	
							'course-curriculum-section' => array(
								'title' => esc_html__('Course Curriculum Section Title', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#4a4a4a',
								'selector'=> '.gdlr-course-curriculum-quiz, .gdlr-course-curriculum-section-head{ color: #gdlr#; }'
							),
							'course-curriculum-lecture' => array(
								'title' => esc_html__('Course Curriculum Lecture Title', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#686868',
								'selector'=> '.gdlr-course-curriculum-lecture{ color: #gdlr#; }'
							),
							'course-curriculum-border' => array(
								'title' => esc_html__('Course Curriculum Border', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#e7e7e7',
								'selector'=> '.gdlr-course-curriculum-section-head, .gdlr-course-curriculum-lecture, .gdlr-course-curriculum-quiz{ border-color: #gdlr#; }'
							),
							'course-content-info-background' => array(
								'title' => esc_html__('Course Content Info Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#f3f3f3',
								'selector'=> '.gdlr-lms-course-single .gdlr-lms-course-info-wrapper, ' .
									'.gdlr-lms-course-pdf .gdlr-lms-part-pdf-info, ' . 
									'.gdlr-lms-course-part.gdlr-current .gdlr-lms-course-part-bullet{ background-color: #gdlr#; }'
							),
							'course-content-info-title-background' => array(
								'title' => esc_html__('Course Content Info Title Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#5c5c5c',
								'selector'=> '.gdlr-lms-content-type .gdlr-lms-course-info-title{ background-color: #gdlr#; }'
							),
							'course-content-info-title' => array(
								'title' => esc_html__('Course Info Title', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#ffffff',
								'selector'=> '.gdlr-lms-content-type .gdlr-lms-course-info-title{ color: #gdlr#; }'
							),
							'course-content-social-background' => array(
								'title' => esc_html__('Course Content Social Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#f5f5f5',
								'selector'=> '.gdlr-lms-single-course-info{ background-color: #gdlr#; }'
							),
							'course-bullet-background' => array(
								'title' => esc_html__('Course Bullet Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#dbdbdb',
								'selector'=> '.gdlr-lms-course-part.gdlr-next .gdlr-lms-course-part-bullet, ' .
									'.gdlr-lms-course-part.gdlr-next .gdlr-lms-course-part-line, ' . 
									'.gdlr-lms-course-part.gdlr-current .gdlr-lms-course-part-line{ background-color: #gdlr#; }'
							),
							'course-bullet-background-active' => array(
								'title' => esc_html__('Course Bullet Background Active', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#72d5cd',
								'selector'=> '.gdlr-lms-course-part-bullet, .gdlr-lms-course-part-line, ' . 
									'.gdlr-lms-quiz-type .gdlr-lms-course-part.gdlr-current .gdlr-lms-course-part-bullet{ background-color: #gdlr#; }' . 
									'.gdlr-lms-course-part.gdlr-current .gdlr-lms-course-part-bullet{ border-color: #gdlr#; }' .
									'.gdlr-lms-lecture-part i{ color: #gdlr#; }'
							),
							'course-bullet-inactive-title' => array(
								'title' => esc_html__('Course Bullet Inactive Title', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#b9b9b9',
								'selector'=> '.gdlr-lms-course-part.gdlr-pass .gdlr-lms-course-part-content .part, .gdlr-lms-course-part.gdlr-pass .gdlr-lms-course-part-content .title, ' .
									'.gdlr-lms-course-pdf .gdlr-lms-part-caption, ' . 
									'.gdlr-lms-course-part.gdlr-next .gdlr-lms-course-part-content .title, ' . 
									'.gdlr-lms-course-part.gdlr-next .gdlr-lms-course-part-content .part, ' . 
									'.gdlr-lms-lecture-part.lecture-next .lecture-part, .gdlr-lms-lecture-part.lecture-next .lecture-title{ color: #gdlr#; }'
							),
							'course-bullet-title' => array(
								'title' => esc_html__('Course Bullet Active Title', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#5c5c5c',
								'selector'=> '.gdlr-lms-course-part.gdlr-current .gdlr-lms-course-part-content .part, .gdlr-lms-course-part.gdlr-current .gdlr-lms-course-part-content .title,' . 
									'.gdlr-lms-course-pdf .gdlr-lms-part-title, ' . 
									'.gdlr-lms-lecture-part .lecture-part, .gdlr-lms-lecture-part .lecture-title{ color: #gdlr#; }'
							),
						)
					),
					'quiz-color' => array(
						'title' => esc_html__('Quiz Color', 'gdlr-lms'),
						'options' => array(	
							'quiz-info-background' => array(
								'title' => esc_html__('Quiz Info Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#333333',
								'selector'=> '.gdlr-lms-quiz-type .gdlr-lms-course-info-wrapper, ' . 
									'.gdlr-lms-quiz-type .gdlr-lms-course-info-title{ background-color: #gdlr#; }'
							),
							'quiz-info-title-text' => array(
								'title' => esc_html__('Quiz Info Title Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#ffffff',
								'selector'=> '.gdlr-lms-quiz-type .gdlr-lms-course-info-title{ color: #gdlr#; }'
							),
							'quiz-info-timer-background' => array(
								'title' => esc_html__('Quiz Info Timer Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#71cbde',
								'selector'=> '.gdlr-lms-quiz-type .gdlr-lms-quiz-timer{ background-color: #gdlr#; }'
							),
							'quiz-info-timer-text' => array(
								'title' => esc_html__('Quiz Info Timer Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#ffffff',
								'selector'=> '.gdlr-lms-quiz-type .gdlr-lms-quiz-timer{ color: #gdlr#; }'
							),
							'quiz-bullet-background' => array(
								'title' => esc_html__('Quiz Bullet Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#6f6f6f',
								'selector'=> '.gdlr-lms-quiz-type .gdlr-lms-course-part.gdlr-next .gdlr-lms-course-part-bullet, ' .
									'.gdlr-lms-quiz-type .gdlr-lms-course-part.gdlr-next .gdlr-lms-course-part-line, ' . 
									'.gdlr-lms-quiz-type .gdlr-lms-course-part.gdlr-current .gdlr-lms-course-part-line{ background-color: #gdlr#; }'
							),
							'quiz-bullet-inactive-title' => array(
								'title' => esc_html__('Quiz Bullet Inactive Title', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#6f6f6f',
								'selector'=> '.gdlr-lms-quiz-type .gdlr-lms-course-part.gdlr-pass .gdlr-lms-course-part-content .part, ' . 
									'.gdlr-lms-quiz-type .gdlr-lms-course-part.gdlr-pass .gdlr-lms-course-part-content .title, ' .
									'.gdlr-lms-quiz-type .gdlr-lms-course-part.gdlr-next .gdlr-lms-course-part-content .title, ' . 
									'.gdlr-lms-quiz-type .gdlr-lms-course-part.gdlr-next .gdlr-lms-course-part-content .part{ color: #gdlr#; }'
							),
							'quiz-bullet-active-title' => array(
								'title' => esc_html__('Quiz Bullet Active Title', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#d9d9d9',
								'selector'=> '.gdlr-lms-quiz-type .gdlr-lms-course-part.gdlr-current .gdlr-lms-course-part-content .part, ' . 
									'.gdlr-lms-quiz-type .gdlr-lms-course-part.gdlr-current .gdlr-lms-course-part-content .title{ color: #gdlr#; }'
							),
							'quiz-question-background' => array(
								'title' => esc_html__('Quiz Question Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#f0f0f0',
								'selector'=> '.gdlr-lms-quiz-question { background-color: #gdlr#; }'
							),
							'quiz-question-text' => array(
								'title' => esc_html__('Quiz Question Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#656565',
								'selector'=> '.gdlr-lms-quiz-question { color: #gdlr#; }'
							),
							'quiz-answer-border' => array(
								'title' => esc_html__('Quiz Answer Border', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#e8e8e8',
								'selector'=> '.gdlr-lms-quiz-answer{ border-color: #gdlr#; }'
							),
							'quiz-answer-text' => array(
								'title' => esc_html__('Quiz Answer Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#a8a8a8',
								'selector'=> '.gdlr-lms-quiz-answer{ color: #gdlr#; }'
							),
							'quiz-score-background' => array(
								'title' => esc_html__('Quiz Score Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#f8f8f8',
								'selector'=> '.gdlr-lms-question-score{ background-color: #gdlr#; }'
							),
							'quiz-score-text' => array(
								'title' => esc_html__('Quiz Score Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#afafaf',
								'selector'=> '.gdlr-lms-question-score .gdlr-tail{ color: #gdlr#; }'
							),
							'quiz-score-head-text' => array(
								'title' => esc_html__('Quiz Score Title Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#515151',
								'selector'=> '.gdlr-lms-question-score .gdlr-head { color: #gdlr#; }'
							),
						)
					),
					'instructor-color' => array(
						'title' => esc_html__('Instructor Color', 'gdlr-lms'),
						'options' => array(	
							'instructor-item-background' => array(
								'title' => esc_html__('Instructor Item Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#f5f5f5',
								'selector'=> '.gdlr-lms-instructor-content{ background-color: #gdlr#; }'
							),
							'instructor-item-title' => array(
								'title' => esc_html__('Instructor Item Title', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#72d5cd',
								'selector'=> '.gdlr-lms-instructor-title{ color: #gdlr#; }'
							),
							'instructor-item-position' => array(
								'title' => esc_html__('Instructor Item Position', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#888888',
								'selector'=> '.gdlr-lms-instructor-position{ color: #gdlr#; }'
							),
							'instructor-item-description' => array(
								'title' => esc_html__('Instructor Item Description', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#a9a9a9',
								'selector'=> '.gdlr-lms-author-description{ color: #gdlr#; }'
							),
							'single-instructor-info-background' => array(
								'title' => esc_html__('Single Instructor Info Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#313131',
								'selector'=> '.gdlr-lms-author-info-wrapper{ background-color: #gdlr#; }'
							),
							'single-instructor-info-title' => array(
								'title' => esc_html__('Single Instructor Info Title', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#ffffff',
								'selector'=> '.gdlr-lms-author-name{ color: #gdlr#; }'
							),
							'single-instructor-info-position' => array(
								'title' => esc_html__('Single Instructor Info Position', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#888888',
								'selector'=> '.gdlr-lms-admin-bar{ color: #gdlr#; }'
							),
							'single-instructor-info-text' => array(
								'title' => esc_html__('Single Instructor Info Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#cccccc',
								'selector'=> '.gdlr-lms-author-info, .gdlr-lms-author-info a, .gdlr-lms-author-info a:hover{ color: #gdlr#; }'
							),
							'single-instructor-info-border' => array(
								'title' => esc_html__('Single Instructor Info Border', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#444444',
								'selector'=> '.gdlr-lms-author-info-wrapper *{ border-color: #gdlr#; }'
							),
							'single-instructor-extra-info-background' => array(
								'title' => esc_html__('Single Instructor Extra Info Background', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#f5f5f5',
								'selector'=> '.gdlr-lms-author-extra-info-wrapper{ background-color: #gdlr#; }'
							),
							'single-instructor-extra-info-bottom-border' => array(
								'title' => esc_html__('Single Instructor Extra Info Bottom Border', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#72d5cd',
								'selector'=> '.gdlr-lms-author-extra-info-wrapper{ border-bottom-color: #gdlr#; }'
							),
							'single-instructor-extra-info-text' => array(
								'title' => esc_html__('Single Instructor Extra Info Text', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#b1b1b1',
								'selector'=> '.gdlr-lms-extra-info .gdlr-tail{ color: #gdlr#; }'
							),
							'single-instructor-extra-info-head' => array(
								'title' => esc_html__('Single Instructor Extra Info Head', 'gdlr-lms'),
								'type' => 'colorpicker',
								'default' => '#717171',
								'selector'=> '.gdlr-lms-extra-info .gdlr-head{ color: #gdlr#; }'
							),
						)
					)
				)
			)									
		), $gdlr_lms_option);		
		add_action('wp_ajax_gdlr_lms_save_admin_panel', array(&$lms_admin_option, 'gdlr_lms_save_admin_panel'));	
	}	
	function gdlr_lms_main_option(){
		global $lms_admin_option;
		$lms_admin_option->create_admin_option();
	}

	// save the lms-style-custom.css file when the admin option is saved
	add_action('gdlr_save_lms_admin_option', 'gdlr_lms_generate_style_custom');
	function gdlr_lms_generate_style_custom($options){
		
		// for multisite
		$file_url = plugin_dir_path(dirname(__FILE__)) . '/lms-style-custom.css';
		if( is_multisite() && get_current_blog_id() > 1 ){
			$file_url = plugin_dir_path(dirname(__FILE__)) . '/lms-style-custom' . get_current_blog_id() . '.css';
		}
		
		// open file
		$file_stream = @fopen($file_url, 'w');
		if( !$file_stream ){
			$ret = array(
				'status'=>'failed', 
				'message'=> '<span class="head">' . esc_html__('Cannot Generate Custom File', 'gdlr-lms') . '</span> ' .
					esc_html__('Please try changing the lms-style-custom.css file permission to 775 or 777 for this.' ,'gdlr-lms')
			);	
			
			die(json_encode($ret));				
		}
		
		// write file content
		$plugin_option = get_option('gdlr_lms_admin_option', array());
		
		// for updating google font list to use on front end
		foreach( $options as $menu_key => $menu ){
			foreach( $menu['options'] as $submenu_key => $submenu ){
				if( !empty($submenu['options']) ){
					foreach( $submenu['options'] as $option_slug => $option ){
						if( !empty($option['selector']) ){
							// prevents warning message
							$option['data-type'] = (empty($option['data-type']))? 'color': $option['data-type'];
							
							if( !empty($plugin_option[$option_slug]) ){
								$value = gdlr_lms_check_option_data_type($plugin_option[$option_slug], $option['data-type']);
							}else{
								$value = '';
							}

							if($value){
								fwrite( $file_stream, str_replace('#gdlr#', $value, $option['selector']) . "\r\n" );
							}
						}
					}
				}
			}
		}

		// close file after finish writing
		fclose($file_stream);
	}
	function gdlr_lms_check_option_data_type( $value, $data_type = 'color' ){
		if( $data_type == 'color' ){
			return (strpos($value, '#') === false)? '#' . $value: $value; 
		}else if( $data_type == 'text' ){
			return $value;
		}else if( $data_type == 'pixel' ){
			return (is_numeric($value))? $value . 'px': $value;
		}else if( $data_type == 'upload' ){
			if(is_numeric($value)){
				$image_src = wp_get_attachment_image_src($value, 'full');	
				return (!empty($image_src))? $image_src[0]: false;
			}else{
				return $value;
			}
		}else if( $data_type == 'font'){
			if( strpos($value, ',') === false ){
				return '"' . $value . '"';
			}
			return $value;
		}else if( $data_type == 'percent' ){
			return (is_numeric($value))? $value . '%': $value;
		}
	
	}	

?>