<?php
/**
 * The template for displaying 404 pages (Not Found).
 */

get_header(); ?>

	<div class="page-not-found-container container">
		<div class="gdlr-item page-not-found-item">
			<div class="page-not-found-block" >
			
				<div>
<img src="https://rescue.thelastcage.org/wp-content/uploads/sites/5/2021/06/404kot.gif" alt="404" style="align:center";/>				</div>
				<div class="page-not-found-caption">
					<?php _e('Sorry, we couldn\'t find the page you\'re looking for.', 'gdlr_translate'); ?>
				</div>
				<div class="page-not-found-search">
					<?php get_search_form(); ?>
				</div>
			</div>
		</div>
	</div>

<?php get_footer(); ?>