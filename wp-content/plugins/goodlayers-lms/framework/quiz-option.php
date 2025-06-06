<?php
	/*	
	*	Goodlayers Quiz Option File
	*/	
	 
	// create the quiz post type
	add_action( 'init', 'gdlr_lms_create_quiz' );
	function gdlr_lms_create_quiz() {
		register_post_type( 'quiz',
			array(
				'labels' => array(
					'name'               => esc_html__('Quiz', 'gdlr-lms'),
					'singular_name'      => esc_html__('Quiz', 'gdlr-lms'),
					'add_new'            => esc_html__('Add New', 'gdlr-lms'),
					'add_new_item'       => esc_html__('Add New Quiz', 'gdlr-lms'),
					'edit_item'          => esc_html__('Edit Quiz', 'gdlr-lms'),
					'new_item'           => esc_html__('New Quiz', 'gdlr-lms'),
					'all_items'          => esc_html__('All Quizzes', 'gdlr-lms'),
					'view_item'          => esc_html__('View Quiz', 'gdlr-lms'),
					'search_items'       => esc_html__('Search Quiz', 'gdlr-lms'),
					'not_found'          => esc_html__('No quiz found', 'gdlr-lms'),
					'not_found_in_trash' => esc_html__('No quiz found in Trash', 'gdlr-lms'),
					'parent_item_colon'  => '',
					'menu_name'          => esc_html__('Quizzes', 'gdlr-lms')
				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				//'rewrite'            => array( 'slug' => 'quiz'  ),
				'capability_type'    => array('quiz', 'quizzes'),
				'map_meta_cap' 		 => true,
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 5,
				'supports'           => array( 'title', 'author', 'thumbnail', 'custom-fields' )
			)
		);
	}

	// enqueue the necessary script
	add_action('admin_enqueue_scripts', 'gdlr_lms_quiz_script');
	function gdlr_lms_quiz_script() {
		global $post; if( empty($post) || $post->post_type != 'quiz' ) return;
		
		gdlr_lms_include_jquery_ui_style();
		wp_enqueue_style('gdlr-lms-meta-box', plugins_url('/stylesheet/meta-box.css', __FILE__));
		
		wp_enqueue_script('jquery-ui-datepicker');	
		wp_enqueue_script('jquery-ui-core');
		wp_enqueue_script('jquery-ui-slider');	
		wp_enqueue_script('gdlr-lms-quiz-question', plugins_url('/javascript/quiz-question.js', __FILE__));
		wp_enqueue_script('gdlr-lms-meta-box', plugins_url('/javascript/meta-box.js', __FILE__));
	}

	// add the quiz option
	add_action('add_meta_boxes', 'gdlr_lms_add_quiz_meta_box');	
	add_action('pre_post_update', 'gdlr_lms_save_quiz_meta_box');
	function gdlr_lms_add_quiz_meta_box(){
		add_meta_box('quiz-option', esc_html__('Quiz Option', 'gdlr-lms'), 
			'gdlr_lms_create_quiz_meta_box', 'quiz', 'normal', 'high');
	}
	function gdlr_lms_create_quiz_meta_box(){
		global $post;
		
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'quiz_meta_box', 'quiz_meta_box_nonce' );

		////////////////////////////////
		//// quiz setting section ////
		////////////////////////////////
		
		$quiz_settings = array(
			'retake-quiz' => array(
				'title' => esc_html__('Allow to retake this quiz', 'gdlr-lms'),
				'type' => 'checkbox',
				'default' => 'enable'
			),
			'retake-times' => array(
				'title' => esc_html__('Number of quiz retakes', 'gdlr-lms'),
				'type' => 'text',
				'class' => 'small',
				'description' => esc_html__('times', 'gdlr-lms')
			)
		);
		$quiz_val = gdlr_lms_decode_preventslashes(get_post_meta($post->ID, 'gdlr-lms-quiz-settings', true));
		$quiz_settings_val = empty($quiz_val)? array(): json_decode($quiz_val, true);
		
		echo '<div class="gdlr-lms-meta-wrapper">';
		echo '<h3>' . esc_html__('Quiz Settings', 'gdlr-lms') . '</h3>';
		foreach($quiz_settings as $slug => $quiz_setting){
			$quiz_setting['slug'] = $slug;
			$quiz_setting['value'] = empty($quiz_settings_val[$slug])? '': $quiz_settings_val[$slug];
			gdlr_lms_print_meta_box($quiz_setting);
		}
		echo '<textarea name="gdlr-lms-quiz-settings">' . esc_textarea($quiz_val) . '</textarea>';
		echo '</div>';
		
		/////////////////////
		//// tab section ////
		/////////////////////
		
		$course_content_options = array(
			'section-name' => array(
				'title' => esc_html__('Section Name', 'gdlr-lms'),
				'type' => 'text'
			),
			'question-type' => array(
				'title' => esc_html__('Question Type', 'gdlr-lms'),
				'type' => 'combobox',
				'options' => array(
					'single' => esc_html__('Single Choice', 'gdlr-lms'),
					'multiple' => esc_html__('Multiple Choice', 'gdlr-lms'),
					'small' => esc_html__('Small Fill', 'gdlr-lms'),
					'large' => esc_html__('Large Fill', 'gdlr-lms'),
				),
				'description' => esc_html__('( Manual marking the score for small and large fill )', 'gdlr-lms')
			),
			'section-timer' => array(
				'title' => esc_html__('Section Timer', 'gdlr-lms'),
				'type' => 'checkbox'
			),
			'time-period' => array(
				'title' => esc_html__('Time Period', 'gdlr-lms'),
				'type' => 'text',
				'class' => 'small',
				'description' => esc_html__('Minutes', 'gdlr-lms')
			),
			'question' => array(
				'type' => 'question'
			)
		);			
		$course_content_val = gdlr_lms_decode_preventslashes(get_post_meta($post->ID, 'gdlr-lms-content-settings', true));
		$course_content_options_val = empty($course_content_val)? array(): json_decode($course_content_val, true);		
		
		echo '<div class="gdlr-lms-meta-wrapper gdlr-tabs">';
		echo '<h3>' . esc_html__('Course Content', 'gdlr-lms') . '</h3>';
		echo '<div class="course-tab-add-new">';
		echo '<span class="head">+</span>';
		echo '<span class="tail">' . esc_html__('Add Section', 'gdlr-lms') . '</span>';
		echo '</div>'; // course-tab-add-new
		echo '<div class="course-tab-title">';
		echo '<span class="active">1</span>';
		for( $i = 2; $i <= sizeof($course_content_options_val); $i++ ){
			echo '<span>' . $i . '</span>';
		} 
		echo '</div>'; // course-tab-title
		echo '<div class="course-tab-content">';
		echo '<div class="course-tab-remove">' . esc_html__('Delete', 'gdlr-lms') . '</div>';
		foreach($course_content_options as $slug => $course_content_option){
			$course_content_option['slug'] = $slug;
			$course_content_option['value'] = empty($course_content_options_val[0][$slug])? '': $course_content_options_val[0][$slug];
			gdlr_lms_print_meta_box($course_content_option);
		}		
		echo '</div>'; // course-tab-content
		echo '<textarea name="gdlr-lms-content-settings">' . esc_textarea($course_content_val) . '</textarea>';
		echo '</div>';	
	}
	function gdlr_lms_save_quiz_meta_box($post_id){
	
		// verify nonce & user's permission
		if(!isset($_POST['quiz_meta_box_nonce'])){ return; }
		if(!wp_verify_nonce($_POST['quiz_meta_box_nonce'], 'quiz_meta_box')){ return; }
		if(!current_user_can('edit_post', $post_id)){ return; }

		// save value
		if( isset($_POST['gdlr-lms-quiz-settings']) ){
			update_post_meta($post_id, 'gdlr-lms-quiz-settings', gdlr_lms_preventslashes($_POST['gdlr-lms-quiz-settings']));
		}
		if( isset($_POST['gdlr-lms-content-settings']) ){
			update_post_meta($post_id, 'gdlr-lms-content-settings', gdlr_lms_preventslashes($_POST['gdlr-lms-content-settings']));
		}
		
	}

?>