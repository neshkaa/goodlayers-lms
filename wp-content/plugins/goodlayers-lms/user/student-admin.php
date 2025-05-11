<div class="gdlr-lms-admin-head">
	<div class="gdlr-lms-admin-head-thumbnail">
		<a href="/author/<?php echo gdlr_lms_text_filter($current_user->data->display_name); ?>/?type=my-courses" >
		<?php global $current_user; echo get_avatar($current_user->data->ID, 70); ?>
		</a>
	</div>
	<div class="gdlr-lms-admin-head-content">
		<span class="gdlr-lms-welcome"><?php esc_html_e('Привет', 'gdlr-lms'); ?></span>
		<a href="/author/<?php echo gdlr_lms_text_filter($current_user->data->display_name); ?>/?type=my-courses" >
            <span class="gdlr-lms-name"><?php echo gdlr_lms_text_filter($current_user->data->display_name); ?></span>
        </a>
	</div>
	<div class="clear"></div>
</div>
<ul class="gdlr-lms-admin-list">
    	<li><a href="<?php echo esc_url(add_query_arg('type', 'my-courses')); ?>" ><?php esc_html_e('Моите курсове', 'gdlr-lms'); ?></a></li>
        <li><a href="<?php echo esc_url(add_query_arg('type', 'profile')); ?>" ><?php esc_html_e('Профил', 'gdlr-lms'); ?></a></li>
	    <li><a href="<?php echo esc_url(add_query_arg('type', 'password')); ?>" ><?php esc_html_e('Смяна на парола', 'gdlr-lms'); ?></a></li>
</ul>
<div class="gdlr-lms-logout">
	<a href="<?php echo wp_logout_url(get_home_url()); ?>"><?php esc_html_e('Изход', 'gdlr-lms'); ?></a>
</div>