<?php
	/*	
	*	Goodlayers Framework File
	*	---------------------------------------------------------------------
	*	This file contains the code to register the goodlayers plugin option
	*	to admin area
	*	---------------------------------------------------------------------
	*/

	// add an admin option to portfolio
	add_filter('gdlr_admin_option', 'gdlr_register_portfolio_admin_option');
	if( !function_exists('gdlr_register_portfolio_admin_option') ){
		
		function gdlr_register_portfolio_admin_option( $array ){		
			if( empty($array['general']['options']) ) return $array;
			
			$portfolio_option = array( 									
				'title' => __('Portfolio Style', 'gdlr_translate'),
				'options' => array(
					'portfolio-slug' => array(
						'title' => __('Portfolio Slug ( Permalink )', 'gdlr_translate'),
						'type' => 'text',
						'default' => 'portfolio',
						'description' => __('Only <strong>a-z (lower case), hyphen and underscore</strong> is allowed here <br><br>', 'gdlr_translate') .
							__('After changing, you have to set the permalink at the setting > permalink to default (to reset the permalink rules) as well.', 'gdlr_translate')
					),
					'portfolio-category-slug' => array(
						'title' => __('Portfolio Category Slug ( Permalink )', 'gdlr_translate'),
						'type' => 'text',
						'default' => 'portfolio_category',
					),
					'portfolio-tag-slug' => array(
						'title' => __('Portfolio Tag Slug ( Permalink )', 'gdlr_translate'),
						'type' => 'text',
						'default' => 'portfolio_tag',
					),					
					'portfolio-comment' => array(
						'title' => __('Enable Comment On Portfolio', 'gdlr_translate'),
						'type' => 'checkbox',
						'default' => 'disable'
					),	
					'portfolio-related' => array(
						'title' => __('Enable Related Portfolio', 'gdlr_translate'),
						'type' => 'checkbox',
						'default' => 'enable'
					),					
					'portfolio-page-style' => array(
						'title' => __('Portfolio Page Style', 'gdlr_translate'),
						'type'=> 'combobox',
						'options'=> array(
							'style1'=> __('Portfolio Style 1', 'gdlr_translate'),
							'style2'=> __('Portfolio Style 2', 'gdlr_translate'),
							'blog-style'=> __('Blog Style', 'gdlr_translate'),
						)
					),					
					'portfolio-thumbnail-size' => array(
						'title' => __('Single Portfolio Thumbnail Size', 'gdlr_translate'),
						'type'=> 'combobox',
						'options'=> gdlr_get_thumbnail_list(),
						'default'=> 'post-thumbnail-size'
					),	
					'related-portfolio-style' => array(
						'title' => __('Related Portfolio Style', 'gdlr_translate'),
						'type'=> 'combobox',
						'options'=> array(
							'classic-portfolio'=> __('Portfolio Classic Style', 'gdlr_translate'),
							'modern-portfolio'=> __('Portfolio Modern Style', 'gdlr_translate')
						)
					),
					'related-portfolio-size' => array(
						'title' => __('Related Portfolio Size', 'gdlr_translate'),
						'type'=> 'combobox',
						'options'=> array(
							'2'=> __('1/2', 'gdlr_translate'),
							'3'=> __('1/3', 'gdlr_translate'),
							'4'=> __('1/4', 'gdlr_translate'),
							'5'=> __('1/5', 'gdlr_translate')
						),
						'default'=>'4'
					),					
					'related-portfolio-num-fetch' => array(
						'title' => __('Related Portfolio Num Fetch', 'gdlr_translate'),
						'type'=> 'text',
						'default'=> '4'
					),
					'related-portfolio-thumbnail-size' => array(
						'title' => __('Related Portfolio Thumbnail Size', 'gdlr_translate'),
						'type'=> 'combobox',
						'options'=> gdlr_get_thumbnail_list(),
						'default'=> 'small-grid-size'
					),					
				)
			);
			
			$array['general']['options']['portfolio-style'] = $portfolio_option;
			return $array;
		}
		
	}		

	// add an admin option for causes
	add_filter('gdlr_admin_option', 'gdlr_register_cause_admin_option');
	if( !function_exists('gdlr_register_cause_admin_option') ){
		
		function gdlr_register_cause_admin_option( $array ){	
			global $gdlr_sidebar_controller;
			if( empty($array['general']['options']) ) return $array;
			
			$cause_option = array( 									
				'title' => __('Cause Style', 'gdlr_translate'),
				'options' => array(
					'cause-slug' => array(
						'title' => __('Cause Slug ( Permalink )', 'gdlr_translate'),
						'type' => 'text',
						'default' => 'cause',
						'description' => __('Only <strong>a-z (lower case), hyphen and underscore</strong> is allowed here <br><br>', 'gdlr_translate') .
							__('After changing, you have to set the permalink at the setting > permalink to default (to reset the permalink rules) as well.', 'gdlr_translate')
					),
					'cause-category-slug' => array(
						'title' => __('Cause Category Slug ( Permalink )', 'gdlr_translate'),
						'type' => 'text',
						'default' => 'cause_category',
					),
					'cause-thumbnail-size' => array(
						'title' => __('Single Cause Thumbnail Size', 'gdlr_translate'),
						'type'=> 'combobox',
						'options'=> gdlr_get_thumbnail_list(),
						'default'=> 'post-thumbnail-size'
					),	
					'cause-recipient-name' => array(
						'title' => __('Recipient Name (Appear in sending mail)', 'gdlr_translate'),
						'type'=> 'text',
						'default'=> 'ORGANIZATION NAME'
					),	
					'cause-donation-form' => array(
						'title' => __('Default Donation Form', 'gdlr_translate'),
						'type'=> 'textarea',
						'default'=> '[gdlr_paypal user="youruser@domain.com"]',
						'description'=> __('You may put the recipient\'s paypal account here.', 'gdlr_translate')
					),					
					'cause-money-format' => array(
						'title' => __('Cause Money Format', 'gdlr_translate'),
						'type'=> 'text',
						'default'=> '$NUMBER',
					),					
					'cause-sidebar-template' => array(
						'title' => __('Default Post Sidebar', 'gdlr_translate'),
						'type' => 'radioimage',
						'options' => array(
							'no-sidebar'=>GDLR_PATH . '/include/images/no-sidebar.png',
							'both-sidebar'=>GDLR_PATH . '/include/images/both-sidebar.png', 
							'right-sidebar'=>GDLR_PATH . '/include/images/right-sidebar.png',
							'left-sidebar'=>GDLR_PATH . '/include/images/left-sidebar.png'
						),
						'default' => 'right-sidebar'							
					),
					'cause-sidebar-left' => array(
						'title' => __('Default Post Sidebar Left', 'gdlr_translate'),
						'type' => 'combobox',
						'options' => $gdlr_sidebar_controller->get_sidebar_array(),		
						'wrapper-class'=>'left-sidebar-wrapper both-sidebar-wrapper cause-sidebar-template-wrapper',											
					),
					'cause-sidebar-right' => array(
						'title' => __('Default Post Sidebar Right', 'gdlr_translate'),
						'type' => 'combobox',
						'options' => $gdlr_sidebar_controller->get_sidebar_array(),
						'wrapper-class'=>'right-sidebar-wrapper both-sidebar-wrapper cause-sidebar-template-wrapper',
					),						
				)
			);
			
			$array['general']['options']['cause-style'] = $cause_option;
			return $array;
		}
		
	}		
	
// add an admin option to kot
	add_filter('gdlr_admin_option', 'gdlr_register_kot_admin_option');
	if( !function_exists('gdlr_register_kot_admin_option') ){
		
		function gdlr_register_kot_admin_option( $array ){		
			if( empty($array['general']['options']) ) return $array;
			
			$kot_option = array( 									
				'title' => __('Kot Style', 'gdlr_translate'),
				'options' => array(
					'kot-slug' => array(
						'title' => __('Kot Slug ( Permalink )', 'gdlr_translate'),
						'type' => 'text',
						'default' => 'kot',
						'description' => __('Only <strong>a-z (lower case), hyphen and underscore</strong> is allowed here <br><br>', 'gdlr_translate') .
							__('After changing, you have to set the permalink at the setting > permalink to default (to reset the permalink rules) as well.', 'gdlr_translate')
					),
					'kot-category-slug' => array(
						'title' => __('Kot Category Slug ( Permalink )', 'gdlr_translate'),
						'type' => 'text',
						'default' => 'kot_category',
					),
					'kot-tag-slug' => array(
						'title' => __('Kot Tag Slug ( Permalink )', 'gdlr_translate'),
						'type' => 'text',
						'default' => 'kot_tag',
					),					
					'kot-comment' => array(
						'title' => __('Enable Comment On Kot', 'gdlr_translate'),
						'type' => 'checkbox',
						'default' => 'disable'
					),	
					'kot-related' => array(
						'title' => __('Enable Related Kot', 'gdlr_translate'),
						'type' => 'checkbox',
						'default' => 'enable'
					),					
					'kot-page-style' => array(
						'title' => __('Kot Page Style', 'gdlr_translate'),
						'type'=> 'combobox',
						'options'=> array(
							'style1'=> __('Kot Style 1', 'gdlr_translate'),
							'style2'=> __('Kot Style 2', 'gdlr_translate'),
							'blog-style'=> __('Blog Style', 'gdlr_translate'),
						)
					),					
					'kot-thumbnail-size' => array(
						'title' => __('Single Kot Thumbnail Size', 'gdlr_translate'),
						'type'=> 'combobox',
						'options'=> gdlr_get_thumbnail_list(),
						'default'=> 'post-thumbnail-size'
					),	
					'related-kot-style' => array(
						'title' => __('Related Kot Style', 'gdlr_translate'),
						'type'=> 'combobox',
						'options'=> array(
							'classic-kot'=> __('Kot Classic Style', 'gdlr_translate'),
							'modern-kot'=> __('Kot Modern Style', 'gdlr_translate')
						)
					),
					'related-kot-size' => array(
						'title' => __('Related Kot Size', 'gdlr_translate'),
						'type'=> 'combobox',
						'options'=> array(
							'2'=> __('1/2', 'gdlr_translate'),
							'3'=> __('1/3', 'gdlr_translate'),
							'4'=> __('1/4', 'gdlr_translate'),
							'5'=> __('1/5', 'gdlr_translate')
						),
						'default'=>'4'
					),					
					'related-kot-num-fetch' => array(
						'title' => __('Related Kot Num Fetch', 'gdlr_translate'),
						'type'=> 'text',
						'default'=> '4'
					),
					'related-kot-thumbnail-size' => array(
						'title' => __('Related Kot Thumbnail Size', 'gdlr_translate'),
						'type'=> 'combobox',
						'options'=> gdlr_get_thumbnail_list(),
						'default'=> 'small-grid-size'
					),					
				)
			);
			
			$array['general']['options']['kot-style'] = $kot_option;
			return $array;
		}
		
	}			
	
	
?>