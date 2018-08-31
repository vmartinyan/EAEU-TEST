<p>
    <label for="<?php echo esc_attr( $widget->get_field_id('boxtitle') ); ?>"><?php _e('Title', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?>:</label>
    <input class="widefat" type="text" id="<?php echo esc_attr( $widget->get_field_id('boxtitle') ); ?>" name="<?php echo esc_attr( $widget->get_field_name('boxtitle') ); ?>" value="<?php echo esc_attr( $instance['boxtitle'] ); ?>" />
</p>
<p>
    <?php
    $checked = "";
    if ($instance['show_title'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr( $widget->get_field_id('show_title') ); ?>" name="<?php echo esc_attr( $widget->get_field_name('show_title') ); ?>" value="true" <?php echo $checked; ?> />
    <label for="<?php echo esc_attr( $widget->get_field_id('show_title') ); ?>"><?php _e('Show event title', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?>:</label>
</p>

<p>
    <?php
    $checked = "";
    if ($instance['show_time_zone'] == 'true') {
        $checked = 'checked="checked"';
    }
    ?>
    <input type="checkbox" id="<?php echo esc_attr( $widget->get_field_id('show_time_zone') ); ?>" name="<?php echo esc_attr( $widget->get_field_name('show_time_zone') ); ?>" value="true" <?php echo $checked; ?> />
    <label for="<?php echo esc_attr( $widget->get_field_id('show_time_zone') ); ?>"><?php _e('Show time zone', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?>:</label>
</p>

<p>
    <label for="<?php echo esc_attr( $widget->get_field_id('month_deep') ); ?>"><?php _e('Soonest event data parsing', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?>:</label>
    <select id="<?php echo esc_attr( $widget->get_field_id('month_deep') ); ?>" name="<?php echo esc_attr( $widget->get_field_name('month_deep') ); ?>" class="widefat">
        <?php for ($i = 1; $i <= 12; $i++) : ?>
            <option <?php echo($instance['month_deep'] == $i ? "selected" : "") ?> value="<?php echo $i ?>"><?php echo $i ?> <?php _e('month', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?><?php if($i>1) echo 's' ?></option>
        <?php endfor; ?>
    </select>
</p>


