<?php
	/*	
	*	Goodlayers Portfolio Item Management File
	*	---------------------------------------------------------------------
	*	This file contains functions that help you create portfolio item
	*	---------------------------------------------------------------------
	*/
	
	// get cause info
	if( !function_exists('gdlr_get_cause_info') ){
		function gdlr_get_cause_info( $array = array(), $post_option = array(), $wrapper = true ){
			global $theme_option; $ret = '';
			
			foreach($array as $post_info){	
				switch( $post_info ){
					case 'date':
						$ret .= '<div class="cause-info cause-date"><i class="icon-time fa fa-clock-o"></i>';
						$ret .= '<a href="' . get_day_link( get_the_time('Y'), get_the_time('m'), get_the_time('d')) . '">';
						//$ret .= '<time date-time="' . get_the_time('Y-m-d') . '" pubdate>';
						$ret .= get_the_time($theme_option['date-format']);
						//$ret .= '</time>';
						$ret .= '</a>';
						$ret .= '</div>';
						break;	
						
					case 'category':
						$cat = get_the_term_list(get_the_ID(), 'cause_category', '', '<span class="sep">,</span> ' , '' );
						if(empty($cat)) break;					
					
						$ret .= '<div class="cause-info cause-category"><i class="icon-folder-close-alt fa fa-folder-o"></i>';
						$ret .= $cat;						
						$ret .= '</div>';						
						break;		

					case 'pdf':
						
						if( !empty($post_option['pdf']) ){
							$ret .= '<div class="cause-info cause-pdf"><i class="icon-file-text fa fa-file-text"></i>';
							$ret .= '<a href="' . wp_get_attachment_url($post_option['pdf']) . '" >';
							$ret .= __('Download PDF', 'gdlr-cause');
							$ret .= '</a>';						
							$ret .= '</div>';	
						}
						break;							
				}
			}

			if($wrapper && !empty($ret)){
				return '<div class="gdlr-cause-info">' . $ret . '<div class="clear"></div></div>';
			}else if( !empty($ret) ){
				return $ret . '<div class="clear"></div>';
			}
			return '';
		}
	}	
	
	// get cause thumbnail
	if( !function_exists('gdlr_get_cause_thumbnail') ){
		function gdlr_get_cause_thumbnail($size = 'full'){
			$ret = ''; $image_id = get_post_thumbnail_id();
			
			if( !empty($image_id) ){
				$ret  = '<div class="gdlr-cause-thumbnail" >';
				if( is_single() ){
					$ret .= gdlr_get_image($image_id, $size);
				}else{
					$ret .= '<a href="' . get_permalink() . '" >';
					$ret .= gdlr_get_image($image_id, $size);
					$ret .= '</a>';
				}
				$ret .= '</div>';
			}
			return $ret;
		}
	}	
	
	if( !function_exists('gdlr_cause_donation_button') ){
		function gdlr_cause_donation_button($cause_option = array()){
			// if( intval($cause_option['goal-of-donation']) <= intval($cause_option['current-funding']) ) return;
		
			global $theme_option, $gdlr_donation_id; $button = '';
			$gdlr_donation_id = empty($gdlr_donation_id)? 1: $gdlr_donation_id + 1;

			$donation_form = trim($theme_option['cause-donation-form']);
			if( !empty($cause_option['donation-form']) ){
				$donation_form = trim($cause_option['donation-form']);
			}
			
			if( !empty($donation_form) && strpos($donation_form, 'http') === 0 ){
				$button  = '<a class="gdlr-donate-button gdlr-button with-border" ';
				$button .= 'href="' . $donation_form . '">';
				$button .= __('Donate Now', 'gdlr-cause') . '</a>';	
			}else if( !empty($donation_form) ){
				$button  = '<a class="gdlr-donate-button gdlr-button with-border" data-rel="fancybox" ';
				$button .= 'href="#donate-button-' . $gdlr_donation_id . '">';
				$button .= __('Donate Now', 'gdlr-cause') . '</a>';
				$button .= '<div id="donate-button-' . $gdlr_donation_id . '" style="display: none;">';
				$button .= do_shortcode($donation_form) . '</div>';
			}
			return $button;
		}
	}	
	
	if( !function_exists('gdlr_cause_donation_amount') ){
		function gdlr_cause_donation_amount($goal = 0, $current = 0){
			$ret = '';
			if( !empty($goal) ){
				$goal = floatval($goal);
				$current = floatval($current);
				if( $goal < $current ){ $current = $goal; }
				
				if(pll_current_language() == 'bg'){
				    
				$percent = intval(($current / $goal)*100); 
				$ret .= '<div class="gdlr-donation-bar-outer">';
				$ret .= '<div class="gdlr-donation-bar-wrapper">';
				$ret .= '<div class="gdlr-donation-bar" style="width: ' . $percent . '%;"></div>';
				$ret .= '</div>';
				
				$ret .= '<div class="gdlr-donation-goal-text" >';
				$ret .= '<span class="front">';
				$ret .= $percent . '% ' . __('Събрани', 'gdlr-cause');
				$ret .= '</span>';
				$ret .= __(' от ', 'gdlr-cause');$ret .= gdlr_cause_money_format($goal - $current) . ' ';
				
				$ret .= '</span>';				
				$ret .= '</div>';
				$ret .= '</div>'; // donation bar outer
			
			} else {
			   	$percent = intval(($current / $goal)*100); 
				$ret .= '<div class="gdlr-donation-bar-outer">';
				$ret .= '<div class="gdlr-donation-bar-wrapper">';
				$ret .= '<div class="gdlr-donation-bar" style="width: ' . $percent . '%;"></div>';
				$ret .= '</div>';
				
				$ret .= '<div class="gdlr-donation-goal-text" >';
				$ret .= '<span class="front">';
				$ret .= $percent . '% ' . __('Donated', 'gdlr-cause');
				$ret .= '</span>';
				
				$ret .= '<span class="back"><span class="sep">/</span>';
				$ret .= gdlr_cause_money_format($goal - $current) . ' ';
				$ret .= __('To Go', 'gdlr-cause');
				$ret .= '</span>';				
				$ret .= '</div>';
				$ret .= '</div>'; // donation bar outer 
			    
			}
			return $ret;
		}
	}	}
	
	// add action to check for cause item
	add_action('gdlr_print_item_selector', 'gdlr_check_cause_item', 10, 2);
	if( !function_exists('gdlr_check_cause_item') ){
		function gdlr_check_cause_item( $type, $settings = array() ){
			if($type == 'cause'){
				echo gdlr_print_cause_item( $settings );
			}else if($type == 'cause-search'){
				echo gdlr_print_cause_search_item( $settings );
			}else if($type == 'urgent-cause'){
				echo gdlr_print_urgent_cause( $settings );
			}
		}
	}	
	
	// print cause item
	if( !function_exists('gdlr_print_cause_item') ){
		function gdlr_print_cause_item( $settings = array() ){
			$item_id = empty($settings['page-item-id'])? '': ' id="' . $settings['page-item-id'] . '" ';

			global $gdlr_spaces, $gdlr_excerpt_read_more;
			$margin = (!empty($settings['margin-bottom']) && 
				$settings['margin-bottom'] != $gdlr_spaces['bottom-blog-item'])? 'margin-bottom: ' . $settings['margin-bottom'] . ';': '';
			$margin_style = (!empty($margin))? ' style="' . $margin . '" ': '';
			
			$settings['title-type'] = (empty($settings['title-type']))? 'none': $settings['title-type'];
			$settings['title'] = (empty($settings['title']))? '': $settings['title'];
			$settings['caption'] = (empty($settings['caption']))? '': $settings['caption'];
			$settings['icon'] = (empty($settings['icon']))? '': $settings['icon'];
			
			$right_text = ''; $right_text_class = ''; $carousel = false;
			$settings['right-text'] = (empty($settings['right-text']))? '': $settings['right-text'];		
			$settings['right-text-link'] = (empty($settings['right-text-link']))? '': $settings['right-text-link'];	
			if( !empty($settings['right-text-link']) && !empty($settings['right-text']) ){
				$right_text_class = 'gdlr-right-text ';
				$right_text .= '<a class="gdlr-right-text-link" href="' . $settings['right-text-link'] . '" >' . $settings['right-text'] . '</a>';
			}
			if( $settings['cause-style'] == 'grid' && $settings['cause-layout'] == 'carousel' ){
				$carousel = true;
				$right_text_class .= 'gdlr-nav-container ';
			}
			$ret  = gdlr_get_item_title(array(
				'title' => $settings['title'],
				'caption' => $settings['caption'],
				'icon' => $settings['icon'],
				'type' => $settings['title-type'],
				'carousel' => $carousel,
				'additional_class' => $right_text_class,
				'additional_html' => $right_text
			));				

			$ret .= '<div class="cause-item-wrapper" ' . $item_id . $margin_style . ' >'; 
			
			// query posts section
			$args = array('post_type' => 'cause', 'suppress_filters' => false);
			$args['posts_per_page'] = (empty($settings['num-fetch']))? '5': $settings['num-fetch'];
			if( $settings['orderby'] == 'nearly' ){
				$args['meta_key'] = 'gdlr-donation-percent';
				$args['meta_compare'] = '>';
				$args['meta_value'] = '100';
				$args['orderby'] = 'meta_value_num';
				$args['order'] = 'desc';
			}else if( $settings['orderby'] == 'finish' ){
				$args['meta_key'] = 'gdlr-donation-percent';
				$args['meta_compare'] = '=';
				$args['meta_value'] = '100';
				$args['orderby'] = (empty($settings['orderby']))? 'post_date': $settings['orderby'];
				$args['order'] = (empty($settings['order']))? 'desc': $settings['order'];
			}else{
				$args['orderby'] = (empty($settings['orderby']))? 'post_date': $settings['orderby'];
				$args['order'] = (empty($settings['order']))? 'desc': $settings['order'];
			}
			
			$args['paged'] = (get_query_var('paged'))? get_query_var('paged') : 1;

			if( !empty($settings['category']) ){
				$args['tax_query'] = array(array(
						'terms'=> explode(',', $settings['category']), 
						'taxonomy'=> 'cause_category', 
						'field'=> 'slug' ));			
			}			
			$query = new WP_Query( $args );
			
			// excerpt number
			if( !empty($settings['num-excerpt']) ){
				global $gdlr_excerpt_length; $gdlr_excerpt_length = $settings['num-excerpt'];
				add_filter('excerpt_length', 'gdlr_set_excerpt_length');
			} 
			
			$ret .= '<div class="cause-item-holder">';
			if( $settings['cause-style'] == 'grid' ){
				$gdlr_excerpt_read_more = false;
				$settings['cause-size'] = str_replace('1/', '', $settings['cause-size']);
				$ret .= gdlr_get_cause_grid($query, $settings['cause-size'], 
							$settings['thumbnail-size'], $settings['cause-layout'], $settings['num-excerpt']);
				$gdlr_excerpt_read_more = true;
			}else if( $settings['cause-style'] == 'medium' ){
				$ret .= gdlr_get_cause_medium($query, $settings['thumbnail-size'], $settings['num-excerpt']);			
			}else if( $settings['cause-style'] == 'full' ){
				$ret .= gdlr_get_cause_full($query, $settings['thumbnail-size'], $settings['num-excerpt']);
			}
			$ret .= '<div class="clear"></div>';
			$ret .= '</div>';
			
			
			// create pagination
			if($settings['pagination'] == 'enable'){
				$ret .= gdlr_get_pagination($query->max_num_pages, $args['paged']);
			}

			remove_filter('excerpt_length', 'gdlr_set_excerpt_length');
			$ret .= '</div>'; // cause-item-wrapper
			return $ret;
		}
	}

	// print grid cause
	if( !function_exists('gdlr_get_cause_grid') ){
		function gdlr_get_cause_grid($query, $size, $thumbnail_size, $layout = 'fitRows', $excerpt){
			if($layout == 'carousel'){ 
				return gdlr_get_carousel_cause_grid($query, $size, $thumbnail_size, $excerpt); 
			}		
		
			global $post;

			$current_size = 0;
			$ret  = '<div class="gdlr-isotope" data-type="causes" data-layout="' . $layout  . '" >';
			while($query->have_posts()){ $query->the_post();
				if( $current_size % $size == 0 ){
					$ret .= '<div class="clear"></div>';
				}			
    
				$ret .= '<div class="' . gdlr_get_column_class('1/' . $size) . '">';
				$ret .= '<div class="gdlr-item gdlr-cause-item gdlr-cause-grid">';
				$ret .= '<div class="gdlr-ux gdlr-cause-grid-ux">';
				
				$cause_option = json_decode(gdlr_decode_preventslashes(get_post_meta($post->ID, 'post-option', true)), true);
				$ret .= '<div class="gdlr-cause-thumbnail-wrapper">';
				$ret .= gdlr_get_cause_thumbnail($thumbnail_size);
				$ret .= '</div>'; // cause-thumbnail
			
				$ret .= '<div class="cause-content-wrapper">';
				$ret .= '<h3 class="cause-title"><a href="' . get_permalink() . '" >' . get_the_title() . '</a></h3>';
				$ret .= gdlr_cause_donation_amount($cause_option['goal-of-donation'], $cause_option['current-funding']);
				if( $excerpt == '-1' ){
					$ret .= '<div class="cause-content">' . gdlr_content_filter(get_the_content()) . '</div>';
				}else if( !empty($excerpt) ){
					$ret .= '<div class="cause-content">' . get_the_excerpt() . '</div>';
				}
				$ret .= gdlr_cause_donation_button($cause_option);
				$ret .= '</div>';
				
				$ret .= '</div>'; // gdlr-ux
				$ret .= '</div>'; // gdlr-item
				$ret .= '</div>'; // column class
				$current_size ++;
			}
			$ret .= '</div>';
			wp_reset_postdata();
			
			return $ret;
		}
	}	
	
	// print grid cause
	if( !function_exists('gdlr_get_carousel_cause_grid') ){
		function gdlr_get_carousel_cause_grid($query, $size, $thumbnail_size, $excerpt){
			global $post;

			$current_size = 0;
			$ret  = '<div class="gdlr-item gdlr-cause-carousel-wrapper">';
			$ret .= '<div class="flexslider" data-type="carousel" data-nav-container="cause-item-wrapper" data-columns="' . $size . '" >';	
			$ret .= '<ul class="slides" >';
			while($query->have_posts()){ $query->the_post();
				$ret .= '<li class="gdlr-item gdlr-cause-item gdlr-cause-grid">';
				$ret .= '<div class="gdlr-ux gdlr-cause-grid-ux">';
				
				$cause_option = json_decode(gdlr_decode_preventslashes(get_post_meta($post->ID, 'post-option', true)), true);
				$ret .= '<div class="gdlr-cause-thumbnail-wrapper">';
				$ret .= gdlr_get_cause_thumbnail($thumbnail_size);
				$ret .= '</div>'; // cause-thumbnail
			
				$ret .= '<div class="cause-content-wrapper">';
				$ret .= '<h3 class="cause-title"><a href="' . get_permalink() . '" >' . get_the_title() . '</a></h3>';
				$ret .= gdlr_cause_donation_amount($cause_option['goal-of-donation'], $cause_option['current-funding']);
				if( $excerpt == '-1' ){
					$ret .= '<div class="cause-content">' . gdlr_content_filter(get_the_content()) . '</div>';
				}else if( !empty($excerpt) ){
					$ret .= '<div class="cause-content">' . get_the_excerpt() . '</div>';
				}
				$ret .= gdlr_cause_donation_button($cause_option);
				$ret .= '</div>';
				
				$ret .= '</div>'; // gdlr-ux
				$ret .= '</li>'; // gdlr-item
				$current_size ++;
			}
			$ret .= '</ul>';
			$ret .= '</div>'; // flexslider
			$ret .= '</div>'; // cause carousel
			wp_reset_postdata();
			
			return $ret;
		}
	}	
	
	// print grid cause
	if( !function_exists('gdlr_get_cause_medium') ){
		function gdlr_get_cause_medium($query, $thumbnail_size, $excerpt){
			global $post;
			
			$ret = '';
			while($query->have_posts()){ $query->the_post();		
   
				$ret .= '<div class="gdlr-item gdlr-cause-item gdlr-cause-medium">';
				$ret .= '<div class="gdlr-ux gdlr-cause-medium-ux">';
				
				$cause_option = json_decode(gdlr_decode_preventslashes(get_post_meta($post->ID, 'post-option', true)), true);
				$ret .= '<div class="gdlr-cause-thumbnail-wrapper">';
				$ret .= gdlr_get_cause_thumbnail($thumbnail_size);
				$ret .= gdlr_cause_donation_button($cause_option);
				$ret .= '</div>'; // cause-thumbnail
			
				$ret .= '<div class="cause-content-wrapper">';
				$ret .= '<h3 class="cause-title"><a href="' . get_permalink() . '" >' . get_the_title() . '</a></h3>';
				$ret .= gdlr_get_cause_info(array('date', 'category'));
				$ret .= gdlr_cause_donation_amount($cause_option['goal-of-donation'], $cause_option['current-funding']);
				if( $excerpt == '-1' ){
					$ret .= '<div class="cause-content">' . gdlr_content_filter(get_the_content()) . '</div>';
				}else if( !empty($excerpt) ){
					$ret .= '<div class="cause-content">' . get_the_excerpt() . '</div>';
				}
				$ret .= '</div>';
				
				$ret .= '<div class="clear"></div>';
				$ret .= '</div>'; // gdlr-ux
				$ret .= '</div>'; // gdlr-item
			}
			wp_reset_postdata();
			
			return $ret;
		}
	}		
	
	// print full cause
	if( !function_exists('gdlr_get_cause_full') ){
		function gdlr_get_cause_full($query, $thumbnail_size, $excerpt){
			global $post;
			
			$ret = '';
			while($query->have_posts()){ $query->the_post();		
   
				$ret .= '<div class="gdlr-item gdlr-cause-item gdlr-cause-full">';
				$ret .= '<div class="gdlr-ux gdlr-cause-full-ux">';
				
				$cause_option = json_decode(gdlr_decode_preventslashes(get_post_meta($post->ID, 'post-option', true)), true);
				$ret .= '<div class="gdlr-cause-thumbnail-wrapper">';
				$ret .= gdlr_get_cause_thumbnail($thumbnail_size);
				$ret .= '</div>'; // cause-thumbnail
			
				$ret .= '<div class="gdlr-cause-info-wrapper" >';
				$ret .= gdlr_cause_donation_button($cause_option);
				$ret .= gdlr_get_cause_info(array('date', 'category'));
				$ret .= '</div>';
			
				$ret .= '<div class="cause-content-wrapper">';
				$ret .= '<h3 class="cause-title"><a href="' . get_permalink() . '" >' . get_the_title() . '</a></h3>';
				$ret .= gdlr_cause_donation_amount($cause_option['goal-of-donation'], $cause_option['current-funding']);
				if( $excerpt == '-1' ){
					$ret .= '<div class="cause-content">' . gdlr_content_filter(get_the_content()) . '</div>';
				}else if( !empty($excerpt) ){
					$ret .= '<div class="cause-content">' . get_the_excerpt() . '</div>';
				}
				$ret .= '</div>';
				
				$ret .= '<div class="clear"></div>';
				$ret .= '</div>'; // gdlr-ux
				$ret .= '</div>'; // gdlr-item
			}
			wp_reset_postdata();
			
			return $ret;
		}
	}	

	// cause search
	if( !function_exists('gdlr_print_cause_search_item') ){
		function gdlr_print_cause_search_item($settings){
			$item_id = empty($settings['page-item-id'])? '': ' id="' . $settings['page-item-id'] . '" ';

			global $gdlr_spaces, $gdlr_excerpt_read_more;
			$margin = (!empty($settings['margin-bottom']) && 
				$settings['margin-bottom'] != $gdlr_spaces['bottom-item'])? 'margin-bottom: ' . $settings['margin-bottom'] . ';': '';
			$margin_style = (!empty($margin))? ' style="' . $margin . '" ': '';

			$ret  = '<div class="gdlr-cause-search-wrapper gdlr-item" ' . $item_id . $margin_style . ' >';
			if( !empty($settings['title']) ){
				$ret .= '<h3 class="cause-search-title">' . $settings['title'] . '</h3>';
			}
			if( !empty($settings['caption']) ){
				$ret .= '<div class="cause-search-caption">' . $settings['caption'] . '</div>';
			}
			
			// input
			$ret .= '<div class="gdlr-cause-input-wrapper">';
			$ret .= '<form method="get" id="search-cause" action="' . home_url() . '">';

			$ret .= '<input type="text" name="s" id="search-cause-s" autocomplete="off" data-default="' . __('Keywords', 'gdlr-cause') . '" />';
			$ret .= '<div class="gdlr-combobox">';
			$ret .= '<select name="cause_category" >';
			$ret .= '<option value="" >' . __('Category', 'gdlr-cause') . '</option>';
			$cause_category = gdlr_get_term_list('cause_category');
			foreach( $cause_category as $key => $value ){
				$ret .= '<option value="' . $key . '" >' . $value . '</option>';
			}
			$ret .= '</select>';
			$ret .= '</div>';
			$ret .= '<input type="hidden" name="post_type" value="cause" />';
			$ret .= '<input type="submit" id="search-cause-submit" value="' . __('Search', 'gdlr-cause') . '" />';
			
			$ret .= '<div class="clear"></div>';
			$ret .= '</form>';				
			$ret .= '</div>'; // cause input wrapper

			$ret .= '</div>'; // gdlr-item
			return $ret;
		}
	}
	
	if( !function_exists('gdlr_print_urgent_cause') ){
		function gdlr_print_urgent_cause( $settings ){	
			$item_id = empty($settings['page-item-id'])? '': ' id="' . $settings['page-item-id'] . '" ';

			global $post, $gdlr_spaces, $gdlr_excerpt_read_more;
			$margin  = (!empty($settings['margin-bottom']) && 
				$settings['margin-bottom'] != $gdlr_spaces['bottom-item'])? 'margin-bottom: ' . $settings['margin-bottom'] . ';': '';
			$margin .= (!empty($settings['min-height']))? ' min-height: ' . $settings['min-height'] . 'px; ': '';
		
			// excerpt number
			if( !empty($settings['num-excerpt']) ){
				global $gdlr_excerpt_length; $gdlr_excerpt_length = $settings['num-excerpt'];
				add_filter('excerpt_length', 'gdlr_set_excerpt_length');
			} 		
	
			$posts = get_posts(array('name'=>$settings['cause'], 'post_type'=>'cause', 'posts_per_page'=>1));
			foreach($posts as $post){ setup_postdata($post);
				$cause_option = json_decode(gdlr_decode_preventslashes(get_post_meta(get_the_ID(), 'post-option', true)), true);			
				$image_id = get_post_thumbnail_id();
				if( !empty($image_id) ){
					$image_src = wp_get_attachment_image_src($image_id, 'full');	
					$margin .= ' background: url(' . $image_src[0] . ') center 0px; ';
				}
				$margin_style = (!empty($margin))? ' style="' . $margin . '" ': '';
				
				$ret  = '<div class="urgent-cause-wrapper gdlr-item" ' . $item_id . $margin_style . '>';
				$ret .= '<div class="urgent-cause-overlay" ></div>';
				$ret .= '<div class="urgent-cause-inner" >';
				$ret .= '<div class="urgent-cause-caption">' . $settings['title'] . '</div>';
				$ret .= '<h3 class="urgent-cause-title"><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
				$ret .= '<div class="urgent-cause-content">' . get_the_excerpt() . '</div>';
				$ret .= '<div class="urgent-cause-info">';
				$ret .= gdlr_cause_donation_button($cause_option);
				$ret .= gdlr_cause_donation_amount($cause_option['goal-of-donation'], $cause_option['current-funding']);
				$ret .= '<div class="clear"></div>';
				$ret .= '</div>'; // urgent-cause-info
				$ret .= '</div>'; // urgent-cause-inner
				$ret .= '</div>';
			}
			
			remove_filter('excerpt_length', 'gdlr_set_excerpt_length');
			wp_reset_postdata();
			return $ret;
		}
	}
	
?>