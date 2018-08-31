<p>
    <label for="<?php echo esc_attr( $widget->get_field_id('title') ); ?>"><?php _e('Title', TMM_EVENTS_PLUGIN_TEXTDOMAIN) ?>:</label>
    <input class="widefat" type="text" id="<?php echo esc_attr( $widget->get_field_id('title') ); ?>" name="<?php echo esc_attr( $widget->get_field_name('title') ); ?>" value="<?php echo esc_attr( $instance['title'] ); ?>" />
</p>
