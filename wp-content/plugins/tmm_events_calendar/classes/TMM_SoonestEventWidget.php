<?php

/* 
 * Soonest Event Widget
 */

class TMM_SoonestEventWidget extends WP_Widget {

    //Widget Setup
    function __construct() {
        //Basic settings
        $settings = array('classname' => __CLASS__, 'description' => __('Soonest event', TMM_EVENTS_PLUGIN_TEXTDOMAIN));

        //Creation
	    parent::__construct(__CLASS__, __('ThemeMakers Soonest Event', TMM_EVENTS_PLUGIN_TEXTDOMAIN), $settings);
    }

    //Widget view
    function widget($args, $instance) {
        $args['instance'] = $instance;
	    tmm_locate_template(TMM_EVENTS_PLUGIN_PATH . 'views/widgets/soonest_event.php', $args);
    }

    //Update widget
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['boxtitle'] = $new_instance['boxtitle'];
        $instance['show_title'] = $new_instance['show_title'];
        $instance['month_deep'] = $new_instance['month_deep'];
        $instance['show_time_zone'] = $new_instance['show_time_zone'];
        return $instance;
    }

    //Widget form
    function form($instance) {
        //Defaults
        $defaults = array(
            'boxtitle' => __('Soonest Event', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
            'show_title' => 0,
            'month_deep' => 1,
            'show_time_zone'=>1
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $args = array();
        $args['instance'] = $instance;
        $args['widget'] = $this;
	    tmm_locate_template(TMM_EVENTS_PLUGIN_PATH . 'views/widgets/soonest_event_form.php', $args);
    }

}