<?php

if (isset($_GET['date']) || isset($wp_query->query_vars['date'])) {

	include_once TMM_EVENTS_PLUGIN_PATH . 'views/templates/archive-event.php';

} else {

	get_header();

	$queried_object = get_queried_object();

	$category = (is_object($queried_object) && isset($queried_object->term_taxonomy_id)) ? (int) $queried_object->term_taxonomy_id : 0;
	$now = current_time('timestamp');
	$start = strtotime(date("Y", $now) . '-' . date("m", $now) . '-' . 01, $now);

	$options = array(
		'start' => $start,
		'end' => false,
		'category' => $category,
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
			var app_event_listing = new THEMEMAKERS_EVENT_EVENTS_LISTING();
			app_event_listing.init(<?php echo json_encode($options); ?>);
		});
	</script>

	<?php

	get_footer();

}