<input type="hidden" value="1" name="thememakers_meta_saving" />
<table class="form-table">
    <tbody>

        <tr>
            <th style="width:25%">
                <label for="event_repeating">
                    <strong><?php _e('Event Repeating', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
                    <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
                </label>
            </th>
            <td>

                <select id="event_repeating" name="event_repeating" autocomplete="off">
					<?php foreach (TMM_Event::$event_repeatings as $key => $value) : ?>
						<option value="<?php echo esc_attr($key); ?>" <?php echo($event_repeating == $key ? 'selected' : '') ?>><?php echo esc_html($value); ?></option>
					<?php endforeach; ?>
                </select>
				<?php $week_repeatings_array = array('week', '2week', '3week') ?>
                <ul style="display: <?php echo(in_array($event_repeating, $week_repeatings_array) ? 'block' : 'none') ?>;" id="event_week_days">

					<?php
					$week_days_array = array(
						__('Monday', TMM_EVENTS_PLUGIN_TEXTDOMAIN), //0
						__('Tuesday', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
						__('Wednesday', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
						__('Thursday', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
						__('Friday', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
						__('Saturday', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
						__('Sunday', TMM_EVENTS_PLUGIN_TEXTDOMAIN), //6
					);
					$event_repeating_week = unserialize($event_repeating_week);
					?>

					<?php foreach ($week_days_array as $key => $value) : ?>
					<li><label><input autocomplete="off" type="checkbox" value="<?php echo esc_attr($key); ?>" <?php if (is_array($event_repeating_week)) echo(in_array($key, $event_repeating_week) ? 'checked' : '') ?> name="event_repeating_week[]" />&nbsp;<?php echo esc_html($value); ?></label><br /></li>
					<?php endforeach; ?>

                </ul>

            </td>
        </tr>

        <tr>
            <th style="width:25%">
                <label for="event_date">
                    <strong><?php _e('Event Start Date', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
                    <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
                </label>
            </th>
            <td>
                <input autocomplete="off" type="text" value="<?php echo esc_attr($event_date); ?>" id="event_date" name="event_date" readonly="" />
            </td>
        </tr>

        <tr>
            <th style="width:25%">
                <label for="event_end_date">
                    <strong><?php _e('Event End Date', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
                    <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
                </label>
            </th>
            <td>
                <input autocomplete="off" type="text" value="<?php echo esc_attr($event_end_date); ?>" id="event_end_date" name="event_end_date" readonly="" />
            </td>
        </tr>

        <tr>
	        <th style="width:25%">
		        <label for="event_allday_cb">
			        <strong><?php _e('All Day', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
			        <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
		        </label>
	        </th>
	        <td>
		        <input id="event_allday" type="hidden" name="event_allday" autocomplete="off" value="<?php echo($event_allday == '1' ? '1' : '0') ?>" /><br />
		        <input id="event_allday_cb" type="checkbox" autocomplete="off" <?php echo($event_allday == 1 ? 'checked' : ''); ?> /><br />
	        </td>
        </tr>

        <?php $disabled = ($event_allday == 1) ? ' disabled' : ''; ?>

        <tr class="event_time_wrapper">
            <th style="width:25%">
                <label for="event_hh">
                    <strong><?php _e('Event Start Time', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
                    <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
                </label>
            </th>
            <td>

                <div id="error_event_datas" style="display: none;">
                    <p>
                        <strong><?php _e('Warning! Event start time must be earlier than event end time!', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
                    </p>
                </div>

				<select name="event_hh" id="event_hh" class="event_time_select" autocomplete="off"<?php echo  $disabled; ?>>
					<?php for ($i = 0; $i <= 23; $i++): ?>
						<option value="<?php echo $i ?>" <?php echo($event_hh == $i ? 'selected' : '') ?>><?php echo ($i < 10 ? "0" . $i : $i); ?></option>
					<?php endfor; ?>
                </select>&nbsp;:&nbsp;
				<select name="event_mm" id="event_mm" class="event_time_select" autocomplete="off"<?php echo  $disabled; ?>>
					<?php for ($i = 0; $i <= 55; $i+=5): ?>
						<option value="<?php echo $i ?>" <?php echo($event_mm == $i ? 'selected' : '') ?>><?php echo ($i < 10 ? "0" . $i : $i); ?></option>
					<?php endfor; ?>
                </select>&nbsp;<?php echo TMM_Event::get_timezone_string() ?>
            </td>
        </tr>

        <tr class="event_time_wrapper">
            <th style="width:25%">
                <label for="event_end_hh">
                    <strong><?php _e('Event End Time', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
                    <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
                </label>
            </th>
            <td>
				<select name="event_end_hh" id="event_end_hh" class="event_time_select" autocomplete="off"<?php echo  $disabled; ?>>
					<?php for ($i = 0; $i <= 23; $i++): ?>
						<option value="<?php echo $i ?>" <?php echo($event_end_hh == $i ? 'selected' : '') ?>><?php echo ($i < 10 ? "0" . $i : $i); ?></option>
					<?php endfor; ?>
                </select>&nbsp;:&nbsp;
				<select name="event_end_mm" id="event_end_mm" class="event_time_select" autocomplete="off"<?php echo  $disabled; ?>>
					<?php for ($i = 0; $i <= 55; $i+=5): ?>
						<option value="<?php echo $i ?>" <?php echo($event_end_mm == $i ? 'selected' : '') ?>><?php echo ($i < 10 ? "0" . $i : $i); ?></option>
					<?php endfor; ?>
                </select>&nbsp;<?php echo TMM_Event::get_timezone_string() ?>
            </td>
        </tr>

        <tr>
	        <th colspan="2"><h3><?php _e('Event Organizer Info', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?><hr></h3></th>
        </tr>

        <tr>
	        <th style="width:25%">
		        <label for="event_organizer_phone">
			        <strong><?php _e('Phone', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
			        <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
		        </label>
	        </th>
	        <td>
		        <input type="text" value="<?php echo esc_attr($event_organizer_phone); ?>" id="event_organizer_phone" name="event_organizer_phone" autocomplete="off" style="display:inline-block;width: 50%;" />
	        </td>
        </tr>

        <tr>
	        <th style="width:25%">
		        <label for="event_organizer_website">
			        <strong><?php _e('Website', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
			        <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
		        </label>
	        </th>
	        <td>
		        <input type="text" value="<?php echo esc_attr($event_organizer_website); ?>" id="event_organizer_website" name="event_organizer_website" autocomplete="off" style="display:inline-block;width: 50%;" />
	        </td>
        </tr>

        <tr>
	        <th style="width:25%">
		        <label for="event_organizer_name">
			        <strong><?php _e('Contact Person', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
			        <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
		        </label>
	        </th>
	        <td>
		        <input type="text" value="<?php echo esc_attr($event_organizer_name); ?>" id="event_organizer_name" name="event_organizer_name" autocomplete="off" style="display:inline-block;width: 50%;" />
	        </td>
        </tr>

        <tr>
	        <th colspan="2"><h3><?php _e('Event Venue', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?><hr></h3></th>
        </tr>

        <tr>
	        <th style="width:25%">
		        <label for="event_place_phone">
			        <strong><?php _e('Phone', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
			        <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
		        </label>
	        </th>
	        <td>
		        <input type="text" value="<?php echo esc_attr($event_place_phone); ?>" id="event_place_phone" name="event_place_phone" autocomplete="off" style="display:inline-block;width: 50%;" />
	        </td>
        </tr>

        <tr>
	        <th style="width:25%">
		        <label for="event_place_website">
			        <strong><?php _e('Website', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
			        <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
		        </label>
	        </th>
	        <td>
		        <input type="text" value="<?php echo esc_attr($event_place_website); ?>" id="event_place_website" name="event_place_website" autocomplete="off" style="display:inline-block;width: 50%;" />
	        </td>
        </tr>
        
        <tr>
            <th style="width:25%">
                <label for="event_place_address">
                    <strong><?php _e('Event Place', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
                    <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
                </label>
            </th>
            <td>
                <input type="text" value="<?php echo esc_attr($event_place_address); ?>" id="event_place_address" name="event_place_address" autocomplete="off" style="display:inline-block;width: 79%;" />&nbsp;
				<a class="repeatable-add button" style="display: inline-block;" href="#" id="set_event_place"><?php _e('Set Location', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></a><br />
                <br />
				<?php
				if(class_exists('TMM_Content_Composer')){
					if (!$event_map_latitude) {
						$event_map_latitude = 40.714623;
					}
					if (!$event_map_longitude) {
						$event_map_longitude = -74.006605;
					}
					echo do_shortcode('[google_map width="800" height="600" latitude="' . $event_map_latitude . '" longitude="' . $event_map_longitude . '" zoom="' . $event_map_zoom . '" controls="" enable_scrollwheel="0" map_type="ROADMAP" enable_marker="1" enable_popup="0"][/google_map]');
				}
				?>
            </td>
        </tr>
        <tr>
	        <th style="width:25%">
		        <label for="hide_event_place_cb">
			        <strong><?php _e('Hide Map', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
			        <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
		        </label>
	        </th>
	        <td>
		        <input id="hide_event_place" type="hidden" name="hide_event_place" autocomplete="off" value="<?php echo($hide_event_place == '1' ? '1' : '0') ?>" /><br />
		        <input id="hide_event_place_cb" type="checkbox" autocomplete="off" <?php echo($hide_event_place == 1 ? 'checked' : ''); ?> /><br />
	        </td>
        </tr>
        <tr>
            <th style="width:25%">
                <label for="event_map_zoom">
                    <strong><?php _e('Event Map Zoom', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
                    <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
                </label>
            </th>
            <td>
                <select name="event_map_zoom" id="event_map_zoom" autocomplete="off">
					<?php for ($i = 0; $i <= 30; $i++): ?>
						<option value="<?php echo $i ?>" <?php echo($event_map_zoom == $i ? 'selected' : '') ?>><?php echo ($i < 10 ? "0" . $i : $i); ?></option>
					<?php endfor; ?>
                </select>
            </td>
        </tr>
        <tr>
            <th style="width:25%">
                <label for="event_map_latitude">
                    <strong><?php _e('Event Map Latitude', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
                    <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
                </label>
            </th>
            <td>
                <input type="text" value="<?php echo esc_attr($event_map_latitude); ?>" id="event_map_latitude" name="event_map_latitude" autocomplete="off" /><br />
            </td>
        </tr>
        <tr>
            <th style="width:25%">
                <label for="event_map_longitude">
                    <strong><?php _e('Event Map Longitude', TMM_EVENTS_PLUGIN_TEXTDOMAIN); ?></strong>
                    <span style=" display:block; color:#999; margin:5px 0 0 0; line-height: 18px;"></span>
                </label>
            </th>
            <td>
                <input type="text" value="<?php echo esc_attr($event_map_longitude); ?>" id="event_map_longitude" name="event_map_longitude" autocomplete="off" /><br />
            </td>
        </tr>
    </tbody>
</table>

<style type="text/css">
    #error_event_datas {
        background-color: #FFEBE8;
        border-color: #CC0000;
        margin: 5px 0 2px 0;
        padding: 0 0.6em;
    }
</style>

<script type="text/javascript">
var calendar_event_date = "<?php echo esc_js($event_date); ?>";
var calendar_event_end_date = "<?php echo esc_js($event_end_date); ?>";

(function($){
            
	jQuery(function() {
        
		jQuery("#event_date").datepicker({
			dateFormat: "dd-mm-yy",
			showButtonPanel: true,
			showOtherMonths: true,
			defaultDate: "+1w",
			changeMonth: true,
			numberOfMonths: 3,
			showWeek: true,
			setDate: "<?php echo esc_js($event_date); ?>",
			onClose: function(selectedDate) {
				calendar_event_date = selectedDate;
				jQuery("#event_end_date").datepicker("option", "minDate", selectedDate);
				check_date_errors();
                $(this).removeClass('active');
			}
		}).on('focus', function(){
            $(this).addClass('active');
        });


		jQuery("#event_end_date").datepicker({
			dateFormat: "dd-mm-yy",
			showButtonPanel: true,
			showOtherMonths: true,
			defaultDate: "+1w",
			changeMonth: true,
			numberOfMonths: 3,
			showWeek: true,
			setDate: "<?php echo esc_js($event_end_date); ?>",
			onClose: function(selectedDate) {
				calendar_event_end_date = selectedDate;
				//jQuery("#event_date").datepicker("option", "maxDate", selectedDate);
				check_date_errors();
                $(this).removeClass('active');
			}
		}).on('focus', function(){
            $(this).addClass('active');
        });
		
        jQuery(".ui-datepicker-current").life('click', function(e) {
			var active_input = jQuery('.hasDatepicker.active'),
                cur_date = new Date(),
                day = cur_date.getDate(),
                month = cur_date.getMonth() + 1,
                year = cur_date.getFullYear();
                
            cur_date = day + '-' + month + '-' + year;
            active_input.val(cur_date);
		});
        
		jQuery(".event_time_select").change(function() {
			check_date_errors();
		});
		
		jQuery("#hide_event_place_cb").life('click', function() {
			if(jQuery(this).is(':checked')){
				jQuery('#hide_event_place').val(1);
			}else{
				jQuery('#hide_event_place').val(0);
			}
		});

		jQuery("#event_allday_cb").life('click', function() {
			if(jQuery(this).is(':checked')){
				jQuery('#event_allday').val(1);
				jQuery('.event_time_wrapper').find('select').prop('disabled', true);
			}else{
				jQuery('#event_allday').val(0);
				jQuery('.event_time_wrapper').find('select').prop('disabled', false);
			}
		});
		
		jQuery("#set_event_place").click(function(e) {
            e.preventDefault();
			var map_canvas_id = jQuery(jQuery(".google_map").eq(0)).attr('id'),
                geocoder = new google.maps.Geocoder,
				latitude = <?php echo (int) $event_map_latitude ?>,
				longitude = <?php echo (int) $event_map_longitude ?>,
                latlng = new google.maps.LatLng(latitude, longitude),
                mapOptions = {
                    zoom: <?php echo (int) $event_map_zoom ?>,
                    center: latlng,
                    mapTypeId: 'roadmap'
                },
                map = new google.maps.Map(document.getElementById(map_canvas_id), mapOptions),
                address = jQuery("#event_place_address").val();

			geocoder.geocode({'address': address}, function(results, status) {
				var latitude = null;
				var longitude = null;
				
				if (status == google.maps.GeocoderStatus.OK) {
                    map.setCenter(results[0].geometry.location);
                    var marker = new google.maps.Marker({
                        map: map,
                        position: results[0].geometry.location
                    });
                    
					latitude = results[0].geometry.location.lat();
					longitude = results[0].geometry.location.lng();
			
					jQuery("#event_map_latitude").val(latitude);
					jQuery("#event_map_longitude").val(longitude);
				} else {
					alert("<?php echo esc_js( __('Geocode was not successful for the following reason', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ); ?>: " + status);
					return false;
				}

			});
		});


		jQuery('[name="event_repeating"]').live('change', function() {
			var val = jQuery(this).val();
			if (val === 'week' | val === '2week' | val === '3week') {
				jQuery("#event_week_days").show(333);
			} else {
				jQuery("#event_week_days").hide(333);
			}
		});

		check_date_errors();

	});


	function check_date_errors() {
		if (calendar_event_date == calendar_event_end_date) {
			var event_hh = parseInt(jQuery("#event_hh").val());
			var event_mm = parseInt(jQuery("#event_mm").val());
			var event_end_hh = parseInt(jQuery("#event_end_hh").val());
			var event_end_mm = parseInt(jQuery("#event_end_mm").val());

			var show_error_event_datas = false;

			if (event_end_hh < event_hh) {
				show_error_event_datas = true;
			}

			if (event_hh == event_end_hh) {
				if (event_end_mm < event_mm) {
					show_error_event_datas = true;
				}
			}

			if (show_error_event_datas) {
				jQuery("#error_event_datas").show(200);
			} else {
				jQuery("#error_event_datas").hide(200);
			}


		} else {
			jQuery("#error_event_datas").hide(200);
		}
	}
    
}(jQuery));

</script>