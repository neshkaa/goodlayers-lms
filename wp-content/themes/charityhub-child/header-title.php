	<?php  if( is_single() && ($post->post_type == 'post' ) ){ 
		
			}if( !empty($gdlr_post_option['page-title']) ){
			$page_title = get_the_title();
			$page_caption = $gdlr_post_option['page-caption'];
		}else{
			$page_title = get_the_title();
			$page_caption = $theme_option['post-caption'];
		} 

	?>