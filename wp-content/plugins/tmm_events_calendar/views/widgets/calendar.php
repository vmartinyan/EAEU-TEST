<div class="widget widget_events_calendar">

    <?php if (!empty($instance['title'])): ?>
        <h3 class="widget-title"><?php esc_html_e($instance['title']); ?></h3>
    <?php endif; ?>

    <?php $inique_id = uniqid(); ?>
		
    <div id='calendar_<?php echo $inique_id ?>' style="width: 100%;"></div>
	
    <script type="text/javascript">
        var calendar_<?php echo $inique_id ?> = null;
        jQuery(function() {
            var arguments = {
                header: {
                    left: "prev,next",
                    right: "today",
                    center: "title"                    
                },
				first_day:<?php echo esc_js( get_option('start_of_week') ); ?>
            };
            calendar_<?php echo $inique_id ?> = new THEMEMAKERS_EVENT_CALENDAR("#calendar_<?php echo $inique_id ?>", arguments, true, "");
            calendar_<?php echo $inique_id ?>.init();
        });
    </script>

</div><!--/ .widget-->
