<div class="gdlr-lms-admin-head">
	<div class="gdlr-lms-admin-head-thumbnail">
		<a href="http://en.gravatar.com/" target="_blank" >
		<?php global $current_user; echo get_avatar($current_user->data->ID, 70); ?>
		</a>
	</div>
	<div class="gdlr-lms-admin-head-content">
		<span class="gdlr-lms-welcome"><?php esc_html_e('Welcome', 'gdlr-lms'); ?></span>
		<span class="gdlr-lms-name"><?php echo gdlr_lms_text_filter($current_user->data->display_name); ?></span>
	</div>
	<div class="clear"></div>
</div>
<ul class="gdlr-lms-admin-list">
	<li><a href="<?php echo esc_url(add_query_arg('type', 'badge-certificate')); ?>" ><?php esc_html_e('Profile, Badges and Certifications', 'gdlr-lms'); ?></a></li>
	<li><a href="<?php echo esc_url(add_query_arg('type', 'profile')); ?>" ><?php esc_html_e('Edit Profile', 'gdlr-lms'); ?></a></li>
	<li><a href="<?php echo esc_url(add_query_arg('type', 'password')); ?>" ><?php esc_html_e('Edit Password', 'gdlr-lms'); ?></a></li>
	<li><a href="<?php echo esc_url(add_query_arg('type', 'quiz-scores')); ?>" ><?php esc_html_e('View Quiz Scores', 'gdlr-lms'); ?></a></li>
	<li><a href="<?php echo esc_url(add_query_arg('type', 'attended-courses')); ?>" ><?php esc_html_e('Attended Courses', 'gdlr-lms'); ?></a></li>
	<li><a href="<?php echo esc_url(add_query_arg('type', 'missing-courses')); ?>" ><?php esc_html_e('Missing Courses', 'gdlr-lms'); ?></a></li>
	<li><a href="<?php echo esc_url(add_query_arg('type', 'book-courses')); ?>" ><?php esc_html_e('Booked Courses', 'gdlr-lms'); ?></a></li>
	<li><a href="<?php echo esc_url(add_query_arg('type', 'confirm-courses')); ?>" ><?php esc_html_e('Confirmed Courses', 'gdlr-lms'); ?></a></li>
	<li><a href="<?php echo esc_url(add_query_arg('type', 'free-onsite')); ?>" ><?php esc_html_e('Free Onsite Courses', 'gdlr-lms'); ?></a></li>
</ul>
<div class="gdlr-lms-logout">
	<a href="<?php echo wp_logout_url(get_home_url()); ?>"><?php esc_html_e('Logout', 'gdlr-lms'); ?></a>
</div>