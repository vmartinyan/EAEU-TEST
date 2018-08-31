<?php

if (!empty($events)){

	$thumb_size = '745*450';

	foreach ($events as $event) {

		$thumb = (class_exists('TMM_Helper') && $event['featured_image_src']) ? TMM_Helper::resize_image($event['featured_image_src'], $thumb_size) : '';
		$event_allday = get_post_meta($event['post_id'], 'event_allday', true);
		$hide_event_place = get_post_meta($event['post_id'], 'hide_event_place', true);
		$event_place_address = get_post_meta($event['post_id'], 'event_place_address', true);
		$event_place_phone = get_post_meta($event['post_id'], 'event_place_phone', true);
		$event_place_website = get_post_meta($event['post_id'], 'event_place_website', true);

		$event_date = TMM_Event::get_event_date($event['start_mktime']);
		$day = date('d', $event['start_mktime']);
		$month = tmm_get_short_month_name( date('n', $event['start_mktime']) );

		$ev_end_mktime = (int) get_post_meta($event['post_id'], 'ev_end_mktime', true);
		$event_end_date = TMM_Event::get_event_date($event['end_mktime']);

		$repeats_every = get_post_meta($event['post_id'], 'event_repeating', true);
		$events_show_duration = tmm_events_get_option('tmm_events_show_duration');
		if($events_show_duration){
			$event_duration_sec = TMM_Event::get_event_duration($event['start_mktime'], $ev_end_mktime);
			$duration_hh = $event_duration_sec[0];
			$duration_mm = $event_duration_sec[1];
		}

		if($event_allday == 1){
			$event_start_time = '';
			$event_end_time = '';
		}else{
			$event_start_time = TMM_Event::get_event_time($event['start_mktime']);
			$event_end_time = TMM_Event::get_event_time($ev_end_mktime);
		}

		$article_class = 'event';

		if (!$thumb) {
			$article_class .= ' no-image';
		}

		if (class_exists('TMM') && TMM::get_option("tmm_events_listing_effect") !== 'none') {
			$article_class .= ' ' . TMM::get_option("tmm_events_listing_effect");
		}
		?>

		<article class="<?php echo $article_class; ?>">

			<span class="event-date"><?php echo $day; ?><b><?php echo $month; ?></b></span>

			<?php if ($thumb) { ?>

				<a href="<?php echo esc_url($event['url']); ?>" class="image-post  item-overlay">
					<img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($event['title']); ?>">
				</a>

			<?php } ?>

			<div class="event-content clearfix">

				<h3 class="event-title"><a href="<?php echo esc_url($event['url']); ?>"><?php echo esc_html($event['title']); ?></a></h3>

				<?php if (!$hide_event_place) { ?>

					<div class="event-location">
						<div id="map_address" class="google_map">
							<?php
							$event_map_longitude = (float) get_post_meta($event['post_id'], 'event_map_longitude', true);
							$event_map_latitude = (float) get_post_meta($event['post_id'], 'event_map_latitude', true);
							$event_map_zoom = (int) get_post_meta($event['post_id'], 'event_map_zoom', true);
							$map_size = '255x160';
							echo '<img src="https://maps.googleapis.com/maps/api/staticmap?center=' . $event_map_latitude . ',' . $event_map_longitude . '&zoom=' . $event_map_zoom . '&size='.$map_size.'&markers=color:red|label:P|' . $event_map_latitude . ',' . $event_map_longitude . '&sensor=false">';
							?>
						</div>
					</div>

				<?php } ?>

				<?php if (!empty($event['post_excerpt'])) { ?>

					<p><?php echo esc_html($event['post_excerpt']); ?></p>

				<?php } ?>

			</div>

			<div class="event-details">
				<dl>
					<dt><?php _e('Start', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
					<dd><?php echo $event_date.' '.$event_start_time; ?></dd>

					<dt><?php _e('End', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
					<dd><?php echo $event_end_date.' '.$event_end_time; ?></dd>

					<?php if($events_show_duration) { ?>
						<dt><?php _e('Duration', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
						<dd><?php echo $duration_hh . ":" . $duration_mm; ?></dd>
					<?php } ?>
				</dl>

				<?php if ( !empty($event_place_phone) || !empty($event_place_address) || (!empty($event_place_website) && tmm_events_get_option('tmm_events_show_venue_website')) ) { ?>

				<dl>
					<?php if (!empty($event_place_phone)) { ?>
						<dt><?php _e('Phone', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
						<dd><?php echo esc_html($event_place_phone); ?></dd>
					<?php } ?>

					<?php if (!empty($event_place_address)) { ?>
						<dt><?php _e('Address', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
						<dd><?php echo esc_html($event_place_address); ?></dd>
					<?php } ?>

					<?php if (!empty($event_place_website) && tmm_events_get_option('tmm_events_show_venue_website')) { ?>
						<dt><?php _e('Website', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
						<dd><a target="_blank" href="<?php echo esc_url($event_place_website); ?>"><?php echo esc_url($event_place_website); ?></a></dd>
					<?php } ?>
				</dl>

				<?php } ?>

			</div><!--/ .event-details-->

		</article><!--/ .entry-->

	<?php	
	}
}else{
	_e('NO EVENTS', TMM_EVENTS_PLUGIN_TEXTDOMAIN);
}
?>
