<?php
/*
Plugin Name: Goodlayers Cause Post Type
Plugin URI: 
Description: A Custom Post Type Plugin To Use With Goodlayers Theme ( This plugin functionality might not working properly on another theme )
Version: 1.0.0
Author: Goodlayers
Author URI: http://www.goodlayers.com
License: 
*/
include_once( 'gdlr-cause-item.php');	
include_once( 'gdlr-cause-option.php');		

// action to loaded the plugin translation file
add_action('plugins_loaded', 'gdlr_cause_init');
if( !function_exists('gdlr_cause_init') ){
	function gdlr_cause_init() {
		load_plugin_textdomain( 'gdlr-cause', false, dirname(plugin_basename( __FILE__ ))  . '/languages/' ); 
	}
}

// add action to create cause post type
add_action( 'init', 'gdlr_create_cause' );
if( !function_exists('gdlr_create_cause') ){
	function gdlr_create_cause() {
		global $theme_option;
		
		if( !empty($theme_option['cause-slug']) ){
			$cause_slug = $theme_option['cause-slug'];
			$cause_category_slug = $theme_option['cause-category-slug'];
		}else{
			$cause_slug = 'cause';
			$cause_category_slug = 'cause_category';
		}
		
		register_post_type( 'cause',
			array(
				'labels' => array(
					'name'               => __('Causes', 'gdlr-cause'),
					'singular_name'      => __('Cause', 'gdlr-cause'),
					'add_new'            => __('Add New', 'gdlr-cause'),
					'add_new_item'       => __('Add New Cause', 'gdlr-cause'),
					'edit_item'          => __('Edit Cause', 'gdlr-cause'),
					'new_item'           => __('New Cause', 'gdlr-cause'),
					'all_items'          => __('All Causes', 'gdlr-cause'),
					'view_item'          => __('View Cause', 'gdlr-cause'),
					'search_items'       => __('Search Cause', 'gdlr-cause'),
					'not_found'          => __('No causes found', 'gdlr-cause'),
					'not_found_in_trash' => __('No causes found in Trash', 'gdlr-cause'),
					'parent_item_colon'  => '',
					'menu_name'          => __('Causes', 'gdlr-cause')
				),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => $cause_slug  ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => 5,
				'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments', 'custom-fields' )
			)
		);
		
		// create cause categories
		register_taxonomy(
			'cause_category', array("cause"), array(
				'hierarchical' => true,
				'show_admin_column' => true,
				'label' => __('Cause Categories', 'gdlr-cause'), 
				'singular_label' => __('Cause Category', 'gdlr-cause'), 
				'rewrite' => array( 'slug' => $cause_category_slug  )));
		register_taxonomy_for_object_type('cause_category', 'cause');

		// add filter to style single template
		if( defined('WP_THEME_KEY') && WP_THEME_KEY == 'goodlayers' ){
			add_filter('single_template', 'gdlr_register_cause_template');
		}
		
		// add hook to save page options
		add_action('pre_post_update', 'gdlr_save_cause_meta_option');
	}
}

if( !function_exists('gdlr_register_cause_template') ){
	function gdlr_register_cause_template($single_template) {
		global $post;

		if ($post->post_type == 'cause') {
			$single_template = dirname( __FILE__ ) . '/single-cause.php';
		}
		return $single_template;	
	}
}

// add filter for adjacent cause 
add_filter('get_previous_post_where', 'gdlr_cause_post_where', 10, 2);
add_filter('get_next_post_where', 'gdlr_cause_post_where', 10, 2);
if(!function_exists('gdlr_cause_post_where')){
	function gdlr_cause_post_where( $where, $in_same_cat ){ 
		global $post; 
		if ( $post->post_type == 'cause' ){
			$current_taxonomy = 'cause_category'; 
			$cat_array = wp_get_object_terms($post->ID, $current_taxonomy, array('fields' => 'ids')); 
			if($cat_array){ 
				$where .= " AND tt.taxonomy = '$current_taxonomy' AND tt.term_id IN (" . implode(',', $cat_array) . ")"; 
			} 
			}
		return $where; 
	} 	
}
	
add_filter('get_previous_post_join', 'get_cause_post_join', 10, 2);
add_filter('get_next_post_join', 'get_cause_post_join', 10, 2);	
if(!function_exists('get_cause_post_join')){
	function get_cause_post_join( $join, $in_same_cat ){ 
		global $post, $wpdb; 
		if ( $post->post_type == 'cause' ){
			$current_taxonomy = 'cause_category'; 
			if(wp_get_object_terms($post->ID, $current_taxonomy)){ 
				$join .= " INNER JOIN $wpdb->term_relationships AS tr ON p.ID = tr.object_id INNER JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id"; 
			} 
		}
		return $join; 
	}
}

if( !function_exists('gdlr_save_cause_meta_option') ){
	function gdlr_save_cause_meta_option( $post_id ){
		if( get_post_type() == 'cause' && isset($_POST['post-option']) ){
			$post_option = gdlr_preventslashes(gdlr_stripslashes($_POST['post-option']));
			$event_option = json_decode(gdlr_decode_preventslashes($post_option), true);
			
			if(!empty($event_option['current-funding'])){
				update_post_meta($post_id, 'gdlr-current-funding', $event_option['current-funding']);
			}
			
			if(!empty($event_option['goal-of-donation'])){
				$goal = floatval($event_option['goal-of-donation']);
				$current = floatval($event_option['current-funding']);			
			
				$percent = intval(($current / $goal)*100); 
				update_post_meta($post_id, 'gdlr-donation-percent', $percent);
			}
		}
	}
}

?>