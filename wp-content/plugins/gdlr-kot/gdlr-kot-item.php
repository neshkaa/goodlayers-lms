<?php
	/*
	*	Goodlayers Kot Item Management File
	*	---------------------------------------------------------------------
	*	This file contains functions that help you create kot item
	*	---------------------------------------------------------------------
	*/

	// add action to check for kot item
	add_action('gdlr_print_item_selector', 'gdlr_check_kot_item', 10, 2);
	if( !function_exists('gdlr_check_kot_item') ){
		function gdlr_check_kot_item( $type, $settings = array() ){
			if($type == 'kot'){
				echo gdlr_print_kot_item( $settings );
			}
		}
	}

	// include kot script
	if( !function_exists('gdlr_include_kot_scirpt') ){
		function gdlr_include_kot_scirpt( $settings = array() ){
			wp_enqueue_script('isotope', GDLR_PATH . '/plugins/jquery.isotope.min.js', array(), '1.0', true);
			wp_enqueue_script('kot-script', plugins_url('gdlr-kot-script.js', __FILE__), array(), '1.0', true);
		}
	}

	// print kot item
	if( !function_exists('gdlr_print_kot_item') ){
		function gdlr_print_kot_item( $settings = array() ){
			gdlr_include_kot_scirpt();

			$item_id = empty($settings['page-item-id'])? '': ' id="' . $settings['page-item-id'] . '" ';

			global $gdlr_spaces;
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
			if( $settings['kot-layout'] == 'carousel' ){
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


			$ret .= '<div class="kot-item-wrapper type-' . $settings['kot-style'] . '" ';
			$ret .= $item_id . $margin_style . ' data-ajax="' . AJAX_URL . '" >';

			// query posts section
			$args = array('post_type' => 'kot', 'suppress_filters' => false);
			$args['posts_per_page'] = (empty($settings['num-fetch']))? '5': $settings['num-fetch'];
			$args['orderby'] = (empty($settings['orderby']))? 'post_date': $settings['orderby'];
			$args['order'] = (empty($settings['order']))? 'desc': $settings['order'];
			$args['paged'] = (get_query_var('paged'))? get_query_var('paged') : 1;

			if( !empty($settings['category']) || (!empty($settings['tag']) && $settings['kot-filter'] == 'disable') ){
				$args['tax_query'] = array('relation' => 'OR');

				if( !empty($settings['category']) ){
					array_push($args['tax_query'], array('terms'=>explode(',', $settings['category']), 'taxonomy'=>'kot_category', 'field'=>'slug'));
				}
				if( !empty($settings['tag']) && $settings['kot-filter'] == 'disable' ){
					array_push($args['tax_query'], array('terms'=>explode(',', $settings['tag']), 'taxonomy'=>'kot_tag', 'field'=>'slug'));
				}
			}
			$query = new WP_Query( $args );

			// create the kot filter
			$settings['kot-size'] = str_replace('1/', '', $settings['kot-size']);
			if( $settings['kot-filter'] == 'enable' ){

				// ajax infomation
				$ret .= '<div class="gdlr-ajax-info" data-num-fetch="' . $args['posts_per_page'] . '" ';
				$ret .= 'data-orderby="' . $args['orderby'] . '" data-order="' . $args['order'] . '" ';
				$ret .= 'data-thumbnail-size="' .  $settings['thumbnail-size'] . '" data-port-style="' . $settings['kot-style'] . '" ';
				$ret .= 'data-port-size="' . $settings['kot-size'] . '" data-port-layout="' .  $settings['kot-layout'] . '" ';
				$ret .= 'data-ajax="' . admin_url('admin-ajax.php') . '" data-category="' . $settings['category'] . '" ></div>';

				// category filter
				if( empty($settings['category']) ){
					$parent = array('gdlr-all'=>__('All', 'gdlr-kot'));
					$settigns['category-id'] = '';
				}else{
					$term = get_term_by('slug', $settings['category'], 'kot_category');
					$parent = array($settings['category']=>$term->name);
					$settings['category-id'] = $term->term_id;
				}

				$filters = $parent + gdlr_get_term_list('kot_category', $settings['category-id']);
				$filter_active = 'active';
				$ret .= '<div class="kot-item-filter">';
				foreach($filters as $filter_id => $filter){
					$filter_id = ($filter_id == 'gdlr-all')? '': $filter_id;

					$ret .= '<a class="' . $filter_active . '" href="#" ';
					$ret .= 'data-category="' . $filter_id . '" ><span class="sep">/</span>' . $filter . '</a>';
					$filter_active = '';
				}
				$ret .= '</div>';
			}

			$no_space  = (strpos($settings['kot-style'], 'no-space') > 0)? 'gdlr-item-no-space': '';
			$no_space .= ' gdlr-kot-column-' . $settings['kot-size'];
			$ret .= '<div class="kot-item-holder ' . $no_space . '">';
			if( $settings['kot-style'] == 'classic-kot' ||
				$settings['kot-style'] == 'classic-kot-no-space'){

				$ret .= gdlr_get_classic_kot($query, $settings['kot-size'],
							$settings['thumbnail-size'], $settings['kot-layout'] );
			}else if($settings['kot-style'] == 'modern-kot' ||
				$settings['kot-style'] == 'modern-kot-no-space'){

				$ret .= gdlr_get_modern_kot($query, $settings['kot-size'],
							$settings['thumbnail-size'], $settings['kot-layout'] );
			}
			$ret .= '<div class="clear"></div>';
			$ret .= '</div>';

			// create pagination
			if($settings['kot-filter'] == 'enable' && $settings['pagination'] == 'enable'){
				$ret .= gdlr_get_ajax_pagination($query->max_num_pages, $args['paged']);
			}else if($settings['pagination'] == 'enable'){
				$ret .= gdlr_get_pagination($query->max_num_pages, $args['paged']);
			}

			$ret .= '</div>'; // kot-item-wrapper
			return $ret;
		}
	}

	// ajax function for kot filter / pagination
	add_action('wp_ajax_gdlr_get_kot_ajax', 'gdlr_get_kot_ajax');
	add_action('wp_ajax_nopriv_gdlr_get_kot_ajax', 'gdlr_get_kot_ajax');
	if( !function_exists('gdlr_get_kot_ajax') ){
		function gdlr_get_kot_ajax(){
			$settings = $_POST['args'];

			$args = array('post_type' => 'kot', 'suppress_filters' => false);
			$args['posts_per_page'] = (empty($settings['num-fetch']))? '5': $settings['num-fetch'];
			$args['orderby'] = (empty($settings['orderby']))? 'post_date': $settings['orderby'];
			$args['order'] = (empty($settings['order']))? 'desc': $settings['order'];
			$args['paged'] = empty($settings['paged'])? 1: $settings['paged'];

			if( !empty($settings['category']) ){
				$args['tax_query'] = array(
					array('terms'=>explode(',', $settings['category']), 'taxonomy'=>'kot_category', 'field'=>'slug')
				);
			}
			$query = new WP_Query( $args );

			$no_space = (strpos($settings['kot-style'], 'no-space') > 0)? 'gdlr-item-no-space': '';
			$no_space .= ' gdlr-kot-column-' . $settings['kot-size'];
			$ret .= '<div class="kot-item-holder ' . $no_space . '">';
			if( $settings['kot-style'] == 'classic-kot' ||
				$settings['kot-style'] == 'classic-kot-no-space'){

				$ret .= gdlr_get_classic_kot($query, $settings['kot-size'],
							$settings['thumbnail-size'], $settings['kot-layout'] );
			}else if($settings['kot-style'] == 'modern-kot' ||
				$settings['kot-style'] == 'modern-kot-no-space'){

				$ret .= gdlr_get_modern_kot($query, $settings['kot-size'],
							$settings['thumbnail-size'], $settings['kot-layout'] );
			}
			$ret .= '<div class="clear"></div>';
			$ret .= '</div>';

			// pagination section
			$ret .= gdlr_get_ajax_pagination($query->max_num_pages, $args['paged']);
			die($ret);
		}
	}

	// get kot info
	if( !function_exists('gdlr_get_kot_info') ){
		function gdlr_get_kot_info( $array = array(), $option = array(), $wrapper = true ){
			$ret = '';

			foreach($array as $post_info){
				switch( $post_info ){
					case 'gender':
						if(empty($option['gender'])) break;

						$ret .= '<div class="kot-info kot-gender">';
						$ret .= '<span class="info-head gdlr-title">' . pll__('Пол', 'gdlr-kot') . ' </span>';
						$ret .= $option['gender'];
						$ret .= '</div>';

						break;
					case 'age':
						if(empty($option['age'])) break;

						$ret .= '<div class="kot-info kot-age">';
						$ret .= '<span class="info-head gdlr-title">' . pll__('Възраст', 'gdlr-kot') . ' </span>';
						$ret .= $option['age'];
						$ret .= '</div>';

						break;
					case 'color':
						if(empty($option['color'])) break;

						$ret .= '<div class="kot-info kot-color">';
						$ret .= '<span class="info-head gdlr-title">' . pll__('Цвят', 'gdlr-kot') . ' </span>';
						$ret .= $option['color'];
						$ret .= '</div>';

						break;
					case 'coat':
						if(empty($option['coat'])) break;

						$ret .= '<div class="kot-info kot-coat">';
						$ret .= '<span class="info-head gdlr-title">' . pll__('Козина', 'gdlr-kot') . ' </span>';
						$ret .= $option['coat'];
						$ret .= '</div>';
						break;

						case 'neutered':
						if(empty($option['neutered'])) break;

						$ret .= '<div class="kot-info kot-neutered">';
						$ret .= '<span class="info-head gdlr-title">' . pll__('Кастрация', 'gdlr-kot') . ' </span>';
						$ret .= $option['neutered'];
						$ret .= '</div>';
						break;

						case 'kids':
						if(empty($option['kids'])) break;

						$ret .= '<div class="kot-info kot-kids">';
						$ret .= '<span class="info-head gdlr-title">' . pll__('Разбира се с деца', 'gdlr-kot') . ' </span>';
						$ret .= $option['kids'];
						$ret .= '</div>';

						break;
					case 'cats':
						if(empty($option['cats'])) break;

						$ret .= '<div class="kot-info kot-cats">';
						$ret .= '<span class="info-head gdlr-title">' . pll__('Разбира се с други котки', 'gdlr-kot') . ' </span>';
						$ret .= $option['cats'];
						$ret .= '</div>';
						break;

						case 'location':
						if(empty($option['location'])) break;

						$ret .= '<div class="kot-info kot-location">';
						$ret .= '<span class="info-head gdlr-title">' . pll__('Намира се в', 'gdlr-kot') . ' </span>';
						$ret .= $option['location'];
						$ret .= '</div>';
						break;
				}
			}

			if($wrapper && !empty($ret)){
				return '<div class="gdlr-kot-info">' . $ret . '<div class="clear"></div></div>';
			}else if( !empty($ret) ){
				return $ret . '<div class="clear"></div>';
			}
			return '';
		}
	}

	// get kot thumbnail class
	if( !function_exists('gdlr_get_kot_thumbnail_class') ){
		function gdlr_get_kot_thumbnail_class( $post_option ){
			global $gdlr_related_section;
			if( is_single() && $post_option['inside-thumbnail-type'] != 'thumbnail-type'
				&& empty($gdlr_related_section) ){ $type = 'inside-';
			}else{ $type = ''; }

			switch($post_option[$type . 'thumbnail-type']){
				case 'feature-image': return 'gdlr-image' ;
				case 'image': return 'gdlr-image' ;
				case 'video': return 'gdlr-video' ;
				case 'slider': return 'gdlr-slider' ;
				case 'stack-images': return 'gdlr-stack-images' ;
				default: return '';
			}
		}
	}

	// get kot icon class
	if( !function_exists('gdlr_get_kot_icon_class') ){
		function gdlr_get_kot_icon_class($post_option){
			switch($post_option['thumbnail-link']){
				case 'current-post': return 'icon-link fa fa-link' ;
				case 'current': return 'icon-search fa fa-search' ;
				case 'url': return 'icon-link fa fa-link' ;
				case 'image': return'icon-search fa fa-search' ;
				case 'video': return 'icon-play fa fa-play' ;
				default: return 'icon-link fa fa-link';
			}
		}
	}

	// get kot link attribute
	if( !function_exists('gdlr_get_kot_thumbnail_link') ){
		function gdlr_get_kot_thumbnail_link($post_option, $location = 'media'){
			if($location == 'title'){
				$link_type = (!empty($post_option['thumbnail-link']) && $post_option['thumbnail-link'] == 'url')? 'url': 'current-post';
			}else{
				$link_type = $post_option['thumbnail-link'];
			}

			switch($link_type){
				case 'current':
					$image_full = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
					return ' href="' . $image_full[0] . '" data-rel="fancybox" ';
				case 'url':
					$ret  = ' href="' . $post_option['thumbnail-url'] . '" ';
					$ret .= ($post_option['thumbnail-new-tab'] == 'enable')? 'target="_blank" ': '';
					return $ret;
				case 'image': return ' href="' . $post_option['thumbnail-url'] . '" data-rel="fancybox" ';
				case 'video': return ' href="' . $post_option['thumbnail-url'] . '" data-rel="fancybox" data-fancybox-type="iframe" ';
				case 'current-post': default: return ' href="' . get_permalink() . '" data-lightbox="' . get_the_ID() . '" ';
			}

		}
	}

	// get kot thumbnail
	if( !function_exists('gdlr_get_kot_thumbnail') ){
		function gdlr_get_kot_thumbnail($post_option, $size = 'full', $modern_style = false){
			global $gdlr_related_section;
			if( is_single() && $post_option['inside-thumbnail-type'] != 'thumbnail-type'
				&& empty($gdlr_related_section)){ $type = 'inside-';
			}else{ $type = ''; }

			switch($post_option[$type . 'thumbnail-type']){
				case 'feature-image':
					$image_id = get_post_thumbnail_id();
					if( !empty($image_id) ){
						if( $modern_style ){
							$ret  = gdlr_get_image($image_id, $size);
							$ret .= '<span class="kot-overlay" >&nbsp;</span>';
							$ret .= '<div class="kot-overlay-content">';
							$ret .= '<a class="kot-overlay-wrapper" ' . gdlr_get_kot_thumbnail_link($post_option) . ' >';
							$ret .= '<span class="kot-icon" ><i class="' . gdlr_get_kot_icon_class($post_option) . '" ></i></span>';
							$ret .= '</a>';
							$ret .= '<h3 class="kot-title"><a ' . gdlr_get_kot_thumbnail_link($post_option, 'title') . ' >' . get_the_title() . '</a></h3>';
							$ret .= '</div>'; // kot-overlay-content
						}else if( !is_single() || $gdlr_related_section ){
							$ret  = gdlr_get_image($image_id, $size);
							$ret .= '<a class="kot-overlay-wrapper" ' . gdlr_get_kot_thumbnail_link($post_option) . ' >';
							$ret .= '<span class="kot-overlay" >&nbsp;</span>';
							$ret .= '<span class="kot-icon" ><i class="' . gdlr_get_kot_icon_class($post_option) . '" ></i></span>';
							$ret .= '</a>';
						}else{
							$ret  = gdlr_get_image($image_id, $size, true);
						}
					}
					break;
				case 'image':
					$ret = gdlr_get_image($post_option[$type . 'thumbnail-image'], $size, true);
					break;
				case 'video':
					if( is_single() && empty($gdlr_related_section) ){
						$ret = gdlr_get_video($post_option[$type . 'thumbnail-video'], 'full');
					}else{
						$ret = gdlr_get_video($post_option[$type . 'thumbnail-video'], $size);
					}
					break;
				case 'gallery':
					$settings = array();
					$settings['slider'] = $post_option['inside-thumbnail-slider'];
					$settings['thumbnail-size'] = $post_option['inside-gallery-thumbnail'];
					$settings['gallery-columns'] = $post_option['inside-gallery-columns'];
					$settings['show-caption'] = $post_option['inside-gallery-caption'];
					$settings['gallery-style'] = 'grid';

					$ret = gdlr_get_gallery_item($settings);
					break;
				case 'slider':
					$ret = gdlr_get_slider($post_option[$type . 'thumbnail-slider'], $size);
					break;
				case 'stack-image':
					$ret = gdlr_get_stack_images($post_option[$type . 'thumbnail-slider']);
					break;
				default :
					$ret = '';
			}

			return $ret;
		}
	}

	// print classic kot
	if( !function_exists('gdlr_get_classic_kot') ){
		function gdlr_get_classic_kot($query, $size, $thumbnail_size, $layout = 'fitRows'){
			if($layout == 'carousel'){
				return gdlr_get_classic_carousel_kot($query, $size, $thumbnail_size);
			}

			global $post;

			$current_size = 0;
			$ret  = '<div class="gdlr-isotope" data-type="kot" data-layout="' . $layout  . '" >';
			while($query->have_posts()){ $query->the_post();
				if( $current_size % $size == 0 ){
					$ret .= '<div class="clear"></div>';
				}

				$ret .= '<div class="' . gdlr_get_column_class('1/' . $size) . '">';
				$ret .= '<div class="gdlr-item gdlr-kot-item gdlr-classic-kot">';
				$ret .= '<div class="gdlr-ux gdlr-classic-kot-ux">';

				$port_option = json_decode(gdlr_decode_preventslashes(get_post_meta($post->ID, 'post-option', true)), true);
				$ret .= '<div class="kot-thumbnail ' . gdlr_get_kot_thumbnail_class($port_option) . '">';
				$ret .= gdlr_get_kot_thumbnail($port_option, $thumbnail_size);
				$ret .= '</div>'; // kot-thumbnail

				$ret .= '<div class="kot-content-wrapper">';
				$ret .= '<h3 class="kot-title"><a ' . gdlr_get_kot_thumbnail_link($port_option, 'title') . ' >' . get_the_title() . '</a></h3>';
				$ret .= gdlr_get_kot_info(array('tag'));
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
	if( !function_exists('gdlr_get_classic_carousel_kot') ){
		function gdlr_get_classic_carousel_kot($query, $size, $thumbnail_size){
			global $post;

			$ret  = '<div class="gdlr-kot-carousel-item gdlr-item" >';
			$ret .= '<div class="gdlr-ux gdlr-classic-kot-ux">';
			$ret .= '<div class="flexslider" data-type="carousel" data-nav-container="kot-item-wrapper" data-columns="' . $size . '" >';
			$ret .= '<ul class="slides" >';
			while($query->have_posts()){ $query->the_post();
				$ret .= '<li class="gdlr-item gdlr-kot-item gdlr-classic-kot">';

				$port_option = json_decode(gdlr_decode_preventslashes(get_post_meta($post->ID, 'post-option', true)), true);
				$ret .= '<div class="kot-thumbnail ' . gdlr_get_kot_thumbnail_class($port_option) . '">';
				$ret .= gdlr_get_kot_thumbnail($port_option, $thumbnail_size);
				$ret .= '</div>'; // kot-thumbnail

				$ret .= '<div class="kot-content-wrapper">';
				$ret .= '<h3 class="kot-title gdlr-skin-title"><a ' . gdlr_get_kot_thumbnail_link($port_option, 'title') . ' >' . get_the_title() . '</a></h3>';
				$ret .= '<div class="gdlr-kot-info gdlr-skin-info">';
				$ret .= gdlr_get_kot_info(array('tag'), array(), false);
				$ret .= '</div>';
				$ret .= '</div>';
				$ret .= '</li>';
			}
			$ret .= '</ul>';
			$ret .= '</div>';
			$ret .= '</div>'; // gdlr-ux
			$ret .= '</div>';

			return $ret;
		}
	}

	// print modern kot
	if( !function_exists('gdlr_get_modern_kot') ){
		function gdlr_get_modern_kot($query, $size, $thumbnail_size, $layout = 'fitRows'){
			if($layout == 'carousel'){
				return gdlr_get_modern_carousel_kot($query, $size, $thumbnail_size);
			}

			global $post;

			$current_size = 0;
			$ret  = '<div class="gdlr-isotope" data-type="kot" data-layout="' . $layout  . '" >';
			while($query->have_posts()){ $query->the_post();
				if( $current_size % $size == 0 ){
					$ret .= '<div class="clear"></div>';
				}

				$ret .= '<div class="' . gdlr_get_column_class('1/' . $size) . '">';
				$ret .= '<div class="gdlr-item gdlr-kot-item gdlr-modern-kot">';
				$ret .= '<div class="gdlr-ux gdlr-modern-kot-ux">';

				$port_option = json_decode(gdlr_decode_preventslashes(get_post_meta($post->ID, 'post-option', true)), true);
				$ret .= '<div class="kot-thumbnail ' . gdlr_get_kot_thumbnail_class($port_option) . '">';
				$ret .= gdlr_get_kot_thumbnail($port_option, $thumbnail_size, true);
				$ret .= '</div>'; // kot-thumbnail

				$ret .= '</div>'; // gdlr-ux
				$ret .= '</div>'; // gdlr-item
				$ret .= '</div>'; // gdlr-column-class
				$current_size ++;
			}
			$ret .= '</div>';
			wp_reset_postdata();

			return $ret;
		}
	}
	if( !function_exists('gdlr_get_modern_carousel_kot') ){
		function gdlr_get_modern_carousel_kot($query, $size, $thumbnail_size){
			global $post;

			$ret  = '<div class="gdlr-kot-carousel-item gdlr-item" >';
			$ret .= '<div class="gdlr-ux gdlr-modern-kot-ux">';
			$ret .= '<div class="flexslider" data-type="carousel" data-nav-container="kot-item-wrapper" data-columns="' . $size . '" >';
			$ret .= '<ul class="slides" >';
			while($query->have_posts()){ $query->the_post();
				$ret .= '<li class="gdlr-item gdlr-kot-item gdlr-modern-kot">';

				$port_option = json_decode(gdlr_decode_preventslashes(get_post_meta($post->ID, 'post-option', true)), true);
				$ret .= '<div class="kot-thumbnail ' . gdlr_get_kot_thumbnail_class($port_option) . '">';
				$ret .= gdlr_get_kot_thumbnail($port_option, $thumbnail_size, true);
				$ret .= '</div>'; // kot-thumbnail
				$ret .= '</li>';
			}
			$ret .= '</ul>';
			$ret .= '</div>'; // flexslider
			$ret .= '</div>'; // gdlr-ux
			$ret .= '</div>'; // gdlr-item

			return $ret;
		}
	}

?>
