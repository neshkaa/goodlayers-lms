<?php
/**
 * The template for displaying video post format
 */
 
	if( !is_single() ){ 
		global $gdlr_post_settings; 
	}else{
		global $gdlr_post_settings, $theme_option;
	}

	$post_format_data = '';
	$content = trim(get_the_content(__('Read More', 'gdlr_translate')));	

	if( !preg_match('#^https?://\S+#', $content, $match) ){
		if( !preg_match('#^\[video\s.+\[/video\]#', $content, $match) ){
			if( !preg_match('#^\[embed.+\[/embed\]#', $content, $match) ){
				preg_match('#\<figure.+\<\/figure\>#sim', $content, $match_html);
			}
		}
	}
	
	if( !empty($match[0]) ){
		if( is_single() || $gdlr_post_settings['blog-style'] == 'blog-full' ){
			$post_format_data = gdlr_get_video($match[0], 'full');
		}else{
			$post_format_data = gdlr_get_video($match[0], $gdlr_post_settings['thumbnail-size']);

		}
		$gdlr_post_settings['content'] = str_replace($match[0], '', $content);
	}else if( !empty($match_html[0]) ){
		$post_format_data = gdlr_replace_oembed($match_html[0]);
		$gdlr_post_settings['content'] = str_replace($match_html[0], '', $content);
	}

	if ( !empty($post_format_data) ){
		echo '<div class="gdlr-blog-thumbnail gdlr-video">' . $post_format_data . '</div>';
	} 
?>	

