<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://acmemk.com
 * @since      1.0.0
 *
 * @package    Acme_Wpml_Language_Switch
 * @subpackage Acme_Wpml_Language_Switch/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Acme_Wpml_Language_Switch
 * @subpackage Acme_Wpml_Language_Switch/admin
 * @author     Mirko Bianco <mirko@acmemk.com>
 */
class Acme_Wpml_Language_Switch_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;


	/**
	 * WPML array of active languages
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array $languages Language array from WPML filtered by active.
	 */
	private $languages;


	/**
	 * # of active languages
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      integer $c_lang Count of active languages (excluding hidden).
	 */
	private $c_lang;

	/**
	 * Version# of current WPML installation
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $wpml_ersion The current wpml version installed.
	 */
	private $wpml_version;

	/**
	 * Menu location available in current theme
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      array with menu locations.
	 */
	private $menu_location;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 *
	 * @param      string $plugin_name The name of this plugin.
	 * @param      string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * We won't load this script
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/acme-wpml-language-switch-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * We won't load this script
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/acme-wpml-language-switch-admin.js', array( 'jquery' ), $this->version, false );

	}


	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.1.0
	 */
	public function add_plugin_admin_menu() {

		$parent = 'acme_plugin_panel';

		$this->create_acme_admin_menu();
		add_submenu_page( $parent, 'ACME WPML Language Switch', 'Language Switch', 'manage_options', $this->plugin_name, array(
			$this,
			'display_plugin_setup_page'
		) );
		remove_submenu_page( $parent, 'acme_plugin_panel' );

	}

	/**
	 * If not exists, create a ACME Menu for all plugins
	 * 
	 * @since 1.1.0
	 */
	public function create_acme_admin_menu() {
		global $admin_page_hooks;
		if( ! isset( $admin_page_hooks['acme_plugin_panel'] ) ){
			add_menu_page( 'acme_plugin_panel', 'ACME', 'manage_options', 'acme_plugin_panel', NULL, 'dashicons-carrot', 81 );
		}

		return false;
	}
	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */

	public function add_action_links( $links ) {
		/*
		*  Documentation : https://codex.wordpress.org/Plugin_API/Filter_Reference/plugin_action_links_(plugin_file_name)
		*/
		$settings_link = array(
			'<a href="' . admin_url( 'admin.php?page=' . $this->plugin_name ) . '">' . __( 'Settings', $this->plugin_name ) . '</a>',
		);

		return array_merge( $settings_link, $links );

	}

	/**
	 * Check if WPML is Installed
	 *
	 * @since    1.0.0
	 */
	public function is_wpml() {
		$response = false;
		$this->wpml_version = null;
		if ( function_exists( 'icl_object_id' ) ) {
			$cur_ver = ICL_SITEPRESS_VERSION;
			$version = explode( ".", $cur_ver );
			if ( $version[0] > 3 || ( $version[0] > 2 && $version[1] > 1 ) ) {
				$this->wpml_version = $cur_ver;
				$this->c_lang       = 0;
				$languages          = apply_filters( 'wpml_active_languages', null, 'orderby=id&order=desc' );

				$options = get_option( 'icl_sitepress_settings' );
				$hidden  = $options['hidden_languages'];
				foreach ( $languages as $code => $lan ) {
					if ( ! in_array( $code, $hidden ) ) {
						$this->languages[] = $lan;
						++ $this->c_lang;
					}
				}

				$response = true;
			}
		}

		return $response;
	}


	/**
	 * Retrieves menus
	 *
	 * @since    1.0.0
	 */
	public function get_registered_menus() {
		$menus = get_registered_nav_menus();
		foreach ( $menus as $location => $useless ) {
			$this->menu_location[] = $location;
		}

		return false;
	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_setup_page() {
		if ( $this->is_wpml() ) {
			$this->get_registered_menus();
		}
		include_once( 'partials/acme-wpml-language-switch-admin-display.php' );
	}

	/**
	 *
	 * some string validation from form data
	 *
	 * @since    1.0.0
	 *
	 * @param   array $input POST data from WP
	 *
	 * @return array
	 **/
	public function validate( $input ) {
		// All checkboxes inputs
		$valid = array();
		//Cleanup
		foreach ( $input as $k => $val ) {
			switch ( $k ) {
				case substr( $k, 0, 6 ) == "custom":
					$valid[ $k ] = sanitize_text_field( $val );
					break;
				case substr( $k, 0, 8 ) == "location":
					$valid[ $k ] = sanitize_text_field( $val );
					break;
				default:
					$valid[ $k ] = absint( $val );
					break;
			}
		}

		return $valid;
	}

	/**
	 *
	 * let's save our options
	 *
	 * @since    1.0.0
	 **/
	public function options_update() {
		register_setting( $this->plugin_name, $this->plugin_name, array( $this, 'validate' ) );
	}

}
