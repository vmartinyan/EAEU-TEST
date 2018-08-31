<?php

/* 
 * Events Calendar Widget
 */

class TMM_EventsCalendarWidget extends WP_Widget {

    //Widget Setup
    function __construct() {
        //Basic settings
        $settings = array('classname' => __CLASS__, 'description' => __('Events calendar', TMM_EVENTS_PLUGIN_TEXTDOMAIN));

        //Creation
	    parent::__construct(__CLASS__, __('ThemeMakers Events Calendar', TMM_EVENTS_PLUGIN_TEXTDOMAIN), $settings);
    }

    //Widget view
    function widget($args, $instance) {
        $args['instance'] = $instance;
	    tmm_locate_template(TMM_EVENTS_PLUGIN_PATH . 'views/widgets/calendar.php', $args);
    }

    //Update widget
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = $new_instance['title'];
        return $instance;
    }

    //Widget form
    function form($instance) {
        //Defaults
        $defaults = array(
            'title' => __('Events Calendar', TMM_EVENTS_PLUGIN_TEXTDOMAIN),
        );
        $instance = wp_parse_args((array) $instance, $defaults);
        $args = array();
        $args['instance'] = $instance;
        $args['widget'] = $this;
	    tmm_locate_template(TMM_EVENTS_PLUGIN_PATH . 'views/widgets/calendar_form.php', $args);
    }

}