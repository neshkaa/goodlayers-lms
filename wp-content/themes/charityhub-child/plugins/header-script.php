<?php
/**
Plugin Name: Header Scripts
*/
add_action( 'wp_head', 'my_header_scripts' );
function my_header_scripts(){
  ?>
  <script>alert( 'Hi Roy' ); </script>
  <?php
}