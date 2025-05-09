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
            <a href="#">Кастрирай <br>& върни</a>
            </li>
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