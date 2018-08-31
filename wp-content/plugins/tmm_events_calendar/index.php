<?php
/**
 * Plugin Name: ThemeMakers Events
 * Plugin URI: http://webtemplatemasters.com
 * Description: Events calendar, events list
 * Author: ThemeMakers
 * Author URI: http://themeforest.net/user/ThemeMakers
 * Version: 1.1.0
 * Text Domain: tmm_events
*/

define('TMM_EVENTS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('TMM_EVENTS_PLUGIN_URI', plugin_dir_url(__FILE__));
define('TMM_EVENTS_PLUGIN_TEXTDOMAIN', 'tmm_events');

include_once TMM_EVENTS_PLUGIN_PATH. '/functions.php';

function tmm_events_plugin_autoloader($class) {
	if(file_exists(TMM_EVENTS_PLUGIN_PATH. '/classes/' . $class . '.php')){
		include_once TMM_EVENTS_PLUGIN_PATH. '/classes/' . $class . '.php';
	}
}
spl_autoload_register('tmm_events_plugin_autoloader');


/**
 * Load plugin textdomain.
 */
function tmm_events_load_textdomain() {
	load_plugin_textdomain( 'tmm_events', false, plugin_basename( dirname( __FILE__ ) ) . '/languages' );
}

add_action( 'plugins_loaded', 'tmm_events_load_textdomain' );

add_action("init", array('TMM_EventsPlugin', 'register'));
add_action("admin_init", array('TMM_EventsPlugin', 'admin_init'));
add_action('admin_enqueue_scripts', array('TMM_EventsPlugin', 'admin_head'));
add_action('wp_enqueue_scripts', array('TMM_EventsPlugin', 'wp_head'));
add_action('save_post', array('TMM_Event', 'save_post'));

/* ajax callbacks */
add_action('wp_ajax_nopriv_app_events_get_calendar_data', array('TMM_Event', 'get_calendar_data'));
add_action('wp_ajax_nopriv_app_events_get_widget_calendar_data', array('TMM_Event', 'get_widget_calendar_data'));
add_action('wp_ajax_nopriv_app_events_get_events_listing', array('TMM_Event', 'get_events_listing'));
add_action('wp_ajax_app_events_get_calendar_data', array('TMM_Event', 'get_calendar_data'));
add_action('wp_ajax_app_events_get_widget_calendar_data', array('TMM_Event', 'get_widget_calendar_data'));
add_action('wp_ajax_app_events_get_events_listing', array('TMM_Event', 'get_events_listing'));

/* register widgets */
function tmm_register_events_widgets() {
	register_widget('TMM_SoonestEventWidget');
	register_widget('TMM_EventsCalendarWidget');
	register_widget('TMM_UpcomingEventsWidget');
}
 
add_action( 'widgets_init', 'tmm_register_events_widgets' );

/* on plugin activation */
register_activation_hook( __FILE__, array('TMM_EventsPlugin', 'on_plugin_activation') );

/* add rewrite rules */
add_filter( 'query_vars', array('TMM_EventsPlugin', 'add_event_rewrite_var') );
add_filter( 'init', array('TMM_EventsPlugin', 'add_event_rewrite_rule') );


add_action( 'tmm_add_theme_options_tab', 'tmm_events_add_settings_tab', 1 );
/**
 * Add Settings tab.
 */
function tmm_events_add_settings_tab() {
	if ( current_user_can('manage_options') && class_exists('TMM_OptionsHelper') ) {
		include_once TMM_EVENTS_PLUGIN_PATH . '/views/admin/theme_options_tab.php';
	}
}