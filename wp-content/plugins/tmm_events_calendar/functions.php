<?php
/**
 * General functions
 */

if (!function_exists('tmm_locate_template')) {
	function tmm_locate_template($path, $data = array(), $echo = true) {
		@extract($data);

		if (!$echo) {
			ob_start();
		}

		include $path;

		if (!$echo) {
			return ob_get_clean();
		}
	}
}

if (!function_exists('tmm_events_get_option')) {
	function tmm_events_get_option($option) {
		if (class_exists('TMM')) {
			return TMM::get_option($option);
		} else {
			return get_option($option);
		}
	}
}

if (!function_exists('tmm_get_weekday')) {
	function tmm_get_weekday($num) {
		$days = array(
			0 => esc_js( __('Sunday', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			1 => esc_js( __('Monday', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			2 => esc_js( __('Tuesday', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			3 => esc_js( __('Wednesday', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			4 => esc_js( __('Thursday', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			5 => esc_js( __('Friday', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			6 => esc_js( __('Saturday', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
		);

		return $days[$num];
	}
}

if (!function_exists('tmm_get_short_weekday')) {
	function tmm_get_short_weekday($num) {
		$days = array(
			0 => esc_js( __('Sun', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			1 => esc_js( __('Mon', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			2 => esc_js( __('Tue', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			3 => esc_js( __('Wed', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			4 => esc_js( __('Thu', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			5 => esc_js( __('Fri', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			6 => esc_js( __('Sat', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
		);

		return $days[$num];
	}
}

if (!function_exists('tmm_get_month_name')) {
	function tmm_get_month_name($num) {
		$months = array(
			1 => esc_js( __("January", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			2 => esc_js( __("February", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			3 => esc_js( __("March", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			4 => esc_js( __("April", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			5 => esc_js( __("May", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			6 => esc_js( __("June", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			7 => esc_js( __("July", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			8 => esc_js( __("August", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			9 => esc_js( __("September", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			10 => esc_js( __("October", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			11 => esc_js( __("November", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			12 => esc_js( __("December", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
		);

		return $months[$num];
	}
}

if (!function_exists('tmm_get_short_month_name')) {
	function tmm_get_short_month_name($num) {
		$months = array(
			1 => esc_js( __('Jan', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			2 => esc_js( __('Feb', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			3 => esc_js( __('Mar', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			4 => esc_js( __('Apr', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			5 => esc_js( __('May', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			6 => esc_js( __('Jun', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			7 => esc_js( __('Jul', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			8 => esc_js( __('Aug', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			9 => esc_js( __('Sep', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			10=> esc_js( __('Oct', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			11 => esc_js( __('Nov', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
			12 => esc_js( __('Dec', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ),
		);

		return $months[$num];
	}
}