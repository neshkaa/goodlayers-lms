<?php
	/*	
	*	Goodlayers Post Option file
	*	---------------------------------------------------------------------
	*	This file creates all post options to the post page
	*	---------------------------------------------------------------------
	*/
	
	// add a post admin option
	add_filter('gdlr_admin_option', 'gdlr_register_post_admin_option');
	if( !function_exists('gdlr_register_post_admin_option') ){
		function gdlr_register_post_admin_option( $array ){		
			if( empty($array['general']['options']) ) return $array;
			
			global $gdlr_sidebar_controller;
			$post_option = array(
				'title' => __('Blog Style', 'gdlr_translate'),
				'options' => array(
					'post-title' => array(
						'title' => __('Default Post Title', 'gdlr_translate'),
						'type' => 'text',	
						'default' => 'Single Blog Title'
					),
					'post-caption' => array(
						'title' => __('Default Post Caption', 'gdlr_translate'),
						'type' => 'textarea',
						'default' => 'This is a single blog caption'
					),			
					'post-thumbnail-size' => array(
						'title' => __('Single Post Thumbnail Size', 'gdlr_translate'),
						'type'=> 'combobox',
						'options'=> gdlr_get_thumbnail_list(),
						'default'=> 'post-thumbnail-size'
					),
					'post-meta-data' => array(
						'title' => __('Disable Post Meta Data', 'gdlr_translate'),
						'type'=> 'multi-combobox',
						'options'=> array(
							'date'=>'Date',
							'tag'=>'Tag',
							'category'=>'Category',
							'comment'=>'Comment',
							'author'=>'Author',
						),
						'description'=> __('Select this to remove the meta data out of the post.<br><br>', 'gdlr_translate') .
							__('You can use Ctrl/Command button to select multiple option or remove the selected option.', 'gdlr_translate')
					),
					'single-post-author' => array(
						'title' => __('Enable Single Post Author', 'gdlr_translate'),
						'type'=> 'checkbox'
					),
					'post-sidebar-template' => array(
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
					'post-sidebar-left' => array(
						'title' => __('Default Post Sidebar Left', 'gdlr_translate'),
						'type' => 'combobox',
						'options' => $gdlr_sidebar_controller->get_sidebar_array(),		
						'wrapper-class'=>'left-sidebar-wrapper both-sidebar-wrapper post-sidebar-template-wrapper',											
					),
					'post-sidebar-right' => array(
						'title' => __('Default Post Sidebar Right', 'gdlr_translate'),
						'type' => 'combobox',
						'options' => $gdlr_sidebar_controller->get_sidebar_array(),
						'wrapper-class'=>'right-sidebar-wrapper both-sidebar-wrapper post-sidebar-template-wrapper',
					),										
				)
			);
			
			
			$array['general']['options']['blog-style'] = $post_option;
			return $array;
		}
	}		

	// add a post option to post page
	if( is_admin() ){ add_action('init', 'gdlr_create_post_options'); }
	if( !function_exists('gdlr_create_post_options') ){
	
		function gdlr_create_post_options(){
			global $gdlr_sidebar_controller;
			
			if( !class_exists('gdlr_page_options') ) return;
			new gdlr_page_options( 
				
				// page option attribute
				array(
					'post_type' => array('post'),
					'meta_title' => __('Goodlayers Post Option', 'gdlr_translate'),
					'meta_slug' => 'goodlayers-page-option',
					'option_name' => 'post-option',
					'position' => 'normal',
					'priority' => 'high',
				),
					  
				// page option settings
				array(
					'page-layout' => array(
						'title' => __('Page Layout', 'gdlr_translate'),
						'options' => array(
								'sidebar' => array(
									'title' => __('Sidebar Template' , 'gdlr_translate'),
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
									'title' => __('Left Sidebar' , 'gdlr_translate'),
									'type' => 'combobox',
									'options' => $gdlr_sidebar_controller->get_sidebar_array(),
									'wrapper-class' => 'sidebar-wrapper left-sidebar-wrapper both-sidebar-wrapper'
								),
								'right-sidebar' => array(
									'title' => __('Right Sidebar' , 'gdlr_translate'),
									'type' => 'combobox',
									'options' => $gdlr_sidebar_controller->get_sidebar_array(),
									'wrapper-class' => 'sidebar-wrapper right-sidebar-wrapper both-sidebar-wrapper'
								),						
						)
					),
					
					'page-option' => array(
						'title' => __('Page Option', 'gdlr_translate'),
						'options' => array(
							'page-title' => array(
								'title' => __('Post Title' , 'gdlr_translate'),
								'type' => 'text',
								'description' => __('Leave this field blank to use the default title from admin panel > general > blog style section.', 'gdlr_translate')
							),
							'page-caption' => array(
								'title' => __('Post Caption' , 'gdlr_translate'),
								'type' => 'textarea'
							),							
						'thumbnail-type' => array(
								'title' => __('Thumbnail Type' , 'gdlr-kot'),
								'type' => 'combobox',
								'options' => array(
									'feature-image'=> __('Feature Image', 'gdlr-kot'),
									'video'=> __('Video', 'gdlr-kot'),
									'slider'=> __('Slider', 'gdlr-kot')
								),
								'wrapper-class' => 'gdlr-top-divider'
							),
							'thumbnail-link' => array(
								'title' => __('Thumbnail Link' , 'gdlr-kot'),
								'type' => 'combobox',
								'options' => array(
									'current-post'=> __('Link to Kot', 'gdlr-kot'),
									'current'=> __('Lightbox to Full Image', 'gdlr-kot'),
									'url'=> __('Link to URL', 'gdlr-kot'),
									'image'=> __('Lightbox Image', 'gdlr-kot'),
									'video'=> __('Lightbox Video', 'gdlr-kot')
								),
								'wrapper-class' => 'thumbnail-type-wrapper feature-image-wrapper'
							),
							'thumbnail-url' => array(
								'title' => __('Specify Url' , 'gdlr-kot'),
								'type' => 'text',
								'wrapper-class' => 'thumbnail-link-wrapper video-wrapper image-wrapper url-wrapper'
							),
							'thumbnail-new-tab' => array(
								'title' => __('Open In New Tab' , 'gdlr-kot'),
								'type' => 'checkbox',
								'wrapper-class' => 'thumbnail-link-wrapper url-wrapper'
							),
							'thumbnail-video' => array(
								'title' => __('Video Url' , 'gdlr-kot'),
								'type' => 'text',
								'wrapper-class' => 'video-wrapper thumbnail-type-wrapper'
							),
							'thumbnail-slider' => array(
								'title' => __('Slider' , 'gdlr-kot'),
								'type' => 'slider',
								'wrapper-class' => 'slider-wrapper thumbnail-type-wrapper'
							),
							'inside-thumbnail-type' => array(
								'title' => __('Inside Kot Thumbnail Type' , 'gdlr-kot'),
								'type' => 'combobox',
								'options' => array(
									'thumbnail-type'=> __('Same As Thumbnail Type', 'gdlr-kot'),
									'image'=> __('Image', 'gdlr-kot'),
									'video'=> __('Video', 'gdlr-kot'),
									'slider'=> __('Slider', 'gdlr-kot'),
									'gallery'=> __('Gallery', 'gdlr-kot'),
									'stack-image'=> __('Stack Images', 'gdlr-kot')
								),
								'wrapper-class' => 'gdlr-top-divider'
							),
							'inside-thumbnail-image' => array(
								'title' => __('Image Url' , 'gdlr-kot'),
								'type' => 'upload',
								'wrapper-class' => 'image-wrapper inside-thumbnail-type-wrapper'
							),
							'inside-thumbnail-video' => array(
								'title' => __('Video Url' , 'gdlr-kot'),
								'type' => 'text',
								'wrapper-class' => 'video-wrapper inside-thumbnail-type-wrapper'
							),
							'inside-thumbnail-slider' => array(
								'title' => __('Slider' , 'gdlr-kot'),
								'type' => 'slider',
								'wrapper-class' => 'stack-image-wrapper slider-wrapper gallery-wrapper inside-thumbnail-type-wrapper'
							),
							'inside-gallery-columns' => array(
								'title' => __('Inside Gallery Columns' , 'gdlr-kot'),
								'type' => 'combobox',
								'options'=> array('1'=>'1', '2'=>'2', '3'=>'3', '4'=>'4', '5'=>'5', '6'=>'6'),
								'default'=> 3,
								'wrapper-class' => 'gallery-wrapper inside-thumbnail-type-wrapper'
							),
							'inside-gallery-thumbnail' => array(
								'title' => __('Inside Gallery Thumbnail Size' , 'gdlr-kot'),
								'type' => 'combobox',
								'options'=> gdlr_get_thumbnail_list(),
								'wrapper-class' => 'gallery-wrapper inside-thumbnail-type-wrapper'
							),
							'inside-gallery-caption' => array(
								'title' => __('Enable Inside Gallery Caption' , 'gdlr-kot'),
								'type' => 'combobox',
								'options'=> array('yes'=>'Yes', 'no'=>'No'),
								'wrapper-class' => 'gallery-wrapper inside-thumbnail-type-wrapper'
							),
						)
					),

				)
			);

		}
	}

	// add kot in page builder area
	add_filter('gdlr_page_builder_option', 'gdlr_register_kot_item');
	if( !function_exists('gdlr_register_kot_item') ){
		function gdlr_register_kot_item( $page_builder = array() ){
			global $gdlr_spaces;

			$page_builder['content-item']['options']['kot'] = array(
				'title'=> __('Kot', 'gdlr-kot'),
				'type'=>'item',
				'options'=>array(
					'title-type'=> array(
						'title'=> __('Title Type' ,'gdlr-kot'),
						'type'=> 'combobox',
						'options'=> array(
							'none'=> __('None' ,'gdlr-kot'),
							'icon'=> __('Title With Icon' ,'gdlr-kot'),
							'left'=> __('Left Align With Icon' ,'gdlr-kot'),
							'center'=> __('Center Align With Caption' ,'gdlr-kot')
						)
					),
					'icon'=> array(
						'title'=> __('Icon' ,'gdlr-kot'),
						'type'=> 'text',
						'wrapper-class'=>'title-type-wrapper icon-wrapper left-wrapper'
					),
					'title'=> array(
						'title'=> __('Title' ,'gdlr-kot'),
						'type'=> 'text',
						'wrapper-class'=>'title-type-wrapper left-wrapper center-wrapper icon-wrapper'
					),
					'caption'=> array(
						'title'=> __('Caption' ,'gdlr-kot'),
						'type'=> 'textarea',
						'wrapper-class'=>'title-type-wrapper center-wrapper'
					),
					'right-text'=> array(
						'title'=> __('Title Right Text' ,'gdlr-kot'),
						'type'=> 'text',
						'default'=> __('View All Works', 'gdlr-kot'),
						'wrapper-class'=>'title-type-wrapper left-wrapper center-wrapper icon-wrapper'
					),
					'right-text-link'=> array(
						'title'=> __('Title Right Text Link' ,'gdlr-kot'),
						'type'=> 'text',
						'wrapper-class'=>'title-type-wrapper left-wrapper center-wrapper icon-wrapper'
					),
					'category'=> array(
						'title'=> __('Category' ,'gdlr-kot'),
						'type'=> 'multi-combobox',
						'options'=> gdlr_get_term_list('kot_category'),
						'description'=> __('You can use Ctrl/Command button to select multiple categories or remove the selected category. <br><br> Leave this field blank to select all categories.', 'gdlr-kot')
					),
					'tag'=> array(
						'title'=> __('Tag' ,'gdlr-kot'),
						'type'=> 'multi-combobox',
						'options'=> gdlr_get_term_list('kot_tag'),
						'description'=> __('Will be ignored when the kot filter option is enabled.', 'gdlr-kot')
					),
					'kot-style'=> array(
						'title'=> __('Kot Style' ,'gdlr-kot'),
						'type'=> 'combobox',
						'options'=> array(
							'classic-kot' => __('Classic Style', 'gdlr-kot'),
							'classic-kot-no-space' => __('Classic No Space Style', 'gdlr-kot'),
							'modern-kot' => __('Modern Style', 'gdlr-kot'),
							'modern-kot-no-space' => __('Modern No Space Style', 'gdlr-kot'),
						),
					),
					'num-fetch'=> array(
						'title'=> __('Num Fetch' ,'gdlr-kot'),
						'type'=> 'text',
						'default'=> '8',
						'description'=> __('Specify the number of kots you want to pull out.', 'gdlr-kot')
					),
					'kot-size'=> array(
						'title'=> __('Kot Size' ,'gdlr-kot'),
						'type'=> 'combobox',
						'options'=> array(
							'1/4'=>'1/4',
							'1/3'=>'1/3',
							'1/2'=>'1/2',
							'1/1'=>'1/1'
						),
						'default'=>'1/3'
					),
					'kot-layout'=> array(
						'title'=> __('Kot Layout Order' ,'gdlr-kot'),
						'type'=> 'combobox',
						'options'=> array(
							'fitRows' =>  __('FitRows ( Order items by row )', 'gdlr-kot'),
							'masonry' => __('Masonry ( Order items by spaces )', 'gdlr-kot'),
							'carousel' => __('Carousel ( Only For Grid And Modern Style )', 'gdlr-kot'),
						),
						'description'=> __('You can see an example of these two layout here', 'gdlr-kot') .
							'<br><br> http://isotope.metafizzy.co/demos/layout-modes.html'
					),
					'kot-filter'=> array(
						'title'=> __('Enable Kot filter' ,'gdlr-kot'),
						'type'=> 'checkbox',
						'default'=> 'disable',
						'description'=> __('*** You have to select only 1 ( or none ) kot category when enable this option','gdlr-kot')
					),
					'thumbnail-size'=> array(
						'title'=> __('Thumbnail Size' ,'gdlr-kot'),
						'type'=> 'combobox',
						'options'=> gdlr_get_thumbnail_list(),
						'description'=> __('Only effects to <strong>standard and gallery post format</strong>','gdlr-kot')
					),
					'orderby'=> array(
						'title'=> __('Order By' ,'gdlr-kot'),
						'type'=> 'combobox',
						'options'=> array(
							'date' => __('Publish Date', 'gdlr-kot'),
							'title' => __('Title', 'gdlr-kot'),
							'rand' => __('Random', 'gdlr-kot'),
						)
					),
					'order'=> array(
						'title'=> __('Order' ,'gdlr-kot'),
						'type'=> 'combobox',
						'options'=> array(
							'desc'=>__('Descending Order', 'gdlr-kot'),
							'asc'=> __('Ascending Order', 'gdlr-kot'),
						)
					),
					'pagination'=> array(
						'title'=> __('Enable Pagination' ,'gdlr-kot'),
						'type'=> 'checkbox'
					),
					'margin-bottom' => array(
						'title' => __('Margin Bottom', 'gdlr-kot'),
						'type' => 'text',
						'default' => $gdlr_spaces['bottom-blog-item'],
						'description' => __('Spaces after ending of this item', 'gdlr-kot')
					),
				)
			);
			return $page_builder;
		}
	}

?>