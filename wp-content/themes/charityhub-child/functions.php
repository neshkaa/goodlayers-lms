<?php
/*security to prevent direct access of php files*/
if ( ! defined ('ABSPATH' )) {
    exit;
}

add_action( 'wp_enqueue_scripts', 'my_theme_enqueue_styles' );
function my_theme_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_template_directory_uri() . '/style.css' );
}


 /**
 * Filter Force Login to allow exceptions for specific URLs.
 *
 * @return array An array of URLs. Must be absolute.
 **/
function my_forcelogin_whitelist( $whitelist ) {
  $whitelist[] = site_url( '/xmlrpc.php' );
  return $whitelist;
}
add_filter('v_forcelogin_whitelist', 'my_forcelogin_whitelist', 10, 1);

// echo('langis'.pll_current_language());
// function custom_multilang_logo( $value ) {
// 	if ( function_exists( 'pll_current_language' ) ) {
// 		$logos = array(
// 			'en' => wp_get_attachment_image('1555', 'full'),
// 			'fr' => wp_get_attachment_image('1556', 'full'),
// 		);
// 		$default_logo = $logos['en'];
// 		$current_lang = pll_current_language();
// 		if ( isset( $logos[ $current_lang ] ) )
// 			$value = $logos[ $current_lang ];
// 		else
// 			$value = $default_logo;
// 	}
// 	$html = sprintf( '<a href="%1$s" class="custom-logo-link" rel="home" itemprop="url">%2$s</a>',
//             esc_url( home_url( '/' ) ),
//             $value
//         );
// 	return $html;
//
// }
// add_filter( 'get_custom_logo', 'custom_polylang_multilang_logo' );

// create Post Type: Cruelty-Free
function create_posttype() {
  register_post_type( 'wpl_crueltyfree',
    array(
      'labels' => array(
        'name' => __( 'Cruelty-Free' ),
        'singular_name' => __( 'Cruelty-Free' )
      ),
      'public' => true,
      'has_archive' => true,
      'supports' => array( 'title', 'editor', 'thumbnail', 'custom-fields', 'excerpt','trackback','comment','revisions','page-attributes','post-format'),
      'rewrite' => array('slug' => 'crueltyfree'),
      'menu_icon' => 'dashicons-buddicons-activity', 'f452'

    )
  );
}
add_action( 'init', 'create_posttype' );





// Participants Database: activate multilingual site
 define( 'PDB_MULTILINGUAL', true );

//Polylang Shortcode for language switcher flags
/*https://polylang.wordpress.com/documentation/documentation-for-developers/functions-reference/*/
function polylang_flags_shortcode() {
    ob_start();
    pll_the_languages(array('show_flags'=>1,'show_names'=>0));
    $flags = ob_get_clean();
    return '<ul class="polylang-flags">' . $flags . '</ul>';
}
add_shortcode('POLYLANGflags', 'polylang_flags_shortcode');

// [polylang lang="bg"]Български[/polylang][polylang lang="en"]English[/polylang]
/*https://wordpress.org/support/topic/compatibility-with-polylang-12/
*/
function polylang_shortcode($atts, $content = null)
{
    if (empty($content))
return '';
    extract( shortcode_atts(array('lang' => ''), $atts ) );
    if (empty($lang))
    return "###You must specify 'lang' using shortcode: polylang";

    return ($lang == pll_current_language()) ? $content : '';
}
add_shortcode('polylang', 'polylang_shortcode');

//for Polylang lang shortcode to work in Participants Database Fields
add_filter( 'pdb-translate_string', 'shortcode_unautop');
add_filter( 'pdb-translate_string', 'do_shortcode', 11);

//Participants Database single record translation with Polylang
add_filter( 'pdb-single_record_url', function($singleurl, $recordid) {
    if (pll_current_language() == 'bg') {
        return $singleurl = 'марка' . '/?pdb=' . $recordid;
   }elseif (pll_current_language() == 'en') {
        return $singleurl = 'brand'. '/?pdb=' . $recordid; }}
            , 10, 2 );


add_filter( 'option_generate_blog_settings', function( $options ) {
    $options['read_more'] = __( 'Read more', 'gp-premium' );

    return $options;
} );

/*from: https://typerocket.com/ultimate-guide-to-custom-post-types-in-wordpress/
// Add theme support for featured image / thumbnails
add_theme_support('post-thumbnail');

// Declare what the post type supports
$supports = ['title', 'editor', 'thumbnail', 'custom-fields', 'excerpt','trackback','comment','revisions','page-attributes','post format']*/

/* Create custom posts in WordPress taking data from a MySQL database.
@link https://imelgrat.me/wordpress/bulk-upload-custom-posts-wordpress/
Load WordPress functions and plug-ins. Put correct path for this file.
This example assumes you're using it from a sub-folder of WordPress.
If you run it from somewhere else, adjust path to wp-load.php accordingly.
*/
/*require_once('../wp-load.php');

$database['hostname'] = 'localhost';
$database['username'] = 'ethicals_admin4e';
$database['password'] = 'HipH0padetoHl0pa';
$database['database'] = 'ethicals_list';

$mysql_link = mysqli_connect($database['hostname'], $database['username'], $database['password']);
mysqli_select_db($mysql_link, $database['database']);
mysqli_query($mysql_link, "SET NAMES UTF8");
mysqli_query($mysql_link, "SET NAMES 'UTF8'");
mysqli_query($mysql_link, "SET CHARACTER SET UTF8");

/*
—
— Table structure for table `products`
—
CREATE TABLE IF NOT EXISTS `products` (
    `product_id` bigint(20) NOT NULL AUTO_INCREMENT,
    `name` varchar(255) NOT NULL,
    `description` text NOT NULL,
    `price` float NOT NULL,
    `ingredients` text NOT NULL,
    PRIMARY KEY (`product_id`)	 )
    ENGINE=InnoDB DEFAULT CHARSET=utf8;
    */
/*$query = "SELECT * FROM `LB` WHERE `Cruelty-Free`= 0 ORDER BY `BRAND`.`id` ASC;"; // products
$result = mysqli_query($mysql_link, $query);

while ($row = mysqli_fetch_assoc($result)) {
    // Insert the post and set the category.
    // See https://gist.github.com/imelgrat/85ea75390757f18293674abdc9bd89e6#file-bulk-upload-php
    // for custom post type declaration
    $post_id = wp_insert_post(array(
    'post_type' => 'crueltyfree',
    'post_title' => $row['BRAND'],
    'post_content' => $row['Description'],
    'post_status' => 'pending', // Can be draft, pending or any other post status
    'comment_status' => 'closed', // if you prefer
    'ping_status' => 'closed', // if you prefer
        ));

    if ($post_id) {
    // Insert post meta (ACF Custom Fields)
    add_post_meta($post_id, 'Cruelty-Free', $row['Cruelty-Free']);
    add_post_meta($post_id, 'Vegan', $row['Vegan']);

    }


    echo $row['BRAND'] . ' posted<br>';
}
?>
 */


/*
function snej_single_record_url($singleurl, $pdb_record_id)
{
    if (pll_current_language() == 'bg'){
        return $singleurl = 'марка' ;
    } elseif (pll_current_language() == 'en') {
        return $singleurl  = 'brand';}
}

add_filter( 'pdb-single_record_url', 'snej_single_record_url',10,2);


/*
if ( !function_exists( 'pll_current_language' ) ) {
    require_once '/include/api.php';
}

// optional the language field to return 'name', 'locale', defaults to 'slug'
$field = 'locale';

// NOTICE! Understand what this does before running.
$result = pll_current_language($field);
*/
/*
function pdb_single_record_url($default = 'brand', $translatedURL) {
 if(pll_current_language() == 'списък') {
  return $translatedURL='марка';
 } else if(pll_current_language() == 'cruelty-free-list') {
    return $translatedURL='brand';
 }
*/
/*
if (!function_exists('pdb_list')) {
    function pdb_list($atts){
        extract (shortcode_atts('single_record_link', $atts));

        if ('en'($single_record_link)) {
        return 'single_record_link' == 'brand';
        }
        elseif ('bg'($single_record_link)){
        return 'single_record_link' == 'списък';
        }}}
*/
/*
if ( $result->pll_current_language['lang'] === 'bg_BG' ) {

	echo do_shortcode( '[pdb_list single_record_link="марка"]' );
}
elseif ( $result->pll_current_language['lang'] === 'en_EN' ) {
	echo do_shortcode( '[pdb_list single_record_link="brand"]' );
}

function pdb_single_record_url( $abcd, $wtf) {
    if ($result='bg_BG'){
$abcd .= 'марка';
if ($result='en_EN'){
$abcd .= 'brand';

return $abcd;}}}
add_filter('pdb-single_record_url',$abcd, $wtf)
*/
/*
return $abcd;return $abcd='марка';
    if ($result='en_EN')
    return $abcd='brands';
*/
/*
function pdb_single_callback( 'pdb-single_record_url' ) {
    // Maybe modify $example in some way.
    return $example;
}

function single_record_change_lang()
add_filter( 'pdb-single_record_url'
*/

/*Polylang logo change
https://stackoverflow.com/questions/48960489/wordpress-change-logo-image-when-i-click-to-a-different-language
*/

// add_filter( 'get_custom_logo', 'my_polylang_logo' );
// function my_polylang_logo() {
//   echo('test');
//   $logos = array(
//      'en' => 'logo_en.jpg',
//      'fr' => 'logo_fr.jpg',
//      'de' => 'logo_de.jpg',
//      'es' => 'logo_esp.jpg'
//   );
//   $current_lang = pll_current_language();
//   $img_path = get_stylesheet_directory_uri() . '/images/';
//   if ( isset( $logos[ $current_lang ] ) ) {
//      $logo_url = $img_path . $logos[$current_lang];
//   } else {
//      $logo_url = $img_path . $logos['en'];
//   }
//   $home_url = home_url();
//   $html = sprintf( '<a href="%1$s" rel="home" itemprop="url"><img src="%2$s"></a>', esc_url( $home_url ), $logo_url);
//    return $html;
// }

// add_filter('avf_logo_final_output','avf_change_logo');
// function avf_change_logo($logo)
// {
//   echo('test');
// 	$lang = pll_current_language('locale');
// 	switch ($lang) {
//     case 'en':
//         $logo = "https://thelastcage.org/LC3.png";
//         break;
//     case 'bg':
//         $logo = "https://thelastcage.org/LC2.png";
//         break;
// 	}
//
// 	return $logo;
// }

function nd_dosth_theme_setup() {
    // Add <title> tag support
    add_theme_support( 'title-tag' );
    // Add custom-logo support
    add_theme_support( 'custom-logo' );
}
add_action( 'after_setup_theme', 'nd_dosth_theme_setup');
/*
function custom_polylang_multilang_logo( $value ) {
		$logos = array(
			'en' => wp_get_attachment_image('1555', 'full'),
			'fr' => wp_get_attachment_image('1556', 'full'),
		);
		$default_logo = $logos['en'];
		$current_lang = pll_current_language();
		if ( isset( $logos[ $current_lang ] ) )
			$value = $logos[ $current_lang ];
		else
			$value = $default_logo;

	$html = sprintf( '<a href="%1$s" class="custom-logo-link" rel="home" itemprop="url">%2$s</a>',
            esc_url( home_url( '/' ) ),
            $value
        );
	return $html;
}*/

function pl_string_after_setup_theme() {

// register our translatable strings - again first check if function exists.
    if ( function_exists( 'pll_register_string' ) ) {

  pll_register_string('Response', 'Response');
  pll_register_string('Responses', 'Responses');
  pll_register_string('Comment navigation', 'Comment navigation');
  pll_register_string('&larr; Older Comments', '&larr; Older Comments');
  pll_register_string('Newer Comments &rarr;', 'Newer Comments &rarr;');
  pll_register_string('You must be logged in to post', 'You must be <a href="%s">logged in</a> to post a comment.');
  pll_register_string('Share post', 'Share Post:');
  pll_register_string('Leave a Reply', 'Leave a Reply');
  pll_register_string('Post Comment', 'Post Comment');
  pll_register_string('Leave a Reply to %s', 'Leave a Reply to %s');
  pll_register_string('Cancel Reply', 'Cancel Reply');
  pll_register_string('Logged in as', 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>');

    }
}
 add_action( 'after_setup_theme', 'pl_string_after_setup_theme' );
 
 function add_linebreak_shortcode() {
return '<br />';
}
add_shortcode('br', 'add_linebreak_shortcode' );

/*TNR Custom Navigation */
function tnr_custom_new_menu() {
  register_nav_menu('tnr-custom-menu',__( 'TNR Custom Menu' ));
}
add_action( 'init', 'tnr_custom_new_menu' );

/*TNR Nav JavaScript Animation*/
function tnrnav_enqueue_custom_js() {
    wp_enqueue_script('tnrmenu', '/rescue/scripts/tnrmenu.js', array(), false, false);
}
add_action('wp_enqueue_scripts', 'tnrnav_enqueue_custom_js',11);

	// add page builder to kot
	if( is_admin() ){ add_filter('gdlr_core_page_builder_post_type', 'gdlr_core_kot_add_page_builder'); }
	if( !function_exists('gdlr_core_kot_add_page_builder') ){
		function gdlr_core_kot_add_page_builder( $post_type ){
			$post_type[] = 'kot';
			return $post_type;
		}
	}

$template_name = get_post_meta( $wp_query->post->ID, '_wp_page_template', true );



// Kot Post Type Polylang Strings
add_action('init', function() {
  pll_register_string('age', 'Възраст', 'Kot Post Type');
});

add_action('init', function() {
  pll_register_string('gender', 'Пол', 'Kot Post Type');
});

add_action('init', function() {
  pll_register_string('color', 'Цвят', 'Kot Post Type');
});

add_action('init', function() {
  pll_register_string('coat', 'Козина', 'Kot Post Type');
});

add_action('init', function() {
  pll_register_string('neutered', 'Кастрация', 'Kot Post Type');
});

add_action('init', function() {
  pll_register_string('kids', 'Разбира се с деца', 'Kot Post Type');
});

add_action('init', function() {
  pll_register_string('cats', 'Разбира се с други котки', 'Kot Post Type');
});

add_action('init', function() {
  pll_register_string('location', 'Намира се в', 'Kot Post Type');
});

add_action('init', function() {
  pll_register_string('kotshare', 'СПОДЕЛИ и помогни на %s да намери своите Хора:', 'Kot Post Type');
});

add_action('init', function() {
  pll_register_string('passport', '* Всички котета се предават на осиновителите с европейски паспорт, микрочип и договор за осиновяване. Котетата, които не са кастрирани в момента на осиновяването защото са прекалено млади задължително се кастрират от осиновителите когато навършат подходяща възраст.', 'Kot Post Type');
});

add_action('init', function() {
  pll_register_string('morekot', 'Още котета, които търсят дом', 'Kot Post Type');
});

 /* Function for post duplication. Dups appear as drafts. User is redirected to the edit screen
 */
function rd_duplicate_post_as_draft(){
  global $wpdb;
  if (! ( isset( $_GET['post']) || isset( $_POST['post'])  || ( isset($_REQUEST['action']) && 'rd_duplicate_post_as_draft' == $_REQUEST['action'] ) ) ) {
    wp_die('No post to duplicate has been supplied!');
  }
 
  /*
   * Nonce verification
   */
  if ( !isset( $_GET['duplicate_nonce'] ) || !wp_verify_nonce( $_GET['duplicate_nonce'], basename( __FILE__ ) ) )
    return;
 
  /*
   * get the original post id
   */
  $post_id = (isset($_GET['post']) ? absint( $_GET['post'] ) : absint( $_POST['post'] ) );
  /*
   * and all the original post data then
   */
  $post = get_post( $post_id );
 
  /*
   * if you don't want current user to be the new post author,
   * then change next couple of lines to this: $new_post_author = $post->post_author;
   */
  $current_user = wp_get_current_user();
  $new_post_author = $current_user->ID;
 
  /*
   * if post data exists, create the post duplicate
   */
  if (isset( $post ) && $post != null) {
 
    /*
     * new post data array
     */
    $args = array(
      'comment_status' => $post->comment_status,
      'ping_status'    => $post->ping_status,
      'post_author'    => $new_post_author,
      'post_content'   => $post->post_content,
      'post_excerpt'   => $post->post_excerpt,
      'post_name'      => $post->post_name,
      'post_parent'    => $post->post_parent,
      'post_password'  => $post->post_password,
      'post_status'    => 'draft',
      'post_title'     => $post->post_title,
      'post_type'      => $post->post_type,
      'to_ping'        => $post->to_ping,
      'menu_order'     => $post->menu_order
    );
 
    /*
     * insert the post by wp_insert_post() function
     */
    $new_post_id = wp_insert_post( $args );
 
    /*
     * get all current post terms ad set them to the new post draft
     */
    $taxonomies = get_object_taxonomies($post->post_type); // returns array of taxonomy names for post type, ex array("category", "post_tag");
    foreach ($taxonomies as $taxonomy) {
      $post_terms = wp_get_object_terms($post_id, $taxonomy, array('fields' => 'slugs'));
      wp_set_object_terms($new_post_id, $post_terms, $taxonomy, false);
    }
 
    /*
     * duplicate all post meta just in two SQL queries
     */
    $post_meta_infos = $wpdb->get_results("SELECT meta_key, meta_value FROM $wpdb->postmeta WHERE post_id=$post_id");
    if (count($post_meta_infos)!=0) {
      $sql_query = "INSERT INTO $wpdb->postmeta (post_id, meta_key, meta_value) ";
      foreach ($post_meta_infos as $meta_info) {
        $meta_key = $meta_info->meta_key;
        if( $meta_key == '_wp_old_slug' ) continue;
        $meta_value = addslashes($meta_info->meta_value);
        $sql_query_sel[]= "SELECT $new_post_id, '$meta_key', '$meta_value'";
      }
      $sql_query.= implode(" UNION ALL ", $sql_query_sel);
      $wpdb->query($sql_query);
    }
 
 
    /*
     * finally, redirect to the edit post screen for the new draft
     */
    wp_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
    exit;
  } else {
    wp_die('Post creation failed, could not find original post: ' . $post_id);
  }
}
add_action( 'admin_action_rd_duplicate_post_as_draft', 'rd_duplicate_post_as_draft' );
 
/*
 * Add the duplicate link to action list for post_row_actions
 */
function rd_duplicate_post_link( $actions, $post ) {
  if (current_user_can('edit_posts')) {
    $actions['duplicate'] = '<a href="' . wp_nonce_url('admin.php?action=rd_duplicate_post_as_draft&post=' . $post->ID, basename(__FILE__), 'duplicate_nonce' ) . '" title="Duplicate this item" rel="permalink">Duplicate</a>';
  }
  return $actions;
}
 
add_filter( 'post_row_actions', 'rd_duplicate_post_link', 10, 2 );
add_filter('page_row_actions', 'rd_duplicate_post_link', 10, 2);
add_filter('kot_row_actions', 'rd_duplicate_kot_link', 10, 2);

/* Shop Manager Permissions */
$result = add_role(
'shop_manager',
    __( 'Shop Manager' ),
array(

$role = get_role( 'shop_manager' ),
$role->remove_cap( 'edit_published_posts' ),
$role->remove_cap( 'edit_published_pages' ),
$role->remove_cap( 'delete_published_pages' ),
$role->remove_cap( 'delete_published_posts' ),
$role->remove_cap( 'edit_posts' ),
$role->remove_cap( 'edit_pages' ),
$role->remove_cap( 'edit_woocommerce_coupons' ),
$role->remove_cap( 'edit_shop_coupons' ),
$role->remove_cap( 'export' ),
$role->remove_cap( 'manage_categories' ),

$role->remove_cap( 'delete_others_pages' ),
$role->remove_cap( 'delete_others_posts' ),
$role->remove_cap( 'manage_links' ),
$role->remove_cap( 'edit_theme_options' ),
$role->remove_cap( 'edit_pdb_records' ),
$role->remove_cap( 'configure_pdb' ),
$role->remove_cap( 'edit_others_posts' ),
$role->remove_cap( 'list_users' ),
$role->remove_cap( 'publish_pages' ),
$role->remove_cap( 'edit_others_attachments' ),
)
);

/**
 * @snippet       Translate a String in WooCommerce (English to Spanish)
 * @author        Rodolfo Melogli
 * @compatible    WooCommerce 4.6
 * https://www.businessbloomer.com/translate-single-string-woocommerce-wordpress
 */
  
add_filter( 'gettext', 'bbloomer_translate_woocommerce_strings', 999, 3 );
  
function bbloomer_translate_woocommerce_strings( $translated, $untranslated, $domain ) {
   if ( ! is_admin() && 'woocommerce' === $domain ) {
      switch ( $untranslated ) {
         case 'Billing &amp; Shipping' :
            $translated = 'Данни за доставка';
            break;
         case 'Street address' :
            $translated = 'Адрес / офис на Speedy';
            break;
         // ETC
      }
   }   
   return $translated;
}

/* Add Field: Speedy Office */
add_filter( 'woocommerce_billing_fields', 'speedy_add_field' );
function speedy_add_field( $fields ) {
	
	$fields[ 'billing_office' ]   = array(
		'label'        => 'Офис на Speedy',
		'required'     => true,
		'class'        => array( 'form-row-wide', 'my-custom-class' ),
		'priority'     => 20,
	);
	
	return $fields;
}

// Set billing address fields to not required
add_filter( 'woocommerce_checkout_fields', 'unrequire_checkout_fields' );
function unrequire_checkout_fields( $fields ) {
	unset( $fields[ 'billing_office' ][ 'required' ] );
		$fields['billing']['billing_company']['required']   = false;
	$fields['billing']['billing_city']['required']      = false;
	$fields['billing']['billing_postcode']['required']  = false;
	$fields['billing']['billing_state']['required']     = false;
	$fields['billing']['billing_address_1']['required'] = false;
	$fields['billing']['billing_address_2']['required'] = false;
	return $fields;
}

/* Show Checkout Shipping Fields According to Selected Shipping Method */
add_filter('woocommerce_checkout_fields', 'xa_remove_billing_checkout_fields');

function xa_remove_billing_checkout_fields($fields) {
   

    unset($fields['billing']['billing_company']); 
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_state']); 
    unset($fields['billing']['billing_office']);

return $fields;}

/* Show Checkout Shipping Fields According to Selected Shipping Method 
add_filter('woocommerce_checkout_fields', 'xa_remove_billing_checkout_fields');

function xa_remove_billing_checkout_fields($fields) {
    $shipping_method1 ='flat_rate:1'; // Value of the applicable shipping method
    $shipping_method3 ='flat_rate:3'; 
global $woocommerce;
    $chosen_methods = WC()->session->get( 'chosen_shipping_methods' );
    $chosen_shipping = $chosen_methods[0];

if ($chosen_shipping == $shipping_method1) {
    unset($fields['billing']['billing_company']); 
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_address_1']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_state']); 
}
elseif ($chosen_shipping == $shipping_method3) {
    unset($fields['billing']['billing_company']); 
    unset($fields['billing']['billing_postcode']);
    unset($fields['billing']['billing_address_2']);
    unset($fields['billing']['billing_state']); 
    unset($fields['billing']['billing_office']);}

return $fields;}*/



/*
add_filter( 'woocommerce_shipping_fields', 'misha_remove_shipping_fields' );
function misha_remove_shipping_fields( $fields ) {
	unset( $fields[ 'shipping_address_1' ] ); 
	unset( $fields[ 'shipping_address_2' ] ); 
	unset( $fields[ 'shipping_company' ] ); 
	unset( $fields[ 'shipping_postcode' ] ); 
	unset( $fields[ 'shipping_country' ] ); 
	unset( $fields[ 'shipping_state' ] ); 
	return $fields;
}
add_filter( 'woocommerce_billing_fields', 'misha_remove_billing_fields' );
function misha_remove_billing_fields( $fields ) {
	unset( $fields[ 'billing_company' ] ); 
	unset( $fields[ 'billing_country' ] ); 
	unset( $fields[ 'billing_state' ] ); 
	unset( $fields[ 'billing_postcode' ] ); 
	return $fields;
}*/
remove_filter( 'lostpassword_url', 'wc_lostpassword_url', 10 );
/* Woocommerce Product Page - Enable Gallery display */
add_theme_support( 'wc-product-gallery-slider' );
/* 
add_theme_support( 'wc-product-gallery-zoom' );
add_theme_support( 'wc-product-gallery-lightbox' );
 */

/* GPT Cart Counter: Display cart icon and count in menu */
function cart_icon_with_count( $count ) {
    $icon_markup = '<div class="gdlr-icon icon-shopping-cart"></div>';
    $cart_link = wc_get_cart_url();

    return '<a href="' . $cart_link . '">' . $icon_markup . ($count > 0 ? '<span class="cart-counter">' . esc_html( $count ) . '</span>' : '') . '</a>';
}

function add_cart_counter_to_menu_item( $items, $args ) {
    if ( ! is_admin() && 'tnr-custom-menu' === $args->theme_location ) {
        $cart_count = WC()->cart->get_cart_contents_count();
        $items = str_replace( 'Cart', cart_icon_with_count( $cart_count ), $items );
    }
    
    return $items;
}

function shopping_cart_count_shortcode() {
    $cart_count = WC()->cart->get_cart_contents_count();
    return cart_icon_with_count( $cart_count );
}

add_shortcode('shopping-cart-count', 'shopping_cart_count_shortcode');
add_filter( 'wp_nav_menu_items', 'add_cart_counter_to_menu_item', 10, 2 );


/* REORDER ELEMENTS ON WOOCOMMERCE PRODUCT PAGE in single_product_summary which are in plugins/woocommerce/includes/wc-template-hooks.php */
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10);
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 20 );

/*/ Move Tabs below the add to cart buttons // 
function move_tabs() {
  remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
  add_action( 'woocommerce_single_product_summary', 'woocommerce_output_product_data_tabs', 70 );
}
add_action('wp', 'move_tabs');*/

/* Use dots in WooCommerce FlexSlider instead of thumbnails on mobile
https://stackoverflow.com/questions/63187694/use-dots-in-woocommerce-flexslider-instead-of-thumbnails-on-mobile */

add_filter( 'woocommerce_single_product_carousel_options', 'custom_update_woo_flexslider_options' );

function custom_update_woo_flexslider_options( $options ) {
    $options['controlNav'] = wp_is_mobile() ? true : 'thumbnails';
    return $options;
}

/* https://stackoverflow.com/questions/77713799/switch-woocommerce-flexslider-from-dots-to-thumbnails-according-to-screen-width
add_filter( 'woocommerce_single_product_carousel_options', 'custom_update_woo_flexslider_options' );

function custom_update_woo_flexslider_options( $options ) {
    // Set both options for controlNav
    $options['controlNav'] = array(
        true,
        'thumbnails',
    );

    return $options;
}

*/
add_filter( 'woocommerce_product_tabs', 'misha_change_tabs_order', 98 );

function misha_change_tabs_order( $tabs ) {

    $tabs['reviews']['priority'] = 5;

    return $tabs;
}

/* Add Other Products on Product Page under Product Details and Reviews */
add_action('woocommerce_after_single_product', 'custom_section_below_tabs', 20);

function custom_section_below_tabs() {
    echo '<div class="custom-section"><hr>';
    
    echo '<h1>Последни:</h1>';
    echo '<div class="recent-products">' . do_shortcode('[recent_products]') . '</div>';
    echo '<hr>';

    echo '<h1>Най-продавани</h1>';
    echo '<div class="best_selling_products">' . do_shortcode('[best_selling_products]') . '</div>';
    echo '<hr>';

    echo '</div>';
}

/* Woocommerce ADD TO CART Buttons */

/*/ Modify "Add to Cart" text for Single product page
add_filter('woocommerce_product_single_add_to_cart_text', 'woocommerce_custom_add_to_cart_text'); 
function woocommerce_custom_add_to_cart_text() {
    return __('Добави в количката', 'woocommerce');
}*/

// Remove Add to Cart text from  Shortcode Products
add_filter('woocommerce_product_add_to_cart_text', 'remove_add_to_cart_text');
function remove_add_to_cart_text() {
    return '';
}

// Add an icon to the Add to Cart button from Shortcode Products
add_action('wp_footer', 'add_icon_to_add_to_cart_button');
function add_icon_to_add_to_cart_button() {
?>
<script type="text/javascript">
    jQuery(document).ready(function(jQuery) {
        // Replace 'icon-class' with the class of your desired icon (e.g., FontAwesome)
        var iconClass = 'gdlr-icon icon-shopping-cart';
        
        // Loop through each Add to Cart button
        jQuery('.add_to_cart_button').each(function() {
            // Remove existing content (text) and add the icon
            jQuery(this).html('<span class="' + iconClass + '"></span>');
        });
    });
</script>
<?php
}

/*  */
/*  */
// Remove Add to Cart text on single product page
add_filter('woocommerce_product_single_add_to_cart_text', 'remove_add_to_cart_text_single');
function remove_add_to_cart_text_single() {
    return '';
}

// Add an icon and text to the single product Add to Cart button
add_action('wp_footer', 'add_icon_and_text_to_single_add_to_cart_button');
function add_icon_and_text_to_single_add_to_cart_button() {
?>
<script type="text/javascript">
    jQuery(document).ready(function($) {
        var iconClass = 'gdlr-icon icon-shopping-cart';
        var buttonText = ' Добави в количката';

        // Get the existing button content
        var existingContent = $('.single_add_to_cart_button').html();

        // Create the new button content with icon and text
        var newContent = '<span class="' + iconClass + '"></span> <span class="addtocart-text">' + buttonText; + '</span>'

        // Set the new content for the button
        $('.single_add_to_cart_button').html(newContent + existingContent);
    });
</script>
<?php
}

/** WCOOCOMMERCE Product Page
 * Change the breadcrumb home text from "Home" to "Shop".
 * @param  array $defaults The default array items.
 * @return array           Modified array
 */
add_filter( 'woocommerce_breadcrumb_defaults', 'woo_change_breadcrumb_home_text' );

function woo_change_breadcrumb_home_text( $defaults ) {
	$defaults['home'] = 'Shop';

	return $defaults;
}

add_filter( 'woocommerce_breadcrumb_home_url', 'woo_custom_breadrumb_home_url' );
/**
 * Change the breadcrumb home link URL from / to /shop.
 * @return string New URL for Home link item.
 */
function woo_custom_breadrumb_home_url() {
	return '/shop/';
}
function enqueue_woocommerce_mobile_styles() {
    // Enqueue your stylesheet
    wp_enqueue_style('woocommerce-mobile-styles', get_stylesheet_directory_uri() . '/woocommerce-mobile-styles.css', array('wp-custom-css'), '1.0', 'screen');
}

// Hook the function to the wp_enqueue_scripts action
add_action('wp_enqueue_scripts', 'enqueue_woocommerce_mobile_styles');

// REMOVE BREADCRUMBS //
remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);

//
function register_custom_header_widget_area() {
register_sidebar(
array(
'id' => 'new-header-widget-area',
'name' => esc_html__( 'New Header Widget Area', 'theme-domain' ),
'description' => esc_html__( 'Custom header after widget area', 'theme-domain' ),
'before_widget' => '<div id="%1$s" class="widget %2$s">',
'after_widget' => '</div>',
'before_title' => '<div class="widget-title-holder"><h3 class="widget-title">',
'after_title' => '</h3></div>'
)
);
}
add_action( 'widgets_init', 'register_custom_header_widget_area' );


function display_custom_header_widget_area() {
    if ( is_active_sidebar( 'new-header-widget-area' ) ) { ?>
        <div id="header-after" class="additional-header-widget-area"
            <?php dynamic_sidebar( 'new-header-widget-area' ); ?>
        </div>
    <?php }
}
add_action( 'woocommerce_before_main_content', 'display_custom_header_widget_area' );

/* Redirect Shop Page URL
function custom_shop_page_redirect() {
    if( is_shop() ){
        wp_redirect( home_url( '/shop/' ) );
        exit();
    }
}
add_action( 'template_redirect', 'custom_shop_page_redirect' );*/

add_theme_support('woocommerce');

/* Redirect "Back to Shop" button URL (Cart Page) */
add_filter( 'woocommerce_return_to_shop_redirect', 'st_woocommerce_shop_url' );
/**
 * Redirect WooCommerce Shop URL
 */

function st_woocommerce_shop_url(){
return site_url() . '/shop/';
}

// Remove WooCommerce Archive Description
remove_action( 'woocommerce_archive_description', 'woocommerce_taxonomy_archive_description', 10 );
remove_action( 'woocommerce_archive_description', 'woocommerce_product_archive_description', 10 );

/////////////////////
// Add custom user roles for unverified and pending users
add_role('unverified_user', __('Unverified User'), array('read' => false));
add_role('pending_student', __('Pending Student'), array('read' => false));

function custom_wp_new_user_notification_email($wp_new_user_notification_email, $user, $blogname) {
    // Check if the user's email is verified
    $email_verified = get_user_meta($user->ID, 'email_verified', true);

    // If the email is not verified, proceed with sending the confirmation email
    if (!$email_verified) {
        // Assign the "Unverified User" role to the user
        $user->set_role('unverified_user');

        $message  = sprintf(__('New user registration on %s:'), $blogname) . "\r\n\r\n";
        $message .= sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
        $message .= sprintf(__('Email: %s'), $user->user_email) . "\r\n";

        // Generate a random key for email verification
        $verification_key = md5(uniqid(rand(), true));

        // Save the verification key in user metadata
        update_user_meta($user->ID, 'email_verification_key', $verification_key);

        // Your custom email confirmation link
        $confirmation_link = site_url('/email-verification/?key=' . $verification_key . '&user=' . $user->user_login);

        $message .= __('Please click on the following link to confirm your email address:') . "\r\n";
        $message .= $confirmation_link . "\r\n";

        // Set the redirect URL after successful email confirmation
        $redirect_url = site_url('/email-verification-success/'); // Change to your desired success page

        // Add the redirect URL to the email content
        $message .= __('After confirming your email, you will be redirected to:') . "\r\n";
        $message .= $redirect_url . "\r\n";

        // Modify the email content
        $wp_new_user_notification_email['message'] = $message;
    }

    return $wp_new_user_notification_email;
}

add_filter('wp_new_user_notification_email', 'custom_wp_new_user_notification_email', 10, 3);

// Hook to handle email verification
add_action('init', 'custom_email_verification');
function custom_email_verification() {
    if (isset($_GET['key']) && isset($_GET['user'])) {
        $user_login = sanitize_text_field($_GET['user']);
        $verification_key = sanitize_text_field($_GET['key']);

        $user = get_user_by('login', $user_login);

        if ($user && $verification_key === get_user_meta($user->ID, 'email_verification_key', true)) {
            // Mark the user's email as verified
            update_user_meta($user->ID, 'email_verified', true);

            // Change the user role to "Pending Student" after email verification
            $user->set_role('pending_student');

            // Send an email notification to the admin for pending student approval
            $admin_email = get_option('admin_email');
            $subject = 'New Pending Student Registration';
            $message = 'A new user has registered and is pending student approval. Username: ' . $user->user_login . ', Email: ' . $user->user_email;
            wp_mail($admin_email, $subject, $message);
            
            // Redirect to the success page
            wp_redirect(site_url('/email-verification-success/'));
            exit();
        }
    }
}
add_filter('single_template', 'custom_course_visibility_control');

function custom_course_visibility_control($template) {
    global $post, $wpdb, $current_user;

    // Check if the user has the role "Pending Student" or "Unverified User"
    if (in_array('pending_student', $current_user->roles) || in_array('unverified_user', $current_user->roles)) {
        // Return a template for users with "Pending Student" or "Unverified User" roles
        return get_template_directory() . '/custom-template-for-restricted-users.php';
    }

    // Rest of the existing code...

    // Your existing code for course authorization goes here...

    return $template;
}
// Add to functions.php
function handle_email_verification_redirect() {
    if (isset($_GET['action']) && $_GET['action'] === 'verify_email') {
        // This ensures the verification process happens even if theme templates interfere
        if (isset($_GET['user_id']) && isset($_GET['token'])) {
            $user_id = intval($_GET['user_id']);
            $token = sanitize_text_field($_GET['token']);
            
            // Include the verification function if not already available
            if (!function_exists('gdlr_lms_verify_email')) {
                // Path to the file containing the function - adjust as needed
                include_once(WP_CONTENT_DIR . '/plugins/goodlayers-lms/user/author-update.php');
            }
            
            $verification_result = gdlr_lms_verify_email($user_id, $token);
            
            // Redirect with status
            $status = $verification_result['success'] ? 'success' : 'error';
            wp_safe_redirect(add_query_arg('verification_status', $status, home_url('/email-verification/' . $user_id)));
            exit;
        }
    }
}
add_action('template_redirect', 'handle_email_verification_redirect', 5);

// DONTAION FIELD ON CART PAGE - Add donation input field on the cart page
add_action('woocommerce_after_cart_table', 'add_donation_input_to_cart');

function add_donation_input_to_cart() {
    $donation_product_id = 7702;
    if (!wc_get_product($donation_product_id)) {
        echo 'Donation product not found.';
        return;
    }
    $cart = WC()->cart;
    foreach ($cart->get_cart() as $cart_item) {
        if ($cart_item['product_id'] === $donation_product_id) {
            echo '';
            return;
        }
    }
    
        if (pll_current_language() == 'bg') {
            echo '<h4>В случай че желаете да дарите в подкрепа на нашата работа допълнителна сума, моля посочете сумата в лева и я добавите в количката от тук: </h4>
            <form method="post" class="donation-form">
            <input type="number" name="donation_amount" step="any" placeholder="Въведете сума" required>
            <input type="hidden" name="add_donation_to_cart" value="' . $donation_product_id . '">
            <button type="submit" class="button">Добави дарение</button>
          </form> ';
   }elseif (pll_current_language() == 'en') {
            echo '<form method="post" class="donation-form">
            <input type="number" name="donation_amount" step="any" placeholder="Enter donation amount" required>
            <input type="hidden" name="add_donation_to_cart" value="' . $donation_product_id . '">
            <button type="submit" class="button">Add Donation</button>
          </form> <h4>В случай че желаете да дарите в подкрепа на нашата работа допълнителна сума по Ваш избор, моля посочете сумата в лева по-долу и я добавите в количката. </h4>';
   }
}


// Process adding donation to cart and update donation product price
add_action('template_redirect', 'process_donation_to_cart');

function process_donation_to_cart() {
    if (isset($_POST['add_donation_to_cart'], $_POST['donation_amount'])) {
        $donation_product_id = absint($_POST['add_donation_to_cart']);
        $donation_amount = floatval($_POST['donation_amount']);
        if ($donation_product_id > 0 && $donation_amount > 0) {
            WC()->cart->add_to_cart($donation_product_id, 1, 0, array(), array('donation_amount' => $donation_amount));
            wp_safe_redirect(wc_get_cart_url());
            exit;
        }
    }
}

// Update donation product price based on user input
add_action('woocommerce_before_calculate_totals', 'update_donation_product_price');

function update_donation_product_price($cart) {
    if (is_admin() && !defined('DOING_AJAX')) return;
    $donation_product_id = 7702;
    foreach ($cart->get_cart() as $cart_item) {
        if (isset($cart_item['donation_amount']) && $cart_item['product_id'] === $donation_product_id) {
            $cart_item['data']->set_price($cart_item['donation_amount']);
        }
    }
}

// numbers counter 
function enqueue_counter_script() {
    wp_enqueue_script( 'jquery' ); // Ensure jQuery is enqueued
    wp_enqueue_script( 'counter-js', get_stylesheet_directory_uri() . '/js/counter.js', array('jquery'), null, true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_counter_script' );

//WOOCOMMERCE PRODUCT PAGE - ADD "DONATED BY" AND "DESIGN"

// Add custom fields to Woocommerce backend under General tab
add_action( 'woocommerce_product_options_general_product_data', 'add_custom_text_fields' );
function add_custom_text_fields() {
    woocommerce_wp_text_input( array(
        'id'            => '_donated_by',
        'label'         => __( 'Donated by / Дарител', 'woocommerce' ),
        'description'   => __( 'Enter the donor\'s name', 'woocommerce' ),
        'desc_tip'      => 'true',
        'type'          => 'text'
    ) );

    woocommerce_wp_text_input( array(
        'id'            => '_donor_url',
        'label'         => __( 'Donor URL', 'woocommerce' ),
        'description'   => __( 'Enter the donor\'s URL', 'woocommerce' ),
        'desc_tip'      => 'true',
        'type'          => 'url'
    ) );
     woocommerce_wp_text_input( array(
        'id'            => '_design_by',
        'label'         => __( 'Design by / Дизайн', 'woocommerce' ),
        'description'   => __( 'Enter the designer\'s name', 'woocommerce' ),
        'desc_tip'      => 'true',
        'type'          => 'text'
    ) );

    woocommerce_wp_text_input( array(
        'id'            => '_design_url',
        'label'         => __( 'Design URL', 'woocommerce' ),
        'description'   => __( 'Enter the designer\'s URL', 'woocommerce' ),
        'desc_tip'      => 'true',
        'type'          => 'url'
    ) );
}

// Save custom field values
add_action( 'woocommerce_admin_process_product_object', 'save_fields', 10, 1 );
function save_fields( $product ) {
    if ( isset( $_POST['_donated_by'] ) ) {        
        $product->update_meta_data( '_donated_by', sanitize_text_field( $_POST['_donated_by'] ) );
    }
    if ( isset( $_POST['_donor_url'] ) ) {
        $product->update_meta_data( '_donor_url', esc_url_raw( $_POST['_donor_url'] ) );
    }
    if ( isset( $_POST['_design_by'] ) ) {        
        $product->update_meta_data( '_design_by', sanitize_text_field( $_POST['_design_by'] ) );
    }
    if ( isset( $_POST['_design_url'] ) ) {
        $product->update_meta_data( '_design_url', esc_url_raw( $_POST['_design_url'] ) );
    }
}



// Display this custom field on Woocommerce single product pages
add_action( 'woocommerce_single_product_summary', 'display_custom_field_value', 15 );
function display_custom_field_value() {
    global $product;

    // Is a WC product
    if ( is_a( $product, 'WC_Product' ) ) {
         // Get meta for donor
        $donor_name = $product->get_meta( '_donated_by' );
        $donor_url = $product->get_meta( '_donor_url' );

        // Get meta for designer
        $design_name = $product->get_meta( '_design_by' );
        $design_url = $product->get_meta( '_design_url' );

        // Display donor information
        if ( ! empty ( $donor_name ) ) {
            if ( ! empty ( $donor_url ) ) {
                echo '<div class="product-donor-field">' ;
                if (pll_current_language() == 'bg') { echo'<span>Дарено от: ';} 
                elseif (pll_current_language() =='en') { echo '<span>Donated by: ';} 
                echo '<a href="' . esc_url( $donor_url ) . '" target="_blank">' . esc_html( $donor_name ) . '</a></span></div>';
            } else {
                echo '<div class="product-donor-field">' ;
                if (pll_current_language() == 'bg') { echo'<span>Дарено от: ';} 
                elseif (pll_current_language() =='en') { echo '<span>Donated by: ';} 
                echo esc_html( $donor_name ) . '</span></div>';
            }
        }

        // Display designer information
        if ( ! empty ( $design_name ) ) {
            if ( ! empty ( $design_url ) ) {
                echo '<div class="product-design-field">' ;
                if (pll_current_language() == 'bg') { echo'<span>Дизайн: ';} 
                elseif (pll_current_language() =='en') { echo '<span>Design by: ';} 
                echo '<a href="' . esc_url( $design_url ) . '" target="_blank">' . esc_html( $design_name ) . '</a></span></div>';
            } else {
                echo '<div class="product-design-field">' ;
                if (pll_current_language() == 'bg') { echo'<span>Дизайн: ';} 
                elseif (pll_current_language() =='en') { echo '<span>Design by: ';} 
                echo esc_html( $design_name ) . '</span></div>';
            }
        }
    }
}

// Add Page Category Filter to Pages List
function filter_pages_by_page_category($post_type, $which) {
    if ($post_type !== 'page') return;

    $terms = get_terms(array(
        'taxonomy'   => 'page_category', // Use your actual page category taxonomy name
        'hide_empty' => false
    ));

    echo '<select name="page_category_filter">';
    echo '<option value="">All Page Categories</option>';
    echo '<option value="uncategorized" ' . selected($_GET['page_category_filter'] ?? '', 'uncategorized', false) . '>Uncategorized</option>';

    foreach ($terms as $term) {
        echo '<option value="' . $term->term_id . '" ' . selected($_GET['page_category_filter'] ?? '', $term->term_id, false) . '>' . esc_html($term->name) . '</option>';
    }

    echo '</select>';
}
add_action('restrict_manage_posts', 'filter_pages_by_page_category', 10, 2);

// Modify Query to Filter Pages by Page Category (Including Uncategorized)
function filter_pages_by_page_category_query($query) {
    if (is_admin() && $query->is_main_query() && isset($_GET['page_category_filter'])) {
        if ($_GET['page_category_filter'] === 'uncategorized') {
            $query->set('tax_query', array(array(
                'taxonomy' => 'page_category', // Use your actual page category taxonomy name
                'operator' => 'NOT EXISTS', // Show pages without a category
            )));
        } elseif (!empty($_GET['page_category_filter'])) {
            $query->set('tax_query', array(array(
                'taxonomy' => 'page_category',
                'field'    => 'term_id',
                'terms'    => intval($_GET['page_category_filter']),
            )));
        }
    }
}
add_action('pre_get_posts', 'filter_pages_by_page_category_query');

function override_plugin_register_template($template) {
    if (is_page('register')) { // Adjust if needed
        $new_template = get_stylesheet_directory() . '/my-custom-register.php'; 
        if (file_exists($new_template)) {
            return $new_template;
        }
    }
    return $template;
}
add_filter('template_include', 'override_plugin_register_template');

// Add Page Category column to Pages list table
function add_page_category_column($columns) {
    $new_columns = array();

    foreach ($columns as $key => $value) {
        if ($key === 'title') {
            $new_columns[$key] = $value;
            $new_columns['page_category'] = __('Page Category');
        } elseif ($key === 'author') {
            // Skip 'author' here — we'll move it after 'date'
            continue;
        } elseif ($key === 'date') {
            $new_columns[$key] = $value;
            $new_columns['author'] = __('Author'); // Move author here
        } else {
            $new_columns[$key] = $value;
        }
    }

    return $new_columns;
}
add_filter('manage_pages_columns', 'add_page_category_column');

// Populate Page Category column content
function show_page_category_column($column, $post_id) {
    if ($column === 'page_category') {
        $terms = get_the_terms($post_id, 'page_category');
        if (!empty($terms) && !is_wp_error($terms)) {
            $term_names = wp_list_pluck($terms, 'name');
            echo esc_html(implode(', ', $term_names));
        } else {
            echo '—';
        }
    }
}
add_action('manage_pages_custom_column', 'show_page_category_column', 10, 2);
// Make 'Page Category' column sortable
function make_page_category_column_sortable($columns) {
    $columns['page_category'] = 'page_category';
    return $columns;
}
add_filter('manage_edit-page_sortable_columns', 'make_page_category_column_sortable');

// Handle sorting logic for 'Page Category' column
function sort_pages_by_page_category($query) {
    if (!is_admin() || !$query->is_main_query()) {
        return;
    }

    if ($query->get('orderby') === 'page_category') {
        $query->set('orderby', 'taxonomy');
        $query->set('tax_query', array(
            array(
                'taxonomy' => 'page_category',
                'field'    => 'term_id',
                'operator' => 'EXISTS',
            )
        ));
    }
}
add_action('pre_get_posts', 'sort_pages_by_page_category');

//***//

/**
 * This code should be added to your theme's functions.php file 
 * or included in the main plugin file
 */

/**
 * Override the GoodLMS course structure display to show public/private status
 * and handle access restrictions
 */
function gdlms_override_course_structure_display() {
    // Check if we're on a course page
    if (!is_singular('gdlms-course')) {
        return;
    }
    
    // Remove the default course structure display
    remove_action('gdlms_single_course_content', 'gdlms_single_course_content_main', 10);
    
    // Add our custom course structure display
    add_action('gdlms_single_course_content', 'custom_gdlms_course_structure_display', 10);
}
add_action('wp', 'gdlms_override_course_structure_display');

/**
 * Custom course structure display with part labels and access indicators
 */
function custom_gdlms_course_structure_display() {
    global $post;
    $course_id = $post->ID;
    
    // Get course structure
    $structure = gdlms_get_course_structure($course_id);
    
    if (empty($structure)) {
        return;
    }
    
    // Public parts definition (must match the plugin)
    $public_parts = array(1, 4, 5);
    
    // Start output
    echo '<div class="gdlms-course-structure-wrapper">';
    echo '<h3 class="gdlms-course-structure-title">' . esc_html__('Course Structure', 'gdlms') . '</h3>';
    
    echo '<div class="gdlms-course-structure-legend">';
    echo '<div class="gdlms-legend-item gdlms-public"><span class="dashicons dashicons-visibility"></span> Public Content</div>';
    echo '<div class="gdlms-legend-item gdlms-private"><span class="dashicons dashicons-lock"></span> Registered Users Only</div>';
    echo '</div>';
    
    echo '<div class="gdlms-course-structure">';
    
    // Track the current part
    $current_part = 0;
    $part_started = false;
    
    foreach ($structure as $item) {
        $item_id = $item['id'];
        $item_type = $item['type'];
        $item_title = $item['title'];
        
        // Get the part number
        $part = get_post_meta($item_id, '_gdlms_course_part', true);
        
        // If we're entering a new part, show part header
        if (!empty($part) && $current_part != $part) {
            // Close previous part if needed
            if ($part_started) {
                echo '</div>'; // Close .gdlms-part-items
                echo '</div>'; // Close .gdlms-course-part
                $part_started = false;
            }
            
            $current_part = $part;
            $is_public = in_array((int)$part, $public_parts);
            $part_class = $is_public ? 'gdlms-public-part' : 'gdlms-private-part';
            $icon_class = $is_public ? 'dashicons-visibility' : 'dashicons-lock';
            $access_text = $is_public ? 'Public Access' : 'Registered Users Only';
            
            echo '<div class="gdlms-course-part ' . esc_attr($part_class) . '">';
            echo '<div class="gdlms-part-header">';
            echo '<h4 class="gdlms-part-title">Part ' . esc_html($part) . ' <span class="gdlms-part-access"><span class="dashicons ' . esc_attr($icon_class) . '"></span> ' . esc_html($access_text) . '</span></h4>';
            echo '</div>';
            echo '<div class="gdlms-part-items">';
            $part_started = true;
        }
        
        // If we haven't started any part yet but have an item, start a generic part
        if (!$part_started) {
            echo '<div class="gdlms-course-part">';
            echo '<div class="gdlms-part-items">';
            $part_started = true;
        }
        
        // Get access status for this item
        $has_access = gdlms_check_course_item_access($item_id);
        $item_url = get_permalink($item_id);
        $class = '';
        
        // Check if user completed this item
        $completed = false;
        if (is_user_logged_in()) {
            $user_id = get_current_user_id();
            $progress = gdlms_check_user_progress($user_id, $item_id);
            $completed = ($progress === 'complete');
            $class .= $completed ? ' gdlms-completed' : '';
        }
        
        // Item output
        echo '<div class="gdlms-course-item' . esc_attr($class) . '">';
        
        // Icon based on item type
        $icon = 'dashicons-text-page';
        if ($item_type === 'gdlms-quiz') {
            $icon = 'dashicons-forms';
        } else if ($item_type === 'gdlms-video') {
            $icon = 'dashicons-video-alt3';
        } else if ($item_type === 'gdlms-audio') {
            $icon = 'dashicons-format-audio';
        }
        
        echo '<span class="gdlms-item-icon"><span class="dashicons ' . esc_attr($icon) . '"></span></span>';
        
        // Output item title with appropriate access
        if ($has_access) {
            echo '<a href="' . esc_url($item_url) . '" class="gdlms-item-title">' . esc_html($item_title) . '</a>';
            
            // Show completion status for logged-in users
            if (is_user_logged_in()) {
                if ($completed) {
                    echo '<span class="gdlms-completion-status completed"><span class="dashicons dashicons-yes-alt"></span> Completed</span>';
                } else {
                    echo '<span class="gdlms-completion-status incomplete"><span class="dashicons dashicons-marker"></span> In Progress</span>';
                }
            }
        } else {
            // Show locked status with appropriate action
            if (!is_user_logged_in() && !in_array((int)$part, $public_parts)) {
                $register_url = wp_registration_url();
                echo '<span class="gdlms-item-title">' . esc_html($item_title) . '</span>';
                echo ' <a href="' . esc_url($register_url) . '" class="gdlms-lock-icon" title="Register to unlock this content"><span class="dashicons dashicons-lock"></span> Register to Access</a>';
            } else if (is_user_logged_in()) {
                // This must be locked because a previous quiz is not completed
                // Find the prerequisite quiz
                $previous_part = (int)$part - 1;
                $quiz_id = get_quiz_for_part($previous_part);
                
                if ($quiz_id) {
                    $quiz_url = get_permalink($quiz_id);
                    echo '<span class="gdlms-item-title">' . esc_html($item_title) . '</span>';
                    echo ' <a href="' . esc_url($quiz_url) . '" class="gdlms-lock-icon" title="Complete the previous quiz to unlock this content"><span class="dashicons dashicons-lock"></span> Complete Quiz to Unlock</a>';
                } else {
                    echo '<span class="gdlms-item-title">' . esc_html($item_title) . '</span>';
                    echo ' <span class="gdlms-lock-icon"><span class="dashicons dashicons-lock"></span> Locked</span>';
                }
            } else {
                echo '<span class="gdlms-item-title">' . esc_html($item_title) . '</span>';
                echo ' <span class="gdlms-lock-icon"><span class="dashicons dashicons-lock"></span> Access Restricted</span>';
            }
        }
        
        echo '</div>'; // .gdlms-course-item
    }
    
    // Close any open part
    if ($part_started) {
        echo '</div>'; // Close .gdlms-part-items
        echo '</div>'; // Close .gdlms-course-part
    }
    
    echo '</div>'; // .gdlms-course-structure
    echo '</div>'; // .gdlms-course-structure-wrapper
    
    // Add styles
    ?>
    <style type="text/css">
        .gdlms-course-structure-wrapper {
            margin: 30px 0;
        }
        .gdlms-course-structure-legend {
            display: flex;
            margin-bottom: 20px;
        }
        .gdlms-legend-item {
            margin-right: 20px;
            display: flex;
            align-items: center;
        }
        .gdlms-legend-item .dashicons {
            margin-right: 5px;
        }
        .gdlms-public .dashicons {
            color: #27ae60;
        }
        .gdlms-private .dashicons {
            color: #e74c3c;
        }
        .gdlms-course-part {
            margin-bottom: 25px;
            border: 1px solid #e0e0e0;
            border-radius: 5px;
        }
        .gdlms-part-header {
            background-color: #f5f5f5;
            padding: 10px 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .gdlms-part-title {
            margin: 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .gdlms-part-access {
            font-size: 14px;
            font-weight: normal;
            display: flex;
            align-items: center;
        }
        .gdlms-public-part .gdlms-part-access {
            color: #27ae60;
        }
        .gdlms-private-part .gdlms-part-access {
            color: #e74c3c;
        }
        .gdlms-part-access .dashicons {
            margin-right: 5px;
        }
        .gdlms-part-items {
            padding: 10px 15px;
        }
        .gdlms-course-item {
            padding: 10px 0;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            align-items: center;
        }
        .gdlms-course-item:last-child {
            border-bottom: none;
        }
        .gdlms-item-icon {
            margin-right: 10px;
            color: #7f8c8d;
        }
        .gdlms-lock-icon {
            margin-left: 10px;
            color: #e74c3c;
            display: flex;
            align-items: center;
            text-decoration: none;
        }
        .gdlms-lock-icon .dashicons {
            margin-right: 5px;
        }
        .gdlms-completion-status {
            margin-left: auto;
            display: flex;
            align-items: center;
        }
        .gdlms-completion-status.completed {
            color: #27ae60;
        }
        .gdlms-completion-status.incomplete {
            color: #f39c12;
        }
        .gdlms-completion-status .dashicons {
            margin-right: 5px;
        }
    </style>
    <?php
}

/**
 * Helper function: Get quiz for part
 */
function get_quiz_for_part($part) {
    $args = array(
        'post_type' => 'gdlms-quiz',
        'meta_key' => '_gdlms_course_part',
        'meta_value' => $part,
        'posts_per_page' => 1
    );
    
    $quizzes = get_posts($args);
    
    if (!empty($quizzes)) {
        return $quizzes[0]->ID;
    }
    
    return false;
}
?>