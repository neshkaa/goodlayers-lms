<div class="gdlr-lms-admin-head">
	<div class="gdlr-lms-admin-head-thumbnail">
		<?php 
			global $current_user;
			$author_image_id = get_user_meta($current_user->data->ID, 'author-image', true);
			if( empty($author_image_id) ){
				echo get_avatar($current_user->data->ID, 70); 
			}else{
				$image_url = wp_get_attachment_image_src($author_image_id, 'thumbnail');
				if( !empty($image_url) ){
					echo '<img alt="" src="' . $image_url[0] . '" width="' . $image_url[1] . '" height="' . $image_url[2] . '" />';
				}
			}
		
		?>
	</div>
	<div class="gdlr-lms-admin-head-content">
		<span class="gdlr-lms-welcome"><?php esc_html_e('Welcome', 'gdlr-lms'); ?></span>
		<span class="gdlr-lms-name"><?php echo gdlr_lms_text_filter($current_user->data->display_name); ?></span>
		<span class="gdlr-lms-role"><?php esc_html_e('Instructor', 'gdlr-lms'); ?></span>
	</div>
	<div class="clear"></div>
</div>
<ul class="gdlr-lms-admin-list">
	<li><a href="<?php echo esc_url(add_query_arg('type', 'profile')); ?>" ><?php esc_html_e('Edit Profile', 'gdlr-lms'); ?></a></li>
	<li><a href="<?php echo esc_url(add_query_arg('type', 'password')); ?>" ><?php esc_html_e('Edit Password', 'gdlr-lms'); ?></a></li>
	<li><a href="<?php echo esc_url(add_query_arg('type', 'my-course')); ?>" ><?php esc_html_e('My Courses', 'gdlr-lms'); ?></a></li>
	<li><a href="<?php echo esc_url(add_query_arg('type', 'scoring-status')); ?>" ><?php esc_html_e('Scoring Status', 'gdlr-lms'); ?></a></li>
<?php
	global $wpdb;

	$args = array('post_type' => 'course', 'suppress_filters' => false);
	$args['author'] = $current_user->ID;
	$args['posts_per_page'] = 9999;
	$args['orderby'] = 'post_date';
	$args['order'] = 'desc';		
	$query = new WP_Query($args);
	
	$course_list = array();
	while( $query->have_posts() ){ $query->the_post();
		$course_list[] = get_the_ID();
	}
	$course_list = empty($course_list)? array(0): $course_list;
	wp_reset_postdata();
	
	$temp_sql  = "SELECT * FROM " . $wpdb->prefix . "gdlrquiz ";
	$temp_sql .= "WHERE course_id IN (" . implode(',', $course_list) . ") ";
	$temp_sql .= "AND (quiz_status IS NULL OR quiz_status != 'complete') ";	
	$temp_sql .= "AND quiz_status != 'pending' ";
		
	
	$manual_check_results = $wpdb->get_results($temp_sql);
?>	
	<li>
		<a href="<?php echo esc_url(add_query_arg('type', 'manual-check-needed')); ?>" ><?php esc_html_e('Manual Check Needed', 'gdlr-lms'); ?></a>
		<?php 
			if( sizeof($manual_check_results) > 0 ){
				echo '<span class="gdlr-lms-notification">' . sizeof($manual_check_results) . '</span>';
			} 
		?>
	</li>
	<li><a href="<?php echo esc_url(add_query_arg('type', 'earning')); ?>" ><?php esc_html_e('Earning', 'gdlr-lms'); ?></a></li>
</ul>
<div class="gdlr-lms-logout">
	<a href="<?php echo wp_logout_url(get_home_url()); ?>"><?php esc_html_e('Logout', 'gdlr-lms'); ?></a>
</div>