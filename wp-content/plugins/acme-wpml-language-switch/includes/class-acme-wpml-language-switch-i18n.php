<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       http://acmemk.com
 * @since      1.0.0
 *
 * @package    Acme_Wpml_Language_Switch
 * @subpackage Acme_Wpml_Language_Switch/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Acme_Wpml_Language_Switch
 * @subpackage Acme_Wpml_Language_Switch/includes
 * @author     Mirko Bianco <mirko@acmemk.com>
 */
class Acme_Wpml_Language_Switch_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'acme-wpml-language-switch',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
