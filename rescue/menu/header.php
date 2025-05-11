	<!-- top navigation -->
		<?php if( empty($theme_option['enable-top-bar']) || $theme_option['enable-top-bar'] == 'enable' ){ ?>
		<div class="top-navigation-wrapper">
			<div class="top-navigation-container container">
			    
	<nav>
        <div class="logo">
           <img src="TNR560.png"/>
        </div>
        <ul class="nav-links">
            <li>
            <a href="#"><span id="content1">Кастрирай </span<span id="content2">& върни</span></a>
            <i class='icon-chevron-down fa fa-chevron-down'></i>             </li>

            <div class="submenu"><ul class="sub-links">
              <li><a href="#" class="js-toggle-submenu" >Как да кастрирам бездомна котка?</a></li>
              <li><a href="#" class="js-toggle-submenu">Маркировка</a></li>
            </ul>
            </div>
            
            <li>
            <a href="#">Осинови</a>
            </li>
            <li>
            <a href="#">Спаси коте</a>
            </li>
            <li>
            <a href="#" class="dari-desktop">Дари</a>
            </li>
            </ul> 
            <ul>
                
/* Custom Shoping Cart in the top */
function YOURTHEME_wc_print_mini_cart() {
    ?>
    <div id="YOURTHEME-minicart-top">
        <?php if ( sizeof( WC()->cart->get_cart() ) > 0 ) : ?>
            <ul class="YOURTHEME-minicart-top-products">
                <?php foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
                $_product = $cart_item['data'];
                // Only display if allowed
                if ( ! apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) || ! $_product->exists() || $cart_item['quantity'] == 0 ) continue;
                // Get price
                $product_price = get_option( 'woocommerce_tax_display_cart' ) == 'excl' ? $_product->get_price_excluding_tax() : $_product->get_price_including_tax();
                $product_price = apply_filters( 'woocommerce_cart_item_price_html', woocommerce_price( $product_price ), $cart_item, $cart_item_key );
                ?>
                <li class="YOURTHEME-mini-cart-product clearfix">
                    <span class="YOURTHEME-mini-cart-thumbnail">
                        <?php echo $_product->get_image(); ?>
                    </span>
                    <span class="YOURTHEME-mini-cart-info">
                        <a class="YOURTHEME-mini-cart-title" href="<?php echo get_permalink( $cart_item['product_id'] ); ?>">
                            <h4><?php echo apply_filters('woocommerce_widget_cart_product_title', $_product->get_title(), $_product ); ?></h4>
                        </a>
                        <?php echo apply_filters( 'woocommerce_widget_cart_item_price', '<span class="woffice-mini-cart-price">' . __('Unit Price', 'YOURTHEME') . ':' . $product_price . '</span>', $cart_item, $cart_item_key ); ?>
                        <?php echo apply_filters( 'woocommerce_widget_cart_item_quantity', '<span class="YOURTHEME-mini-cart-quantity">' . __('Quantity', 'woffice') . ':' . $cart_item['quantity'] . '</span>', $cart_item, $cart_item_key ); ?>
                    </span>
                </li>
                <?php endforeach; ?>
            </ul><!-- end .YOURTHEME-mini-cart-products -->
        <?php else : ?>
            <p class="YOURTHEME-mini-cart-product-empty"><?php _e( 'No products in the cart.', 'YOURTHEME' ); ?></p>
        <?php endif; ?>
        <?php if (sizeof( WC()->cart->get_cart()) > 0) : ?>
            <h4 class="text-center YOURTHEME-mini-cart-subtotal"><?php _e( 'Cart Subtotal', 'YOURTHEME' ); ?>: <?php echo WC()->cart->get_cart_subtotal(); ?></h4>
            <div class="text-center">
                <a href="<?php echo WC()->cart->get_cart_url(); ?>" class="cart btn btn-default">
                    <i class="fa fa-shopping-cart"></i> <?php _e( 'Cart', 'YOURTHEME' ); ?>
                </a>
                <a href="<?php echo WC()->cart->get_checkout_url(); ?>" class="alt checkout btn btn-default">
                    <i class="fa fa-credit-card"></i> <?php _e( 'Checkout', 'YOURTHEME' ); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

<?php pll_the_languages( array( 'show_flags' => 1,'show_names' => 0 ) ); ?>
</ul>

<button type="button" name="dari" class="dari-desktop"><a href="/dari">Дари</a></button>

        <div class="burger">
          <div class="line1"></div>
          <div class="line2"></div>
          <div class="line3"></div>
        </div>
    </nav>

<script src="app.js"></script>

				<div class="clear"></div>
			</div>
		</div>
		
			<?php 
		//Polylang Shortcode for language switcher flags
/*https://polylang.wordpress.com/documentation/documentation-for-developers/functions-reference/*/
function polylang_flags_shortcode() {
    ob_start();
    pll_the_languages(array('show_flags'=>1,'show_names'=>0));
    $flags = ob_get_clean();
    return '<ul class="polylang-flags">' . $flags . '</ul>';
}
add_shortcode('POLYLANGflags', 'polylang_flags_shortcode');
		
		} ?>