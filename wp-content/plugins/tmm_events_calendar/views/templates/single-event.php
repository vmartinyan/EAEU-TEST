<?php
get_header();

global $post;

$thumb_size = '745*450';
$thumb = class_exists('TMM_Helper') ? TMM_Helper::get_post_featured_image($post->ID, $thumb_size) : '';

if(have_posts()){
	while (have_posts()) {
		the_post();

		$event_allday = get_post_meta($post->ID, 'event_allday', true);
		$hide_event_place = get_post_meta($post->ID, 'hide_event_place', true);
		$event_place_address = get_post_meta($post->ID, 'event_place_address', true);
		$event_place_phone = get_post_meta($post->ID, 'event_place_phone', true);
		$event_place_website = get_post_meta($post->ID, 'event_place_website', true);
		$repeats_every = get_post_meta($post->ID, 'event_repeating', true);

		$ev_mktime = (int) get_post_meta($post->ID, 'ev_mktime', true);
		$ev_end_mktime = (int) get_post_meta($post->ID, 'ev_end_mktime', true);

		$events_show_duration = tmm_events_get_option('tmm_single_event_show_duration');
		if($events_show_duration){
			$event_duration_sec = TMM_Event::get_event_duration($ev_mktime, $ev_end_mktime);
			$duration_hh = $event_duration_sec[0];
			$duration_mm = $event_duration_sec[1];
		}

		if($event_allday == 1){
			$event_start_time = '';
			$event_end_time = '';
		}else{
			$event_start_time = TMM_Event::get_event_time($ev_mktime);
			$event_end_time = TMM_Event::get_event_time($ev_end_mktime);
		}
		$e_category = get_the_term_list($post->ID, 'events-categories', '', ', ', '');

		$next_post = get_next_post();
		$prev_post = get_previous_post();

		$event_organizer_phone = get_post_meta($post->ID, 'event_organizer_phone', true);
		$event_organizer_website = get_post_meta($post->ID, 'event_organizer_website', true);
		$event_organizer_name = get_post_meta($post->ID, 'event_organizer_name', true);

		$css_classes = 'event';

		if (!$thumb || !has_post_thumbnail()) {
			$css_classes .= ' no-image';
		}

		$events_button_page = tmm_events_get_option('tmm_single_event_button_page');
		$events_button_url = '';

		if ($events_button_page === '0') {
			$events_button_url = home_url() . '/' . get_post_type();
		} else if ($events_button_page && $events_button_page !== '-1') {
			$events_button_url = get_permalink($events_button_page);
		}

		if ($repeats_every !== 'no_repeat') {

			$tmp_date = '';

			if (isset($_GET['date'])) {
				$tmp_date = explode('-', $_GET['date']);
			} else if (isset($wp_query->query_vars['date'])) {
				$tmp_date = explode('-', $wp_query->query_vars['date']);
			}

			if (is_array($tmp_date) && !empty($tmp_date[0]) && !empty($tmp_date[1]) && !empty($tmp_date[2])) {
				if(tmm_events_get_option('tmm_events_date_format') === '1'){
					$ev_mktime = mktime(0, 0, 0 , $tmp_date[1], $tmp_date[0], $tmp_date[2]);
				}else{
					$ev_mktime = mktime(0, 0, 0 , $tmp_date[0], $tmp_date[1], $tmp_date[2]);
				}

				$ev_end_mktime = $ev_mktime;
			}

		}

		$event_date = TMM_Event::get_event_date($ev_mktime);
		$event_end_date = TMM_Event::get_event_date($ev_end_mktime);
		$day = date('d', $ev_mktime);
		$month = tmm_get_short_month_name( date('n', $ev_mktime) );
		?>

		<div id="post-<?php the_ID(); ?>" <?php post_class($css_classes); ?>>

			<span class="event-date"><?php echo $day; ?><b><?php echo $month; ?></b></span>

			<?php if (has_post_thumbnail() && $thumb) { ?>

				<a href="<?php echo esc_url( TMM_Helper::get_post_featured_image($post->ID, '') ); ?>" class="image-post single-image-link item-overlay">
					<img src="<?php echo esc_url($thumb); ?>" alt="<?php the_title_attribute(); ?>" />
				</a>

			<?php } ?>

			<h3 class="event-title"><?php the_title(); ?></h3>

			<?php
			the_content();

			if(function_exists('tmm_link_pages')){
				tmm_link_pages();
			}else{
				wp_link_pages();
			}

			if(function_exists('tmm_layout_content')){
				tmm_layout_content(get_the_ID(), 'default');
			}
			?>

			<div class="event-details boxed">
				<dl>
					<h3><?php _e('Details', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></h3>
					<dt><?php _e('Start', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
					<dd><?php echo $event_date.' '.$event_start_time; ?></dd>

					<dt><?php _e('End', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
					<dd><?php echo $event_end_date.' '.$event_end_time; ?></dd>

					<?php if ($events_show_duration) { ?>
						<dt><?php _e('Duration', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
						<dd><?php echo $duration_hh . ":" . $duration_mm; ?></dd>
					<?php } ?>

					<?php if (!empty($e_category)) { ?>
					<dt><?php _e('Event Category', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
					<dd><?php echo $e_category; ?></dd>
					<?php } ?>
				</dl>

				<dl>
					<h3><?php _e('Organizer', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></h3>

					<?php if (!empty($event_organizer_name)) { ?>
						<dt><?php _e('Contact Person', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
						<dd><?php echo esc_html($event_organizer_name); ?></dd>
					<?php } ?>

					<?php if (!empty($event_organizer_phone)) { ?>
						<dt><?php _e('Phone', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
						<dd><?php echo esc_html($event_organizer_phone); ?></dd>
					<?php } ?>

					<?php if (!empty($event_organizer_website)) { ?>
						<dt><?php _e('Website', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
						<dd><a target="_blank" href="<?php echo esc_url($event_organizer_website); ?>"><?php echo esc_url($event_organizer_website); ?></a></dd>
					<?php } ?>
				</dl>

			</div><!--/ .event-details-->

			<div class="row collapse event-address">

				<div class="large-6 columns">
					<div class="event-details boxed">
						<dl>
							<h3><?php _e('Venue', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></h3>

							<?php if (!empty($event_place_phone)) { ?>
								<dt><?php _e('Phone', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
								<dd><?php echo esc_html($event_place_phone); ?></dd>
							<?php } ?>

							<?php if (!empty($event_place_address)) { ?>
								<dt><?php _e('Address', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
								<dd><?php echo esc_html($event_place_address); ?></dd>
							<?php } ?>

							<?php if (!empty($event_place_website)) { ?>
								<dt><?php _e('Website', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></dt>
								<dd><a target="_blank" href="<?php echo esc_url($event_place_website); ?>"><?php echo esc_url($event_place_website); ?></a></dd>
							<?php } ?>
						</dl>
					</div>
				</div>

				<?php if (!$hide_event_place) { ?>
				<div class="large-6 columns">
					<div class="event-map">
						<div id="map_address" class="google_map">
							<?php
							if (class_exists('TMM_Content_Composer')) {
								$event_map_longitude = (float) get_post_meta($post->ID, 'event_map_longitude', true);
								$event_map_latitude = (float) get_post_meta($post->ID, 'event_map_latitude', true);
								$event_map_zoom = (int) get_post_meta($post->ID, 'event_map_zoom', true);
								echo do_shortcode('[google_map width="375" height="195" latitude="' . $event_map_latitude . '" longitude="' . $event_map_longitude . '" zoom="' . $event_map_zoom . '" controls="" enable_scrollwheel="0" map_type="ROADMAP" enable_marker="1" enable_popup="0"][/google_map]');
							}
							?>
						</div>
					</div>

				</div>
				<?php } ?>

			</div><!--/ .row-->

		</div><!--/ .event-->

		<?php if($prev_post || $next_post){ ?>

			<div class="single-nav">

				<?php if($prev_post){ ?>

					<a href="<?php echo get_the_permalink($prev_post->ID); ?>" class="prev">
						<?php _e('Previous article', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?>
						<b><?php echo esc_html($prev_post->post_title); ?></b>
					</a>

				<?php } ?>

				<?php if($next_post){ ?>

					<a href="<?php echo get_the_permalink($next_post->ID); ?>" class="next">
						<?php _e('Next article', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?>
						<b><?php echo esc_html($next_post->post_title); ?></b>
					</a>

				<?php } ?>

			</div><!--/ .single-nav-->

		<?php } ?>

		<?php if ($events_button_url) { ?>
		<a href="<?php echo esc_url($events_button_url); ?>" class="back-link"><?php _e('All Events', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></a>
		<?php } ?>

		<hr/>

	<?php
	}
}

?>

<div class="clear"></div>

<?php
if ( (!isset($_REQUEST['disable_blog_comments']) || !$_REQUEST['disable_blog_comments']) && tmm_events_get_option('tmm_single_event_show_comments') !== '0' ) {
	comments_template();
}

get_footer();