<?php
$args=array(
	'post_type' => 'event',
	'post_status' => 'publish',
	'posts_per_page' => -1,
);

$event_posts = get_posts( $args );

if (!$event_posts) {
	$event_posts = array();
}

?>

<p>
    <label for="<?php echo esc_attr( $widget->get_field_id('title') ); ?>"><?php _e('Title', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?>:</label>
    <input class="widefat" type="text" id="<?php echo esc_attr( $widget->get_field_id('title') ); ?>" name="<?php echo esc_attr( $widget->get_field_name('title') ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
</p>

<p>
	<label for="<?php echo esc_attr( $widget->get_field_id('event_type') ); ?>"><?php _e('Choose type', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?>:</label>
	<select id="<?php echo esc_attr( $widget->get_field_id('event_type') ); ?>" name="<?php echo esc_attr( $widget->get_field_name('event_type') ); ?>" class="widefat upcoming_event_widget_type">
		<option <?php selected($instance['event_type'], 0) ?> value="0"><?php _e('Upcoming Event', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?></option>
		<option <?php selected($instance['event_type'], 1); ?> value="1"><?php _e('Featured Event', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?></option>
	</select>
</p>

<p>
	<?php
	$checked = "";
	if ($instance['show_event_excerpt'] == 'true') {
		$checked = 'checked="checked"';
	}
	?>
	<input type="checkbox" id="<?php echo esc_attr( $widget->get_field_id('show_event_excerpt') ); ?>" name="<?php echo esc_attr( $widget->get_field_name('show_event_excerpt') ); ?>" value="true" <?php echo $checked; ?> />
	<label for="<?php echo esc_attr( $widget->get_field_id('show_event_excerpt') ); ?>"><?php _e('Display Excerpt', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?></label>
</p>
<p>
	<label for="<?php echo esc_attr( $widget->get_field_id('excerpt_event_symbols_count') ); ?>"><?php _e('Truncate event excerpt', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?>:</label>
	<input class="widefat" type="text" id="<?php echo esc_attr( $widget->get_field_id('excerpt_event_symbols_count') ); ?>" name="<?php echo esc_attr( $widget->get_field_name('excerpt_event_symbols_count') ); ?>" value="<?php echo esc_attr( $instance['excerpt_event_symbols_count'] ); ?>" />
</p>

<div class="upcoming_event_block" style="display: <?php echo $instance['event_type'] === '0' ? 'block' : 'none'; ?>">
	<p>
	    <label for="<?php echo esc_attr( $widget->get_field_id('month_deep') ); ?>"><?php _e('Upcoming events period', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?>:</label>
	    <select id="<?php echo esc_attr( $widget->get_field_id('month_deep') ); ?>" name="<?php echo esc_attr( $widget->get_field_name('month_deep') ); ?>" class="widefat">
	        <?php for ($i = 1; $i <= 12; $i++) : ?>
	            <option <?php selected($instance['month_deep'], $i); ?> value="<?php echo $i ?>"><?php echo $i ?> <?php _e('month', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?><?php if($i>1) echo 's' ?></option>
	        <?php endfor; ?>
	    </select>
	</p>

	<p>
		<label for="<?php echo esc_attr( $widget->get_field_id('count') ); ?>"><?php _e('Count', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?>:</label>
		<input class="widefat" type="text" id="<?php echo esc_attr( $widget->get_field_id('count') ); ?>" name="<?php echo esc_attr( $widget->get_field_name('count') ); ?>" value="<?php echo esc_attr( $instance['count'] ); ?>" />
	</p>
</div>

<div class="featured_event_block" style="display: <?php echo $instance['event_type'] === '1' ? 'block' : 'none'; ?>">
	<p>
		<label for="<?php echo esc_attr( $widget->get_field_id('event_list') ); ?>"><?php _e('Choose Event to Display', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?>:</label>
		<select id="<?php echo esc_attr( $widget->get_field_id('event_list') ); ?>" name="<?php echo esc_attr( $widget->get_field_name('event_list') ); ?>[]" value="<?php echo esc_attr( $instance['event_list'] ); ?>" class="widefat" multiple>
			<?php foreach ($event_posts as $event) { ?>
				<option <?php echo in_array($event->ID, $instance['event_list']) ? 'selected' : ''; ?> value="<?php echo $event->ID; ?>"><?php echo esc_html($event->post_title); ?></option>
			<?php } ?>
		</select>
	</p>
</div>



