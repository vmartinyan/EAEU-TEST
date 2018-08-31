<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              http://acmemk.com
 * @since             1.0.0
 * @package           Acme_Wpml_Language_Switch
 *
 * @wordpress-plugin
 * Plugin Name:       ACME WPML Language Switch
 * Plugin URI:        http://acmemk.com/acme-wpml-language-switch
 * Description:       Have Just Two languages WPML website? Replace WMPL default switcher with this very quick menu item. It just displays the translation link
 * Version:           1.1.1
 * Author:            Mirko Bianco
 * Author URI:        http://acmemk.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       acme-wpml-language-switch
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-acme-wpml-language-switch-activator.php
 */
function activate_acme_wpml_language_switch() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-acme-wpml-language-switch-activator.php';
	Acme_Wpml_Language_Switch_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-acme-wpml-language-switch-deactivator.php
 */
function deactivate_acme_wpml_language_switch() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-acme-wpml-language-switch-deactivator.php';
	Acme_Wpml_Language_Switch_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_acme_wpml_language_switch' );
register_deactivation_hook( __FILE__, 'deactivate_acme_wpml_language_switch' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-acme-wpml-language-switch.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_acme_wpml_language_switch() {

	$plugin = new Acme_Wpml_Language_Switch();
	$plugin->run();

}
run_acme_wpml_language_switch();
