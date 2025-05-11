<?php
	/*	
	*	Goodlayers Course File
	*/		


	// get quiz option array
	if( !function_exists('gdlr_lms_get_quiz_options') ){
		function gdlr_lms_get_quiz_options( $post_id ){
			$quiz_val = gdlr_lms_decode_preventslashes(get_post_meta($post_id, 'gdlr-lms-quiz-settings', true));
			if( empty($quiz_val) ){
				$quiz_val = array();
			}else{
				$quiz_val = json_decode($quiz_val, true);
			}

			$quiz_val['retake-quiz'] = empty($quiz_val['retake-quiz'])? 'disable': $quiz_val['retake-quiz'];
			$quiz_val['retake-times'] = empty($quiz_val['retake-times'])? 9999: $quiz_val['retake-times'];
			// * after view quiz answer, the retake times will be set to 9999
			return $quiz_val;

		} // gdlr_lms_get_quiz_options
	} // function_exists

	// return 'please-login-first', 'new-quiz', 'old-quiz-retakable', 'old-quiz-disable'
	if( !function_exists('gdlr_lms_quiz_status') ){
		function gdlr_lms_quiz_status( $quiz_id, $course_id, $user_id, $section_id = '' ){

			if( !is_user_logged_in() ){
				return 'please-login-first';
			}

			$quiz_row = gdlr_lms_get_quiz_row($quiz_id, $course_id, $user_id, $section_id);

			// previously take a quiz
			if( !empty($quiz_row) && ($quiz_row->quiz_status == 'complete' || $quiz_row->quiz_status == 'submitted') ){
				$quiz_options = gdlr_lms_get_quiz_options($quiz_id);
				
				// if retake enable && retake times < value && not yet view an answer
				if( $quiz_options['retake-quiz'] == 'enable' && $quiz_row->retake_times < intval($quiz_options['retake-times']) && $quiz_row->retake_times < 9999 ){
					return 'old-quiz-retakable';
				}else{
					return 'old-quiz-disable';
				}
			}else{
				return 'new-quiz';
			}


		} // gdlr_lms_quiz_status
	} // function_exists

	// get course option array
	if( !function_exists('gdlr_lms_get_course_options') ){
		function gdlr_lms_get_course_options( $post_id ){
			$course_val = gdlr_lms_decode_preventslashes(get_post_meta($post_id, 'gdlr-lms-course-settings', true));
			
			if( empty($course_val) ){
				return array();
			}else{
				$course_options = json_decode($course_val, true);
				$course_options['price-one'] = !empty($course_options['discount-price'])? floatval($course_options['discount-price']): floatval($course_options['price']);
				
				return 	$course_options;
			}
		}
	}
	
	// get content settings array
	if( !function_exists('gdlr_lms_get_course_content_settings') ){
		function gdlr_lms_get_course_content_settings( $post_id ){
			$course_val = gdlr_lms_decode_preventslashes(get_post_meta($post_id, 'gdlr-lms-content-settings', true));
			$course_val = empty($course_val)? array(): json_decode($course_val, true);
			
			// for old data
			foreach($course_val as $tabs_key => $tabs_value){
				if( empty($tabs_value['lecture-section']) ){
					$course_val[$tabs_key]['lecture-section'] = array(0=>array());
					if( !empty($tabs_value['pdf-download-link']) ){
						 $course_val[$tabs_key]['lecture-section'][0]['pdf-download-link'] = $tabs_value['pdf-download-link'];
					}
					if( !empty($tabs_value['course-content']) ){
						$course_val[$tabs_key]['lecture-section'][0]['lecture-content'] = $tabs_value['course-content'];
					}
					$course_val[$tabs_key]['lecture-section'] = json_encode($course_val[$tabs_key]['lecture-section']);
					$old_data = true;
				}
			}	
			
			return $course_val;
		}
	}
	
	// print course search
	if( !function_exists('gdlr_lms_print_course_search') ){
		function gdlr_lms_print_course_search( $settings, $page_builder = false ){
		
			if( $page_builder ){
				$item_id = empty($settings['page-item-id'])? '': ' id="' . $settings['page-item-id'] . '" ';

				global $gdlr_spaces;
				$margin = (!empty($settings['margin-bottom']) && 
					$settings['margin-bottom'] != $gdlr_spaces['bottom-blog-item'])? 'margin-bottom: ' . $settings['margin-bottom'] . ';': '';
				$margin_style = (!empty($margin))? ' style="' . $margin . '" ': '';		
			
				echo gdlr_get_item_title($settings);
			}else{
				$item_id = ''; $margin_style= "";
			}

			$search_val = get_search_query();
			if( empty($search_val) ){
				$search_val = esc_html__("Keywords" , "gdlr-lms");
			}
			$categories = gdlr_lms_get_term_list('course_category');
			
			echo '<div class="course-search-wrapper" ' . $item_id . $margin_style . ' >';
?>
<form class="gdlr-lms-form" action="<?php echo home_url(); ?>/" >
	<div class="course-search-column gdlr-lms-1">
		<span class="gdlr-lms-combobox">
			<select name="course_category" >
				<option value="" ><?php esc_html_e('Category', 'gdlr-lms'); ?></option>
				<?php
					foreach( $categories as $slug => $category ){
						echo '<option value="' . $slug . '" >' . $category . '</option>';
					}
				?>
			</select>
		</span>
	</div>
	<div class="course-search-column gdlr-lms-2">
		<span class="gdlr-lms-combobox">
			<select name="course_type" id="gender" >
				<option value="" ><?php esc_html_e('Type', 'gdlr-lms'); ?></option>
				<option value="online" ><?php esc_html_e('Online Course', 'gdlr-lms'); ?></option>
				<option value="onsite" ><?php esc_html_e('Onsite Course', 'gdlr-lms'); ?></option>
			</select>
		</span>	
	</div>
	<div class="course-search-column gdlr-lms-3">
		<input type="text" name="s" id="s" autocomplete="off" placeholder="<?php echo esc_attr($search_val); ?>" />
	</div>
	<div class="course-search-column gdlr-lms-4">
		<input type="hidden" name="post_type" value="course" />
		<input class="gdlr-lms-button" type="submit" value="<?php esc_html_e('Search!', 'gdlr-lms'); ?>" />
	</div>
	<div class="clear"></div>
</form>

<?php		
			echo '</div>'; // course-search-wrapper
		}
	}
	
	// print course item
	if( !function_exists('gdlr_lms_print_course_item') ){
		function gdlr_lms_print_course_item( $settings, $page_builder = false ){

			if( $page_builder ){
				$item_id = empty($settings['page-item-id'])? '': ' id="' . $settings['page-item-id'] . '" ';

				global $gdlr_spaces;
				$margin = (!empty($settings['margin-bottom']) && 
					$settings['margin-bottom'] != $gdlr_spaces['bottom-blog-item'])? 'margin-bottom: ' . $settings['margin-bottom'] . ';': '';
				$margin_style = (!empty($margin))? ' style="' . $margin . '" ': '';		
			
				if( in_array($settings['course-style'], array('grid', 'grid-2')) &&
					$settings['course-layout'] == 'carousel' ){
					$settings['carousel'] = true;
				}
			
				echo gdlr_get_item_title($settings);
			}else{
				$item_id = ''; $margin_style= "";
			}

			echo '<div class="course-item-wrapper" ' . $item_id . $margin_style . ' >';

			// query course section
			$args = array('post_type' => 'course', 'suppress_filters' => false);
			$args['posts_per_page'] = (empty($settings['num-fetch']))? '3': $settings['num-fetch'];
			$args['orderby'] = (empty($settings['orderby']))? 'post_date': $settings['orderby'];
			if( $args['orderby'] == 'start-date' ){
				$args['orderby'] = 'meta_value';
				$args['meta_key'] = 'gdlr_course_start_date';
			}
			$args['order'] = (empty($settings['order']))? 'desc': $settings['order'];
			$args['paged'] = (get_query_var('paged'))? get_query_var('paged') : get_query_var('page');
			$args['paged'] = empty($args['paged'])? 1: $args['paged'];
		
			if( !empty($settings['category']) ){
				$args['tax_query'] = array(
					array('terms'=>explode(',', $settings['category']), 'taxonomy'=>'course_category', 'field'=>'slug')
				);		
			}			
			if( !empty($settings['course_id']) ){
				$args['post__in'] = $settings['course_id'];
			}
			$query = new WP_Query( $args );

			$settings['course-layout'] = empty($settings['course-layout'])? 'fitRows': $settings['course-layout'];
			$settings['course-size'] = empty($settings['course-size'])? 3: $settings['course-size'];		
			if( $settings['course-style'] == 'grid' ){
				if($settings['course-layout'] == 'carousel'){
					gdlr_lms_print_course_grid_carousel($query, $settings['thumbnail-size'], $settings['course-size']);
				}else{
					gdlr_lms_print_course_grid($query, $settings['thumbnail-size'], $settings['course-size']);
				}
			}else if( $settings['course-style'] == 'grid-2' ){
				if($settings['course-layout'] == 'carousel'){
					gdlr_lms_print_course_grid2_carousel($query, $settings['thumbnail-size'], $settings['course-size']);
				}else{
					gdlr_lms_print_course_grid2($query, $settings['thumbnail-size'], $settings['course-size']);
				}
			}else if( $settings['course-style'] == 'medium' ){
				gdlr_lms_print_course_medium($query, $settings['thumbnail-size']);
			}else if( $settings['course-style'] == 'full' ){
				gdlr_lms_print_course_full($query, $settings['thumbnail-size'], $settings['num-excerpt']);
			}
			
			if($settings['pagination'] == 'enable'){
				echo gdlr_lms_get_pagination($query->max_num_pages, $args['paged']);
			}		
			
			echo '</div>'; // course-item-wrapper
		}
	}

	// course full
	if( !function_exists('gdlr_lms_print_course_full') ){
		function gdlr_lms_print_course_full($query, $thumbnail, $num_excerpt = 50){
			if($num_excerpt == NULL) $num_excerpt = 50;
			
			global $gdlr_lms_excerpt_length; $gdlr_lms_excerpt_length = $num_excerpt;
			add_filter('excerpt_more', 'gdlr_lms_excerpt_more');	
			add_filter('excerpt_length', 'gdlr_lms_set_excerpt_length', 999);

			echo '<div class="gdlr-lms-course-full-wrapper">';
			while( $query->have_posts() ){ $query->the_post();
				$course_options = gdlr_lms_get_course_options(get_the_ID());
			
				echo '<div class="gdlr-lms-course-full gdlr-lms-item">';
				gdlr_lms_print_course_thumbnail($thumbnail);
				
				echo '<div class="gdlr-lms-course-info-wrapper">';
				gdlr_lms_print_course_info($course_options);
				gdlr_lms_print_course_price($course_options);
				gdlr_lms_print_course_button($course_options, array('buy', 'book'));			
				echo '</div>';
				
				echo '<div class="gdlr-lms-course-content">';
				echo '<h3 class="gdlr-lms-course-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
				
				echo gdlr_lms_print_course_rating(get_the_ID());
				
				echo '<div class="gdlr-lms-course-excerpt">' . get_the_excerpt() . '</div>';
				echo '</div>'; // course-content
				
				echo '<div class="clear"></div>';
				echo '</div>'; // course-full
			}
			wp_reset_postdata();
			
			remove_filter('excerpt_more', 'gdlr_lms_excerpt_more');	
			remove_filter('excerpt_length', 'gdlr_lms_set_excerpt_length');
			echo '</div>'; // course-full-wrapper	
		}
	}
	
	// course medium
	if( !function_exists('gdlr_lms_print_course_medium') ){
		function gdlr_lms_print_course_medium($query, $thumbnail){
			echo '<div class="gdlr-lms-course-medium-wrapper">';
			while( $query->have_posts() ){ $query->the_post();
				$course_options = gdlr_lms_get_course_options(get_the_ID());
			
				echo '<div class="gdlr-lms-course-medium gdlr-lms-item">';
				gdlr_lms_print_course_thumbnail($thumbnail);
				
				echo '<div class="gdlr-lms-course-content">';
				echo '<h3 class="gdlr-lms-course-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
				
				echo gdlr_lms_print_course_rating(get_the_ID());
				
				gdlr_lms_print_course_info($course_options);
				gdlr_lms_print_course_price($course_options);
				gdlr_lms_print_course_button($course_options, array('buy', 'book'));
				
				echo '</div>'; // course-content
				echo '<div class="clear"></div>';
				echo '</div>'; // course-medium
			}
			wp_reset_postdata();
			echo '</div>'; // course-medium-wrapper	
		}
	}
	
	// course grid
	if( !function_exists('gdlr_lms_print_course_grid') ){
		function gdlr_lms_print_course_grid($query, $thumbnail, $column = 3){
			$count = 0;
		
			echo '<div class="gdlr-lms-course-grid-wrapper">';
			while( $query->have_posts() ){ $query->the_post();
				if($count % $column == 0){ echo '<div class="clear"></div>'; } $count++; 
				
				$course_options = gdlr_lms_get_course_options(get_the_ID());
			
				echo '<div class="gdlr-lms-course-grid gdlr-lms-col' . $column . '">';
				echo '<div class="gdlr-lms-item">';
				gdlr_lms_print_course_thumbnail($thumbnail);
				
				echo '<div class="gdlr-lms-course-content">';
				echo '<h3 class="gdlr-lms-course-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
				
				echo gdlr_lms_print_course_rating(get_the_ID());
				
				gdlr_lms_print_course_info($course_options);
				gdlr_lms_print_course_price($course_options);
				gdlr_lms_print_course_button($course_options, array('buy', 'book'));
				
				echo '</div>'; // course-content
				echo '<div class="clear"></div>';
				echo '</div>'; // lms-item
				echo '</div>'; // course-grid
			}
			wp_reset_postdata();
			echo '<div class="clear"></div>';
			echo '</div>'; // course-grid-wrapper	
		}	
	}	
	
	// course grid carousel
	if( !function_exists('gdlr_lms_print_course_grid_carousel') ){
		function gdlr_lms_print_course_grid_carousel($query, $thumbnail, $column = 3){
			$count = 0;
		
			echo '<div class="gdlr-lms-course-grid-wrapper gdlr-lms-carousel">';
			echo '<div class="flexslider" data-type="carousel" data-nav-container="course-item-wrapper" data-columns="' . $column . '" >';	
			echo '<ul class="slides" >';	
			while( $query->have_posts() ){ $query->the_post();
				$course_options = gdlr_lms_get_course_options(get_the_ID());
			
				echo '<li class="gdlr-lms-course-grid gdlr-lms-item">';
				gdlr_lms_print_course_thumbnail($thumbnail);
				
				echo '<div class="gdlr-lms-course-content">';
				echo '<h3 class="gdlr-lms-course-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
				
				gdlr_lms_print_course_info($course_options);
				gdlr_lms_print_course_price($course_options);
				gdlr_lms_print_course_button($course_options, array('buy', 'book'));
				
				echo '</div>'; // course-content
				echo '<div class="clear"></div>';
				echo '</li>'; // course-grid
			}
			wp_reset_postdata();
			echo '</ul>';
			echo '</div>'; // flexslider
			echo '</div>'; // course-grid-wrapper	
		}		
	}		
	
	// course grid
	if( !function_exists('gdlr_lms_print_course_grid2') ){
		function gdlr_lms_print_course_grid2($query, $thumbnail, $column = 3){
			$count = 0;
		
			echo '<div class="gdlr-lms-course-grid2-wrapper">';
			while( $query->have_posts() ){ $query->the_post();
				if($count % $column == 0){ echo '<div class="clear"></div>'; } $count++; 
				
				$course_options = gdlr_lms_get_course_options(get_the_ID());
			
				$lms_item_class = (empty($course_options['price']) && empty($course_options['discount-price']))? 'gdlr-lms-free': '';
			
				echo '<div class="gdlr-lms-course-grid2 gdlr-lms-col' . $column . '">';
				echo '<div class="gdlr-lms-item ' . $lms_item_class . '">';
				gdlr_lms_print_course_thumbnail($thumbnail);
				
				echo '<div class="gdlr-lms-course-content">';
				echo '<h3 class="gdlr-lms-course-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
				
				// price
				echo '<div class="gdlr-lms-course-price">';
				if( !empty($course_options['price']) && empty($course_options['discount-price']) ){
					echo '<span class="price-button">' . gdlr_lms_money_format($course_options['price']) . '</span>';
				}else if( !empty($course_options['discount-price']) ){
					echo '<span class="price-button">' . gdlr_lms_money_format($course_options['discount-price']) . '</span>';
				}else{
					echo '<span class="price-button blue">' . esc_html__('Free' ,'gdlr-lms') . '</span>';
				}
				echo '</div>';
				
				// date
				echo '<div class="gdlr-lms-course-info" >';
				echo '<i class="fa fa-clock-o icon-time"></i>';
				echo '<span class="tail">' . gdlr_lms_date_format($course_options['start-date']); 
				echo empty($course_options['end-date'])? '': ' - ' . gdlr_lms_date_format($course_options['end-date']);
				echo '</span>';
				echo '</div>';
				
				echo '<div class="clear"></div>';
				echo '</div>'; // course-content
				echo '</div>'; // lms-item
				echo '</div>'; // course-grid2
			}
			wp_reset_postdata();
			echo '<div class="clear"></div>';
			echo '</div>'; // course-grid-wrapper	
		}		
	}		
	
	// course grid
	if( !function_exists('gdlr_lms_print_course_grid2_carousel') ){
		function gdlr_lms_print_course_grid2_carousel($query, $thumbnail, $column = 3){
			$count = 0;
		
			echo '<div class="gdlr-lms-course-grid2-wrapper gdlr-lms-carousel">';
			echo '<div class="flexslider" data-type="carousel" data-nav-container="course-item-wrapper" data-columns="' . $column . '" >';	
			echo '<ul class="slides" >';		
			while( $query->have_posts() ){ $query->the_post();
				$course_options = gdlr_lms_get_course_options(get_the_ID());
			
				$lms_item_class = (empty($course_options['price']) && empty($course_options['discount-price']))? 'gdlr-lms-free': '';
			
				echo '<li class="gdlr-lms-course-grid2 gdlr-lms-item ' . $lms_item_class . '">';
				gdlr_lms_print_course_thumbnail($thumbnail);
				
				echo '<div class="gdlr-lms-course-content">';
				echo '<h3 class="gdlr-lms-course-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
				
				// price
				echo '<div class="gdlr-lms-course-price">';
				if( !empty($course_options['price']) && empty($course_options['discount-price']) ){
					echo '<span class="price-button">' . gdlr_lms_money_format($course_options['price']) . '</span>';
				}else if( !empty($course_options['discount-price']) ){
					echo '<span class="price-button">' . gdlr_lms_money_format($course_options['discount-price']) . '</span>';
				}else{
					echo '<span class="price-button blue">' . esc_html__('Free' ,'gdlr-lms') . '</span>';
				}
				echo '</div>';
				
				// date
				echo '<div class="gdlr-lms-course-info" >';
				echo '<i class="fa fa-clock-o icon-time"></i>';
				echo '<span class="tail">' . gdlr_lms_date_format($course_options['start-date']); 
				echo empty($course_options['end-date'])? '': ' - ' . gdlr_lms_date_format($course_options['end-date']);
				echo '</span>';
				echo '</div>';
				
				echo '<div class="clear"></div>';
				echo '</div>'; // course-content
				echo '</li>'; // course-grid2
			}
			wp_reset_postdata();
			echo '</ul>';
			echo '</div>';
			echo '</div>'; // course-grid-wrapper	
		}	
	}	
	
	// print course info
	if( !function_exists('gdlr_lms_print_course_info') ){
		function gdlr_lms_print_course_info($course_options, $options = array('instructor', 'type', 'date', 'time', 'place', 'seat'), $additional_code = '', $single = false){
			
			global $gdlr_lms_option;
			
			echo '<div class="gdlr-lms-course-info">';
			foreach( $options as $value ){
				switch($value){
					case 'instructor':
						$author_id = '';
						if( !empty($course_options['author_id']) ){
							$author_id = $course_options['author_id'];
						}else if( is_single() ){
							$author_id = get_the_author_meta('ID');
						}else{
							global $post;
							$user_info = get_user_meta($post->post_author);
							$author_id = $post->post_author;
						}

						echo '<div class="gdlr-lms-info gdlr-lms-info-instructor" >';
						if( $single && !empty($gdlr_lms_option['course-info-style']) && $gdlr_lms_option['course-info-style'] == 'style-2' ){
							echo '<span class="head"><img src="' . plugins_url('../images/course-info/instructor.png', __FILE__) . '" alt="" /></span>';
						}else{
							echo '<span class="head">' . esc_html__('Instructor', 'gdlr-lms') . '</span>';
						}
						echo '<span class="tail"><a href="' . get_author_posts_url($author_id) . '" >' . gdlr_lms_get_user_info($author_id) . '</a></span>';
						echo '</div>';
						break;
					case 'type': 
						if( !empty($course_options['online-course']) ){
							echo '<div class="gdlr-lms-info gdlr-lms-info-type" >';
							if( $single && !empty($gdlr_lms_option['course-info-style']) && $gdlr_lms_option['course-info-style'] == 'style-2' ){
								echo '<span class="head"><img src="';
								if( $course_options['online-course'] == 'enable' ){
									echo plugins_url('../images/course-info/online-course.png', __FILE__);
								}else{
									echo plugins_url('../images/course-info/onsite-course.png', __FILE__);
								}
								echo '" alt="" /></span>';
							}else{
								echo '<span class="head">' . esc_html__('Type', 'gdlr-lms') . '</span>';
							}
							echo '<span class="tail">';
							if( $course_options['online-course'] == 'enable' ){
								echo esc_html__('Online Course', 'gdlr-lms');
							}else{
								echo esc_html__('Onsite Course', 'gdlr-lms');
							}
							echo '</span>';
							echo '</div>';
						}
						break;
					case 'date': 
						if( !empty($course_options['start-date']) ){
							echo '<div class="gdlr-lms-info gdlr-lms-info-start-date" >';
							if( $single && !empty($gdlr_lms_option['course-info-style']) && $gdlr_lms_option['course-info-style'] == 'style-2' ){
								echo '<span class="head"><img src="' . plugins_url('../images/course-info/calendar.png', __FILE__) . '" alt="" /></span>';
							}else{
								echo '<span class="head">' . esc_html__('Date', 'gdlr-lms') . '</span>';
							}
							echo '<span class="tail">' . gdlr_lms_date_format($course_options['start-date']); 
							echo empty($course_options['end-date'])? '': ' - ' . gdlr_lms_date_format($course_options['end-date']);
							echo '</span>';
							echo '</div>';
						}
						break;
					case 'time': 
						if( $course_options['online-course'] == 'disable' ){
							if( !empty($course_options['course-time']) ){
								echo '<div class="gdlr-lms-info gdlr-lms-info-time" >';
								if( $single && !empty($gdlr_lms_option['course-info-style']) && $gdlr_lms_option['course-info-style'] == 'style-2' ){
									echo '<span class="head"><img src="' . plugins_url('../images/course-info/time.png', __FILE__) . '" alt="" /></span>';
								}else{
									echo '<span class="head">' . esc_html__('Time', 'gdlr-lms') . '</span>';
								}
								echo '<span class="tail">' . $course_options['course-time'] . '</span>'; 
								echo '</div>';
							}
						}
						break;					
					case 'place': 
						if( $course_options['online-course'] == 'disable' && !empty($course_options['location']) ){
							echo '<div class="gdlr-lms-info gdlr-lms-info-location" >';
							if( $single && !empty($gdlr_lms_option['course-info-style']) && $gdlr_lms_option['course-info-style'] == 'style-2' ){
								echo '<span class="head"><img src="' . plugins_url('../images/course-info/location.png', __FILE__) . '" alt="" /></span>';
							}else{
								echo '<span class="head">' . esc_html__('Place', 'gdlr-lms') . '</span>';
							}
							echo '<span class="tail">' . $course_options['location'] . '</span>';
							echo '</div>';
						}
						break;
					case 'price': 
						$price = empty($course_options['discount-price'])? $course_options['price']: $course_options['discount-price'];
						echo '<div class="gdlr-lms-info gdlr-lms-info-price" >';
						echo '<span class="head">' . esc_html__('Price', 'gdlr-lms') . '</span>';
						echo '<span class="tail">';
						echo empty($price)? esc_html__('Free', 'gdlr-lms'): gdlr_lms_money_format($price);
						echo '</span>';
						echo '</div>';
						
						break;
					case 'seat': 
						if( $course_options['online-course'] == 'disable' ){
							echo '<div class="gdlr-lms-info gdlr-lms-info-seat" >';
							if( $single && !empty($gdlr_lms_option['course-info-style']) && $gdlr_lms_option['course-info-style'] == 'style-2' ){
								echo '<span class="head"><img src="' . plugins_url('../images/course-info/enrolled.png', __FILE__) . '" alt="" /></span>';
								echo '<span class="tail">';
								echo intval($course_options['booked-seat']) . ' ' . esc_html__('Students Enrolled', 'gdlr-lms');
								echo (!empty($course_options['max-seat']))? '<br>(' . (intval($course_options['max-seat']) - intval($course_options['booked-seat'])) . ' ' . esc_html__('Available', 'gdlr-lms') . ')': '';
								echo '</span>';
							}else{
								echo '<span class="head">' . esc_html__('Seat', 'gdlr-lms') . '</span>';
								echo '<span class="tail">' . intval($course_options['booked-seat']) . '/' . intval($course_options['max-seat']) . '</span>';
								
							}
							echo '</div>';
						}else{
							global $wpdb;
							$sql  = 'SELECT COUNT(*) FROM ' . $wpdb->prefix . 'gdlrpayment ';
							$sql .= $wpdb->prepare('WHERE course_id = %d ', get_the_ID());

							$student_enrolled = $wpdb->get_var($sql);
							if( !empty($student_enrolled) ){
								echo '<div class="gdlr-lms-info gdlr-lms-info-seat" >';
								if( $single && !empty($gdlr_lms_option['course-info-style']) && $gdlr_lms_option['course-info-style'] == 'style-2' ){
									echo '<span class="head"><img src="' . plugins_url('../images/course-info/enrolled.png', __FILE__) . '" alt="" /></span>';
									echo '<span class="tail">' . $student_enrolled . ' ' . esc_html__('Students Enrolled', 'gdlr-lms') . '</span>';
								}else{
									echo '<span class="head">' . esc_html__('Student Enrolled', 'gdlr-lms') . '</span>';
									echo '<span class="tail">' . $student_enrolled . '</span>';
								}
								echo '</div>';
							}
						}
						break;
					case 'certificate': 
						if( !empty($course_options['enable-certificate']) && $course_options['enable-certificate'] == 'enable' ){
							echo '<div class="gdlr-lms-info gdlr-lms-info-certificate" >';
							if( $single && !empty($gdlr_lms_option['course-info-style']) && $gdlr_lms_option['course-info-style'] == 'style-2' ){
								echo '<span class="head"><img src="' . plugins_url('../images/course-info/certificate.png', __FILE__) . '" alt="" /></span>';
								echo '<span class="tail">' . esc_html__('Course Certificate', 'gdlr-lms') . '<br>(';
								echo gdlr_lms_text_filter($course_options['certificate-percent']) . '% ' . esc_html__('of quiz marks', 'gdlr-lms') . ')</span>';
							}else{
								echo '<span class="head">' . esc_html__('Certificate', 'gdlr-lms') . '</span>';
								echo '<span class="tail">' . $course_options['certificate-percent'] . '% ' . esc_html__('of quiz marks', 'gdlr-lms') . '</span>';
							}
							echo '</div>';
						}
						break;
					case 'rating': 
						gdlr_lms_print_course_rating(get_the_ID());
						break;
				}
			}
			echo gdlr_lms_text_filter($additional_code);
			echo '</div>';
		}
	}
	
	// course rating
	if( !function_exists('gdlr_lms_print_course_rating') ){
		function gdlr_lms_print_course_rating($course_id){
			global $gdlr_lms_rating;
			
			if( empty($gdlr_lms_rating) ){ $gdlr_lms_rating = get_option('gdlr_lms_rating', array('course_id'=>'score')); }
			if( empty($gdlr_lms_rating[$course_id]) ) return;
			
			$num_user = 0;
			$all_score = 0;
			foreach($gdlr_lms_rating[$course_id] as $score){ $num_user++;
				$all_score += floatval($score);
			}
			
			$star_count = 0;
			$rating_score = $all_score / $num_user;
			echo '<div class="gdlr-lms-rating-wrapper">';
			while($star_count < 5){ $star_count++;
				if( $rating_score > 1 ){
					$rating_score--;
					echo '<i class="fa fa-star icon-star"></i>';
				}else if( $rating_score > 0.75 ){
					$rating_score = 0;
					echo '<i class="fa fa-star icon-star"></i>';
				}else if( $rating_score > 0.25 ){
					$rating_score = 0;
					echo '<i class="fa fa-star-half-o icon-star-half-full"></i>';
				}else{
					echo '<i class="fa fa-star-o icon-star-empty"></i>';
				}
			}
			echo '<span class="gdlr-lms-rating-amount">(' . $num_user . ' ' . esc_html__('ratings', 'gdlr-lms') . ')</span>';
			echo '</div>';
		}
	}
	
	// print course price
	if( !function_exists('gdlr_lms_print_course_price') ){
		function gdlr_lms_print_course_price($course_options){
			echo '<div class="gdlr-lms-course-price">';
			echo '<span class="head">' . esc_html__('Price', 'gdlr-lms') . '</span>';
			if( !empty($course_options['allow-non-member']) && $course_options['allow-non-member'] == 'enable' &&
				(empty($course_options['online-course']) || $course_options['online-course'] == 'enable') ){
				echo '<span class="price">' . esc_html__('Free' ,'gdlr-lms') . '</span>';
			}else if( !empty($course_options['price']) && empty($course_options['discount-price']) ){
				echo '<span class="price">' . gdlr_lms_money_format($course_options['price']) . '</span>';
			}else if( !empty($course_options['discount-price']) ){
				echo '<span class="price with-discount">' . gdlr_lms_money_format($course_options['price']) . '</span>';
				echo '<span class="discount-price">' . gdlr_lms_money_format($course_options['discount-price']) . '</span>';
			}else{
				echo '<span class="price">' . esc_html__('Free' ,'gdlr-lms') . '</span>';
			}
			echo '</div>';
		}
	}
	
	// print course button
	if( !function_exists('gdlr_lms_print_course_button') ){
		function gdlr_lms_print_course_button($course_options, $options = array('buy', 'book', 'learn')){
			global $gdlr_lms_option, $current_user;
			
			echo '<div class="gdlr-course-button" >';	
			
			// for non member course
			if( !empty($course_options['allow-non-member']) && $course_options['allow-non-member'] == 'enable' &&
				(empty($course_options['online-course']) || $course_options['online-course'] == 'enable') &&
				(in_array('buy', $options) || in_array('book', $options)) ){
				$options = array('start');
				
			// if not logging in
			}else if( !is_user_logged_in() ){ // && !in_array('finish-quiz', $options)
				$lightbox_open = 'login-form';
				gdlr_lms_sign_in_lightbox_form();

			}else{
			
				// when purchase button is available
				if( in_array('buy', $options) || in_array('book', $options) ){
					
					// prerequisite course
					$prerequisite = false;
					if( !empty($course_options['prerequisite-course']) && $course_options['prerequisite-course'] != 'none' ){
						$prerequisite = true;
						
						$find_row = gdlr_lms_get_payment_row($course_options['prerequisite-course'], $current_user->ID);
						$payment_status = empty($find_row)? false: $find_row->payment_status;
						
						$p_course_options = gdlr_lms_get_course_options($course_options['prerequisite-course']);
						
						// if prerequisite is offline course
						if( !empty($p_course_options['online-course']) && $p_course_options['online-course'] == 'disable' ){
							if( $payment_status == 'paid' || $payment_status == 'reserved' ){
								$prerequisite = false;
							}
							
						// for online course
						}else if( $payment_status == 'paid' || $payment_status == 'reserved' ){

							// if prerequisite course has quiz
							if( !empty($p_course_options['quiz']) && $p_course_options['quiz'] != 'none' ){
								$quiz_row = gdlr_lms_get_quiz_row($p_course_options['quiz'], $course_options['prerequisite-course'], $current_user->ID, '');
								$quiz_status = empty($quiz_row)? false: $quiz_row->quiz_status;
								if( $quiz_status == 'complete' || $quiz_status == 'submitted' ){
									$prerequisite = false;
								}
							}else{
								$p_section = empty($find_row)? -1: intval($find_row->attendance_section);
								$p_course_settings = gdlr_lms_get_course_content_settings($course_options['prerequisite-course']);
								if( $p_section == sizeof($p_course_settings) ){
									$prerequisite = false;
								}
							}
						}
					}
					
					// course author
					global $post;
					$is_course_author = ($post->post_author == $current_user->ID);
					
					// print button
					if( $prerequisite ){
						$options = array('prerequisite');
					}else{
						$find_row = gdlr_lms_get_payment_row(get_the_ID(), $current_user->ID, 'payment_status, last_section');
						$payment_status = empty($find_row)? false: $find_row->payment_status;
						
						// for paid and free courses
						if( $is_course_author || (empty($course_options['price']) && empty($course_options['discount-price'])) || $payment_status == 'paid'){
							if( empty($course_options['online-course']) || $course_options['online-course'] == 'enable' ){
								$options = array('start');
								if( !empty($find_row->last_section) ){
									$last_course_section = gdlr_lms_get_section_page($find_row->last_section);
								}
							}else if( $payment_status == 'reserved' ){
								$options = array('booking-status');
							}else if( $payment_status != 'paid' && !$is_course_author ){
								$options = array('book');
							}else{
								$options = array();
							}
							
						// booked course
						}else if( $payment_status == 'pending' || $payment_status == 'submitted' ){
							$options = array('proceed-payment');
						}

					}
				}
			}

			// receipt only 
			if(in_array('buy', $options) && !empty($gdlr_lms_option['payment-method']) && $gdlr_lms_option['payment-method'] == 'receipt'){
				unset($options[array_search('buy', $options)]);
			}else if(in_array('book', $options) && !empty($gdlr_lms_option['payment-method']) && $gdlr_lms_option['payment-method'] == 'paypal'){
				unset($options[array_search('book', $options)]);
			}
			
			foreach( $options as $value ){
				switch($value){
					case 'buy': 
						if( empty($course_options['expired-date']) || time() < strtotime($course_options['expired-date']) ){
							echo '<a data-rel="gdlr-lms-lightbox" data-lb-open="';
							echo empty($lightbox_open)? 'buy-form': $lightbox_open;
							echo '" class="gdlr-lms-buy-button gdlr-lms-button cyan" >' . esc_html__('Buy Now', 'gdlr-lms') . '</a>';
							if(empty($lightbox_open)){ gdlr_lms_purchase_lightbox_form($course_options, 'buy'); }
						}
						break;

					case 'book': 
						if( empty($course_options['expired-date']) || time() < strtotime($course_options['expired-date']) ){
							echo '<a data-rel="gdlr-lms-lightbox" data-lb-open="';
							echo empty($lightbox_open)? 'book-form': $lightbox_open;
							echo '" class="gdlr-lms-book-button gdlr-lms-button blue" >' . esc_html__('Book Now', 'gdlr-lms') . '</a>';
							if(empty($lightbox_open)){ gdlr_lms_purchase_lightbox_form($course_options, 'book'); }
						}
						break;

					case 'learn': 
						echo '<a class="gdlr-lms-learn-more-button gdlr-lms-button black" href="' . get_permalink() . '" >' . esc_html__('Learn More', 'gdlr-lms') . '</a>';
						break;

					case 'start': 
						if( !empty($course_options['lock-course-date']) && $course_options['lock-course-date'] == 'enable' && 
							(( !empty($course_options['start-date']) && strtotime(current_time('Y-m-d')) < strtotime($course_options['start-date']) ) ||
							( !empty($course_options['end-date']) && strtotime(current_time('Y-m-d')) > strtotime($course_options['end-date']) )) ){
								
						}else{
							if( !empty($last_course_section) ){
								echo '<a class="gdlr-lms-start-button gdlr-lms-button cyan" href="' . esc_url(add_query_arg(array('course_type'=>'content', 'course_page'=>$last_course_section[0],'lecture'=>$last_course_section[1]), get_permalink())) . '" >';
								esc_html_e('Continue the course', 'gdlr-lms');
								echo '</a>';
							}else{
								echo '<a class="gdlr-lms-start-button gdlr-lms-button cyan" href="' . esc_url(add_query_arg(array('course_type'=>'content', 'course_page'=>1), get_permalink())) . '" >';
								esc_html_e('Start the course', 'gdlr-lms');
								echo '</a>';
							}
						}
						break;

					case 'proceed-payment':
						global $current_user;
						echo '<a class="gdlr-lms-proceed-payment-button gdlr-lms-button cyan" href="' . esc_url(add_query_arg('type', 'book-courses', get_author_posts_url($current_user->ID))) . '" >';
						esc_html_e('Proceed to Payment', 'gdlr-lms');
						echo '</a>';
						break;

					case 'booking-status':
						global $current_user;
						echo '<a class="gdlr-lms-button cyan" href="' . esc_url(add_query_arg('type', 'free-onsite', get_author_posts_url($current_user->ID))) . '" >';
						esc_html_e('Booking Status', 'gdlr-lms');
						echo '</a>';
						break;	

					case 'prerequisite':
						echo esc_html__('You have to complete ', 'gdlr-lms') . '<a href="' . get_permalink($course_options['prerequisite-course']) . '" >';
						echo get_the_title($course_options['prerequisite-course']);
						echo '</a> ' . esc_html__('before you can access this course.', 'gdlr-lms');
						break;		

					case 'quiz': 
						if( !empty($course_options['quiz']) && $course_options['quiz'] != 'none' ){
							global $current_user;

							$quiz_status = gdlr_lms_quiz_status($course_options['quiz'], get_the_ID(), $current_user->ID);
							if( $quiz_status == 'please-login-first' ){
								echo '<div class="gdlr-login-for-quiz" style="padding: 0px 20px 30px">';
								esc_html_e('You have to register and login to the site before you can take the quiz.', 'gdlr-lms');
								echo '</div>';
							}else if( $quiz_status == 'new-quiz' ){
								echo '<a class="gdlr-lms-button cyan" href="' . esc_url(add_query_arg(array('course_type'=>'quiz', 'course_page'=>1), get_permalink())) . '" >';
								esc_html_e('Take final quiz', 'gdlr-lms');
								echo '</a>';
							}else if( $quiz_status == 'old-quiz-retakable' ){
								echo '<a class="gdlr-lms-button cyan" href="' . esc_url(add_query_arg(array('course_type'=>'quiz', 'course_page'=>1, 'retake'=>1), get_permalink())) . '" >';
								esc_html_e('Retake final quiz', 'gdlr-lms');
								echo '</a>';	
							}
						}
						break;

					case 'finish-quiz': 
						echo '<a href="' . esc_url(add_query_arg(array('course_type'=>'quiz', 'course_page'=> 'finish'))) . '" ';
						echo 'data-loading="' . esc_html__('Submitting the answer','gdlr-lms') . '" ';
						echo 'class="gdlr-lms-button cyan finish-quiz-form-button" >';
						esc_html_e('Finish the quiz', 'gdlr-lms');
						echo '</a>';
						gdlr_lms_finish_quiz_form();
						break;					
				}
			}
			echo '</div>';
		}
	}
	
	// 
	if( !function_exists('gdlr_lms_finish_section_quiz') ){
		function gdlr_lms_finish_section_quiz($redirect){
			echo '<a href="' . esc_url(add_query_arg(array('course_type'=>'section-quiz', 'section-quiz'=> 'finish'))) . '" ';
			echo 'data-loading="' . esc_html__('Submitting the answer','gdlr-lms') . '" ';
			echo 'class="gdlr-lms-button cyan finish-quiz-form-button" >';
			esc_html_e('Finish the quiz', 'gdlr-lms');
			echo '</a>';
			gdlr_lms_finish_quiz_form($redirect);	
		}
	}
	
	// print course thumbnail
	if( !function_exists('gdlr_lms_print_course_thumbnail') ){
		function gdlr_lms_print_course_thumbnail($size = 'full'){
			$image_id = get_post_thumbnail_id();
			if(empty($image_id)) return;
			
			$image =  wp_get_attachment_image_src($image_id, $size);
			$alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);	

			echo '<div class="gdlr-lms-course-thumbnail">';
			echo (!is_single())? '<a href="' . get_permalink() . '" >': '';
			echo '<img src="' . $image[0] . '" alt="' . $alt . '" width="' . $image[1] . '" height="' . $image[2] . '" />';
			echo (!is_single())? '</a>': '';
			echo '</div>';
		}
	}
		
?>