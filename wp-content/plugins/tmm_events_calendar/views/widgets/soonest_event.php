<?php
$now = current_time('timestamp');
$month_deep = isset($instance['month_deep']) ? (int) $instance['month_deep'] : 0;
$event = TMM_Event::get_soonest_event($now, 1, $month_deep);
?>

<?php if (!empty($event)): ?>
    <?php if (isset($event[0])): ?>
        <?php if (!empty($event[0])):$event = $event[0]; ?>
            <div class="widget widget_soonest_event <?php echo ($instance['show_title'] ? "show" : "") ?>">

                <?php if (!empty($instance['boxtitle'])){ ?>
                    <h3 class="widget-title"><?php esc_html_e($instance['boxtitle']); ?></h3>
                <?php } ?>
                <div class="post-content">

                    <?php $inique_id = uniqid(); ?>

                    <div class="clearfix event-timer" id="event_timer_<?php echo $inique_id ?>">

                        <?php if ($instance['show_title']){ ?>
                            <a class="post-title" href="<?php echo esc_url($event['url']); ?>">
                                <?php echo esc_html($event['title']); ?>
                            </a>
                        <?php } ?>

                        <p>
                            <span class="event-numbers">00</span>
                            <span class="event-text"><?php _e('days', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?></span>
                            <span class="event-numbers">00</span>
                            <span class="event-text"><?php _e('hr', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?></span>
                            <span class="event-numbers">00</span>
                            <span class="event-text"><?php _e('min', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?></span>
                            <span class="event-numbers">00</span>
                            <span class="event-text"><?php _e('sec', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?></span>		
                            <?php if($instance['show_time_zone']): ?>
                                <span class="zones"><?php echo TMM_Event::get_timezone_string() ?></span>
                            <?php else: ?>&nbsp;&nbsp;&nbsp;<?php endif; ?>
                        </p>

                    </div><!--/ #event_timer-->	

                </div><!--/ .event-holder-->

                <script type="text/javascript">
                    var countdown_<?php echo $inique_id ?> = null;
                    jQuery(function() {       
                        countdown_<?php echo $inique_id ?> = new THEMEMAKERS_EVENT_COUNTDOWN(<?php echo strtotime(TMM_Event::convert_time_to_zone_time($event['start']), $now) ?>, "#event_timer_<?php echo $inique_id ?>");
                        countdown_<?php echo $inique_id ?>.init();
                    });
                </script>

            </div><!--/ .widget-->

        <?php endif; ?>
    <?php endif; ?>
<?php endif; ?>


