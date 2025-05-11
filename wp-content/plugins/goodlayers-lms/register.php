<?php 
	$error = array();
	if( !empty($_POST['action']) && $_POST['action'] == 'create-new-user' ){
		if( empty($_POST['username']) || empty($_POST['password']) || empty($_POST['re-password']) ||
			empty($_POST['first_name']) || empty($_POST['last_name']) || empty($_POST['gender']) ||
			empty($_POST['birth_date']) || empty($_POST['email']) || empty($_POST['address']) ){
			
			$error[] = esc_html__('Please enter all required fields.', 'gdlr-lms');
		}
		
		if( $_POST['password'] != $_POST['re-password'] ){
			$error[] = esc_html__('Password and password confirmation do not match.', 'gdlr-lms');
		}
		
		if( username_exists($_POST['username']) ){
			$_POST['username'] = '';
			$error[] = esc_html__('Username already exists, please try again with another name.', 'gdlr-lms');
		}
		
		if( email_exists($_POST['email']) ){
			$_POST['email'] = '';
			$error[] = esc_html__('Email already exists, Please try again with new email address.', 'gdlr-lms');
		}
		
		if( !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) ){
			$_POST['email'] = '';
			$error[] = esc_html__('Email is not valid.', 'gdlr-lms');
		}
		
		$check_result = apply_filters( 'gglcptch_verify_recaptcha', true, 'bool', 'gdlr-lms-register-form' );
		if( !$check_result ){
			$error[] = esc_html__('Please verify that you are not a robot.', 'gdlr-lms');
		}

		if( empty($error) ){
			$user_id = wp_insert_user(array(
				'user_login' => $_POST['username'], 
				'user_pass' => $_POST['password'], 
				'user_email' => $_POST['email'],
				'role' => 'student'
			));
			
			if( is_wp_error($user_id) ){
				$error[] = esc_html__('Please only fill latin characters in username and password fields.', 'gdlr-lms');
			}else{
				if( !empty($_POST['first_name']) ){
					update_user_meta($user_id, 'first_name', esc_attr($_POST['first_name']));
				}
				if( !empty($_POST['last_name']) ){
					update_user_meta($user_id, 'last_name', esc_attr($_POST['last_name']));
				}
				if( !empty($_POST['gender']) ){
					update_user_meta($user_id, 'gender', esc_attr($_POST['gender']));
				}
				if( !empty($_POST['birth_date']) ){
					update_user_meta($user_id, 'birth-date', esc_attr($_POST['birth_date']));
				}
				if( !empty($_POST['phone']) ){
					update_user_meta($user_id, 'phone', esc_attr($_POST['phone']));
				}
				if( !empty($_POST['address']) ){
					update_user_meta($user_id, 'address', $_POST['address']);
				}	
				
				wp_new_user_notification($user_id, $_POST['password']);
				

				$redirect_url = home_url();
				if( !empty($_GET['register']) ){
					if( is_numeric($_GET['register']) ){
						$redirect_url = get_permalink($_GET['register']);
					}else{
						$redirect_url = $_GET['register'];
					}
				}
				
?>
<form method="post" action="<?php echo esc_url(site_url('wp-login.php', 'login_post')); ?>" id="login-redirect">
    <input type="hidden" name="log" value="<?php echo esc_attr($_POST['username']); ?>" />
    <input type="hidden" name="pwd" value="<?php echo esc_attr($_POST['password']); ?>" />
	<input type="hidden" name="rememberme"  value="forever" />
	<input type="hidden" name="redirect_to" value="<?php echo esc_url($redirect_url); ?>" />
</form>

<script type="text/javascript">
   document.getElementById("login-redirect").submit();
</script>
<?php				
			}
		}
	}
	get_header(); 
?>
<div id="primary" class="content-area gdlr-lms-primary-wrapper">
<div id="content" class="site-content" role="main">
<?php
	if( function_exists('gdlr_lms_get_header') && !empty($gdlr_lms_option['show-header']) && $gdlr_lms_option['show-header'] == 'enable' ){
		gdlr_lms_get_header();
	}
?>
	<div class="gdlr-lms-content">
		<div class="gdlr-lms-container gdlr-lms-container">
			<div class="gdlr-lms-item">
				<?php
					if( !empty($error) ){
						echo '<div class="gdlr-lms-error">' . implode("<br />", $error) . '</div>'; 
					}		
				?>
				<form class="gdlr-lms-form" method="post" action="">
					<p class="gdlr-lms-half-left">
						<label><?php esc_html_e('Username *', 'gdlr-lms'); ?></label>
						<input type="text" name="username" value="<?php echo isset($_POST['username'])? esc_attr($_POST['username']): ''; ?>" />
					</p>
					<div class="clear"></div>
					<p class="gdlr-lms-half-left">
						<label><?php esc_html_e('Password *', 'gdlr-lms'); ?></label>
						<input type="password" name="password" />
					</p>
					<p class="gdlr-lms-half-right">
						 <label><?php esc_html_e('Re Password *', 'gdlr-lms'); ?></label>
						 <input type="password" name="re-password" />
					</p>
					<div class="clear"></div>
					<p class="gdlr-lms-half-left">
						<label><?php esc_html_e('First Name *', 'gdlr-lms'); ?></label>
						<input type="text" name="first_name" value="<?php echo isset($_POST['first_name'])? esc_attr($_POST['first_name']): ''; ?>" />
					</p>
					<p class="gdlr-lms-half-right">
						 <label><?php esc_html_e('Last Name *', 'gdlr-lms'); ?></label>
						 <input type="text" name="last_name" value="<?php echo isset($_POST['last_name'])? esc_attr($_POST['last_name']): ''; ?>" />
					</p>
					<div class="clear"></div>		
					<p class="gdlr-lms-half-left">
						<label><?php esc_html_e('Gender *', 'gdlr-lms'); ?></label>
						<span class="gdlr-lms-combobox">
							<select name="gender" id="gender" >
								<option value="m" <?php if(isset($_POST['gender']) && $_POST['gender'] == 'm') echo 'selected'; ?> ><?php esc_html_e('Male', 'gdlr-lms'); ?></option>
								<option value="f" <?php if(isset($_POST['gender']) && $_POST['gender'] == 'f') echo 'selected'; ?> ><?php esc_html_e('Female', 'gdlr-lms'); ?></option>
							</select>
						</span>					
					</p>
					<p class="gdlr-lms-half-right">
						 <label><?php esc_html_e('Birth Date *', 'gdlr-lms'); ?></label>
						 <input type="text" name="birth_date" value="<?php echo isset($_POST['birth_date'])? esc_attr($_POST['birth_date']): ''; ?>" />
					</p>
					<div class="clear"></div>		
					<p class="gdlr-lms-half-left">
						<label><?php esc_html_e('Email *', 'gdlr-lms'); ?></label>
						<input type="text" name="email" id="email" value="<?php echo isset($_POST['email'])? esc_attr($_POST['email']): ''; ?>" />
					</p>	
					<p class="gdlr-lms-half-right">
						<label><?php esc_html_e('Phone', 'gdlr-lms'); ?></label>
						<input type="text" name="phone" id="phone" value="<?php echo isset($_POST['phone'])? esc_attr($_POST['phone']): ''; ?>" />
					</p>
					<div class="clear"></div>
					<p class="gdlr-lms-half-left">
						<label><?php esc_html_e('Address *', 'gdlr-lms'); ?></label>
						<textarea name="address" id="address" ><?php echo isset($_POST['address'])? esc_textarea($_POST['address']): ''; ?></textarea>
					</p>
					<?php 
						echo '<div class="gdlr-lms-google-captcha" >';
						echo apply_filters( 'gglcptch_display_recaptcha', '', 'gdlr-lms-register-form' ); 
						echo '</div>';
					?>
					<div class="clear"></div>	
					<p>
						<input type="hidden" name="action" value="create-new-user" />
						<input type="submit" class="gdlr-lms-button" value="<?php esc_html_e('Create an account', 'gdlr-lms'); ?>" />
					</p>
				</form>
			</div>
		</div>
	</div>
</div>
</div>
<?php 
if( !empty($gdlr_lms_option['show-sidebar']) && $gdlr_lms_option['show-sidebar'] == 'enable' ){ 
	get_sidebar( 'content' );
	get_sidebar();
}

get_footer(); ?>