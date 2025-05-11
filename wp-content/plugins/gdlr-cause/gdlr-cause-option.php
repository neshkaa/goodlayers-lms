<?php
	/*	
	*	Goodlayers Cause Option file
	*	---------------------------------------------------------------------
	*	This file creates all cause options and attached to the theme
	*	---------------------------------------------------------------------
	*/
	
	// add a cause option to cause page
	if( is_admin() ){ add_action('after_setup_theme', 'gdlr_create_cause_options'); }
	if( !function_exists('gdlr_create_cause_options') ){
	
		function gdlr_create_cause_options(){
			global $gdlr_sidebar_controller;
			
			if( !class_exists('gdlr_page_options') ) return;
			new gdlr_page_options( 
				
				// page option attribute
				array(
					'post_type' => array('cause'),
					'meta_title' => __('Goodlayers Cause Option', 'gdlr-cause'),
					'meta_slug' => 'goodlayers-page-option',
					'option_name' => 'post-option',
					'position' => 'normal',
					'priority' => 'high',
				),
					  
				// page option settings
				array(
					'page-layout' => array(
						'title' => __('Page Layout', 'gdlr-cause'),
						'options' => array(
								'sidebar' => array(
									'type' => 'radioimage',
									'options' => array(
										'default-sidebar'=>GDLR_PATH . '/include/images/default-sidebar-2.png',
										'no-sidebar'=>GDLR_PATH . '/include/images/no-sidebar-2.png',
										'both-sidebar'=>GDLR_PATH . '/include/images/both-sidebar-2.png', 
										'right-sidebar'=>GDLR_PATH . '/include/images/right-sidebar-2.png',
										'left-sidebar'=>GDLR_PATH . '/include/images/left-sidebar-2.png'
									),
									'default' => 'default-sidebar'
								),	
								'left-sidebar' => array(
									'title' => __('Left Sidebar' , 'gdlr-cause'),
									'type' => 'combobox',
									'options' => $gdlr_sidebar_controller->get_sidebar_array(),
									'wrapper-class' => 'sidebar-wrapper left-sidebar-wrapper both-sidebar-wrapper'
								),
								'right-sidebar' => array(
									'title' => __('Right Sidebar' , 'gdlr-cause'),
									'type' => 'combobox',
									'options' => $gdlr_sidebar_controller->get_sidebar_array(),
									'wrapper-class' => 'sidebar-wrapper right-sidebar-wrapper both-sidebar-wrapper'
								),						
						)
					),
					
					'page-option' => array(
						'title' => __('Page Option', 'gdlr-cause'),
						'options' => array(
							'page-caption' => array(
								'title' => __('Page Caption' , 'gdlr-cause'),
								'type' => 'textarea'
							),							
							'header-background' => array(
								'title' => __('Header Background Image' , 'gdlr-cause'),
								'button' => __('Upload', 'gdlr-cause'),
								'type' => 'upload',
							),
							'goal-of-donation' => array(
								'title' => __('Goal Of Donation' , 'gdlr-cause'),
								'type' => 'text',
								'description' => __('** Only number with no comma is allowed Here', 'gdlr-cause')
							),
							'current-funding' => array(
								'title' => __('Current Funding' , 'gdlr-cause'),
								'type' => 'text',
								'description' => __('** Only number with no comma is allowed Here. This will overwrite the value received via paypal.', 'gdlr-cause')
							),
							'money-format' => array(
								'title' => __('Money Format' , 'gdlr-cause'),
								'type' => 'text',
								'description' => __('** Fill something like $NUMBER to change the default money format on each cause.', 'gdlr-cause')
							),
							'pdf' => array(
								'title' => __('PDF File (Downloaded)' , 'gdlr-cause'),
								'button' => __('Upload', 'gdlr-cause'),
								'type' => 'upload',
								'data-type' => 'all',
							),	
							'donation-form' => array(
								'title' => __('Donation Form' , 'gdlr-cause'),
								'type' => 'textarea',
								'description' => 'You can fill the link (with http:// at the front) to make it linked to the location you specify as well.'
							),							
						)
					),

				)
			);
			
		}
	}	
	
	// add cause in page builder area
	add_filter('gdlr_page_builder_option', 'gdlr_register_cause_item');
	if( !function_exists('gdlr_register_cause_item') ){
		function gdlr_register_cause_item( $page_builder = array() ){
			global $gdlr_spaces;
			$page_builder['content-item']['options']['cause-search'] = array(
				'title'=> __('Cause Search', 'gdlr-cause'), 
				'type'=>'item',
				'options'=>array(		
					'title'=> array(	
						'title'=> __('Title' ,'gdlr-cause'),
						'type'=> 'text',
						'default'=> __('Search For Causes', 'gdlr-cause')
					),	
					'caption'=> array(	
						'title'=> __('Caption' ,'gdlr-cause'),
						'type'=> 'text',
						'default'=> __('Please fill keywords in text box and select particular category to search for all causes.', 'gdlr-cause')
					),					
					'margin-bottom' => array(
						'title' => __('Margin Bottom', 'gdlr-cause'),
						'type' => 'text',
						'default' => $gdlr_spaces['bottom-item'],
						'description' => __('Spaces after ending of this item', 'gdlr-cause')
					)					
				)
			);
		
			$page_builder['content-item']['options']['cause'] = array(
				'title'=> __('Cause', 'gdlr-cause'), 
				'type'=>'item',
				'options'=>array(		
					'title-type'=> array(	
						'title'=> __('Title Type' ,'gdlr-cause'),
						'type'=> 'combobox',
						'options'=> array(
							'none'=> __('None' ,'gdlr-cause'),
							'icon'=> __('Title With Icon' ,'gdlr-cause'),
							'left'=> __('Left Align With Icon' ,'gdlr-cause'),
							'center'=> __('Center Align With Caption' ,'gdlr-cause')
						)
					),
					'icon'=> array(	
						'title'=> __('Icon' ,'gdlr-cause'),
						'type'=> 'text',
						'wrapper-class'=>'title-type-wrapper icon-wrapper left-wrapper'
					),										
					'title'=> array(	
						'title'=> __('Title' ,'gdlr-cause'),
						'type'=> 'text',
						'wrapper-class'=>'title-type-wrapper left-wrapper center-wrapper icon-wrapper'
					),			
					'caption'=> array(	
						'title'=> __('Caption' ,'gdlr-cause'),
						'type'=> 'textarea',
						'wrapper-class'=>'title-type-wrapper center-wrapper'
					),	
					'right-text'=> array(	
						'title'=> __('Title Right Text' ,'gdlr-cause'),
						'type'=> 'text',
						'default'=> __('View All Causes', 'gdlr-cause'),
						'wrapper-class'=>'title-type-wrapper left-wrapper center-wrapper icon-wrapper'
					),	
					'right-text-link'=> array(	
						'title'=> __('Title Right Text Link' ,'gdlr-cause'),
						'type'=> 'text',
						'wrapper-class'=>'title-type-wrapper left-wrapper center-wrapper icon-wrapper'
					),					
					'category'=> array(
						'title'=> __('Category' ,'gdlr-cause'),
						'type'=> 'multi-combobox',
						'options'=> gdlr_get_term_list('cause_category'),
						'description'=> __('You can use Ctrl/Command button to select multiple categories or remove the selected category. <br><br> Leave this field blank to select all categories.', 'gdlr-cause')
					),					
					'cause-style'=> array(
						'title'=> __('Cause Style' ,'gdlr-cause'),
						'type'=> 'combobox',
						'options'=> array(
							'grid' => __('Grid Style', 'gdlr-cause'),
							'medium' => __('Medium Style', 'gdlr-cause'),
							'full' => __('Full Style', 'gdlr-cause'),
						),
					),	
					'cause-size'=> array(
						'title'=> __('Cause Size' ,'gdlr-cause'),
						'type'=> 'combobox',
						'options'=> array(
							'1/4'=>'1/4',
							'1/3'=>'1/3',
							'1/2'=>'1/2',
							'1/1'=>'1/1'
						),
						'default'=>'1/3',
						'wrapper-class'=>'cause-style-wrapper grid-wrapper'
					),	
					'cause-layout'=> array(
						'title'=> __('Cause Layout' ,'gdlr-cause'),
						'type'=> 'combobox',
						'options'=> array(
							'fitRows' =>  __('FitRows ( Order items by row )', 'gdlr-cause'),
							'masonry' => __('Masonry ( Order items by spaces )', 'gdlr-cause'),
							'carousel' => __('Carousel ( Only For Grid And Modern Style )', 'gdlr-cause'),
						),
						'wrapper-class'=>'cause-style-wrapper grid-wrapper',
						'description'=> __('You can see an example of these two layout here', 'gdlr-cause') . 
							'<br><br> http://isotope.metafizzy.co/demos/layout-modes.html'
					),					
					'num-fetch'=> array(
						'title'=> __('Num Fetch' ,'gdlr-cause'),
						'type'=> 'text',	
						'default'=> '8',
						'description'=> __('Specify the number of causes you want to pull out.', 'gdlr-cause')
					),	
					'num-excerpt'=> array(
						'title'=> __('Num Excerpt (Word)' ,'gdlr_translate'),
						'type'=> 'text',	
						'default'=> '15',
						'description'=> __('This is a number of word (decided by spaces) that you want to show on the post excerpt. <strong>Use 0 to hide the excerpt, -1 to show full posts and use the wordpress more tag</strong>.', 'gdlr-cause')
					),						
					'thumbnail-size'=> array(
						'title'=> __('Thumbnail Size' ,'gdlr-cause'),
						'type'=> 'combobox',
						'options'=> gdlr_get_thumbnail_list(),
						'description'=> __('Only effects to <strong>standard and gallery post format</strong>','gdlr-cause')
					),	
					'orderby'=> array(
						'title'=> __('Order By' ,'gdlr-cause'),
						'type'=> 'combobox',
						'options'=> array(
							'date' => __('Publish Date', 'gdlr-cause'), 
							'title' => __('Title', 'gdlr-cause'), 
							'rand' => __('Random', 'gdlr-cause'), 
							'nearly' => __('Almost Complete Cause', 'gdlr-cause'), 
							'finish' => __('Complete Funding Cause', 'gdlr-cause'), 
						)
					),
					'order'=> array(
						'title'=> __('Order' ,'gdlr-cause'),
						'type'=> 'combobox',
						'options'=> array(
							'desc'=>__('Descending Order', 'gdlr-cause'), 
							'asc'=> __('Ascending Order', 'gdlr-cause'), 
						)
					),			
					'pagination'=> array(
						'title'=> __('Enable Pagination' ,'gdlr-cause'),
						'type'=> 'checkbox'
					),					
					'margin-bottom' => array(
						'title' => __('Margin Bottom', 'gdlr-cause'),
						'type' => 'text',
						'default' => $gdlr_spaces['bottom-blog-item'],
						'description' => __('Spaces after ending of this item', 'gdlr-cause')
					),				
				)
			);

			$page_builder['content-item']['options']['urgent-cause'] = array(
				'title'=> __('Urgent Cause', 'gdlr-cause'), 
				'type'=>'item',
				'options'=>array(		
					'title'=> array(	
						'title'=> __('Title' ,'gdlr-cause'),
						'type'=> 'text',
						'default'=> __('Urgent Cause','gdlr-cause')
					),		
					'cause'=> array(	
						'title'=> __('Select Cause' ,'gdlr-cause'),
						'type'=> 'combobox',
						'options'=> gdlr_get_post_list('cause')
					),	
					'num-excerpt'=> array(
						'title'=> __('Num Excerpt (Word)' ,'gdlr_translate'),
						'type'=> 'text',	
						'default'=> '40',
						'description'=> __('This is a number of word (decided by spaces) that you want to show on the post excerpt.', 'gdlr-cause')
					),
					'min-height'=> array(	
						'title'=> __('Min Height (Pixel)' ,'gdlr-cause'),
						'type'=> 'text',
						'default'=> ''
					),
					'margin-bottom' => array(
						'title' => __('Margin Bottom', 'gdlr-cause'),
						'type' => 'text',
						'default' => $gdlr_spaces['bottom-blog-item'],
						'description' => __('Spaces after ending of this item', 'gdlr-cause')
					),
				)
			);
			
			return $page_builder;
		}
	}
	
?>