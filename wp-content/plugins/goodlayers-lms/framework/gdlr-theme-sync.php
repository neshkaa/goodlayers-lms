<?php
	/*
	*	Function to sync with goodlayers theme
	*/

	// add course in page builder area
	add_filter('gdlr_page_builder_option', 'gdlr_register_course_item');
	function gdlr_register_course_item( $page_builder = array() ){
		if( !function_exists('gdlr_page_builder_title_option') ){
			function gdlr_page_builder_title_option(){ return array(); }
		}

		global $gdlr_spaces;

		$page_builder['content-item']['options']['course'] = array(
			'title'=> esc_html__('Course', 'gdlr-lms'),
			'type'=>'item',
			'options'=>array_merge(gdlr_page_builder_title_option(true), array(
				'category'=> array(
					'title'=> esc_html__('Category' ,'gdlr-lms'),
					'type'=> 'multi-combobox',
					'options'=> gdlr_get_term_list('course_category'),
					'description'=> esc_html__('You can use Ctrl/Command button to select multiple categories or remove the selected category. <br><br> Leave this field blank to select all categories.', 'gdlr-lms')
				),
				'course-style'=> array(
					'title'=> esc_html__('Course Style' ,'gdlr-lms'),
					'type'=> 'combobox',
					'options'=> array(
						'grid' => esc_html__('Grid Style', 'gdlr-lms'),
						'grid-2' => esc_html__('Grid 2nd Style', 'gdlr-lms'),
						'medium' => esc_html__('Medium Style', 'gdlr-lms'),
						'full' => esc_html__('Full Style', 'gdlr-lms'),
					),
				),
				'num-fetch'=> array(
					'title'=> esc_html__('Num Fetch' ,'gdlr-lms'),
					'type'=> 'text',
					'default'=> '8',
					'description'=> esc_html__('Specify the number of courses you want to pull out.', 'gdlr-lms')
				),
				'num-excerpt'=> array(
					'title'=> esc_html__('Num Excerpt' ,'gdlr-lms'),
					'type'=> 'text',
					'default'=> '20',
					'wrapper-class'=>'course-style-wrapper full-wrapper'
				),
				'course-size'=> array(
					'title'=> esc_html__('Course Size' ,'gdlr-lms'),
					'type'=> 'combobox',
					'options'=> array(
						'4'=>'1/4',
						'3'=>'1/3',
						'2'=>'1/2',
						'1'=>'1/1'
					),
					'default'=>'3',
					'wrapper-class'=>'course-style-wrapper grid-wrapper grid-2-wrapper'
				),
				'course-layout'=> array(
					'title'=> esc_html__('Course Layout Order' ,'gdlr-lms'),
					'type'=> 'combobox',
					'options'=> array(
						'fitRows' =>  esc_html__('FitRows ( Order items by row )', 'gdlr-lms'),
						'carousel' => esc_html__('Carousel ( Only For Grid Style )', 'gdlr-lms'),
					),
					'wrapper-class'=>'course-style-wrapper grid-wrapper grid-2-wrapper'
				),
				'thumbnail-size'=> array(
					'title'=> esc_html__('Thumbnail Size' ,'gdlr-lms'),
					'type'=> 'combobox',
					'options'=> gdlr_get_thumbnail_list()
				),
				'orderby'=> array(
					'title'=> esc_html__('Order By' ,'gdlr-lms'),
					'type'=> 'combobox',
					'options'=> array(
						'date' => esc_html__('Publish Date', 'gdlr-lms'),
						'start-date' => esc_html__('Start Date', 'gdlr-lms'),
						'title' => esc_html__('Title', 'gdlr-lms'),
						'rand' => esc_html__('Random', 'gdlr-lms'),
					)
				),
				'order'=> array(
					'title'=> esc_html__('Order' ,'gdlr-lms'),
					'type'=> 'combobox',
					'options'=> array(
						'desc'=>esc_html__('Descending Order', 'gdlr-lms'),
						'asc'=> esc_html__('Ascending Order', 'gdlr-lms'),
					)
				),
				'pagination'=> array(
					'title'=> esc_html__('Enable Pagination' ,'gdlr-lms'),
					'type'=> 'checkbox'
				),
				'margin-bottom' => array(
					'title' => esc_html__('Margin Bottom', 'gdlr-lms'),
					'type' => 'text',
					'default' => $gdlr_spaces['bottom-blog-item'],
					'description' => esc_html__('Spaces after ending of this item', 'gdlr-lms')
				),
			))
		);

		$page_builder['content-item']['options']['course-search'] = array(
			'title'=> esc_html__('Course Search', 'gdlr-lms'),
			'type'=>'item',
			'options'=>array_merge(gdlr_page_builder_title_option(true), array(
				'margin-bottom' => array(
					'title' => esc_html__('Margin Bottom', 'gdlr-lms'),
					'type' => 'text',
					'default' => $gdlr_spaces['bottom-blog-item'],
					'description' => esc_html__('Spaces after ending of this item', 'gdlr-lms')
				)
			))
		);

		return $page_builder;
	}

	// add action to check for course item
	add_action('gdlr_print_item_selector', 'gdlr_check_course_item', 10, 2);
	function gdlr_check_course_item( $type, $settings = array() ){
		if($type == 'course'){
			echo gdlr_lms_print_course_item($settings, true);
		}else if($type == 'course-search'){
			echo gdlr_lms_print_course_search($settings, true);
		}
	}

	// add instructor in page builder area
	add_filter('gdlr_page_builder_option', 'gdlr_register_instructor_item');
	function gdlr_register_instructor_item( $page_builder = array() ){
		if( !function_exists('gdlr_page_builder_title_option') ){
			function gdlr_page_builder_title_option(){ return array(); }
		}

		global $gdlr_spaces;

		$page_builder['content-item']['options']['instructor'] = array(
			'title'=> esc_html__('Instructor', 'gdlr-lms'),
			'type'=>'item',
			'options'=>array_merge(gdlr_page_builder_title_option(true), array(
				'instructor-type' => array(
					'title'=> esc_html__('Instructor Type' ,'gdlr-lms'),
					'type'=> 'combobox',
					'options'=> array(
						'single' => esc_html__('Single Instructor', 'gdlr-lms'),
						'multiple' => esc_html__('Instructor List', 'gdlr-lms')
					),
				),
				'user'=> array(
					'title'=> esc_html__('Select User' ,'gdlr-lms'),
					'type'=> 'combobox',
					'options'=> gdlr_lms_get_user_list(),
					'wrapper-class' => 'instructor-type-wrapper single-wrapper'
				),
				'role'=> array(
					'title'=> esc_html__('Role' ,'gdlr-lms'),
					'type'=> 'combobox',
					'options'=> gdlr_lms_get_role_list(),
					'wrapper-class' => 'instructor-type-wrapper multiple-wrapper'
				),
				'instructor-style'=> array(
					'title'=> esc_html__('Instructor Style' ,'gdlr-lms'),
					'type'=> 'combobox',
					'options'=> array(
						'grid' => esc_html__('Grid Style', 'gdlr-lms'),
						'grid-2' => esc_html__('Grid 2nd Style', 'gdlr-lms')
					),
				),
				'num-fetch'=> array(
					'title'=> esc_html__('Num Fetch' ,'gdlr-lms'),
					'type'=> 'text',
					'default'=> '8',
					'wrapper-class' => 'instructor-type-wrapper multiple-wrapper',
					'description'=> esc_html__('Specify the number of instructor you want to pull out.', 'gdlr-lms')
				),
				'num-excerpt'=> array(
					'title'=> esc_html__('Num Excerpt' ,'gdlr-lms'),
					'type'=> 'text',
					'default'=> '20'
				),
				'instructor-size'=> array(
					'title'=> esc_html__('Instructor Size' ,'gdlr-lms'),
					'type'=> 'combobox',
					'options'=> array(
						'4'=>'1/4',
						'3'=>'1/3',
						'2'=>'1/2',
						'1'=>'1/1'
					),
					'default'=>'3',
					'wrapper-class' => 'instructor-type-wrapper multiple-wrapper'
				),
				'thumbnail-size'=> array(
					'title'=> esc_html__('Thumbnail Size' ,'gdlr-lms'),
					'type'=> 'combobox',
					'options'=> gdlr_get_thumbnail_list()
				),
				'orderby'=> array(
					'title'=> esc_html__('Order By' ,'gdlr-lms'),
					'type'=> 'combobox',
					'options'=> array(
						'display_name' => esc_html__('Display Name', 'gdlr-lms'),
						'ID' => esc_html__('ID', 'gdlr-lms'),
						'post_count' => esc_html__('Post Count', 'gdlr-lms'),
					),
					'wrapper-class' => 'instructor-type-wrapper multiple-wrapper'
				),
				'order'=> array(
					'title'=> esc_html__('Order' ,'gdlr-lms'),
					'type'=> 'combobox',
					'options'=> array(
						'asc'=> esc_html__('Ascending Order', 'gdlr-lms'),
						'desc'=>esc_html__('Descending Order', 'gdlr-lms')
					),
					'wrapper-class' => 'instructor-type-wrapper multiple-wrapper'
				),
				//'pagination'=> array(
				//	'title'=> esc_html__('Enable Pagination' ,'gdlr-lms'),
				//	'type'=> 'checkbox'
				//),
				'margin-bottom' => array(
					'title' => esc_html__('Margin Bottom', 'gdlr-lms'),
					'type' => 'text',
					'default' => $gdlr_spaces['bottom-blog-item'],
					'description' => esc_html__('Spaces after ending of this item', 'gdlr-lms')
				),
			))
		);

		return $page_builder;
	}

	// add action to check for instructor item
	add_action('gdlr_print_item_selector', 'gdlr_check_instructor_item', 10, 2);
	function gdlr_check_instructor_item( $type, $settings = array() ){
		if($type == 'instructor'){
			echo gdlr_lms_print_instructor_item($settings, true);
		}
	}

?>
