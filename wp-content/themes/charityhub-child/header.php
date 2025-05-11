<!DOCTYPE html>
<!--[if IE 7]><html class="ie ie7 ltie8 ltie9" <?php language_attributes(); ?>><![endif]-->
<!--[if IE 8]><html class="ie ie8 ltie9" <?php language_attributes(); ?>><![endif]-->
<!--[if !(IE 7) | !(IE 8)  ]><!-->
<html <?php language_attributes(); ?>>
<!--<![endif]-->

<head>
    
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-WBDPKZ8');</script>
<!-- End Google Tag Manager -->

    
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width" />
	<title><?php bloginfo('name'); ?>  <?php wp_title(); ?></title>
	<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>" />

	<?php
		global $theme_option, $gdlr_post_option;
		if( !empty($gdlr_post_option) ){ $gdlr_post_option = json_decode($gdlr_post_option, true); }

		wp_head();
	?>


<meta name="google-site-verification" content="nqiDdXpQsY5Zx0sESA9BHldz7VfPWPBSIDntPYqVB6c" />

</head>

<body <?php body_class(); ?>>
<div id="fb-root"></div>
<script async defer crossorigin="anonymous" src="https://connect.facebook.net/en_GB/sdk.js#xfbml=1&version=v18.0&appId=370045085255421" nonce="JWFutwWZ"></script>
<?php
	$body_wrapper = '';
	if($theme_option['enable-boxed-style'] == 'boxed-style'){
		$body_wrapper  = 'gdlr-boxed-style';
		if( !empty($theme_option['boxed-background-image']) && is_numeric($theme_option['boxed-background-image']) ){
			$alt_text = get_post_meta($theme_option['boxed-background-image'] , '_wp_attachment_image_alt', true);
			$image_src = wp_get_attachment_image_src($theme_option['boxed-background-image'], 'full');
			echo '<img class="gdlr-full-boxed-background" src="' . $image_src[0] . '" alt="' . $alt_text . '" />';
		}else if( !empty($theme_option['boxed-background-image']) ){
			echo '<img class="gdlr-full-boxed-background" src="' . $theme_option['boxed-background-image'] . '" />';
		}
	}
	$body_wrapper .= ($theme_option['enable-float-menu'] != 'disable')? ' float-menu': '';
?>

<div class="body-wrapper <?php echo $body_wrapper; ?>" data-home="<?php echo home_url();
?>">
	<?php
		// page style
		if( empty($gdlr_post_option) || empty($gdlr_post_option['page-style']) ||
			  $gdlr_post_option['page-style'] == 'normal' ||
			  $gdlr_post_option['page-style'] == 'no-footer'){
	?>

	<header class="gdlr-header-wrapper gdlr-header-style-2 gdlr-centered">

		<!-- top navigation -->
		<?php if( empty($theme_option['enable-top-bar']) || $theme_option['enable-top-bar'] == 'enable' ){ ?>
		<div class="top-navigation-wrapper">
			<div class="top-navigation-container container">
				<?php
					if( !empty($theme_option['top-bar-left-text']) || function_exists('icl_get_languages') ){
						echo '<div class="top-navigation-left">';
//						echo gdlr_get_wpml_nav();
						echo gdlr_text_filter($theme_option['top-bar-left-text']);
						echo '</div>';
					}

					if( !empty($theme_option['top-bar-right-text']) ){
						echo '<div class="top-navigation-right">';
						if (pll_current_language() == 'bg'){
							echo '<a href="/our-story/">За нас</a> | <a href="/mission/">Нашата мисия</a>| <a href="/contact-page/">Контакт</a>';
						} else {
							echo '<a href="/en/about-us/">About us</a> | <a href="/en/about-us/">Our mission</a>| <a href="/en/contact-page-en/">Contact</a>';
						}
						// echo '<a href="/our-story/">За нас</a> | <a href="/mission/">Нашата мисия</a>| <a href="/contact-page/">Контакт</a>'
						// echo gdlr_text_filter($theme_option['top-bar-right-text']);
						echo '</div>';
					}
				?>
				<div class="clear"></div>
			</div>
		</div>
		<?php } ?>

		<!-- logo -->
		
		    	<div class="gdlr-header-substitute">
			    <div class="gdlr-header-container container">
				<div class="gdlr-header-inner">

					<!-- logo -->
                        <div class="gdlr-logo gdlr-align-left">
                        
                        <?php 
						echo (is_front_page())? '<h1>':''; ?>
						<a href="<?php 	echo home_url(); ?>" >
							<?php
								if(empty($theme_option['logo-id'])){
									echo gdlr_get_image(GDLR_PATH . '/images/logo.png');
								}else{
									if (pll_current_language() == 'bg' && $_SERVER['SERVER_NAME'] == 'thelastcage.org' ){
										echo gdlr_get_image('https://thelastcage.org/wp-content/uploads/2019/10/LC2.png');
										
										} elseif (pll_current_language() == 'en' && $_SERVER['SERVER_NAME'] == 'rescue.thelastcage.org' ){
										echo '';
										
										} elseif (pll_current_language() == 'bg' && $_SERVER['SERVER_NAME'] == 'rescue.thelastcage.org' ){
										echo '';
										
										} else {
										echo gdlr_get_image('https://thelastcage.org/wp-content/uploads/2019/11/LC3.png');

									}
									// echo gdlr_get_image($theme_option['logo-id']);

									// echo gdlr_get_image($theme_option['logo-id']);
								}
							?>

							
							
							
							
						</a>
						<?php echo (is_front_page())? '</h1>':''; ?>
						<?php
							// mobile navigation
							if( class_exists('gdlr_dlmenu_walker') && ( empty($theme_option['enable-responsive-mode']) || $theme_option['enable-responsive-mode'] == 'enable' ) ){
								echo '<div class="gdlr-responsive-navigation dl-menuwrapper" id="gdlr-responsive-navigation" >';
								echo '<button class="dl-trigger">Open Menu</button>';
								wp_nav_menu( array(
									'theme_location'=>'main_menu',
									'container'=> '',
									'menu_class'=> 'dl-menu gdlr-main-mobile-menu',
									'walker'=> new gdlr_dlmenu_walker()
								) );
								echo '</div>';
							}
						?>
					</div>

					<div class="gdlr-logo-right-text gdlr-align-left">
							<?php echo gdlr_text_filter($theme_option['logo-right-text']); ?>
					</div>
					<?php
						if( $theme_option['enable-top-search'] == 'enable' ){
							echo '<div class="gdlr-header-search">';
							get_search_form();
							echo '</div>';
						}
					?>
					<div class="clear"></div>
				</div>
			</div>
		</div>

		<!-- navigation -->
		<?php
			if (($_SERVER['SERVER_NAME'] == 'rescue.thelastcage.org') &&  (pll_current_language() == 'bg')) {
			    echo '<nav class="tnr-nav"> 
			                <div class="logo-tnr">
			                <a href="https://rescue.thelastcage.org" >
                                 <img src="https://rescue.thelastcage.org/wp-content/uploads/sites/5/2021/01/TNR200.png"/></a>
                            </div>';
                wp_nav_menu( array( 
                'theme_location' => 'tnr-custom-menu', 
                'container_class' => 'custom-menu-class' ) ); 
                echo '<button type="button" name="dari" class="tnr-dari"><a href="/dari">Дари   </a></button>
        <div class="burger-tnr">
          <div class="line1"></div>
          <div class="line2"></div>
          <div class="line3"></div>
        </div></nav>';
                        
			
			} elseif (($_SERVER['SERVER_NAME'] == 'rescue.thelastcage.org') &&  (pll_current_language() == 'en')) {
			      echo '<nav class="tnr-nav"> 
			                <div class="logo-tnr">
			                <a href="https://rescue.thelastcage.org/en" >
                                 <img src="https://rescue.thelastcage.org/wp-content/uploads/sites/5/2021/01/TNR200.png"/></a>
                            </div>';
                wp_nav_menu( array( 
                'theme_location' => 'tnr-custom-menu', 
                'container_class' => 'custom-menu-class' ) ); 
                echo '<button type="button" name="dari" class="tnr-dari"><a href="/dari">Дари   </a></button>
        <div class="burger-tnr">
          <div class="line1"></div>
          <div class="line2"></div>
          <div class="line3"></div>
        </div></nav>';
			    
			    
			} else {
			get_template_part( 'header', 'nav' );
			get_template_part( 'header', 'title' );
			
			
			}

		?>
		
<!--  WOOCOMMERCE SHOP NAVIGATION -->		
		<?php if (class_exists('WooCommerce') && is_woocommerce()) : ?>
  <!-- Include your navigation bar here -->
  <nav class="shop-navbar">
    <div class="shop-nav-left">
      <!-- Back button with left arrow icon -->
      <a href="javascript:history.go(-1)"><i class="gdlr-icon icon-arrow-left"></i></a>
    </div>
     <div class="shop-nav-title">
      <!-- Navbar title -->
      <a href="/shop"><i class="gdlr-icon icon-store"></i>Базар</a>
    </div>
    <div class="shop-nav-right">
      <!-- Cart icon -->
            <?php echo do_shortcode('[shopping-cart-count]'); ?>
    </div>
  </nav>
<?php endif; ?>


		
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-WBDPKZ8"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
					
	</header>
	<?php } // page style ?>
	<div class="content-wrapper">
	    
	<?php    do_shortcode('[lms_login]')?>
	
<!-- Donate Buttons Accordion function -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script>
(function($) {
$(document).ready(function(){
$("a.payment-option").on("click", function(e) {     
    var target = $(this).data("div");   
    $(target).slideToggle("fast");
    $(".details").not(target).hide();
    e.preventDefault();   
});
    });
    
}(jQuery));
</script>
<script>
(function($) {
$(document).ready(function(){
$("a.contents-part1-mobile").on("click", function(e) {     
    var target = $(this).data("div");   
    $(target).slideToggle("fast");
    $(".contents-open").not(target).hide();
    e.preventDefault();   
});
    });
    
}(jQuery));
</script>

<!-- TNR NAV Stick to top -->
<script>
document.addEventListener("DOMContentLoaded", function () {
    var nav = document.querySelector(".tnr-nav");
    var mainContent = document.querySelector(".content-wrapper");

    if (nav && mainContent) {
        var navHeight = nav.offsetHeight;
        mainContent.style.paddingTop = navHeight + "px";
    }
});
</script>
