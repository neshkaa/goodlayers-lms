<?php
	/*	
	*	Goodlayers Utility File
	*/	
	

	if( !function_exists('gdlr_lms_escape_sql_number') ){
		function gdlr_lms_escape_sql_number( $number ){
			return is_numeric($number)? $number: 0;
		} // gdlr_lms_escape_sql_number
	} // function_exists

	if( !function_exists('gdlr_lms_get_section_page') ){
		function gdlr_lms_get_section_page( $course_section ){
			$ret = explode('-', $course_section);
			while( sizeof($ret) < 2 ){
				$ret[] = 1;
			}

			return $ret;
		} // gdlr_lms_get_attendance_section
	} // function_exists

	if( !function_exists('gdlr_lms_get_user_info') ){
		function gdlr_lms_get_user_info( $author_id = null, $type = 'full_name' ){
			if( $type == 'full_name' ){
				$name  = get_the_author_meta('first_name', $author_id);
				if( !empty($name) ){
					$name .= ' ' . get_the_author_meta('last_name', $author_id);
				}else{
					$name  = get_the_author_meta('display_name', $author_id);
				}

				return do_shortcode($name);
			}else{
				$user_info = get_the_author_meta($type, $author_id);
				
				if( !empty($user_info) ){
					return do_shortcode($user_info);
				}
			}

			return '';
		} // gdlr_lms_get_user_info
	} // function_exists

	// send the mail
	if( !function_exists('gdlr_lms_mail') ){
		function gdlr_lms_mail($recipient, $title, $message){
			global $lms_paypal;

			$headers = 'From: ' . $lms_paypal['recipient_name'] . ' <' . $lms_paypal['recipient'] . '>' . "\r\n";
			$headers = $headers . 'Content-Type: text/plain; charset=UTF-8 ' . " \r\n";
			wp_mail($recipient, $title, $message, $headers);		
		}
	}
	
	// format the currency
	if( !function_exists('gdlr_lms_money_format') ){
		function gdlr_lms_money_format($amount, $format = ''){
			if( empty($format) ){
				global $lms_money_format;
				$format = $lms_money_format;
			}
			return str_replace('NUMBER', $amount, $format);
		}
	}
	
	// format the date
	if( !function_exists('gdlr_lms_date_format') ){
		function gdlr_lms_date_format($date, $format = ''){
			if( empty($format) ){
				global $lms_date_format;
				$format = $lms_date_format;
			}
			return empty($date)? '': date_i18n($format, strtotime($date));
		}	
	}	
	
	// course excerpt
	if( !function_exists('gdlr_lms_set_excerpt_length') ){
		function gdlr_lms_set_excerpt_length( $length ){
			global $gdlr_lms_excerpt_length; return $gdlr_lms_excerpt_length ;
		}
	}
	if( !function_exists('gdlr_lms_excerpt_more') ){
		function gdlr_lms_excerpt_more( $more ) {
			return '... <div class="clear"></div><a href="' . get_permalink() . '" class="excerpt-read-more">' . esc_html__( 'Learn More', 'gdlr-lms' ) . '</a>';
		}	
	}	
	
	// custom text filter
	add_filter( 'gdlr_lms_the_content', 'wptexturize'        ); add_filter( 'gdlr_lms_the_content', 'convert_smilies'    );
	add_filter( 'gdlr_lms_the_content', 'convert_chars'      ); add_filter( 'gdlr_lms_the_content', 'wpautop'            );
	add_filter( 'gdlr_lms_the_content', 'shortcode_unautop'  ); add_filter( 'gdlr_lms_the_content', 'prepend_attachment' );	
	add_filter( 'gdlr_lms_the_content', 'do_shortcode'       );
	if( !function_exists('gdlr_lms_content_filter') ){
		function gdlr_lms_content_filter($content, $main_content = false){
			if($main_content) return str_replace(']]>', ']]&gt;', apply_filters('the_content', $content));
			
			global $wp_embed;
			$content = $wp_embed->autoembed($content);
			return apply_filters('gdlr_lms_the_content', $content);
		}		
	}		
	
	// get post list
	if( !function_exists('gdlr_lms_get_post_list') ){
		function gdlr_lms_get_post_list($post_type){
			$post_list = get_posts(array('post_type' => $post_type, 'numberposts'=>9999));

			$ret = array('none'=>esc_html__('None', 'gdlr-lms'));
			if( !empty($post_list) ){
				foreach( $post_list as $post ){
					$ret[$post->ID] = $post->post_title;
				}
			}
				
			return $ret;
		}
	}
	
	// retrieve all categories from each post type
	if( !function_exists('gdlr_lms_get_term_list') ){
		function gdlr_lms_get_term_list( $taxonomy, $parent='' ){
			$term_list = get_categories( array('taxonomy'=>$taxonomy, 'hide_empty'=>0, 'parent'=>$parent) );

			$ret = array();
			if( !empty($term_list) && empty($term_list['errors']) ){
				foreach( $term_list as $term ){
					$ret[$term->slug] = $term->name;
				}
			}
				
			return $ret;
		}		
	}		
	
	
	// get all available role list
	if( !function_exists('gdlr_lms_get_role_list') ){
		function gdlr_lms_get_role_list(){
			global $wp_roles; return array_merge(array('all'=>'All'), $wp_roles->get_names());
		}
	}
	
	// get all available user
	if( !function_exists('gdlr_lms_get_user_list') ){
		function gdlr_lms_get_user_list(){
			$users = get_users();
			$user_list = array();
			foreach ( $users as $user ) {
				$user_list[$user->ID] = $user->display_name;
			}
			return $user_list;
		}	
	}	
	
	// get all sidebar list
	if( !function_exists('gdlr_lms_get_sidebar_list') ){
		function gdlr_lms_get_sidebar_list(){
			$ret = array( 'none' => esc_html__('None', 'gdlr-lms') );

			foreach ( $GLOBALS['wp_registered_sidebars'] as $sidebar ) {
				$ret[$sidebar['name']] = $sidebar['name'];
			}
			return $ret;
		}
	}
	
	// for saving and pulling the json value
	if( !function_exists('gdlr_lms_preventslashes') ){
		function gdlr_lms_preventslashes($value){
			$value = str_replace('\\\\\\\\\\\\\"', '|gq6|', $value);
			$value = str_replace('\\\\\\\\\\\"', '|gq5|', $value);
			$value = str_replace('\\\\\\\\\"', '|gq4|', $value);
			$value = str_replace('\\\\\\\"', '|gq3|', $value);
			$value = str_replace('\\\\\"', '|gq2|', $value);
			$value = str_replace('\\\"', '|gq"|', $value);
			$value = str_replace('\\\\\\t', '|g2t|', $value);
			$value = str_replace('\\\\t', '|g1t|', $value);			
			$value = str_replace('\\\\\\n', '|g2n|', $value);
			$value = str_replace('\\\\n', '|g1n|', $value);
			return $value;
		}
	}
	if( !function_exists('gdlr_lms_decode_preventslashes') ){
		function gdlr_lms_decode_preventslashes($value){
			$value = str_replace('|gq6|', '\\\\\\"', $value);
			$value = str_replace('|gq5|', '\\\\\"', $value);
			$value = str_replace('|gq4|', '\\\\"', $value);
			$value = str_replace('|gq3|', '\\\"', $value);
			$value = str_replace('|gq2|', '\\"', $value);
			$value = str_replace('|gq"|', '\"', $value);
			$value = str_replace('|g2t|', '\\\t', $value);
			$value = str_replace('|g1t|', '\t', $value);			
			$value = str_replace('|g2n|', '\\\n', $value);
			$value = str_replace('|g1n|', '\n', $value);
			return $value;
		}
	}
	
	// for getting pagination
	if( !function_exists('gdlr_lms_get_pagination') ){
		function gdlr_lms_get_pagination($max_num_page, $current_page, $format = 'paged'){
			if( $max_num_page <= 1 ) return '';
		
			$big = 999999999; // need an unlikely integer
			return 	'<div class="gdlr-pagination">' . paginate_links(array(
				'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
				'format' => '?' . $format . '=%#%',
				'current' => max(1, $current_page),
				'total' => $max_num_page,
				'prev_text'=> esc_html__('&lsaquo; Previous', 'gdlr-lms'),
				'next_text'=> esc_html__('Next &rsaquo;', 'gdlr-lms')
			)) . '</div>';
		}		
	}		

	// get all available image sizes
	if( !function_exists('gdlr_lms_get_thumbnail_list') ){
		function gdlr_lms_get_thumbnail_list(){
			global $gdlr_thumbnail_size, $_wp_additional_image_sizes;
			
			$sizes = array();
			foreach( get_intermediate_image_sizes() as $size ){
				if(in_array( $size, array( 'thumbnail', 'medium', 'large' )) ){
					$sizes[$size] = $size . ' -- ' . get_option($size . '_size_w') . 'x' . get_option($size . '_size_h');
				}else if( !empty($gdlr_thumbnail_size[$size]) ){
					$sizes[$size] = $size . ' -- ' . $gdlr_thumbnail_size[$size]['width'] . 'x' . $gdlr_thumbnail_size[$size]['height'];
				}
			}
			$sizes['full'] = esc_html__('full size (Original Images)', 'gdlr-lms');
			
			return $sizes;
		}		
	}		
	
	// calculating score
	if( !function_exists('gdlr_lms_calculating_score') ){
		function gdlr_lms_calculating_score($quiz_options, $answer, $score = array()){
			$pnum = 0;
			foreach($quiz_options as $quiz_option){
				if( $quiz_option['question-type'] == 'single' || $quiz_option['question-type'] == 'multiple' ){
					
					$score[$pnum] = array(); $qnum = 0;
					$quiz_option['question'] = json_decode($quiz_option['question'], true);
					foreach($quiz_option['question'] as $question){
						$point = intval($question['score']);
						$quiz_answer = explode(',', $question['quiz-answer']);
						$score[$pnum][$qnum] = array('from'=>$point);
						
						if($quiz_option['question-type'] == 'single'){
							if( !empty($answer[$pnum][$qnum]) && in_array($answer[$pnum][$qnum], $quiz_answer) ){
								$score[$pnum][$qnum]['score'] = $point;
							}
						}else{
							$correct = 0;
							if( !empty($answer[$pnum][$qnum]) ){
								foreach( $answer[$pnum][$qnum] as $val ){
									$correct = (in_array($val, $quiz_answer))? $correct+1: $correct-1;
								}
							}
							
							if( $correct > 0 ){ 
								$score[$pnum][$qnum]['score'] = $correct * ($point / sizeof($quiz_answer));
							}
						}
						$qnum++;
					}
				}
				$pnum++;
			}
			return $score;
		}
	}
	if( !function_exists('gdlr_lms_score_part_summary') ){
		function gdlr_lms_score_part_summary($score){
			$summary = array();
			
			foreach($score as $key => $part){
				$summary[$key] = array('score'=>0, 'from'=>0);
				foreach($part as $value){
					$summary[$key]['score'] += empty($value['score'])? 0: $value['score'];
					$summary[$key]['from'] += $value['from'];
				} 
			}
			return $summary;
		}
	}
	if( !function_exists('gdlr_lms_score_summary') ){
		function gdlr_lms_score_summary($score){
			$summary = array('score'=>0, 'from'=>0);
			
			foreach($score as $key => $part){
				foreach($part as $value){
					$summary['score'] += empty($value['score'])? 0: $value['score'];
					$summary['from'] += $value['from'];				
				}
			}
			return $summary;
		}	
	}	
	
	if( !function_exists('gdlr_lms_get_social_shares') ){
		function gdlr_lms_get_social_shares(){	
			if ( !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443 ) {
				$http = 'https://';
			}else{
				$http = 'http://';
			}

			global $theme_option;
			
			$root_path = plugins_url('', dirname(__FILE__));
			$page_title = rawurlencode(get_the_title());
			$current_url = $http . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];

			if(empty($theme_option) || $theme_option['enable-social-share'] == 'enable'){ ?>
<div class="gdlr-lms-social-share">
<?php if(empty($theme_option) || $theme_option['digg-share'] == 'enable'){ ?>
	<a href="http://digg.com/submit?url=<?php echo esc_attr($current_url); ?>&#038;title=<?php echo esc_attr($page_title); ?>" target="_blank">
		<img src="<?php echo esc_attr($root_path); ?>/social-icon/digg.png" alt="digg-share" width="32" height="32" />
	</a>
<?php } ?>

<?php if(empty($theme_option) || $theme_option['facebook-share'] == 'enable'){ ?>
	<a href="http://www.facebook.com/share.php?u=<?php echo esc_attr($current_url); ?>" target="_blank">
		<img src="<?php echo esc_attr($root_path); ?>/social-icon/facebook.png" alt="facebook-share" width="32" height="32" />
	</a>
<?php } ?>

<?php if(empty($theme_option) || $theme_option['google-plus-share'] == 'enable'){ ?>
	<a href="https://plus.google.com/share?url=<?php echo esc_attr($current_url); ?>" onclick="javascript:window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=400,width=500');return false;">
		<img src="<?php echo esc_attr($root_path); ?>/social-icon/google-plus.png" alt="google-share" width="32" height="32" />
	</a>
<?php } ?>

<?php if(empty($theme_option) || $theme_option['linkedin-share'] == 'enable'){ ?>
	<a href="http://www.linkedin.com/shareArticle?mini=true&#038;url=<?php echo esc_attr($current_url); ?>&#038;title=<?php echo esc_attr($page_title); ?>" target="_blank">
		<img src="<?php echo esc_attr($root_path); ?>/social-icon/linkedin.png" alt="linked-share" width="32" height="32" />
	</a>
<?php } ?>

<?php if(empty($theme_option) || $theme_option['my-space-share'] == 'enable'){ ?>
	<a href="http://www.myspace.com/Modules/PostTo/Pages/?u=<?php echo esc_attr($current_url); ?>" target="_blank">
		<img src="<?php echo esc_attr($root_path); ?>/social-icon/my-space.png" alt="my-space-share" width="32" height="32" />
	</a>
<?php } ?>

<?php 
	if(empty($theme_option) || $theme_option['pinterest-share'] == 'enable'){ 
		$thumbnail_id = get_post_thumbnail_id( get_the_ID() );
		$thumbnail = wp_get_attachment_image_src( $thumbnail_id , 'large' ); 
?>
	<a href="http://pinterest.com/pin/create/button/?url=<?php echo esc_attr($current_url); ?>&media=<?php echo esc_attr($thumbnail[0]); ?>" class="pin-it-button" count-layout="horizontal" onclick="javascript:window.open(this.href,'', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,height=300,width=600');return false;">
		<img src="<?php echo esc_attr($root_path); ?>/social-icon/pinterest.png" alt="pinterest-share" width="32" height="32" />
	</a>	
<?php } ?>

<?php if(empty($theme_option) || $theme_option['reddit-share'] == 'enable'){ ?>
	<a href="http://reddit.com/submit?url=<?php echo esc_attr($current_url); ?>&#038;title=<?php echo esc_attr($page_title); ?>" target="_blank">
		<img src="<?php echo esc_attr($root_path); ?>/social-icon/reddit.png" alt="reddit-share" width="32" height="32" />
	</a>
<?php } ?>

<?php if(empty($theme_option) || $theme_option['stumble-upon-share'] == 'enable'){ ?>
	<a href="http://www.stumbleupon.com/submit?url=<?php echo esc_attr($current_url); ?>&#038;title=<?php echo esc_attr($page_title); ?>" target="_blank">
		<img src="<?php echo esc_attr($root_path); ?>/social-icon/stumble-upon.png" alt="stumble-upon-share" width="32" height="32" />
	</a>
<?php } ?>

<?php if(empty($theme_option) || $theme_option['twitter-share'] == 'enable'){ ?>
	<a href="http://twitter.com/home?status=<?php echo str_replace('%26%23038%3B', '%26', $page_title) . ' - ' . $current_url; ?>" target="_blank">
		<img src="<?php echo esc_attr($root_path); ?>/social-icon/twitter.png" alt="twitter-share" width="32" height="32" />
	</a>
<?php } ?>
<div class="clear"></div>
</div>
		<?php }
		}	
	}	
	
	if( !function_exists('gdlr_stripslashes') ){
		function gdlr_stripslashes($value){
			$value = is_array($value) ?
						array_map('stripslashes_deep', $value) : 
						stripslashes($value);
						
			return $value;
		}
	}	

	add_action( 'wp_ajax_gdlr_lms_authorize_payment', 'gdlr_lms_authorize_payment' );
	add_action( 'wp_ajax_nopriv_gdlr_lms_authorize_payment', 'gdlr_lms_authorize_payment' );
	if( !function_exists('gdlr_lms_authorize_payment') ){
		function gdlr_lms_authorize_payment(){

			global $gdlr_lms_option, $wpdb;

			$ret = array();

			if( !empty($_POST['tid']) && !empty($_POST['form']) ){

				// prepare data
				$form = stripslashes_deep($_POST['form']);

				$invoice = $_POST['tid'];
				$api_id = trim($gdlr_lms_option['authorize-api-id']);
				$transaction_key = trim($gdlr_lms_option['authorize-transaction-key']);
				
				$live_mode = empty($gdlr_lms_option['authorize-live-mode'])? 'enable': $gdlr_lms_option['authorize-live-mode']; 
				if( empty($live_mode) || $live_mode == 'enable' ){
					$environment = \net\authorize\api\constants\ANetEnvironment::PRODUCTION;
				}else{
					$environment = \net\authorize\api\constants\ANetEnvironment::SANDBOX;
				}

				$temp_sql  = "SELECT * FROM " . $wpdb->prefix . "gdlrpayment ";
				$temp_sql .= $wpdb->prepare("WHERE id = %d ", $invoice);	
				$result = $wpdb->get_row($temp_sql);

				if( empty($result->price) ){
					$ret['status'] = 'failed';
					$ret['message'] = esc_html__('Cannot retrieve pricing data, please try again.', 'gdlr-lms');
				
				// Start the payment process
				}else{

					$price = intval(floatval($result->price) * 100) / 100;

					try{
						// Common setup for API credentials
						$merchantAuthentication = new net\authorize\api\contract\v1\MerchantAuthenticationType();
						$merchantAuthentication->setName(trim($api_id));
						$merchantAuthentication->setTransactionKey(trim($transaction_key));

						// Create the payment data for a credit card
						$creditCard = new net\authorize\api\contract\v1\CreditCardType();
						$creditCard->setCardNumber($form['number']);
						$creditCard->setExpirationDate($form['exp-year'] . '-' . $form['exp-month']);
						$creditCard->setCardCode($form['cvc']);
						$paymentOne = new net\authorize\api\contract\v1\PaymentType();
						$paymentOne->setCreditCard($creditCard);

						// Create transaction
						$transactionRequestType = new net\authorize\api\contract\v1\TransactionRequestType();
						$transactionRequestType->setTransactionType("authCaptureTransaction"); 
						$transactionRequestType->setAmount($price);
						$transactionRequestType->setPayment($paymentOne);

						// Send request
						$request = new net\authorize\api\contract\v1\CreateTransactionRequest();
						$request->setMerchantAuthentication($merchantAuthentication);
						$request->setTransactionRequest($transactionRequestType);
						$controller = new net\authorize\api\controller\CreateTransactionController($request);
						$response = $controller->executeWithApiResponse($environment);
						
						if( $response != null ){
						    $tresponse = $response->getTransactionResponse();

						    if( ($tresponse != null) && ($tresponse->getResponseCode() == '1') ){
						      	
						      	$payment_data = array(
									'payment_method' => 'authorize',
									'amount' => $price,
									'transaction_id' => $tresponse->getTransId()
								);
								
								$wpdb->update( $wpdb->prefix . 'gdlrpayment', 
									array('payment_status'=>'paid', 'attachment'=>serialize($payment_data), 'payment_date'=>date('Y-m-d H:i:s')), 
									array('id'=>$invoice), 
									array('%s', '%s', '%s'), 
									array('%d')
								);

								$payment_info = unserialize($result->payment_info);
								gdlr_lms_mail($payment_info['email'], 
									esc_html__('Authorize Payment Received', 'gdlr-lms'), 
									esc_html__('Your verification code is', 'gdlr-lms') . ' ' . $payment_info['code']);	

								$ret['status'] = 'success';
								$ret['message'] = esc_html__('Payment complete.', 'gdlr-lms');
								$ret['redirect'] = get_permalink($result->course_id);
						    }else{
						        $ret['status'] = 'failed';
						    	$ret['message'] = esc_html__('Cannot charge credit card, please check your card credentials again.', 'gdlr-lms');

						    	$error = $tresponse->getErrors();
						    	if( !empty($error[0]) ){
							    	$ret['message'] = $error[0]->getErrorText();
						    	}

						   	}
						}else{
						    $ret['status'] = 'failed';
						    $ret['message'] = esc_html__('No response returned, please try again.', 'gdlr-lms');
						}
						$ret['data'] = $_POST;

					}catch( Exception $e ){
						$ret['status'] = 'failed';
						$ret['message'] = $e->getMessage();
					}
				}
			}

			die(json_encode($ret));
		}
	}