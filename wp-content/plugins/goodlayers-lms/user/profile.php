<?php 
	global $current_user;
	$user_id = $current_user->data->ID;
?>
<h3 class="gdlr-lms-admin-head" ><?php esc_html_e('Edit Profile', 'gdlr-lms'); ?></h3>
<form class="gdlr-lms-form" method="post" enctype="multipart/form-data" action="<?php echo esc_url(add_query_arg($_GET)); ?>" >
	<?php
		if( $current_user->roles[0] != 'student' ){
			echo '<input class="gdlr-admin-author-image" id="gdlr-admin-author-image" type="file" name="attachment" />';
		}
	?>

	<p class="gdlr-lms-half-left">
		<label for="first-name"><?php esc_html_e('First Name *', 'gdlr-lms'); ?></label>
		<input type="text" name="first-name" id="first-name" value="<?php echo esc_attr(gdlr_lms_get_user_info($user_id, 'first_name')); ?>" />
	</p>
	<p class="gdlr-lms-half-right">
		<label for="last-name"><?php esc_html_e('Last name *', 'gdlr-lms'); ?></label>
		<input type="text" name="last-name" id="last-name" value="<?php echo esc_attr(gdlr_lms_get_user_info($user_id, 'last_name')); ?>" />
	</p>
	<div class="clear"></div>
	<p class="gdlr-lms-half-left">
		<label for="gender"><?php esc_html_e('Gender *', 'gdlr-lms'); ?></label>
		<span class="gdlr-lms-combobox">
			<?php $gender = gdlr_lms_get_user_info($user_id, 'gender'); ?>
			<select name="gender" id="gender" >
				<option value="m" <?php if(!empty($gender) && $gender == 'm') echo 'selected'; ?> ><?php esc_html_e('Male', 'gdlr-lms'); ?></option>
				<option value="f" <?php if(!empty($gender) && $gender == 'f') echo 'selected'; ?> ><?php esc_html_e('Female', 'gdlr-lms'); ?></option>
			</select>
		</span>
	</p>	
	<p class="gdlr-lms-half-right">
		<label for="birth-date"><?php esc_html_e('Birth Date *', 'gdlr-lms'); ?></label>
		<input type="text" name="birth-date" id="birth-date" value="<?php echo esc_attr(gdlr_lms_get_user_info($user_id, 'birth-date')); ?>" />
	</p>
	<div class="clear"></div>
	<p class="gdlr-lms-half-left">
		<label for="email"><?php esc_html_e('Email *', 'gdlr-lms'); ?></label>
		<input type="text" name="email" id="email" value="<?php echo esc_attr(gdlr_lms_get_user_info($user_id, 'user_email')); ?>" />
	</p>	
	<p class="gdlr-lms-half-right">
		<label for="phone"><?php esc_html_e('Phone', 'gdlr-lms'); ?></label>
		<input type="text" name="phone" id="phone" value="<?php echo esc_attr(gdlr_lms_get_user_info($user_id, 'phone')); ?>" />
	</p>
	<div class="clear"></div>
	<p class="gdlr-lms-half-left">
		<label for="address"><?php esc_html_e('Address *', 'gdlr-lms'); ?></label>
		<textarea name="address" id="address" ><?php echo esc_textarea(gdlr_lms_get_user_info($user_id, 'address')); ?></textarea>
	</p>
	<?php if( $current_user->roles[0] == 'administrator' || $current_user->roles[0] == 'instructor' ){ ?>
		<p class="gdlr-lms-half-right">
			<label for="author-biography"><?php esc_html_e('Full Biography', 'gdlr-lms'); ?></label>
			<textarea name="author-biography" id="author-biography" ><?php echo esc_textarea(gdlr_lms_get_user_info($user_id, 'author-biography')); ?></textarea>
		</p>	
	<?php } ?>
	<div class="clear"></div>
	<!-- for teacher/admin user -->
	<?php if( $current_user->roles[0] == 'administrator' || $current_user->roles[0] == 'instructor' ){ ?>
		<p class="gdlr-lms-half-left">
			<label for="location"><?php esc_html_e('Location', 'gdlr-lms'); ?></label>
			<input type="text" name="location" id="location" value="<?php echo esc_attr(gdlr_lms_get_user_info($user_id, 'location')); ?>" />
		</p>	
		<p class="gdlr-lms-half-right">
			<label for="position"><?php esc_html_e('Position', 'gdlr-lms'); ?></label>
			<input type="text" name="position" id="position" value="<?php echo esc_attr(gdlr_lms_get_user_info($user_id, 'position')); ?>" />
		</p>	
		<div class="clear"></div>
		<p class="gdlr-lms-half-left">
			<label for="current-work"><?php esc_html_e('Current Work', 'gdlr-lms'); ?></label>
			<input type="text" name="current-work" id="current-work" value="<?php echo esc_attr(gdlr_lms_get_user_info($user_id, 'current-work')); ?>" />
		</p>	
		<p class="gdlr-lms-half-right">
			<label for="past-work"><?php esc_html_e('Past Work', 'gdlr-lms'); ?></label>
			<input type="text" name="past-work" id="past-work" value="<?php echo esc_attr(gdlr_lms_get_user_info($user_id, 'past-work')); ?>" />
		</p>	
		<div class="clear"></div>		
		<p class="gdlr-lms-half-left">
			<label for="specialist"><?php esc_html_e('Specialist In', 'gdlr-lms'); ?></label>
			<input type="text" name="specialist" id="specialist" value="<?php echo esc_attr(gdlr_lms_get_user_info($user_id, 'specialist')); ?>" />
		</p>	
		<p class="gdlr-lms-half-right">
			<label for="experience"><?php esc_html_e('Experience', 'gdlr-lms'); ?></label>
			<input type="text" name="experience" id="experience" value="<?php echo esc_attr(gdlr_lms_get_user_info($user_id, 'experience')); ?>" />
		</p>	
		<div class="clear"></div>		
		<p class="gdlr-lms-half-left">
			<label for="social-network"><?php esc_html_e('Social Network', 'gdlr-lms'); ?></label>
			<textarea name="social-network" id="social-network" ><?php echo esc_textarea(gdlr_lms_get_user_info($user_id, 'social-network')); ?></textarea>
		</p>		
		<div class="clear"></div>		
	<?php } ?>
	<p>
		<input type="hidden" name="action" value="edit-profile" />
		<input type="submit" class="gdlr-lms-button cyan" value="<?php esc_html_e('Update', 'gdlr-lms'); ?>" />
	</p>		
</form>	