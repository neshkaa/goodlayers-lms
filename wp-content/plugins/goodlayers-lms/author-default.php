<?php get_header(); ?>
<div id="primary" class="content-area gdlr-lms-primary-wrapper">
<div id="content" class="site-content" role="main">
	<div class="gdlr-lms-content">
		<div class="gdlr-lms-container gdlr-lms-container">
			<div class="gdlr-lms-item">
				<?php
					$author_id = get_query_var('author');

					// author info
					echo '<div class="gdlr-lms-author-info-wrapper" >';
					echo '<div class="gdlr-lms-author-thumbnail">';
					echo gdlr_lms_get_author_image($author_id, $gdlr_lms_option['instructor-thumbnail-size']);
					echo '</div>'; // author-thumbnail

					echo '<div class="gdlr-lms-author-title-wrapper">';
					echo '<div class="gdlr-lms-author-name">' . gdlr_lms_get_user_info($author_id) . '</div>';
					$author_position = gdlr_lms_get_user_info($author_id, 'position');
					if( !empty($author_position) ){
						echo '<div class="gdlr-lms-author-position">' . $author_position . '</div>';
					}
					echo '</div>'; // author-title-wrapper

					echo '<div class="gdlr-lms-author-info">';
					$author_phone = gdlr_lms_get_user_info($author_id, 'phone');
					if( !empty($author_phone) ){
						echo '<div class="author-info phone"><i class="fa fa-phone icon-phone"></i>' . $author_phone . '</div>';
					}

					$author_email = gdlr_lms_get_user_info($author_id, 'user_email');
					if( !empty($author_email) ){
						echo '<div class="author-info mail"><i class="fa fa-envelope-o icon-envelope-alt"></i><a href="mailto:' . esc_attr($author_email) . '" >' . $author_email . '</a></div>';
					}

					$author_url = gdlr_lms_get_user_info($author_id, 'user_url');
					if( !empty($author_url) ){
						echo '<div class="author-info url"><i class="fa fa-link icon-link"></i><a href="' . esc_attr($author_url) . '" target="_blank" >' . $author_url . '</a></div>';
					}
					echo '</div>'; // author-info

					$author_social = gdlr_lms_get_user_info($author_id, 'social-network');
					if( !empty($author_social) ){
						echo '<div class="gdlr-lms-author-social">' . do_shortcode($author_social) . '</div>';
					}

					// count the post by this author and display link
					global $wpdb;
					$where = get_posts_by_author_sql('course', true, $author_id);
					$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );

					if( $count ){
						echo '<a class="gdlr-lms-button cyan" href="' . esc_url(add_query_arg('post_type','course'));
						echo '" >' . esc_html__('View courses by','gdlr-lms') . ' ' . gdlr_lms_get_user_info($author_id) . '</a>';
					}else{
						$where = get_posts_by_author_sql('post', true, $author_id);
						$count = $wpdb->get_var( "SELECT COUNT(*) FROM $wpdb->posts $where" );

						if( $count ){
							echo '<a class="gdlr-lms-button cyan" href="' . esc_url(add_query_arg('post_type','post'));
							echo '" >' . esc_html__('View posts by','gdlr-lms') . ' ' . gdlr_lms_get_user_info($author_id) . '</a>';
						}
					}
					echo '</div>'; // author-info-wrapper

					// extra info
					echo '<div class="gdlr-lms-author-content-wrapper">';
					echo '<div class="gdlr-lms-author-extra-info-wrapper">';
					$extra_infos = array(
						'location'=> esc_html__('Location', 'gdlr-lms'),
						'current-work'=> esc_html__('Current Work', 'gdlr-lms'),
						'past-work'=> esc_html__('Past Work', 'gdlr-lms'),
						'specialist'=> esc_html__('Specialist In', 'gdlr-lms'),
						'experience'=> esc_html__('Experience', 'gdlr-lms')
					);

					foreach( $extra_infos as $key => $value ){
						$extra_info = gdlr_lms_get_user_info($author_id, $key);
						if( !empty($extra_info) ){
							echo '<div class="gdlr-lms-extra-info ' . $key . '" >';
							echo '<span class="gdlr-head">' . $value . '</span>';
							echo '<span class="gdlr-tail">' . $extra_info . '</span>';
							echo '</div>';
						}
					}
					echo '</div>'; // author-extra-info

					echo '<div class="gdlr-lms-author-content-wrapper">';
					echo '<h3 class="gdlr-lms-author-content-title">' . esc_html__('Biography', 'gdlr-lms') . '</h3>';
					$author_content = gdlr_lms_get_user_info($author_id, 'author-biography');
					if( empty($author_content) ){
						$author_content = gdlr_lms_get_user_info($author_id, 'description');
					}
					
					echo do_shortcode($author_content);
					echo '</div>'; // author-content
					echo '</div>'; // author-content-wrapper
				?>
				<div class="clear"></div>
			</div><!-- gdlr-lms-item -->
		</div><!-- gdlr-lms-container -->
	</div><!-- gdlr-lms-content -->
</div>
</div>
<?php
if( !empty($gdlr_lms_option['show-sidebar']) && $gdlr_lms_option['show-sidebar'] == 'enable' ){ 
	get_sidebar( 'content' );
	get_sidebar();
}

get_footer(); ?>
