<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       http://acmemk.com
 * @since      1.0.0
 *
 * @package    Acme_Wpml_Language_Switch
 * @subpackage Acme_Wpml_Language_Switch/admin/partials
 */

	$error_msg = null;
	if ( null == $this->wpml_version ) {
		$error_msg = __( 'WPML is not installed or not updated!', $this->plugin_name );
	} else {
		if ( $this->c_lang != 2 ) {
			$error_msg = sprintf ( __( 'There are %d active languages.' , $this->plugin_name) , $this->c_lang );
		}
	}
	//var_dump ( $this->c_lang );
	//
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->
<div class="wrap">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php if ($error_msg): ?>
		<div class="notice notice-error"><p><?php echo $error_msg ?></p></div>
		<div class="notice notice-warning">
			<p><?php printf( __( "Current WPML Version is: <strong>%s</strong>", $this->plugin_name ), $this->wpml_version ); ?></p>

			<p><?php _e( "This Plugin requires WPML 3.2+ in order to work.", $this->plugin_name ) ?></p>

			<p><?php _e( "Only 2 languages must be active.", $this->plugin_name ) ?></p>
		</div>
		<?php ?>
	<?php else: ?>
	<?php

		//Grab all options
		$options = get_option( $this->plugin_name );


		//My Options
		$show          = $options['show'];
		$custom_text_0 = $options['custom_text_' . $this->languages[0]['language_code'] ];
		$custom_text_1 = $options['custom_text_' . $this->languages[1]['language_code'] ];
		$position      = $options['position'];
		foreach($this->menu_location as $location){
			$field_name = 'location_' . $location;//str_replace( "-", '', $location );
			$myLoc[ $field_name ] = $options[ $field_name ];
		}


	?>

	<form method="post" name="acme_wpml_options" action="options.php">

		<?php
			settings_fields( $this->plugin_name );
			do_settings_sections( $this->plugin_name );

		?>
		<input type="hidden" id="<?php echo $this->plugin_name; ?>-checked"
		       name="<?php echo $this->plugin_name; ?>[checked]" value=1 />
		<h2><?php _e('Display Settings:', $this->plugin_name); ?></h2>
		<fieldset>
			<legend class="screen-reader-text"><span><?php _e( 'Display Label:', $this->plugin_name ); ?></span></legend>
			<label for="<?php echo $this->plugin_name; ?>-show">
				<span><?php _e( 'Display Label:', $this->plugin_name ); ?></span>
				<select id="<?php echo $this->plugin_name; ?>-show" name="<?php echo $this->plugin_name; ?>[show]" >
					<option <?php selected($show, 0);?> value=0><?php _e('Choose one...',$this->plugin_name); ?></option>
					<option <?php selected($show, 1);?> value=1><?php _e('Flag',$this->plugin_name); ?></option>
					<option <?php selected($show, 2);?> value=2><?php _e('Language Code',$this->plugin_name); ?></option>
					<option <?php selected($show, 3);?> value=3><?php _e('Translated Language',$this->plugin_name); ?></option>
					<option <?php selected($show, 4);?> value=4><?php _e('Custom Label',$this->plugin_name); ?></option>
				</select>
			</label>
		</fieldset>
		<?php if($show==4): ?>
			<fieldset>
				<legend class="screen-reader-text"><span><?php printf(__( 'Label for %s:', $this->plugin_name ), $this->languages[0]['translated_name']); ?></span></legend>
				<label for="<?php echo $this->plugin_name; ?>-custom_text_<?php echo  $this->languages[0]['language_code'] ?>">
					<span><?php printf(__( 'Label for %s:', $this->plugin_name ), $this->languages[0]['translated_name']); ?></span>
					<input type="text" class="regular-text" id="<?php echo $this->plugin_name; ?>-custom_text_<?php echo  $this->languages[0]['language_code'] ?>"
					       name="<?php echo $this->plugin_name; ?>[custom_text_<?php echo  $this->languages[0]['language_code'] ?>]" value="<?php if(!empty($custom_text_0)) echo $custom_text_0; ?>"/>
				</label>
			</fieldset>
			<fieldset>
				<legend class="screen-reader-text"><span><?php printf(__( 'Label for %s:', $this->plugin_name ), $this->languages[1]['translated_name']); ?></span></legend>
				<label for="<?php echo $this->plugin_name; ?>-custom_text_<?php echo  $this->languages[1]['language_code'] ?>">
					<span><?php printf(__( 'Label for %s:', $this->plugin_name ), $this->languages[1]['translated_name']); ?></span>
					<input type="text" class="regular-text" id="<?php echo $this->plugin_name; ?>-custom_text_<?php echo  $this->languages[1]['language_code'] ?>"
					       name="<?php echo $this->plugin_name; ?>[custom_text_<?php echo  $this->languages[1]['language_code'] ?>]" value="<?php if(!empty($custom_text_1)) echo $custom_text_1; ?>"/>
				</label>
			</fieldset>
		<?php endif; ?>
		<h2><?php _e('Add to Menu:', $this->plugin_name); ?></h2>
		<?php foreach($this->menu_location as $location): ?>
			<?php $field_name = 'location_' . $location;// str_replace( "-", '', $location ); ?>
				<fieldset>
					<legend class="screen-reader-text"><span><?php printf(__('Display in %s',$this->plugin_name),$location); ?></span></legend>
					<label for="<?php echo "$this->plugin_name-$field_name"; ?>">
						<input type="checkbox" id="<?php echo "$this->plugin_name-$field_name"; ?>"
						       name="<?php echo "$this->plugin_name[$field_name]"; ?>" value="<?php echo $location; ?>" <?php checked($myLoc[$field_name], $location); ?>/>
						<span><?php printf(__('Display in %s',$this->plugin_name),$location); ?></span>
					</label>
				</fieldset>
		<?php endforeach; ?>
		<h2><?php _e('Position in Menu:', $this->plugin_name); ?></h2>
		<fieldset>
			<legend class="screen-reader-text"><span><?php _e( 'Insert Item:', $this->plugin_name ); ?></span></legend>
			<label for="<?php echo $this->plugin_name; ?>-position">
				<span><?php _e( 'Insert Item:', $this->plugin_name ); ?></span>
				<select id="<?php echo $this->plugin_name; ?>-position" name="<?php echo $this->plugin_name; ?>[position]" >
					<option <?php selected($position, 0);?> value=0><?php _e('Choose one...',$this->plugin_name); ?></option>
					<option <?php selected($position, 1);?> value=1><?php _e('As FIRST Menu Item',$this->plugin_name); ?></option>
					<option <?php selected($position, 2);?> value=2><?php _e('As LAST Menu Item',$this->plugin_name); ?></option>
				</select>
			</label>
		</fieldset>

		<?php submit_button( __('Save all changes',$this->plugin_name), 'primary', 'submit', true ); ?>
	</form>
		<?php endif; ?>

</div>
