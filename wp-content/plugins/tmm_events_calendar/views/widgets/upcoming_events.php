<?php
$now = current_time('timestamp');
$events = array();
$event_type = isset($instance['event_type']) ? (int) $instance['event_type'] : 0;
$event_list = isset($instance['event_list']) ? $instance['event_list'] : '';

if ($event_type == 1) {
	if (!empty($event_list)) {
		$event_list = array_map('intval', $event_list);
		$events = TMM_Event::get_events_by_id($event_list, '', '', 1);
	}
} else {
	$month_deep = isset($instance['month_deep']) ? (int) $instance['month_deep'] : 0;
	$count = isset($instance['count']) ? (int) $instance['count'] : 1;
	$events = TMM_Event::get_soonest_event($now, $count, $month_deep);
}

$thumb_size = '350*275';

if (is_array($events) && !empty($events)) {
	?>

	<div class="widget widget_upcoming_events">

		<?php if (!empty($instance['title'])){ ?>
			<h3 class="widget-title"><?php esc_html_e($instance['title']); ?></h3>
		<?php } ?>

		<ul>

			<?php
			foreach ($events as $event) {
				$thumb = (class_exists('TMM_Helper') && $event['featured_image_src']) ? TMM_Helper::resize_image($event['featured_image_src'], $thumb_size) : '';
				$day = date('d', $event['start_mktime']);
				$month = tmm_get_short_month_name( date('n', $event['start_mktime']) );
				?>

				<li <?php if ($thumb) { ?>class="has-thumb"<?php } ?>>
					<div class="event-container">
						<span class="event-date"><?php echo $day; ?><b><?php echo $month; ?></b></span>
						<div class="event-media">
							<div class="item-overlay">
								<img src="<?php echo esc_url($thumb); ?>" alt="<?php echo esc_attr($event['title']); ?>">
							</div>
							<div class="event-content<?php if ($instance['show_event_excerpt']) { ?> with-excerpt<?php } ?>">
								<h4 class="event-title">
									<a href="<?php echo esc_url($event['url']); ?>"><?php echo esc_html($event['title']); ?></a>
								</h4>

								<?php if ($instance['show_event_excerpt']) { ?>
									<div class="event-text">
										<?php $excerpt = $event['post_excerpt']; ?>
										<?php if (!empty($excerpt)){ ?>
											<?php
											if ((int) $instance['excerpt_event_symbols_count'] > 0) {
												echo mb_substr(strip_tags($excerpt), 0, (int) $instance['excerpt_event_symbols_count']) . " ...";
											} else {
												echo $excerpt;
											}
											?>
										<?php } else { ?>
											<?php echo mb_substr(strip_tags($post->post_content), 0, (int) $instance['excerpt_event_symbols_count']) . " ..."; ?>
										<?php } ?>
									</div>
								<?php } ?>

							</div>
						</div>
					</div>
				</li>

			<?php
			}
			?>

		</ul>

	</div><!--/ .widget_upcoming_events-->

<?php
}
?>