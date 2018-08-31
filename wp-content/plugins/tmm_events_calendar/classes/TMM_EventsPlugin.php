<?php

/* 
 * Events Plugin
 */

class TMM_EventsPlugin {
	public static function register() {
		
		TMM_Event::init();

		/* Register custom post type and taxonomy */
		$args = array(
			'labels' => array(
				'name' => __('Events', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'singular_name' => __('Event', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'add_new' => __('Add New', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'add_new_item' => __('Add New Event', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'edit_item' => __('Edit Event', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'new_item' => __('New Event', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'view_item' => __('View Event', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'search_items' => __('Search In Events', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'not_found' => __('Nothing found', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'not_found_in_trash' => __('Nothing found in Trash', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
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
			'rewrite' => array('slug' => 'event'),
			'show_in_admin_bar' => true,
			'taxonomies' => array('events-categories'), //this is IMPORTANT
			'menu_icon' => 'dashicons-calendar'
		);

		register_post_type('event', $args);

		$args = array(
			"hierarchical" => true,
			"labels" => array(
				'name' => __('Events Categories', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'singular_name' => __('Event category', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'add_new' => __('Add New', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'add_new_item' => __('Add New Event category', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'edit_item' => __('Edit Event category', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'new_item' => __('New Event category', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'view_item' => __('View Event category', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'search_items' => __('Search Events categories', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'not_found' => __('No Events categories found', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'not_found_in_trash' => __('No Events categories found in Trash', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
				'parent_item_colon' => ''
			),
			"singular_label" => __("Events", TMM_EVENTS_PLUGIN_TEXTDOMAIN),
			'public' => true,
			"show_tagcloud" => true,
			'query_var' => true,
			"rewrite" => true,
			'show_in_nav_menus' => true,
			'capabilities' => array('manage_terms'),
			'show_ui' => true
		);

		register_taxonomy("events-categories", array("event"), $args);

		/* Manage table columns content in /wp-admin/edit.php?post_type=event */
		add_filter("manage_event_posts_columns", array('TMM_Event', "show_edit_columns"));
		add_action("manage_event_posts_custom_column", array('TMM_Event', "show_edit_columns_content"));

		add_filter("manage_edit-event_sortable_columns", array('TMM_Event', "event_sortable_columns"));
		add_action('pre_get_posts', array('TMM_Event', "event_column_orderby"));

		/* Breadcrumbs hook */
		add_action( 'tmm_breadcrumbs_category_item', array(__CLASS__, 'modify_breadcrumbs'), 10 , 1 );
		add_action( 'tmm_breadcrumbs_archive_item', array(__CLASS__, 'modify_breadcrumbs'), 10 , 1 );

		/* Include events templates */
		add_filter( 'template_include', array( __CLASS__, 'template_loader' ) );

		/* Navigation menu fix with adding current page class to 'page_for_posts' on custom posts */
		add_filter( 'nav_menu_css_class', array( __CLASS__, 'fix_blog_menu_css_class'), 10, 2 );

		/* Add meta boxes */
		add_action( 'add_meta_boxes', array(__CLASS__, 'add_meta_boxes') );

		/* Add cron schedule event */
		if(class_exists('TMM')){
			$events_set_old_ev_to_draft = tmm_events_get_option("tmm_events_set_old_to_draft");
			if ($events_set_old_ev_to_draft) {
				add_action('old_events_schedules', array(__CLASS__, 'old_events_schedules'));
				if (!wp_next_scheduled('old_events_schedules')) {
					wp_schedule_event(time(), 'hourly', 'old_events_schedules');
				}
			} else {
				wp_clear_scheduled_hook('old_events_schedules');
			}
		}

	}

	public static function template_loader($template) {
		$queried_object = get_queried_object();

		if ( get_post_type() === 'event' || (isset($queried_object->taxonomy) && $queried_object->taxonomy === 'events-categories') ) {

			if (is_single()) {
				$template = TMM_EVENTS_PLUGIN_PATH . 'views/templates/single-event.php';
			}

			if (is_archive()) {
				// event category or date archive
				$template = TMM_EVENTS_PLUGIN_PATH . 'views/templates/taxonomy-events-categories.php';
			}

		}

		return $template;

	}

	public static function fix_blog_menu_css_class( $classes, $item ) {
		if ( is_tax( 'events-categories' ) || is_singular( 'event' ) || is_post_type_archive( 'event' ) ) {
			if ( $item->object_id == get_option('page_for_posts') ) {
				$key = array_search( 'current_page_parent', $classes );
				if ( false !== $key )
					unset( $classes[ $key ] );
			} else if ( $item->object_id == tmm_events_get_option('tmm_single_event_button_page') ) {
				if (!in_array('current_page_parent', $classes)) {
					$classes[] = 'current_page_parent';
				}
			}
		}

		return $classes;
	}

	public static function add_meta_boxes() {

		if (class_exists('TMM_Page')) {
			add_meta_box("seo_options", __("Seo options", TMM_EVENTS_PLUGIN_TEXTDOMAIN), array('TMM_Page', 'page_seo_options'), "event", "normal", "low");
		}

	}

	public static function modify_breadcrumbs($is_link = true) {
		$breadcrumb_html = '';
		$queried_object = get_queried_object();

		if (is_singular('event')) {
			global $post;
			$categories = get_the_terms($post->ID, 'events-categories');

			if (is_array($categories)) {
				foreach ($categories as $term) {
					$categories = $term;
					break;
				}

				$breadcrumb = array(
					'href' => esc_url(get_term_link( $categories->term_id, 'events-categories' )),
					'text' => $categories->name,
					'title' => esc_attr(__("View all posts in $categories->name", TMM_EVENTS_PLUGIN_TEXTDOMAIN)),
				);

				if ($is_link) {
					$breadcrumb_html .= '<a href="' . $breadcrumb['href'] . '" title="' . $breadcrumb['title'] . '">'; '</a> ';
				}

				$breadcrumb_html .= $breadcrumb['text'];

				if ($is_link) {
					$breadcrumb_html .= '</a> ';
				}

			}
		} else if (is_tax( 'events-categories' )) {

			if (is_object($queried_object) && !empty($queried_object->name)) {
				$breadcrumb_html .= $queried_object->name;
			}

		} if (is_post_type_archive('event')) {
			global $wp_query;

			if (isset($_GET['date']) || isset($wp_query->query_vars['date'])) {

				if (isset($_GET['date'])) {
					$tmp_date = explode('-', $_GET['date']);
				} else if (isset($wp_query->query_vars['date'])) {
					$tmp_date = explode('-', $wp_query->query_vars['date']);
				}

				if (is_array($tmp_date) && !empty($tmp_date[0]) && !empty($tmp_date[1]) && !empty($tmp_date[2])) {
					if(tmm_events_get_option('tmm_events_date_format') === '1'){
						$day = $tmp_date[0];
						$month = $tmp_date[1];
					}else{
						$day = $tmp_date[1];
						$month = $tmp_date[0];
					}

					$year = (int) $tmp_date[2];

					$breadcrumb_html .= mysql2date( get_option( 'date_format' ), $year.'-'.$month.'-'.$day );
				}

			} else {
				$breadcrumb_html .= $queried_object->label;
			}

		}

		echo $breadcrumb_html;
	}

    public static function on_plugin_activation() {
		self::register();
	    self::add_event_rewrite_rule();
		flush_rewrite_rules();
	}

	public static function add_event_rewrite_var( $vars ) {
		$vars[] = 'date';
		return $vars;
	}

	public static function add_event_rewrite_rule(){

		add_rewrite_rule(
			'^event/([^/]*)/date/([^/]*)/?',
			'index.php?event=$matches[1]&date=$matches[2]',
			'top'
		);

		add_rewrite_rule(
			'^event/date/([^/]*)/?',
			'index.php?post_type=event&date=$matches[1]',
			'top'
		);

		add_rewrite_tag( '%date%', '([^&]+)' );
	}
	
	public static function admin_head() {
		wp_enqueue_style('events_css', TMM_EVENTS_PLUGIN_URI . 'css/jquery-ui/jquery-ui.min.css');
		wp_enqueue_script('events_js', TMM_EVENTS_PLUGIN_URI . 'js/admin.js');
		?>
		<script type="text/javascript">
			var error_fetching_events = "<?php echo esc_js( __("There was an error while fetching events!", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ); ?>";
			var lang_time = "<?php echo esc_js( __("Time", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ); ?>";
			var lang_place = "<?php echo esc_js( __("Place", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ); ?>";
		</script>
		<?php
	}

	public static function wp_head() {
		wp_enqueue_style('events_css', TMM_EVENTS_PLUGIN_URI . 'css/styles.css');
		wp_enqueue_style("events_calendar_css", TMM_EVENTS_PLUGIN_URI . 'css/calendar.css');

		wp_enqueue_script('events_calendar_js', TMM_EVENTS_PLUGIN_URI . 'js/front.min.js', array(), false, 1);
		?>

		<script type="text/javascript">
			var tmm_lang_no_events = "<?php echo esc_js( __("No events at this period!", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ); ?>";
			
			var lang_january =  "<?php echo tmm_get_month_name(1); ?>";
			var lang_february = "<?php echo tmm_get_month_name(2); ?>";
			var lang_march =    "<?php echo tmm_get_month_name(3); ?>";
			var lang_april =    "<?php echo tmm_get_month_name(4); ?>";
			var lang_may =      "<?php echo tmm_get_month_name(5); ?>";
			var lang_june =     "<?php echo tmm_get_month_name(6); ?>";
			var lang_july =     "<?php echo tmm_get_month_name(7); ?>";
			var lang_august =   "<?php echo tmm_get_month_name(8); ?>";
			var lang_september = "<?php echo tmm_get_month_name(9); ?>";
			var lang_october =  "<?php echo tmm_get_month_name(10); ?>";
			var lang_november = "<?php echo tmm_get_month_name(11); ?>";
			var lang_december = "<?php echo tmm_get_month_name(12); ?>";

			var lang_sunday =   "<?php echo tmm_get_weekday(0); ?>";
			var lang_monday =   "<?php echo tmm_get_weekday(1); ?>";
			var lang_tuesday =  "<?php echo tmm_get_weekday(2); ?>";
			var lang_wednesday = "<?php echo tmm_get_weekday(3); ?>";
			var lang_thursday = "<?php echo tmm_get_weekday(4); ?>";
			var lang_friday =   "<?php echo tmm_get_weekday(5); ?>";
			var lang_saturday = "<?php echo tmm_get_weekday(6); ?>";

			var lang_sun = "<?php echo tmm_get_short_weekday(0); ?>";
			var lang_mon = "<?php echo tmm_get_short_weekday(1); ?>";
			var lang_tue = "<?php echo tmm_get_short_weekday(2); ?>";
			var lang_wed = "<?php echo tmm_get_short_weekday(3); ?>";
			var lang_thu = "<?php echo tmm_get_short_weekday(4); ?>";
			var lang_fri = "<?php echo tmm_get_short_weekday(5); ?>";
			var lang_sat = "<?php echo tmm_get_short_weekday(6); ?>";

			var lang_today = "<?php echo esc_js( __("today", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ); ?>";
			var lang_month = "<?php echo esc_js( __("month", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ); ?>";
			var lang_week = "<?php echo esc_js( __("week", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ); ?>";
			var lang_day = "<?php echo esc_js( __("day", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ); ?>";
			var lang_time = "<?php echo esc_js( __("Time", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ); ?>";
			var lang_place = "<?php echo esc_js( __("Place", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ); ?>";
			var error_fetching_events = "<?php echo esc_js( __("There was an error while fetching events!", TMM_EVENTS_PLUGIN_TEXTDOMAIN) ); ?>";

			var events_time_format = "<?php echo (int) tmm_events_get_option("tmm_events_time_format"); ?>";
			var events_date_format = <?php echo (int) tmm_events_get_option('tmm_events_date_format'); ?>;
		</script>

		<?php
	}

	public static function admin_init() {
		add_meta_box("event_attributes", __("Event attributes", TMM_EVENTS_PLUGIN_TEXTDOMAIN), array(__CLASS__, 'event_attributes'), "event", "normal", "low");
	}
	
	public static function event_attributes() {
		global $post;
		$now = date('d-m-Y');
		$data = array();
		$custom = get_post_custom($post->ID);
		$data['event_date'] = !empty($custom) ? $custom['event_date'][0] : $now;
		$data['event_end_date'] = !empty($custom) ? $custom['event_end_date'][0] : $now;

		$data['event_hh'] = !empty($custom) ? $custom['event_hh'][0] : 12;
		$data['event_mm'] = (!empty($custom) && isset($custom['event_mm'])) ? $custom['event_mm'][0] : 0;
		$data['event_end_hh'] = !empty($custom) ? $custom['event_end_hh'][0] : 12;
		$data['event_end_mm'] = (!empty($custom) && isset($custom['event_end_mm'])) ? $custom['event_end_mm'][0] : 0;

		$data['event_repeating'] = !empty($custom) ? $custom['event_repeating'][0] : 'no';
		$data['event_repeating_week'] = (!empty($custom) && isset($custom['event_repeating_week'][0])) ? $custom['event_repeating_week'][0] : '';

		$data['hide_event_place'] = (!empty($custom) && isset($custom['hide_event_place'])) ? $custom['hide_event_place'][0] : 1;
		$data['event_allday'] = (!empty($custom) && isset($custom['event_allday'])) ? $custom['event_allday'][0] : 0;
		$data['event_place_address'] = (!empty($custom) && isset($custom['event_place_address'])) ? $custom['event_place_address'][0] : '';
		$data['event_place_phone'] = (!empty($custom) && isset($custom['event_place_phone'])) ? $custom['event_place_phone'][0] : '';
		$data['event_place_website'] = (!empty($custom) && isset($custom['event_place_website'])) ? $custom['event_place_website'][0] : '';
		$data['event_organizer_phone'] = (!empty($custom) && isset($custom['event_organizer_phone'])) ? $custom['event_organizer_phone'][0] : '';
		$data['event_organizer_website'] = (!empty($custom) && isset($custom['event_organizer_website'])) ? $custom['event_organizer_website'][0] : '';
		$data['event_organizer_name'] = (!empty($custom) && isset($custom['event_organizer_name'])) ? $custom['event_organizer_name'][0] : '';
		$data['event_map_zoom'] = !empty($custom) ? $custom['event_map_zoom'][0] : 14;
		$data['event_map_latitude'] = !empty($custom) ? $custom['event_map_latitude'][0] : '40.714623';
		$data['event_map_longitude'] = !empty($custom) ? $custom['event_map_longitude'][0] : '-74.006605';
		
		wp_enqueue_script('jquery-ui-datepicker');
		tmm_locate_template(TMM_EVENTS_PLUGIN_PATH . '/views/admin/event_attributes.php', $data);
	}

	/**
	 * Include shortcode template
	 *
	 * @param string $name Shortcode name ('events_list' , 'events_calendar')
	 * @param array $args Optional. Additional params
	 */
	public static function get_shortcode_template($name, $args = array()) {
		if (is_array($args)) extract($args);
		include TMM_EVENTS_PLUGIN_PATH . 'views/shortcodes/'.$name.'.php';
	}
	
	/* CRON scheduling for old events */
	public static function old_events_schedules() {
		global $wpdb;
		$now = time();

		$meta_query_array = array();

		$meta_query_array[] = array(
			'key' => 'event_repeating',
			'value' => 'no_repeat',
			'compare' => '='
		);

		$meta_query_array[] = array(
			'key' => 'ev_end_mktime',
			'value' => $now,
			'type' => 'numeric',
			'compare' => '<'
		);


		$args = array(
			'post_type' => 'event',
			'meta_query' => $meta_query_array,
			'post_status' => array('publish'),
			'posts_per_page' => -1,
		);
		$query = new WP_Query($args);
		$posts = $wpdb->get_results($query->request, ARRAY_A);

		if (!empty($posts)) {
			foreach ($posts as $post) {
				$wpdb->query("UPDATE {$wpdb->posts} SET post_status='draft' WHERE post_type='event' AND ID=" . $post['ID']);
			}
		}
	}
}