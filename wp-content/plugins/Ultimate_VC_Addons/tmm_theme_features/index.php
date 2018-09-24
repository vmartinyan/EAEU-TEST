<?php

/*
 * Plugin Name: ThemeMakers Diplomat Features
 * Plugin URI: http://webtemplatemasters.com
 * Description: Advanced Features for Diplomat Theme
 * Author: ThemeMakers
 * Version: 1.0.3
 * Author URI: http://themeforest.net/user/ThemeMakers
 * Text Domain: tmm_theme_features
*/

class TMM_Theme_Features {


	protected static $instance = null;

	public static $slug = 'tmm_theme_features';

	private function __construct() {

		/* Register custom post types and taxonomies */
		$this->register_post_types();

		/* Add elements to <head> section (tracking code) */
		$this->add_wp_head_elements();
	}

	private function add_wp_head_elements() {

		if (class_exists('TMM')) {
			add_action('wp_head', array(__CLASS__, 'add_tracking_code'), PHP_INT_MAX);
			add_filter('tmm_add_general_theme_option', array(__CLASS__, 'add_tracking_code_option'));
		}

	}

	public static function add_tracking_code_option($options) {

		if (is_array($options)) {
			$options['tracking_code'] = array(
				'title' => __('Tracking Code', self::$slug),
				'type' => 'textarea',
				'default_value' => '',
				'description' => __('Place here your Google Analytics (or other) tracking code. It will be inserted before closing head tag in your theme.', self::$slug),
				'custom_html' => ''
			);
		}

		return $options;
	}

	public static function add_tracking_code() {
		echo TMM::get_option("tracking_code");
	}

	private function register_post_types() {

		/*
		 * Register Slidergroup Post Type
		 */

		if (class_exists('TMM_Slider')) {

			$cpt_name = isset(TMM_Slider::$slug) ? TMM_Slider::$slug : 'slidergroup';

			register_post_type($cpt_name, array(
				'labels' => array(
					'name' => __('Theme Slider', self::$slug),
					'singular_name' => __('Group', self::$slug),
					'add_new' => __('Add New', self::$slug),
					'add_new_item' => __('Add New Slider Group', self::$slug),
					'edit_item' => __('Edit Slider Group', self::$slug),
					'new_item' => __('New Slider Group', self::$slug),
					'view_item' => __('View Slider Group', self::$slug),
					'search_items' => __('Search Slider Groups', self::$slug),
					'not_found' => __('No Slide Groups found', self::$slug),
					'not_found_in_trash' => __('No Slide Groups found in Trash', self::$slug),
					'parent_item_colon' => ''
				),
				'public' => false,
				'archive' => false,
				'exclude_from_search' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'query_var' => true,
				'capability_type' => 'post',
				'has_archive' => false,
				'hierarchical' => true,
				'menu_position' => null,
				'supports' => array('title', 'thumbnail'),
				'rewrite' => array('slug' => $cpt_name),
				'show_in_admin_bar' => false,
				'menu_icon' => 'dashicons-images-alt2',
				'taxonomies' => array()
			));

		}

		/*
		 * Register Position Taxonomy and Staff Post Type
		 */

		if (class_exists('TMM_Staff')) {

			$cpt_name = isset(TMM_Staff::$slug) ? TMM_Staff::$slug : 'staff-page';

			register_taxonomy("position", array($cpt_name), array(
				"hierarchical" => true,
				"labels" => array(
					'name' => __('Position', self::$slug),
					'singular_name' => __('Position', self::$slug),
					'add_new' => __('Add New', self::$slug),
					'add_new_item' => __('Add New Position', self::$slug),
					'edit_item' => __('Edit Position', self::$slug),
					'new_item' => __('New Position', self::$slug),
					'view_item' => __('View Position', self::$slug),
					'search_items' => __('Search GPosition', self::$slug),
					'not_found' => __('No Position found', self::$slug),
					'not_found_in_trash' => __('No Position found in Trash', self::$slug),
					'parent_item_colon' => ''
				),
				"singular_label" => __("Position", self::$slug),
				"rewrite" => true,
				'show_in_nav_menus' => false,
			));

			register_post_type($cpt_name, array(
				'labels' => array(
					'name' => __('Staff', self::$slug),
					'singular_name' => __('Staff', self::$slug),
					'add_new' => __('Add New', self::$slug),
					'add_new_item' => __('Add New Staff', self::$slug),
					'edit_item' => __('Edit Staff', self::$slug),
					'new_item' => __('New Staff', self::$slug),
					'view_item' => __('View Staff', self::$slug),
					'search_items' => __('Search In Staff', self::$slug),
					'not_found' => __('Nothing found', self::$slug),
					'not_found_in_trash' => __('Nothing found in Trash', self::$slug),
					'parent_item_colon' => ''
				),
				'public' => false,
				'archive' => true,
				'exclude_from_search' => false,
				'publicly_queryable' => true,
				'show_ui' => true,
				'query_var' => true,
				'capability_type' => 'post',
				'has_archive' => true,
				'hierarchical' => true,
				'menu_position' => null,
				'supports' => array('title', 'thumbnail', 'excerpt'),
				'rewrite' => array('slug' => $cpt_name),
				'show_in_admin_bar' => true,
				'taxonomies' => array('position'), // this is IMPORTANT
				'menu_icon' => 'dashicons-businessman'
			));

		}

		/*
		 * Register Testimonials Taxonomy and Testimonial Post Type
		 */

		if (class_exists('TMM_Testimonial')) {

			$cpt_name = isset(TMM_Testimonial::$slug) ? TMM_Testimonial::$slug : 'tmonials';

			register_post_type($cpt_name, array(
				'labels' => array(
					'name' => __('Testimonials', self::$slug),
					'singular_name' => __('Testimonial', self::$slug),
					'add_new' => __('Add New', self::$slug),
					'add_new_item' => __('Add New Testimonial', self::$slug),
					'edit_item' => __('Edit Testimonial', self::$slug),
					'new_item' => __('New Testimonial', self::$slug),
					'view_item' => __('View Testimonial', self::$slug),
					'search_items' => __('Search Testimonials', self::$slug),
					'not_found' => __('No Testimonials found', self::$slug),
					'not_found_in_trash' => __('No Testimonials found in Trash', self::$slug),
					'parent_item_colon' => ''
				),
				'public' => false,
				'exclude_from_search' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'query_var' => true,
				'capability_type' => 'post',
				'has_archive' => true,
				'hierarchical' => true,
				'menu_position' => null,
				'supports' => array('title', 'editor', 'thumbnail'),
				'rewrite' => array('slug' => $cpt_name),
				'show_in_admin_bar' => true,
				'menu_icon' => 'dashicons-edit'
			));

		}

		/*
		 * Register Subscriber Post Type
		 */

		if (class_exists('TMM_Mail_Subscription')) {

			$cpt_name = isset(TMM_Mail_Subscription::$slug) ? TMM_Mail_Subscription::$slug : 'email_subscriber';

			register_post_type($cpt_name, array(
				'labels' => array(
					'name' => __('Subscribers', self::$slug),
					'singular_name' => __('Subscriber', self::$slug),
					'add_new' => __('Add New', self::$slug),
					'add_new_item' => __('Add New Subscriber', self::$slug),
					'edit_item' => __('Edit Subscriber', self::$slug),
					'new_item' => __('New Subscriber', self::$slug),
					'view_item' => __('View Subscriber', self::$slug),
					'search_items' => __('Search Subscriber', self::$slug),
					'not_found' => __('No Subscribers found', self::$slug),
					'not_found_in_trash' => __('No Subscribers found in Trash', self::$slug),
					'parent_item_colon' => ''
				),
				'public' => false,
				'exclude_from_search' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'query_var' => true,
				'capability_type' => 'post',
				'has_archive' => false,
				'hierarchical' => true,
				'menu_position' => null,
				'supports' => array('title'),
				'rewrite' => array('slug' => $cpt_name),
				'show_in_admin_bar' => true,
				'menu_icon' => 'dashicons-email'
			));

		}

		/*
		 * Register Gallery Taxonomy and Gallery Post Type
		 */

		if (class_exists('TMM_Gallery')) {

			$cpt_name = isset(TMM_Gallery::$slug) ? TMM_Gallery::$slug : 'gall';

			register_taxonomy("gallery-category", array($cpt_name), array(
				"hierarchical" => true,
				"labels" => array(
					'name' => __('Gallery Categories', self::$slug),
					'singular_name' => __('Gallery Category', self::$slug),
					'add_new' => __('Add New', self::$slug),
					'add_new_item' => __('Add New Gallery Category', self::$slug),
					'edit_item' => __('Edit Gallery Category', self::$slug),
					'new_item' => __('New Gallery Category', self::$slug),
					'view_item' => __('View Gallery Category', self::$slug),
					'search_items' => __('Search Gallery Categories', self::$slug),
					'not_found' => __('No Gallery Categories found', self::$slug),
					'not_found_in_trash' => __('No Gallery Categories found in Trash', self::$slug),
					'parent_item_colon' => ''
				),
				'show_ui' => true,
				'query_var' => true,
				'capability_type' => 'page',
				'has_archive' => true,
				'hierarchical' => true,
				'show_in_admin_bar' => true,
				"rewrite" => true,
				'show_in_nav_menus' => false,
			));

			register_post_type($cpt_name, array(
				'labels' => array(
					'name' => __('Galleries', self::$slug),
					'singular_name' => __('Gallery', self::$slug),
					'add_new' => __('Add New', self::$slug),
					'add_new_item' => __('Add New Gallery', self::$slug),
					'edit_item' => __('Edit Gallery', self::$slug),
					'new_item' => __('New Gallery', self::$slug),
					'view_item' => __('View Gallery', self::$slug),
					'search_items' => __('Search Gallery', self::$slug),
					'not_found' => __('No Galleries found', self::$slug),
					'not_found_in_trash' => __('No Galleries found in Trash', self::$slug),
					'parent_item_colon' => ''
				),
				'public' => true,
				'exclude_from_search' => false,
				'publicly_queryable' => true,
				'show_ui' => true,
				'query_var' => true,
				'capability_type' => 'post',
				'has_archive' => true,
				'hierarchical' => true,
				'menu_position' => null,
				'supports' => array('title', 'thumbnail'),
				'rewrite' => array('slug' => $cpt_name),
				'show_in_admin_bar' => true,
				'menu_icon' => 'dashicons-format-gallery'
			));

		}

		/*
		 * Register Folio, Clients and Skills Taxonomies and Folio Post Type
		 */

		if (class_exists('TMM_Portfolio')) {

			$cpt_name = isset(TMM_Portfolio::$slug) ? TMM_Portfolio::$slug : 'folio';

			register_taxonomy("clients", array($cpt_name), array(
				"hierarchical" => true,
				"labels" => array(
					'name' => __('Clients', self::$slug),
					'singular_name' => __('Client', self::$slug),
					'add_new' => __('Add New', self::$slug),
					'add_new_item' => __('Add New Client', self::$slug),
					'edit_item' => __('Edit Client', self::$slug),
					'new_item' => __('New Client', self::$slug),
					'view_item' => __('View Client', self::$slug),
					'search_items' => __('Search Clients', self::$slug),
					'not_found' => __('No Clients found', self::$slug),
					'not_found_in_trash' => __('No Clients found in Trash', self::$slug),
					'parent_item_colon' => ''
				),
				"rewrite" => true,
				'show_in_nav_menus' => false,
				'capabilities' => array('manage_terms'),
				'show_ui' => true
			));

			register_taxonomy("skills", array($cpt_name), array(
				"hierarchical" => true,
				"labels" => array(
					'name' => __('Skills', self::$slug),
					'singular_name' => __('Skill', self::$slug),
					'add_new' => __('Add New', self::$slug),
					'add_new_item' => __('Add New Skill', self::$slug),
					'edit_item' => __('Edit Skill', self::$slug),
					'new_item' => __('New Skill', self::$slug),
					'view_item' => __('View Skill', self::$slug),
					'search_items' => __('Search Skills', self::$slug),
					'not_found' => __('No Skills found', self::$slug),
					'not_found_in_trash' => __('No Skills found in Trash', self::$slug),
					'parent_item_colon' => ''
				),
				"show_tagcloud" => true,
				'query_var' => true,
				'rewrite' => true,
				'show_in_nav_menus' => false,
				'capabilities' => array('manage_terms'),
				'show_ui' => true
			));

			register_taxonomy("folio-category", array($cpt_name), array(
				"hierarchical" => true,
				"labels" => array(
					'name' => __('Categories', self::$slug),
					'singular_name' => __('Category', self::$slug),
					'add_new' => __('Add New', self::$slug),
					'add_new_item' => __('Add New Category', self::$slug),
					'edit_item' => __('Edit Category', self::$slug),
					'new_item' => __('New Category', self::$slug),
					'view_item' => __('View Category', self::$slug),
					'search_items' => __('Search Categories', self::$slug),
					'not_found' => __('No Categories found', self::$slug),
					'not_found_in_trash' => __('No Categories found in Trash', self::$slug),
					'parent_item_colon' => ''
				),
				"show_tagcloud" => true,
				'query_var' => true,
				'rewrite' => true,
				'show_in_nav_menus' => false,
				'capabilities' => array('manage_terms'),
				'show_ui' => true
			));

			register_post_type($cpt_name, array(
				'labels' => array(
					'name' => __('Portfolios', self::$slug),
					'singular_name' => __('Portfolio', self::$slug),
					'add_new' => __('Add New', self::$slug),
					'add_new_item' => __('Add New Portfolio', self::$slug),
					'edit_item' => __('Edit Portfolio', self::$slug),
					'new_item' => __('New Portfolio', self::$slug),
					'view_item' => __('View Portfolio', self::$slug),
					'search_items' => __('Search Portfolios', self::$slug),
					'not_found' => __('No Portfolios found', self::$slug),
					'not_found_in_trash' => __('No Portfolios found in Trash', self::$slug),
					'parent_item_colon' => ''
				),
				'public' => true,
				'archive' => true,
				'exclude_from_search' => false,
				'publicly_queryable' => true,
				'show_ui' => true,
				'query_var' => true,
				'capability_type' => 'post',
				'has_archive' => true,
				'hierarchical' => true,
				'menu_position' => null,
				'supports' => array('title', 'editor', 'thumbnail', 'excerpt', 'tags', 'comments'),
				'rewrite' => array('slug' => $cpt_name),
				'show_in_admin_bar' => true,
				'show_in_menu' => true,
				'taxonomies' => array('clients', 'skills', 'post_tag'), // this is IMPORTANT
				'menu_icon' => 'dashicons-portfolio'
			));

		}

	}

	public static function flush_rewrite_rules() {

		self::get_instance();
		flush_rewrite_rules();
	}

	private function __clone() {

	}

	public static function get_instance() {

		if ( self::$instance === null) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	public static function load_plugin_textdomain() {

		load_plugin_textdomain( 'tmm_theme_features', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
	}
}

add_action( 'plugins_loaded', array('TMM_Theme_Features', 'load_plugin_textdomain') );

add_action( 'init', array('TMM_Theme_Features', 'get_instance') );

register_activation_hook( __FILE__, array('TMM_Theme_Features', 'flush_rewrite_rules') );
