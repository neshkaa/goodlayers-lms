<?php get_header(); ?>
<div id="primary" class="content-area gdlr-lms-primary-wrapper">
<div id="content" class="site-content" role="main">
<?php
	if( function_exists('gdlr_lms_get_header') && !empty($gdlr_lms_option['show-header']) && $gdlr_lms_option['show-header'] == 'enable' ){
		gdlr_lms_get_header();
	}
?>
	<div class="gdlr-lms-content">
		<div class="gdlr-lms-container gdlr-lms-container">
			<div class="gdlr-lms-item gdlr-lms-authorize-payment">	


				<form action="" method="POST" class="gdlr-payment-form" id="payment-form" data-ajax="<?php echo admin_url('admin-ajax.php'); ?>" data-invoice="<?php echo esc_attr($_GET['invoice']); ?>" >
					<p class="gdlr-form-half-left">
						<label><span><?php esc_html_e('Card Number', 'gdlr-hotel'); ?></span></label>
						<input type="text" size="20" data-authorize="number"/>
					</p>
					<div class="clear" ></div>
					
					<p class="gdlr-form-half-left">
						<label><span><?php esc_html_e('CVC', 'gdlr-hotel'); ?></span></label>
						<input type="text" size="4" data-authorize="cvc"/>
					</p>
					<div class="clear" ></div>

					<p class="gdlr-form-half-left gdlr-form-expiration">
						<label><span><?php esc_html_e('Expiration (MM/YYYY)', 'gdlr-hotel'); ?></span></label>
						<input type="text" size="2" data-authorize="exp-month"/>
						<span class="gdlr-separator" >/</span>
						<input type="text" size="4" data-authorize="exp-year"/>
					</p>
					<div class="clear" ></div>
					<div class="gdlr-form-error payment-errors" style="display: none;"></div>
					<div class="gdlr-form-loading gdlr-form-instant-payment-loading"><?php esc_html_e('loading', 'gdlr-hotel'); ?></div>
					<div class="gdlr-form-notice gdlr-form-instant-payment-notice"></div>
					<input type="submit" class="gdlr-form-button cyan" value="<?php esc_html_e('Submit Payment', 'gdlr-hotel'); ?>" >
				</form>
				<script type="text/javascript">
					(function($){
						var form = $('#payment-form');

						function goodlayersAuthorizeCharge(){

							var tid = form.attr('data-invoice');
							var form_value = {};
							form.find('[data-authorize]').each(function(){
								form_value[$(this).attr('data-authorize')] = $(this).val(); 
							});

							console.log({ 'ajax_url': form.attr('data-ajax'), 'action':'gdlr_lms_authorize_payment', 'tid': tid, 'form': form_value })
							$.ajax({
								type: 'POST',
								url: form.attr('data-ajax'),
								data: { 'action':'gdlr_lms_authorize_payment', 'tid': tid, 'form': form_value },
								dataType: 'json',
								error: function(a, b, c){ 
									console.log(a, b, c); 
									form.find('input[type="submit"]').prop('disabled', false);

									// display error messages
									form.find('.gdlr-form-notice, .gdlr-form-loading').slideUp(200);
									form.find('.payment-errors').text('<?php echo esc_html__('An error occurs, please refresh the page to try again.', 'gdlr-hotel'); ?>').slideDown(200);
									form.find('input[type="submit"]').prop('disabled', false).removeClass('now-loading'); 

								},
								success: function(data){
									form.find('input[type="submit"]').prop('disabled', false);
									form.find('.payment-errors, .gdlr-form-notice, .gdlr-form-loading').slideUp(200);

									if( data.status == 'success' ){
										if( typeof(data.message) != 'undefined' ){
											form.find('.gdlr-form-instant-payment-notice').html(data.message).slideDown(200);
										}
										if( typeof(data.redirect) != 'undefined' ){
											window.location = data.redirect;
										}
									}else{
										if( typeof(data.message) != 'undefined' ){
											form.find('.payment-errors').html(data.message).slideDown(200);
										}
									}
								}
							});	
						};
						
						form.submit(function(event){
						
							var req = false;
							form.find('input').each(function(){
								if( !$(this).val() ){
									req = true;
								}
							});

							if( req ){
								form.find('.payment-errors').text('<?php esc_html_e('Please fill all required fields', 'gdlr-hotel'); ?>').slideDown();
							}else{
								form.find('input[type="submit"]').prop('disabled', true);
								form.find('.payment-errors, .gdlr-form-notice').slideUp();
								form.find('.gdlr-form-loading').slideDown();

								goodlayersAuthorizeCharge();
							}

							return false;
						});
					})(jQuery);
				</script>

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

	get_footer();
?>