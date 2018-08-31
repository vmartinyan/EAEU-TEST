<?php
get_header();

$start = current_time('timestamp');
$end = $start + 24*60*60;
$tmp_date = array('', '', '');

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

	$start = mktime(0, 0, 0, $month, $day, $year) - 1;
	$end = mktime(0, 0, 0, $month, $day+1, $year) - 1;
}

$options = array(
	'start' => $start,
	'end' => $end,
	'category' => 0,
	'order' => 'DESC',
	'count' => 6,
);
?>

<div id="events_listing"></div>

<div class="pagenavbar">
	<div class="events_listing_navigation pagenavi" style="display:none;clear: both"></div>
</div><!--/ .pagenavbar-->

<script type="text/javascript">
	jQuery(function() {
		if (jQuery('.page-title > h1').length) {
			jQuery('.page-title > h1').append('&nbsp;<span><?php echo esc_js($tmp_date[0]), '-', esc_js($tmp_date[1]), '-', esc_js($tmp_date[2]); ?></span>');
		}
		var app_event_listing = new THEMEMAKERS_EVENT_EVENTS_LISTING();
		app_event_listing.init(<?php echo json_encode($options); ?>);
	});
</script>

<?php

get_footer();