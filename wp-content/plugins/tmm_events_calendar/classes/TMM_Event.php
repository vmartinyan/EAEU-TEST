<?php

class TMM_Event {

	public static $event_repeatings = array();
	public static $gmt_offset = "";

	public static function init() {
		self::$gmt_offset = get_option('gmt_offset');

		self::$event_repeatings = array(
			'no_repeat' => __('No repeating', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
			'week' => __('Week', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
			'2week' => __('2 weeks', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
			'3week' => __('3 weeks', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
			'month' => __('Month', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
			'year' => __('Year', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
		);
	}

	public static function save_post($data = array()) {
		if ( (!empty($data) && isset($data['post_id'])) || (!empty($_POST) && isset($_POST['thememakers_meta_saving'])) ) {

			if(!empty($data) && isset($data['post_id'])){
				$post_id = $data['post_id'];
			}else{
				global $post;
				$post_id = $post->ID;
			}

			$post_type = get_post_type($post_id);
			if ($post_type == 'event') {

				$fields = array(
					'event_date' => '',
					'event_end_date' => '',
					'event_hh' => 0,
					'event_mm' => 0,
					'event_end_hh' => 0,
					'event_end_mm' => 0,
					'event_repeating' => '',
					'event_repeating_week' => '',
					'hide_event_place' => 1,
					'event_allday' => 0,
					'event_place_address' => '',
					'event_place_phone' => '',
					'event_place_website' => '',
					'event_organizer_phone' => '',
					'event_organizer_website' => '',
					'event_organizer_name' => '',
					'event_map_zoom' => '',
					'event_map_latitude' => '',
					'event_map_longitude' => '',
					'google_calendar_event_id' => false,
				);

				foreach($fields as $key => &$value){
					$temp = $value;
					if(isset($_POST[$key])){
						$temp = $_POST[$key];
					}else if(isset($data[$key]) && !empty($data[$key])){
						$temp = $data[$key];
					}
					$value = is_numeric($value) ? (int) $temp : $temp;
					update_post_meta($post_id, $key, $value);
				}

				if(!empty($fields['event_date']) || !empty($fields['event_end_date'])){
					if(!empty($fields['event_date'])){
						$date = explode("-", $fields['event_date']);
						$event_mktime = strtotime($date[0] . '-' . $date[1] . '-' . $date[2] . " " . $fields['event_hh'] . ":" . $fields['event_mm']);
						update_post_meta($post_id, "ev_mktime", $event_mktime);
					}
					if(!empty($fields['event_end_date'])){
						$date_end = explode("-", $fields['event_end_date']);
						$event_end_mktime = strtotime($date_end[0] . '-' . $date_end[1] . '-' . $date_end[2] . " " . $fields['event_end_hh'] . ":" . $fields['event_end_mm']);
						update_post_meta($post_id, "ev_end_mktime", $event_end_mktime);
					}
				}

				if (isset($_POST["meta_title"])) {
					update_post_meta($post_id, "meta_title", $_POST["meta_title"]);
				}

				if (isset($_POST["meta_keywords"])) {
					update_post_meta($post_id, "meta_keywords", $_POST["meta_keywords"]);
				}

				if (isset($_POST["meta_description"])) {
					update_post_meta($post_id, "meta_description", $_POST["meta_description"]);
				}

			}
		}
	}

	public static function show_edit_columns_content($column) {
		global $post;

        $ev_mktime = (int) get_post_meta($post->ID, 'ev_mktime', true);
        $ev_end_mktime = (int) get_post_meta($post->ID, 'ev_end_mktime', true);
        $event_duration_sec = TMM_Event::get_event_duration($ev_mktime, $ev_end_mktime);

		switch ($column) {
			case "place":
				echo "<h3>" . get_post_meta($post->ID, 'event_place_address', true) . "</h3>";
				$lat = get_post_meta($post->ID, 'event_map_latitude', true);
				$lng = get_post_meta($post->ID, 'event_map_longitude', true);
				echo '<img src="https://maps.googleapis.com/maps/api/staticmap?center=' . $lat . ',' . $lng . '&zoom=' . get_post_meta($post->ID, 'event_map_zoom', true) . '&size=300x200&markers=color:red|label:P|' . $lat . ',' . $lng . '&sensor=false" style="max-width:100%">';
				break;
			case "description":
				the_excerpt();
				break;
			case "ev_mktime":
				$repeats_every = get_post_meta($post->ID, 'event_repeating', true);
				$ev_days = self::get_event_days($post->ID);
                $ev_date = self::get_event_date($ev_mktime);
                $ev_end_date = self::get_event_date($ev_end_mktime);
                if ($ev_date != $ev_end_date || $repeats_every != "no_repeat"){
                    $ev_date .= ' - ' . $ev_end_date;
                }
				$event_start_time = self::get_event_time($ev_mktime, true);
				$event_end_time = self::get_event_time($ev_end_mktime, true);
                ?>

                <div><strong><?php echo $ev_days; ?></strong></div>
                <div>
                    <strong>
                        <?php echo $event_start_time . ' - ' . $event_end_time; ?>
                        <span class="zones"><?php echo self::get_timezone_string(); ?></span>
                    </strong>
                </div>

                <div>(<?php echo $ev_date; ?>)</div>

                <?php
				break;
			case "event_repeating":
				$current_event_repeating = get_post_meta($post->ID, 'event_repeating', true);
				if(!empty($current_event_repeating)){
					echo self::$event_repeatings[$current_event_repeating];
				}
				break;
			case "ev_duration":
                $hh = $event_duration_sec[0];
				$mm = $event_duration_sec[1];
				echo '<i>' . $hh . ":" . $mm . '</i>';
				break;
			case "ev_cat":
				echo get_the_term_list($post->ID, 'events-categories', '', ', ', '');
				break;
		}
	}

	public static function show_edit_columns($columns) {
		$columns = array(
			"cb" => "<input type=\"checkbox\" />",
			"title" => __("Title", TMM_EVENTS_PLUGIN_TEXTDOMAIN),
			"place" => __("Place", TMM_EVENTS_PLUGIN_TEXTDOMAIN),
			"description" => __("Description", TMM_EVENTS_PLUGIN_TEXTDOMAIN),
			"ev_mktime" => __("Date", TMM_EVENTS_PLUGIN_TEXTDOMAIN),
			"ev_duration" => __("Duration", TMM_EVENTS_PLUGIN_TEXTDOMAIN),
			"ev_cat" => __("Category", TMM_EVENTS_PLUGIN_TEXTDOMAIN),
			"event_repeating" => __("Repeating", TMM_EVENTS_PLUGIN_TEXTDOMAIN),
		);

		return $columns;
	}

	public static function event_sortable_columns($columns) {
		$columns['ev_mktime'] = 'ev_mktime';
		return $columns;
	}

	public static function event_column_orderby($query) {
		if (!is_admin())
			return;

		$orderby = $query->get('orderby');

		if ('ev_mktime' == $orderby) {
			$query->set('meta_key', 'ev_mktime');
			$query->set('orderby', 'ev_mktime');
		}

		return $query;
	}

	public static function get_events($start, $end, $category = 0) {
		global $wpdb;
		$start = (int) $start;
		$end = (int) $end;
		$category = explode(',', $category);
		$category = array_map('intval', $category);
		$category = implode(',', $category);

		$result = $wpdb->get_results("
			SELECT SQL_CALC_FOUND_ROWS  p.ID , p.post_title, p.post_excerpt
			FROM {$wpdb->posts} p ".
			($category > 0 ? "INNER JOIN {$wpdb->term_relationships} tr ON (p.ID = tr.object_id) " : "")
			."INNER JOIN {$wpdb->postmeta}  pm
				ON (p.ID = pm.post_id) 
			INNER JOIN {$wpdb->postmeta} AS mt1 
				ON (p.ID = mt1.post_id) 
			WHERE ".
				($category > 0 ? "tr.term_taxonomy_id IN ( {$category} ) " : "1=1 ")
				."
				AND p.post_type = 'event'
				AND ((p.post_status = 'publish'))
				AND (
					(pm.meta_key = 'event_repeating' AND CAST(pm.meta_value AS CHAR) != 'no_repeat')
					OR
					(mt1.meta_key = 'ev_end_mktime' AND CAST(mt1.meta_value AS CHAR) > '{$start}')
				) 
			GROUP BY p.ID 
			ORDER BY p.post_date DESC
		", OBJECT_K);

		return self::filter_events($result, $start, $end);
	}

	public static function filter_events($result, $start, $end){
		$data = array();
		$current_year = (int) date('Y', $start);
		$current_month = (int) date('m', $start) + 1;

		if (!empty($result)) {
			foreach ($result as $post) {
				$post->ID = (int) $post->ID;
				$events_data = array();
				$post_meta = get_post_meta($post->ID);
				$start_date = (int) $post_meta['ev_mktime'][0];
				$end_date = (int) $post_meta['ev_end_mktime'][0];
				$place_address = $post_meta['event_place_address'][0];
				$repeating = $post_meta['event_repeating'][0];
				$duration_sec = TMM_Event::get_event_duration($start_date, $end_date);
				$duration_sec = $duration_sec[2];

				/* if current post is a duplicate of original post and WPML is not active */
				if ( !defined( 'ICL_LANGUAGE_CODE' ) && isset($post_meta['_icl_lang_duplicate_of'][0]) ) {
					continue;
				}

				/* if current post is an original post but current language is not default (WPML is active) */
				if ( defined('ICL_LANGUAGE_CODE') && ICL_LANGUAGE_CODE != '' ) {
					$post_id = (int) icl_object_id( $post->ID, 'event', false, ICL_LANGUAGE_CODE );

					if ($post_id !== $post->ID) {
						continue;
					}
				}

				if($end && $start_date > $end){
					continue;
				}

				$featured_image_src = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail');

				if ($featured_image_src) {
					$featured_image_src = $featured_image_src[0];
				} else {
					$featured_image_src = '';
				}

				if ($repeating !== 'no_repeat') {
					$event_year = (int) date('Y', $start_date);
					$event_month = (int) date('m', $start_date);

					switch ($repeating) {
						case 'week':
						case '2week':
						case '3week':
							//if ($current_year > $event_year || ($current_year == $event_year && $current_month >= $event_month-1) ) {
							$repeating_week = unserialize($post_meta['event_repeating_week'][0]);
							$start_day_number = (int) date('N', $start_date);/* mon-1, .., sun-7 */
							$day_duration_sec = 60 * 60 * 24;
							$diff = 7 - $start_day_number;
							$tmp_start = $start;

							if(is_array($repeating_week) && count($repeating_week)){

								foreach ($repeating_week as $key => $value) {
									$value = $value + 1;/* mon-1, .., sun-7 */
									$day_distance = $diff + $value;
									$day_distance = ($day_distance >= 7) ? $day_distance - 7 : $day_distance;
									$tmp_start = $start_date + $day_duration_sec*$day_distance;
									$temp_date = ( $end && ($end_date > $end) ) ? $end : $end_date;
									$i = 1;
									$k = 2;
									$j = 3;
									while($tmp_start < $temp_date){
										$skip = false;
										if($repeating === '2week' && $i%2 == 0){
											$skip = true;
										}
										if($repeating === '3week'){
											if($i == $k){
												$k += 3;
												$skip = true;
											}
											if($i == $j){
												$j += 3;
												$skip = true;
											}
										}
										if(!$skip){
											$events_data[] = array(
												'start' => $tmp_start,
												'end' => $tmp_start + $duration_sec,
											);
										}
										$tmp_start += $day_duration_sec*7;
										$i++;
									}
								}
							}
							//}
							break;
						case 'month':
							//if ($current_year > $event_year || ($current_year == $event_year && $current_month >= $event_month-1) ) {
							if($current_month > $event_month){
								$start_date = mktime((int) date('H', $start_date), (int) date('i', $start_date), 0, $current_month-1, (int) date('j', $start_date), $current_year);
								if($start_date <= $end_date){
									$events_data[] = array(
										'start' => $start_date,
										'end' => $start_date + $duration_sec,
									);
								}
							}
							if($current_month >= $event_month){
								$start_date = mktime((int) date('H', $start_date), (int) date('i', $start_date), 0, $current_month, (int) date('j', $start_date), $current_year);
								if($start_date <= $end_date){
									$events_data[] = array(
										'start' => $start_date,
										'end' => $start_date + $duration_sec,
									);
								}
							}
							if($current_month >= $event_month-1){
								$start_date = mktime((int) date('H', $start_date), (int) date('i', $start_date), 0, $current_month+1, (int) date('j', $start_date), $current_year);
								if($start_date <= $end_date){
									$events_data[] = array(
										'start' => $start_date,
										'end' => $start_date + $duration_sec,
									);
								}
							}
							//}
							break;
						case 'year':
							//if ($current_year >= $event_year && $current_month == $event_month) {
							$start_date = mktime((int) date('H', $start_date), (int) date('i', $start_date), 0, (int) date('n', $start_date), (int) date('j', $start_date), $current_year);
							$events_data[] = array(
								'start' => $start_date,
								'end' => $start_date + $duration_sec,
							);
							//}
							break;
						default:
							break;
					}
				}else{
					$events_data[] = array(
						'start' => $start_date,
						'end' => $end_date,
					);
				}

				$permastruct = get_option('permalink_structure');
				$date_format = tmm_events_get_option('tmm_events_date_format');
				$post_url = get_permalink($post->ID);

				foreach($events_data as $key => $value){

					if($value['end'] < $start){
						continue;
					}

					$event_url = $post_url;

					if ($repeating !== 'no_repeat') {

						if ($permastruct === '/%postname%/') {
							$event_url .= 'date/';
						} else {
							$event_url .= '&date=';
						}

						$day = date('d', $value['start']);

						if($date_format === '1'){
							$event_url .= $day . '-' . date('m', $value['start']) . '-' . date('Y', $value['start']);
						}else{
							$event_url .= date('m', $value['start']) . '-' . $day . '-' . date('Y', $value['start']);
						}

					}

					$data[] = array(
						'id' => uniqid(),
						'post_id' => $post->ID,
						'title' => $post->post_title,
						'start' => date("Y-m-d H:i", $value['start']),
						'end' => date("Y-m-d H:i", $value['end']),
						'start_mktime' => $value['start'],
						'end_mktime' => $value['end'],
						'event_place_address' => $place_address,
						'featured_image_src' => $featured_image_src,
						'post_excerpt' => $post->post_excerpt,
						'allDay' => 0,
						'url' => $event_url,
					);
				}
			}
		}

		return $data;
	}

	//ajax
	public static function get_calendar_data() {
		$data = self::get_events($_REQUEST['start'], $_REQUEST['end']);
		echo json_encode($data);
		exit;
	}

	//ajax
	public static function get_widget_calendar_data() {
		$data = self::get_events($_REQUEST['start'], $_REQUEST['end']);
		$now = current_time('timestamp');
		$permastruct = get_option('permalink_structure');
		$date_format = tmm_events_get_option('tmm_events_date_format');
		$home_url = home_url();

		$buffer = array();
		$result = array();

		if (!empty($data)) {
			foreach ($data as $value) {
				$start_day = (int) date('z', $value['start_mktime']);
				$end_day = (int) date('z', $value['end_mktime']);
				$ic = $end_day - $start_day + 1;
				for($i=0;$i<$ic;$i++){
					$temp_date = $value['start_mktime'] + 86400*$i;
					$temp_date = date('Y-m-d', $temp_date);
					$buffer[$temp_date] = isset($buffer[$temp_date]) ? $buffer[$temp_date] + 1 : 1;
				}
			}

			foreach ($buffer as $date => $count) {
				$tmp = array();
				$tmp['id'] = (string) uniqid();
				$tmp['title'] = (string) $count;
				$tmp['start'] = $date;
				$tmp['start_mktime'] = strtotime($date, $now);
				$tmp['end'] = $date;
				$tmp['allDay'] = 0;

				$date_array = explode("-", $date);
				$event_url = $home_url;

				if ($permastruct === '/%postname%/') {
					$event_url .= '/event/date/';
				} else {
					$event_url .= '/index.php?post_type=event&date=';
				}

				if($date_format === '1'){
					$event_url .= $date_array[2] . '-' . $date_array[1] . '-' . $date_array[0];
				}else{
					$event_url .= $date_array[1] . '-' . $date_array[2] . '-' . $date_array[0];
				}

				$tmp['url'] = $event_url;

				$result[] = $tmp;
			}
		}

		echo json_encode($result);
		exit;
	}

	public static function get_soonest_event($start, $count = 1, $distance = 0, $category = 0, $delay = 0) {

		if (!$distance) {
			$distance = 1 * 60 * 60 * 24 * 31; //1 month by default
		} else {
			$distance = $distance * 60 * 60 * 24 * 31;
		}

		$end = $start + $distance;
		$now = current_time('timestamp') - $delay * 3600;

		$data = self::get_events($start, $end, $category);

		$buffer = array();

		if (!empty($data)) {

			foreach ($data as $key => $value) {
				if ($value['start_mktime'] > $now) {
					if ($distance > 0) {
						if ($value['start_mktime'] > $start + $distance) {
							continue;
						}
					}
					$buffer[$value['start_mktime']] = $value;
				}
			}
		}

		$key_buffer = array();
		if (!empty($buffer)) {
			foreach ($buffer as $key => $value) {
				$key_buffer[] = $key;
			}
		}

		if (!empty($key_buffer)) {
			sort($key_buffer, SORT_NUMERIC);
			$result = array();
			for ($i = 0; $i < $count; $i++) {
				if (isset($key_buffer[$i])) {
					$result[] = $buffer[$key_buffer[$i]];
				} else {
					break;
				}
			}

			return $result;
		}

		return array();
	}

	private static function compare_events($a, $b) {
		return $a['start_mktime'] - $b['start_mktime'];
	}

	public static function get_event_days($post_id) {
		$is_repeat = get_post_meta($post_id, 'event_repeating', true);
		$days = array();
		$result = '';
		$ev_mktime = (int) get_post_meta($post_id, 'ev_mktime', true);

		if($is_repeat !== 'no_repeat'){
			$repeating_days = get_post_meta($post_id, 'event_repeating_week', true);
            if(is_array($repeating_days)){
                foreach ($repeating_days as $v) {
                    $days[] = tmm_get_weekday($v);
                }
            }
		}else{
			$days[] = tmm_get_weekday(date('N', $ev_mktime)-1);
		}

		for($i=0,$ic=count($days);$i<$ic;$i++) {
			if($i > 0){
				$result .= ', ';
			}
			$result .= $days[$i];
		}

		return $result;
	}

	//ajax
	public static function get_events_listing($args = array()) {
		$request_start = 0;
		$request_end = 0;
		$category = 0;
		$order = 'DESC';
		$count = 6;
		$page_num = 0;
		$is_ajax = 0;

		if (isset($_POST['events_list_args'])) {
			$is_ajax = 1;
			$args = $_POST['events_list_args'];
		}

		if (isset($args['start'])) {
			$request_start = (int) $args['start'];
		}
		if (isset($args['end'])) {
			$request_end = (int) $args['end'];
		}
		if (isset($args['category'])) {
			$category = $args['category'];
		}
		if (isset($args['order'])) {
			$order = $args['order'];
		}
		if (isset($args['count'])) {
			$count = (int) $args['count'];
		}
		if (isset($args['page_num'])) {
			$page_num = (int) $args['page_num'];
		}

		$now = current_time('timestamp');

		$start = ($request_start != 0 ? $request_start : $now);
		$days_in_curr_month = date('t', @mktime(0, 0, 0, date("m", $start), 1, date("Y", $start)));

		$distance = 60 * 60 * 24 * $days_in_curr_month - 1;
		if($request_end == 0){
			$end = $start + $distance;
		}else{
			$end = $request_end;
		}
		if ($request_start == 0) {//current month
			$distance = $end - $start;
		}

		$events = self::get_events($start, $end, $category);

		//events filtering
		$filtered_events = array();
		if (!empty($events)) {
			foreach ($events as $key => $value) {
				if ($value['end_mktime'] < $start) {
					unset($events[$key]);
					continue;
				}
				if ($request_end > 0 && $value['start_mktime'] > $request_end) {
					unset($events[$key]);
					continue;
				}

				$filtered_events[] = $value;

			}
		}

		if ($order === 'ASC') {
			usort($filtered_events, array(__CLASS__, 'usort_asc'));
		} else {
			usort($filtered_events, array(__CLASS__, 'usort_desc'));
		}

		$events = $filtered_events;

		$args = array();
		$args['events'] = array_slice($events, $page_num * $count, $count);
		$result = array();
		$result['html'] = tmm_locate_template(TMM_EVENTS_PLUGIN_PATH . '/views/templates/content-event.php', $args, false);
		$result['count'] = count($events);

		$result['year'] = date("Y", $start);
		$result['month'] = tmm_get_month_name(date("n", $start));
		$result['month_num'] = date("m", $start);

		if ($is_ajax) {
			echo json_encode($result);
			exit;
		}else{
			return $result['html'];
		}
	}

	public static function get_events_by_id($post_id, $ev_mktime = '', $ev_end_mktime = '', $single = false){
		$start = current_time('timestamp');

		if (!$ev_mktime) {
			$ev_mktime = $start;
		}

		if (is_array($post_id)) {
			$post_id = implode(',', $post_id);
		} else {
			$post_id = (int) $post_id;
		}

		$args=array(
			'post_type' => 'event',
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'include' => $post_id,
		);

		$event_posts = get_posts( $args );

		$tmp_post = TMM_Event::filter_events($event_posts, $ev_mktime, $ev_end_mktime);

		$filtered_events = array();
		if (!empty($tmp_post)) {
			foreach ($tmp_post as $key => $value) {
				if ($value['end_mktime'] < $start) {
					unset($tmp_post[$key]);
					continue;
				}
				if ($ev_end_mktime > 0 && $value['start_mktime'] > $ev_end_mktime) {
					unset($tmp_post[$key]);
					continue;
				}

				$filtered_events[] = $value;
			}
		}

		usort($filtered_events, array(__CLASS__, 'usort_desc'));

		/* remove dublicated repeating events */
		if ($single) {
			$tmp = array();
			foreach ($filtered_events as $k => $v) {
				if (!isset($tmp[ $v['post_id'] ] )) {
					$tmp[ $v['post_id'] ] = $v;
				}
			}
			$filtered_events = $tmp;
		}

		return $filtered_events;
	}

	public static function usort_desc($a, $b){
		return ($a['start_mktime'] < $b['start_mktime']) ? -1 : 1;
	}

	public static function usort_asc($a, $b){
		return ($a['start_mktime'] > $b['start_mktime']) ? -1 : 1;
	}

	public static function get_timezone_string() {
		$show_timezone = is_single() ? tmm_events_get_option('tmm_single_event_show_timezone') : tmm_events_get_option('tmm_events_show_timezone');

		if ($show_timezone === '0') {
			return '';
		}

		$current_offset = self::$gmt_offset;
		$tzstring = get_option('timezone_string');

		if (false !== strpos($tzstring, 'Etc/GMT'))
			$tzstring = '';

		if (empty($tzstring)) { // Create a UTC+- zone if no timezone string exists
			if (0 == $current_offset)
				$tzstring = 'UTC+0';
			elseif ($current_offset < 0)
				$tzstring = 'UTC' . $current_offset;
			else
				$tzstring = 'UTC+' . $current_offset;
		}

		return "(" . $tzstring . ")";
	}

	//format: 2013-03-01 17:11  YYYY-mm-dd H:i
	public static function convert_time_to_zone_time($time) {
		$gmt_offset = (int) self::$gmt_offset;
		$mk_time = strtotime($time);
		$mk_time+=($gmt_offset * (-1) * 3600);
		$time_converted = date('Y-m-d', $mk_time) . " " . date('H', $mk_time) . ":" . date('i', $mk_time);
		return $time_converted;
	}

    public static function get_event_duration($start, $end) {
		$duration = array('00', '00', 0);
        if($end > $start){
            $start_h = date('H', $start);
            $start_m = date('i', $start);
            $end_h = date('H', $end);
            $end_m = date('i', $end);
            $diff = mktime($end_h, $end_m) - mktime($start_h, $start_m);
            $duration[0] = $diff >= 3600 ? (int) ($diff / 3600) : 0;
            $duration[1] = (int) (($diff % 3600) / 60);
			if($duration[0] < 10){
				$duration[0] = '0' . $duration[0];
			}
			if($duration[1] < 10){
				$duration[1] = '0' . $duration[1];
			}
			$duration[2] = $diff;
        }

		return $duration;
	}

	public static function get_event_date($timestamp) {
		$month = tmm_get_short_month_name( date('n', $timestamp) );
		$day = date('d', (int) $timestamp);
		$year = date('Y', (int) $timestamp);

		if(tmm_events_get_option('tmm_events_date_format') === '1'){
			$date = $day . ' ' . $month . ', ' . $year;
		}else{
			$date = $month . ' ' . $day . ', ' . $year;
		}

		return $date;
	}

	public static function get_event_time($timestamp, $hide_time_zone = false) {
		$time = '';

		if($timestamp){
			$events_time_format = is_single() ? tmm_events_get_option('tmm_single_event_time_format') : tmm_events_get_option('tmm_events_time_format');

			if($events_time_format === '1'){
				$time_format = 'h:i A';
			}else{
				$time_format = 'H:i';
			}

			$time = date($time_format, $timestamp);
			$show_timezone = is_single() ? tmm_events_get_option('tmm_single_event_show_timezone') : tmm_events_get_option('tmm_events_show_timezone');

			if($show_timezone === '1' && !$hide_time_zone){
				$time .= ' ' . TMM_Event::get_timezone_string();
			}
		}

		return $time;
	}

}
