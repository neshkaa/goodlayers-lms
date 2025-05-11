	<?php global $theme_option; ?>
	<div class="clear" ></div>
	</div><!-- content wrapper -->

	<?php 
		// page style
		global $gdlr_post_option;
		if( empty($gdlr_post_option) || empty($gdlr_post_option['page-style']) ||
			  $gdlr_post_option['page-style'] == 'normal' || 
			  $gdlr_post_option['page-style'] == 'no-header'){ 
	?>	
	<footer class="footer-wrapper" >
		<?php if( $theme_option['show-footer'] != 'disable' ){ ?>
		<div class="footer-container container">
			<?php 	
				$i = 1;
				$theme_option['footer-layout'] = empty($theme_option['footer-layout'])? '1': $theme_option['footer-layout'];
				$gdlr_footer_layout = array(
					'1'=>array('twelve columns'),
					'2'=>array('three columns', 'three columns', 'three columns', 'three columns'),
					'3'=>array('three columns', 'three columns', 'six columns',),
					'4'=>array('four columns', 'four columns', 'four columns'),
					'5'=>array('four columns', 'four columns', 'eight columns'),
					'6'=>array('eight columns', 'four columns', 'four columns'),
				);
			?>
			<?php foreach( $gdlr_footer_layout[$theme_option['footer-layout']] as $footer_class ){ ?>
				<div class="footer-column <?php echo $footer_class; ?>" id="footer-widget-<?php echo $i; ?>" >
					<?php dynamic_sidebar('Footer ' . $i); ?>
				</div>
			<?php $i++; ?>
			<?php } ?>
			<div class="clear"></div>
		</div>
		<?php } ?>
		
		<?php if( $theme_option['show-copyright'] != 'disable' ){ ?>
		<div class="copyright-wrapper">
			<div class="copyright-container container">
				<div class="copyright-left">
					<?php echo $theme_option['copyright-left-text']; ?>
				</div>
				<div class="copyright-right">
					<?php echo $theme_option['copyright-right-text']; ?>
				</div>
				<div class="clear"></div>
			</div>
		</div>
		<?php } ?>
	</footer>
	<?php } // page style ?>
</div> <!-- body-wrapper -->
<?php wp_footer(); ?>

<?php if (pll_current_language() == 'en'){ ?>

<script>cookieLaw={dId:"cookie-law-div",bId:"cookie-law-button",iId:"cookie-law-item",show:function(e){if(localStorage.getItem(cookieLaw.iId))return!1;var o=document.createElement("div"),i=document.createElement("p"),t=document.createElement("button");i.innerHTML=e.msg,t.id=cookieLaw.bId,t.innerHTML=e.ok,o.id=cookieLaw.dId,o.appendChild(t),o.appendChild(i),document.body.insertBefore(o,document.body.lastChild),t.addEventListener("click",cookieLaw.hide,!1)},hide:function(){document.getElementById(cookieLaw.dId).outerHTML="",localStorage.setItem(cookieLaw.iId,"1")}},cookieLaw.show({msg:"We use cookies to give you the best possible experience. By continuing to visit our website, you agree to the use of cookies as described in our <a href='https://thelastcage.org/en/privacy-policy-2/'>Privacy Policy</a>",ok:"ОК"});</script>

<?php } else { ?>
	<script>cookieLaw={dId:"cookie-law-div",bId:"cookie-law-button",iId:"cookie-law-item",show:function(e){if(localStorage.getItem(cookieLaw.iId))return!1;var o=document.createElement("div"),i=document.createElement("p"),t=document.createElement("button");i.innerHTML=e.msg,t.id=cookieLaw.bId,t.innerHTML=e.ok,o.id=cookieLaw.dId,o.appendChild(t),o.appendChild(i),document.body.insertBefore(o,document.body.lastChild),t.addEventListener("click",cookieLaw.hide,!1)},hide:function(){document.getElementById(cookieLaw.dId).outerHTML="",localStorage.setItem(cookieLaw.iId,"1")}},cookieLaw.show({msg:"Този сайт използва бизквитки, които ни позволяват да осигурим по-добра фунционалност. Използвайки сайта, Вие се съгласвате с тяхната употреба съгласно нашата <a href='https://thelastcage.org/privacy-policy/'>Политика за поверителност</a>",ok:"OK"});</script>
			<?php 				} ?>

<!-- Copy to Clipboard & Remove spaces by ChatGPt-->
<script>
function copyToClipboard(elementId) {
  var textSpan = document.getElementById(elementId);

  // Get the text with spaces
  var copiedText = textSpan.textContent;

  // Remove spaces from the copied text
  var textWithoutSpaces = copiedText.replace(/\s/g, '');

  // Create a temporary input element
  var tempInput = document.createElement("input");
  tempInput.value = textWithoutSpaces;
  document.body.appendChild(tempInput);

  // Select and copy the modified text
  tempInput.select();
  document.execCommand('copy');

  // Remove the temporary input
  document.body.removeChild(tempInput);

  // Select the text in the original element
  var selection = window.getSelection();
  var range = document.createRange();
  range.selectNodeContents(textSpan);
  selection.removeAllRanges();
  selection.addRange(range);
}
</script>




    
</body>
</html>