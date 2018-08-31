<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://acmemk.com
 * @since      1.0.0
 *
 * @package    Acme_Wpml_Language_Switch
 * @subpackage Acme_Wpml_Language_Switch/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Acme_Wpml_Language_Switch
 * @subpackage Acme_Wpml_Language_Switch/public
 * @author     Mirko Bianco <mirko@acmemk.com>
 */
class Acme_Wpml_Language_Switch_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->acme_options = get_option( $this->plugin_name );

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * Actually this style won't be loaded
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/acme-wpml-language-switch-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * Actually this style won't be loaded
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/acme-wpml-language-switch-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Main plugin function.
	 * The plugin will actually append a new menù item with a link to alternate language.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $items Current Parsed Menu items.
	 * @param      object $args objects from wp_nav_menu()
	 *
	 * @return bool|string
	 */
	public function awls( $items, $args ) {
		$myItems = false;
		if ( $this->acme_options['checked'] && function_exists( 'icl_object_id' ) ) {
			$languages      = apply_filters( 'wpml_active_languages', null, 'orderby=id&order=desc' );
			$lang_active    = null;
			$language_guide = array(
				1 => "country_flag_url",
				2 => "language_code",
				3 => "translated_name",
				4 => "custom",
			);
			foreach ( $languages as $k => $ar ) {
				$lang_active = $ar['active'] != 1 ? $k : $lang_active;
			}
			$url   = !is_wp_error($languages[ $lang_active ]['url'])?$languages[ $lang_active ]['url']:null;
			$label = $languages[ $lang_active ][ $language_guide[ $this->acme_options['show'] ] ];
			if ( $this->acme_options['show'] == 1 ) {
				$label = '<img src="' . $label . '" />';
			}
			if ( $this->acme_options['show'] == 4 ) {
				$label = $this->acme_options[ 'custom_text_' . $lang_active ];
			}
			foreach ( $this->acme_options as $k => $val ) {
				if ( substr( $k, 0, 9 ) == "location_" ) {
						if ( $val == $args->theme_location ) {
							$myItems = $items . '<li class="menu-item awls-item"><a href="' . $url . '">' . $label . '</a></li>';
							if ( $this->acme_options['position'] == 1 ) {
								$myItems = '<li class="menu-item awls-item"><a href="' . $url . '">' . $label . '</a></li>' . $items;
							}

						}
					}
			}
			if ( false !== $myItems ) {
				return $myItems;
			}
		}

		return $items;
	}

}
