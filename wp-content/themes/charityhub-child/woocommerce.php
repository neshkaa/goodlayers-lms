<?php get_header(); ?>
	<div class="gdlr-content">

		<?php 
				global $gdlr_sidebar, $theme_option;
				$woo_page = (is_product())? 'single': 'all';
				
				$gdlr_sidebar = array(
					'type'=>$theme_option[$woo_page . '-products-sidebar'],
					'left-sidebar'=>$theme_option[$woo_page . '-products-sidebar-left'], 
					'right-sidebar'=>$theme_option[$woo_page . '-products-sidebar-right']
				); 
				$gdlr_sidebar = gdlr_get_sidebar_class($gdlr_sidebar);
		?>
		<div class="with-sidebar-wrapper">
		    
<!-- Parallax -->
		    <div class="gdlr-parallax-wrapper gdlr-background-image no-skin" data-bgspeed="0" style="background-image: url('<?php echo is_product_category() ? wp_get_attachment_url(get_term_meta(get_queried_object()->term_id, 'thumbnail_id', true)) : 'https://rescue.thelastcage.org/wp-content/uploads/sites/5/2021/03/bazar4.jpg'; ?>')">
                <div class="container">
                    <div class="gdlr-title-item gdlr-item">
                        <div class="gdlr-item-title-wrapper pos-center ">
                            <h3 class="gdlr-item-title gdlr-skin-title gdlr-skin-border"></h3>
                            <h2 style="color:#fff"><strong>Благотворителен базар</strong></h2>
                            <div class="clear"></div>
                            <div class="gdlr-item-caption gdlr-skin-info">
        <?php
                    $subtitle = is_product_category() ? get_queried_object()->name : 'Подаръци';
                    echo '<h3 style="color:#fff">🎁 ' . $subtitle . ' с кауза</h3>';
                    ?>                            </div>
                            <div class="clear"></div>
                        </div>
                    </div>
                    <div class="clear"></div>
                </div>
            </div>
            
			<div class="with-sidebar-container container">
			    <div class="gdlr-item gdlr-content-item"><br>
<?php
if (is_product_category()) {
    $category_description = term_description(get_queried_object()->term_id, 'product_cat');

    if ($category_description) {
        $category_description = wp_strip_all_tags($category_description);
        echo '<h4 class="woo-description" style="text-align:center">' . esc_html($category_description) . '</h4>';
    } else {
        // Display the default description if no category description available
        echo '<h4 class="woo-description" style="text-align:center">Всички артикули са изработени от доброволци или дарени за котешката кауза.<br>Всяка поръчка е в помощ на кварталните котаци.</h4>';
    }
} elseif (is_product()) {
    // Do nothing on product pages (remove this block if you want to add something else)
} else {
    // Display the default description for non-category pages
    echo '<h4 class="woo-description" style="text-align:center">Всички артикули са изработени от доброволци или дарени за котешката кауза.<br>Всяка поръчка е в помощ на кварталните котаци.</h4>';
}
?>

                </div>
<!-- Search Products -->
                <form role="search" method="get" action="https://rescue.thelastcage.org/" class="wp-block-search__button-outside wp-block-search__text-button wp-block-search">
                    <div class="wp-block-search__inside-wrapper ">
                        <input class="wp-block-search__input" id="wp-block-search__input-1" placeholder="Search products…" value="" type="search" name="s" required="">
                        <input type="hidden" name="post_type" value="product">
                        <button aria-label="Search" class="wp-block-search__button wp-element-button gdlr-icon icon-search" type="submit"></button>
                    </div>
                </form>
				<div class="with-sidebar-left <?php echo $gdlr_sidebar['outer']; ?> columns">
					<div class="with-sidebar-content <?php echo $gdlr_sidebar['center']; ?> columns gdlr-item-start-content">
						<div class="gdlr-item woocommerce-content-item">
						
							<div class="woocommerce-content">
							<?php if (is_singular('product') ) {
                                    woocommerce_content();
                                } else {
//For ANY product archive.
//Product taxonomy, product search or /shop landing
                                    woocommerce_get_template('archive-product.php');
                                }; ?>
							</div>				
						</div>				
					</div>
					<?php get_sidebar('left'); ?>
					<div class="clear"></div>
				</div>
				<?php get_sidebar('right'); ?>
				<div class="clear"></div>
			</div>				
		</div>				
	</div><!-- gdlr-content -->
<?php get_footer(); ?>