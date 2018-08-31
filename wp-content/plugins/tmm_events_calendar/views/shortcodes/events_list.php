<?php
$now = current_time('timestamp');
$start = strtotime(date("Y", $now) . '-' . date("m", $now) . '-' . 01, $now);
$end = mktime(0, 0, 0, date("m", $start)+1, 1, date("Y", $start));
$period_options = array();

$options = array(
	'start' => $start,
	'end' => $end,
	'category' => 0,
	'order' => 'DESC',
	'count' => 5,
	'show_period_selector' => 0,
);

if (isset($category)) {
	$options['category'] = $category;
}

if (isset($sorting)) {
	$options['order'] = $sorting;
}

if (isset($count)) {
	$options['count'] = (int) $count;
}

if (isset($show_period_selector)) {
	$options['show_period_selector'] = $show_period_selector;
}

if ($options['show_period_selector'] && isset($period_selector_amount)) {
	$next_timestamp = $start;

	for ($i=0, $ic=$period_selector_amount; $i<$ic; $i++) {
		$month_name = tmm_get_month_name( date('n', $next_timestamp) );
		$year = date('Y', $next_timestamp);
		$option = array(
			'title' => $month_name . ' ' . $year,
			'start' => $next_timestamp,
		);

		$next_timestamp = strtotime("next month", $next_timestamp);

		$option['end'] = $next_timestamp;

		$period_options[] = $option;
	}
}

if ($options['count'] > 0) { ?>

	<?php if ($options['show_period_selector'] && !empty($period_options)) { ?>

		<div class="row events_filter">
			<div class="medium-9 columns">
				<label for="event_listing_period"><?php _e('Filter by Month', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?>:</label>
			</div>
			<div class="medium-3 columns">
				<select id="event_listing_period" autocomplete="off">
					<?php foreach ($period_options as $key => $value){ ?>
						<option value="<?php echo esc_attr($value['start']); ?>" data-end="<?php echo esc_attr($value['end']); ?>"><?php echo esc_html($value['title']); ?></option>
					<?php } ?>
				</select>
			</div>
		</div>

	<?php } ?>

	<div id="events_listing"></div>

	<div class="pagenavbar">
		<div class="events_listing_navigation pagenavi" style="display:none;clear: both"></div>
	</div><!--/ .pagenavbar-->

	<script type="text/javascript">
	    jQuery(function() {
	        var app_event_listing = new THEMEMAKERS_EVENT_EVENTS_LISTING();
	        app_event_listing.init(<?php echo json_encode($options); ?>);
	    });
	</script>

	<?php
}