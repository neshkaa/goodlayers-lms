<?php
/*
Plugin Name: Goodlayers Portflio Post Type
Plugin URI:
Description: A Custom Post Type Plugin To Use With Goodlayers Theme ( This plugin functionality might not working properly on another theme )
Version: 1.0.1
Author: Goodlayers
Author URI: http://www.goodlayers.com
License:
*/
include_once( 'gdlr-kot-item.php');
include_once( 'gdlr-kot-option.php');

// action to loaded the plugin translation file
add_action('plugins_loaded', 'gdlr_kot_init');
if( !function_exists('gdlr_kot_init') ){
	function gdlr_kot_init() {
		load_plugin_textdomain( 'gdlr-kot', false, dirname(plugin_basename( __FILE__ ))  . '/languages/' );
	}
}

// add action to create kot post type
add_action( 'init', 'gdlr_create_kot' );
if( !function_exists('gdlr_create_kot') ){
	function gdlr_create_kot() {
		global $theme_option;

		if( !empty($theme_option['kot-slug']) ){
			$kot_slug = $theme_option['kot-slug'];
			$kot_category_slug = $theme_option['kot-category-slug'];
			$kot_tag_slug = $theme_option['kot-tag-slug'];
		}else{
			$kot_slug = 'adopt';
			$kot_category_slug = 'kot_category';
			$kot_tag_slug = 'kot_tag';
		}

		register_post_type( 'kot',
			array(
				'labels' => array(
					'name'               => __('Kots', 'gdlr-kot'),
					'singular_name'      => __('Kot', 'gdlr-kot'),
					'add_new'            => __('Add New', 'gdlr-kot'),
					'add_new_item'       => __('Add New Kot', 'gdlr-kot'),
					'edit_item'          => __('Edit Kot', 'gdlr-kot'),
					'new_item'           => __('New Kot', 'gdlr-kot'),
					'all_items'          => __('All Kots', 'gdlr-kot'),
					'view_item'          => __('View Kot', 'gdlr-kot'),
					'search_items'       => __('Search Kot', 'gdlr-kot'),
					'not_found'          => __('No kots found', 'gdlr-kot'),
					'not_found_in_trash' => __('No kots found in Trash', 'gdlr-kot'),
					'parent_item_colon'  => '',
					'menu_name'          => __('Kots', 'gdlr-kot')
				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => $kot_slug  ),
				'capability_type'    => 'post',
				'has_archive'        => false,
				'hierarchical'       => false,
				'menu_position'      => 5,
				'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields' )
			)
		);

		// create kot categories
		register_taxonomy(
			'kot_category', array("kot"), array(
				'hierarchical' => true,
				'show_admin_column' => true,
				'label' => __('Kot Categories', 'gdlr-kot'),
				'singular_label' => __('Kot Category', 'gdlr-kot'),
				'rewrite' => array( 'slug' => $kot_category_slug  )));
		register_taxonomy_for_object_type('kot_category', 'kot');

		// create kot tag
		register_taxonomy(
			'kot_tag', array('kot'), array(
				'hierarchical' => false,
				'show_admin_column' => true,
				'label' => __('Kot Tags', 'gdlr-kot'),
				'singular_label' => __('Kot Tag', 'gdlr-kot'),
				'rewrite' => array( 'slug' => $kot_tag_slug  )));
		register_taxonomy_for_object_type('kot_tag', 'kot');

		// add filter to style single template
		if( defined('WP_THEME_KEY') && WP_THEME_KEY == 'goodlayers' ){
			if( !empty($theme_option['kot-page-style']) && $theme_option['kot-page-style'] == 'blog-style' ){
				add_filter('single_template', 'gdlr_register_kot_blog_template');
			}else{
				add_filter('single_template', 'gdlr_register_kot_template');
			}
		}
	}
}

if( !function_exists('gdlr_register_kot_template') ){
	function gdlr_register_kot_template($single_template) {
		global $post;

		if ($post->post_type == 'kot') {
			$single_template = dirname( __FILE__ ) . '/single-kot.php';
		}
		return $single_template;
	}
}

if( !function_exists('gdlr_register_kot_blog_template') ){
	function gdlr_register_kot_blog_template($single_template) {
		global $post;

		if ($post->post_type == 'kot') {
			$single_template = dirname( __FILE__ ) . '/single-kot-blog.php';
		}
		return $single_template;
	}
}

// add filter for adjacent kot
add_filter('get_previous_post_where', 'gdlr_kot_post_where', 10, 2);
add_filter('get_next_post_where', 'gdlr_kot_post_where', 10, 2);
if(!function_exists('gdlr_kot_post_where')){
	function gdlr_kot_post_where( $where, $in_same_cat ){
		global $post;
		if ( $post->post_type == 'kot' ){
			$current_taxonomy = 'kot_category';
			$cat_array = wp_get_object_terms($post->ID, $current_taxonomy, array('fields' => 'ids'));
			if($cat_array){
				$where .= " AND tt.taxonomy = '$current_taxonomy' AND tt.term_id IN (" . implode(',', $cat_array) . ")";
			}
			}
		return $where;
	}
}

add_filter('get_previous_post_join', 'get_kot_post_join', 10, 2);
add_filter('get_next_post_join', 'get_kot_post_join', 10, 2);
if(!function_exists('get_kot_post_join')){
	function get_kot_post_join($join, $in_same_cat){
		global $post, $wpdb;
		if ( $post->post_type == 'kot' ){
			$current_taxonomy = 'kot_category';
			if(wp_get_object_terms($post->ID, $current_taxonomy)){
				$join .= " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id";
			}
		}
		return $join;
	}
}




?>
