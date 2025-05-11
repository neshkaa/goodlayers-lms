<?php get_header(); ?>
<div class="gdlr-content">

	<?php 
		global $gdlr_sidebar, $theme_option;
		$gdlr_sidebar = array(
			'type'=> 'no-sidebar',
			'left-sidebar'=> '', 
			'right-sidebar'=> ''
		); 
		$gdlr_sidebar = gdlr_get_sidebar_class($gdlr_sidebar);
		
		// init
		$donator = get_option('gdlr_paypal', array());
		$our_donator = $donator[$_GET['invoice']];
		$cause_option = json_decode(gdlr_decode_preventslashes(get_post_meta($our_donator['post-id'], 'post-option', true)), true);
		if( !empty($cause_option['donation-form']) ){
				$shortcode = trim($cause_option['donation-form']);
		}
		if( empty($shortcode) ){
			$shortcode = trim($theme_option['cause-donation-form']);
		}
		$atts = shortcode_parse_atts($shortcode);

		$price = floatval($our_donator['amount']) * 100;
		$invoice = trim($_GET['invoice']);
		$api_key = trim($atts['stripe_secret_key']);
		$publishable_key = trim($atts['stripe_publishable_key']);
		$currency = trim($atts['stripe_currency_code']);

		// set payment intent
		\Stripe\Stripe::setAppInfo(
		  "WordPress CharityHub Theme",
		  "1.32",
		  "https://themeforest.net/item/charity-hub-charity-nonprofit-fundraising-wp/7481543"
		);
		\Stripe\Stripe::setApiKey($api_key);
		$intent = \Stripe\PaymentIntent::create([
			'description' => get_the_ID($our_donator['post-id']),
		    'amount' => $price,
		    'currency' => $currency,
		    'metadata' => array(
		    	'invoice' => $invoice
		    )
		]);

	?>
	<div class="with-sidebar-wrapper">
		<div class="with-sidebar-container container">
			<div class="with-sidebar-left <?php echo esc_attr($gdlr_sidebar['outer']); ?> columns">
				<div class="with-sidebar-content <?php echo esc_attr($gdlr_sidebar['center']); ?> columns">
					<div class="gdlr-item gdlr-blog-full gdlr-item-start-content">
<form action="" method="POST" class="gdlr-single-payment-form" id="stripe-payment-form" data-ajax="<?php echo AJAX_URL; ?>" data-invoice="<?php echo esc_attr($_GET['invoice']); ?>" >
    
    <h1>Вие дарявате <?php echo $record[$num]['amount'] . "\r\n";?></h1>
    
	<p class="gdlr-form-half-left">
		<label><span><?php _e('Card Holder Name', 'gdlr'); ?></span></label>
		<input type="text" size="20" id="cardholder-name" />
	</p>
	<div class="clear" ></div>

	<p class="gdlr-form-half-left">
		<label><span><?php _e('Card Information', 'gdlr'); ?></span></label>
		<div id="card-element"></div>
	</p>
	<div class="clear" ></div>

	<div class="gdlr-form-error payment-errors" style="display: none;"></div>
	<div class="gdlr-form-loading gdlr-form-instant-payment-loading"><?php _e('Loading...', 'gdlr'); ?></div>
	<div class="gdlr-form-notice gdlr-form-instant-payment-notice"></div>
	<input id="card-button" data-secret="<?= $intent->client_secret ?>" type="submit" class="gdlr-form-button cyan" value="<?php _e('Submit Payment', 'gdlr'); ?>" >
</form>
<script type="text/javascript">
(function($){
	var form = $('#stripe-payment-form');
	var invoice = <?php echo esc_js($invoice); ?>;

	var stripe = Stripe('<?php echo esc_js($publishable_key); ?>');
	var elements = stripe.elements();
	var cardElement = elements.create('card');
	cardElement.mount('#card-element');

	var cardholderName = document.getElementById('cardholder-name');
	var cardButton = document.getElementById('card-button');
	var clientSecret = cardButton.dataset.secret;

	cardButton.addEventListener('click', function(ev){

		// validate empty input field
		if( !form.find('#cardholder-name').val() ){
			var req = true;
		}else{
			var req = false;
		}

		// make the payment
		if( req ){
			form.find('.payment-errors').text('<?php _e('Please fill the card holder name', 'gdlr'); ?>').slideDown();
		}else{

			// Disable the submit button to prevent repeated clicks
			form.find('input[type="submit"]').prop('disabled', true);
			form.find('.payment-errors, .gdlr-form-notice').slideUp();
			form.find('.gdlr-form-loading').slideDown();
			
			// made a payment
			stripe.handleCardPayment(
				clientSecret, cardElement, {
					payment_method_data: {
						billing_details: {name: cardholderName.value}
					}
				}
			).then(function(result){
				if( result.error ){

					form.find('.payment-errors').text(result.error.message).slideDown();
					form.find('input[type="submit"]').prop('disabled', false);
					form.find('.gdlr-form-loading').slideUp();

				}else{

					// The payment has succeeded. Display a success message.
					$.ajax({
						type: 'POST',
						url: form.attr('data-ajax'),
						data: { 'action':'gdlr_stripe_payment', 'invoice': invoice, 'paymentIntent': result.paymentIntent },
						dataType: 'json',
						error: function(a, b, c){ 
							console.log(a, b, c); 
							form.find('.gdlr-form-loading').slideUp();
						},
						success: function(data){
							form.children().not('.gdlr-form-notice').slideUp();
							form.find('.gdlr-form-notice').removeClass('success failed')
								.addClass(data.status).html(data.message).slideDown();

							if( data.status == 'failed' ){
								form.find('input[type="submit"]').prop('disabled', false);
							}
						}
					});	
					
				}
			});
		}
	});
	$(cardButton).on('click', function(){
		return false;
	});
})(jQuery);
</script>
					</div>
				</div>
				<div class="clear"></div>
			</div>
			<div class="clear"></div>
		</div>				
	</div>				

</div><!-- gdlr-content -->
<?php get_footer(); ?>