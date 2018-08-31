<?php

$args = array(
	'sort_order' => 'ASC',
	'sort_column' => 'post_title',
	'hierarchical' => 0,
	'parent' => -1,
	'post_type' => 'page',
	'post_status' => 'publish'
);
$pages = get_pages($args);

$pages_list = array(
	-1 => __('Select page', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
	0 => __('Events archive', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
);

if ($pages) {

	foreach ( $pages as $item ) {
		$pages_list[$item->ID] = $item->post_title;
	}

}

$content = array(
	'events' => array(
		'title' => '',
		'type' => 'items_block',
		'items' => array(
			'tmm_events_date_format' => array(
				'title' => __('Event Date Format', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'show_title' => true,
				'type' => 'select',
				'default_value' => 0,
				'values' => array(
					0 => __('Month Day, Year', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
					1 => __('Day Month, Year', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				),
				'description' => __('Event date format', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'custom_html' => '',
				'is_reset' => true
			),
			'tmm_events_set_old_to_draft' => array(
				'title' => __('Send Old Events to Draft', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'type' => 'checkbox',
				'default_value' => 0,
				'description' => __('Send old events to draft (only for no repeating events)', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'custom_html' => ''
			)
		)
	),
	'listing_events' => array(
		'title' => __('Listing Page', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
		'type' => 'items_block',
		'items' => array(
			'tmm_events_show_timezone' => array(
				'title' => __('Display Time Zone', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'type' => 'checkbox',
				'default_value' => 0,
				'description' => __('Display time zone next to the time.', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'custom_html' => ''
			),
			'tmm_events_time_format' => array(
				'title' => __('Enable AP Time Style', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'type' => 'checkbox',
				'default_value' => 0,
				'description' => __('Display time in 12 hour format using a.m. or p.m.', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'custom_html' => ''
			),
			'tmm_events_show_duration' => array(
				'title' => __('Display Event Duration', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'type' => 'checkbox',
				'default_value' => 0,
				'description' => __('Display events duration time', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'custom_html' => ''
			),
			'tmm_events_show_venue_website' => array(
				'title' => __('Display Venue Website', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'type' => 'checkbox',
				'default_value' => 0,
				'description' => __('Display event venue website link', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'custom_html' => ''
			),
			'tmm_events_listing_effect' => array(
				'title' => __('Effect for Appearing Post', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'type' => 'select',
				'default_value' => 'none',
				'values' => array(
					'none' => __('None', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
					'elementFade' => __('Element Fade', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
					'opacity' => __('Opacity', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
					'opacity2xRun' => __('Opacity 2x Run', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
					'scale' => __('Scale', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
					'slideRight' => __('Slide Right', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
					'slideLeft' => __('Slide Left', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
					'slideDown' => __('Slide Down', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
					'slideUp' => __('Slide Up', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
					'slideUp2x' => __('Slide Up 2x', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
					'extraRadius' => __('Extra Radius', TMM_EVENTS_PLUGIN_TEXTDOMAIN)
				),
				'description' => __('Effect for Appearing Post.', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'custom_html' => ''
			),
		)
	),
	'single_events' => array(
		'title' => __('Single Page', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
		'type' => 'items_block',
		'items' => array(
			'tmm_single_event_show_timezone' => array(
				'title' => __('Display Time Zone', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'type' => 'checkbox',
				'default_value' => 0,
				'description' => __('Display time zone next to the time.', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'custom_html' => ''
			),
			'tmm_single_event_time_format' => array(
				'title' => __('Enable AP Time Style', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'type' => 'checkbox',
				'default_value' => 0,
				'description' => __('Display time in 12 hour format using a.m. or p.m.', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'custom_html' => ''
			),
			'tmm_single_event_show_duration' => array(
				'title' => __('Display Event Duration', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'type' => 'checkbox',
				'default_value' => 0,
				'description' => __('Display event duration time', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'custom_html' => ''
			),
			'tmm_single_event_show_comments' => array(
				'title' => __('Enable Comments', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'type' => 'checkbox',
				'default_value' => 1,
				'description' => __('Enable comments', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'custom_html' => ''
			),
			'tmm_single_event_title' => array(
				'title' => __('Override Event Page Title', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'type' => 'text',
				'default_value' => __('Events', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'description' => __('Overrides single event page title', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'custom_html' => ''
			),
			'tmm_single_event_button_page' => array(
				'title' => __('Select page for "All Events" button', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'type' => 'select',
				'values' => $pages_list,
				'default_value' => 0,
				'description' => __('Select page for "All Events" button', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'custom_html' => ''
			),
		)
	)
);


$sections = array(
	'name' => __('Events', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
	'css_class' => 'shortcut-events',
	'show_general_page' => true,
	'content' => $content,
	'child_sections' => array(),
	'menu_icon' => 'dashicons-calendar'
);

TMM_OptionsHelper::$sections[ TMM_EVENTS_PLUGIN_TEXTDOMAIN ] = $sections;
