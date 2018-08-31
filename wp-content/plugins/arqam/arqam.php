<?php
/*
	Plugin Name: Arqam
	Plugin URI: http://codecanyon.net/item/tielabs/5085289
	Description: WordPress Social Counter Plugin
	Author: TieLabs
	Version: 2.4.0
	Author URI: http://tielabs.com
*/


/*-----------------------------------------------------------------------------------*/
# Get Plugin Options and Transient
/*-----------------------------------------------------------------------------------*/
require_once( 'updates.php' );
require_once( 'back-end.php' );
require_once( 'counters.php' );
require_once( 'inc/tumblr/tumblroauth.php' );
require_once( 'inc/500px/500pxoauth.php' );
require_once( 'inc/linkedin/linkedin.php' );


if ( ! class_exists( 'OAuthException' ) ){
	require_once( 'inc/OAuth.php' );
}

define ( 'ARQAM_Plugin',     'Arqam' );
define ( 'ARQAM_Plugin_ver', '2.0.0' );

$arq_data      = array();
$arq_transient = get_transient( 'arq_counters' );
$arq_options	 = get_option( 'arq_options'  );

if( empty($arq_options)	){
	$arq_options = array();
}

if( empty($arq_transient) || (false ===  $arq_transient) ){
	$arq_transient = array();
}

$arq_social_items = array( 'facebook', 'twitter', 'google', 'youtube', 'vimeo', 'dribbble', 'github', 'envato', 'soundcloud', 'behance', 'instagram', 'mailchimp', 'mailpoet', 'mymail', 'foursquare', 'linkedin', 'vk', 'tumblr', '500px', 'pinterest', 'flickr', 'steam', 'spotify', 'twitch', 'mixcloud', 'goodreads', 'rss', 'posts', 'comments', 'groups', 'forums', 'members', 'topics', 'replies');



/*-----------------------------------------------------------------------------------*/
# Register and Enquee plugin's styles and scripts
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arqam_scripts_styles' ) ) :
	function arqam_scripts_styles(){

		wp_register_style( 'arqam-style' , plugins_url('assets/style.css' , __FILE__) ) ;
		wp_enqueue_style ( 'arqam-style' );

		if( !is_admin()){
			wp_register_script( 'arqam-scripts', plugins_url('assets/js/scripts.js', __FILE__) , array( 'jquery' ), false, true );
			wp_enqueue_script ( 'arqam-scripts' );
		}

	}
	add_action( 'init', 'arqam_scripts_styles' );
endif;



/*-----------------------------------------------------------------------------------*/
# Load Text Domain
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arqam_init' ) ) :
	function arqam_init() {

		load_plugin_textdomain( 'arq' , false, dirname( plugin_basename( __FILE__ ) ).'/languages' );

	}
	add_action( 'plugins_loaded', 'arqam_init' );
endif;



/*-----------------------------------------------------------------------------------*/
# Store Defaults settings
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arqam_plugin_activate' ) ) :
	function arqam_plugin_activate() {

		if( !get_option( 'arqam_active' ) ){
			$default_data = array(
				'social' => array(
					'facebook'   => array( 'text' => __( 'Fans',        'arq' ) ),
					'twitter'    => array( 'text' => __( 'Followers',	  'arq' )),
					'google'     => array( 'text' => __( 'Followers',	  'arq' )),
					'linkedin'   => array( 'text' => __( 'Followers',	  'arq' )),
					'tumblr'     => array( 'text' => __( 'Followers',	  'arq' )),
					'500px'      => array( 'text' => __( 'Followers',	  'arq' )),
					'pinterest'  => array( 'text' => __( 'Followers',	  'arq' )),
					'spotify'    => array( 'text' => __( 'Followers',	  'arq' )),
					'twitch'     => array( 'text' => __( 'Followers',	  'arq' )),
					'mixcloud'   => array( 'text' => __( 'Followers',	  'arq' )),
					'dribbble'   => array( 'text' => __( 'Followers',	  'arq' )),
					'envato'     => array( 'text' => __( 'Followers',	  'arq' )),
					'github'     => array( 'text' => __( 'Followers',	  'arq' )),
					'soundcloud' => array( 'text' => __( 'Followers',	  'arq' )),
					'behance'    => array( 'text' => __( 'Followers',	  'arq' )),
					'instagram'  => array( 'text' => __( 'Followers',	  'arq' )),
					'youtube'    => array( 'text' => __( 'Subscribers', 'arq' )),
					'vimeo'      => array( 'text' => __( 'Subscribers',	'arq' )),
					'mailchimp'  => array( 'text' => __( 'Subscribers',	'arq' )),
					'mailpoet'   => array( 'text' => __( 'Subscribers',	'arq' )),
					'mymail'     => array( 'text' => __( 'Subscribers',	'arq' )),
					'rss'        => array( 'text' => __( 'Subscribers',	'arq' )),
					'foursquare' => array( 'text' => __( 'Friends',     'arq' )),
					'goodreads'  => array( 'text' => __( 'Friends',     'arq' )),
					'vk'         => array( 'text' => __( 'Members',     'arq' )),
					'flickr'     => array( 'text' => __( 'Members',     'arq' )),
					'steam'      => array( 'text' => __( 'Members',     'arq' )),
					'members'    => array( 'text' => __( 'Members',     'arq' )),
					'comments'   => array( 'text' => __( 'Comments',    'arq' )),
					'posts'      => array( 'text' => __( 'Posts',       'arq' )),
					'groups'     => array( 'text' => __( 'Groups',      'arq' )),
					'forums'     => array( 'text' => __( 'Forums',      'arq' )),
					'toptcs'     => array( 'text' => __( 'Topics',      'arq' )),
					'replies'    => array( 'text' => __( 'Replies',     'arq' ))
				),
				'cache' => 5
			);

			update_option( 'arq_options',  $default_data);
			update_option( 'arqam_active', ARQAM_Plugin_ver );
		}

	}
	register_activation_hook( __FILE__, 'arqam_plugin_activate' );
endif;



/*-----------------------------------------------------------------------------------*/
# Get Data From API's
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_remote_get' ) ) :
	function arq_remote_get( $url, $json = true, $args = array( 'timeout' => 18 , 'sslverify' => false ) ) {

		$get_request = preg_replace ( '/\s+/', '', $url);
		$get_request = wp_remote_get( $url , $args );

		if( is_admin() && isset( $_GET['page'] ) && $_GET['page'] == 'arqam' && !empty( $_REQUEST['debug'] ) ) {
			print_R( $get_request );
			return 0;
		}

		$request = wp_remote_retrieve_body( $get_request );

		if( $json ){
			$request = @json_decode( $request , true );
		}
		return $request;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Update Options and Transient
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_update_count' ) ) :
	function arq_update_count( $data ){

		global $arq_options, $arq_transient ;

		if( empty( $arq_options['cache'] ) || !is_int($arq_options['cache']) ){
			$cache = 2 ;
		}
		else{
			$cache = $arq_options['cache'] ;
		}

		if( is_array($data) ){
			foreach( $data as $item => $value ){
				$arq_transient[$item] = $value;
				$arq_options['data'][$item] = $value;
			}
		}
		set_transient( 'arq_counters', $arq_transient , $cache*60*60 );
		update_option( 'arq_options' , $arq_options );

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Number Format Function
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_format_num' ) ) :
	function arq_format_num( $number ){

		if( !is_numeric( $number ) ){
		  return $number ;
		}

		global $wp_locale;

		$sep   = array();
		$sep[] = ( isset( $wp_locale ) ) ? $wp_locale->number_format['decimal_point'] : '.';
		$sep[] = ( isset( $wp_locale ) ) ? $wp_locale->number_format['thousands_sep'] : ',';

		$number = str_replace( $sep, '', $number );


		if($number >= 1000000){
		  return round( ($number/1000)/1000 , 1) . "M";
		}
		elseif($number >= 100000){
		  return round( $number/1000, 0) . "k";
		}
		else{
		  return number_format_i18n( $number );
		}
	}
endif;



/*-----------------------------------------------------------------------------------*/
# Counters data
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_counters_data' ) ) :
	function arq_counters_data() {

		global $arq_data, $arq_options, $arq_social_items;

		$arqam_data = array();

		# Counters order ----------
		if (!empty($arq_options['sort']) && is_array($arq_options['sort'])) {
			$arq_sort_items = $arq_options['sort'];
			$arq_new_items  = array_diff( $arq_social_items, $arq_sort_items );

			if (!empty($arq_new_items)) {
				$arq_sort_items = array_merge( $arq_sort_items, $arq_new_items );
			}
		}
		else {
			$arq_sort_items = $arq_social_items;
		}


		# Prepare the Counters data ----------
		foreach ($arq_sort_items as $arq_item) {

			# Reset the include variable ----------
			$include = false;

			switch ($arq_item) {

				# Facebook ----------
				case 'facebook':
					if ( ! empty($arq_options['social']['facebook']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['facebook']['text'] ) ? __('Fans', 'arq') : $arq_options['social']['facebook']['text'];
						$count   = arq_facebook_count();
						$icon    = '<i class="counter-icon arqicon-facebook"></i>';
						$url     = 'http://www.facebook.com/' . $arq_options['social']['facebook']['id'];
					}
					break;


				# Twitter ----------
				case 'twitter':
					if (! empty($arq_options['social']['twitter']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['twitter']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['twitter']['text'];
						$count   = arq_twitter_count();
						$icon    = '<i class="counter-icon arqicon-twitter"></i>';
						$url     = 'http://twitter.com/' . $arq_options['social']['twitter']['id'];
					}
					break;


				# Google+ ----------
				case 'google':
					if ( !empty($arq_options['social']['google']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['google']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['google']['text'];
						$count   = arq_google_count();
						$icon    = '<i class="counter-icon arqicon-google-plus"></i>';
						$url     = 'http://plus.google.com/' . $arq_options['social']['google']['id'];
					}
					break;


				# Youtube ----------
				case 'youtube':
					if ( ! empty($arq_options['social']['youtube']['id']) ){

						$include = true;
						$text    = empty( $arq_options['social']['youtube']['text'] ) ? __('Subscribers', 'arq') : $arq_options['social']['youtube']['text'];
						$count   = arq_youtube_count();
						$icon    = '<i class="counter-icon arqicon-youtube"></i>';

						$type    = 'user';
						if (!empty($arq_options['social']['youtube']['type']) && $arq_options['social']['youtube']['type'] == 'Channel'){
							$type  = 'channel';
						}
						$url     = 'http://youtube.com/' . $type . '/' . $arq_options['social']['youtube']['id'];
					}
					break;


				# Vimeo ----------
				case 'vimeo':
					if ( ! empty($arq_options['social']['vimeo']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['vimeo']['text'] ) ? __('Subscribers', 'arq') : $arq_options['social']['vimeo']['text'];
						$count   = arq_vimeo_count();
						$icon    = '<i class="counter-icon arqicon-vimeo"></i>';
						$url     = 'https://vimeo.com/channels/' . $arq_options['social']['vimeo']['id'];
					}
					break;


				# Github ----------
				case 'github':
					if ( ! empty($arq_options['social']['github']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['github']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['github']['text'];
						$count   = arq_github_count();
						$icon    = '<i class="counter-icon arqicon-github"></i>';
						$url     = 'https://github.com/' . $arq_options['social']['github']['id'];
					}
					break;


				# Dribbble ----------
				case 'dribbble':
					if ( ! empty($arq_options['social']['dribbble']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['dribbble']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['dribbble']['text'];
						$count   = arq_dribbble_count();
						$icon    = '<i class="counter-icon arqicon-dribbble"></i>';
						$url     = 'http://dribbble.com/' . $arq_options['social']['dribbble']['id'];
					}
					break;


				# Envato Market ----------
				case 'envato':
					if ( ! empty($arq_options['social']['envato']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['envato']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['envato']['text'];
						$count   = arq_envato_count();
						$icon    = '<i class="counter-icon arqicon-envato"></i>';
						$url     = 'http://' . $arq_options['social']['envato']['site'] . '.net/user/' . $arq_options['social']['envato']['id'];
					}
					break;


				# SoundCloud ----------
				case 'soundcloud':
					if ( ! empty($arq_options['social']['soundcloud']['id'] ) && !empty($arq_options['social']['soundcloud']['api']) ){
						$include = true;
						$text    = empty( $arq_options['social']['soundcloud']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['soundcloud']['text'];
						$count   = arq_soundcloud_count();
						$icon    = '<i class="counter-icon arqicon-soundcloud"></i>';
						$url     = 'http://soundcloud.com/' . $arq_options['social']['soundcloud']['id'];
					}
					break;


				# Behance ----------
				case 'behance':
					if ( ! empty($arq_options['social']['behance']['id']) && !empty($arq_options['social']['behance']['api']) ){
						$include = true;
						$text    = empty( $arq_options['social']['behance']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['behance']['text'];
						$count   = arq_behance_count();
						$icon    = '<i class="counter-icon arqicon-behance"></i>';
						$url     = 'http://www.behance.net/' . $arq_options['social']['behance']['id'];
					}
					break;


				# Instagram ----------
				case 'instagram':
					if ( ! empty($arq_options['social']['instagram']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['instagram']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['instagram']['text'];
						$count   = arq_instagram_count();
						$icon    = '<i class="counter-icon arqicon-instagram"></i>';
						$url     = 'http://instagram.com/' . $arq_options['social']['instagram']['id'];
					}
					break;


				# MailChimp ----------
				case 'mailchimp':
					if ( ! empty($arq_options['social']['mailchimp']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['mailchimp']['text'] ) ? __('Subscribers', 'arq') : $arq_options['social']['mailchimp']['text'];
						$count   = arq_mailchimp_count();
						$icon    = '<i class="counter-icon arqicon-envelope"></i>';
						$url     = esc_url($arq_options['social']['mailchimp']['url']);
					}
					break;


				# mailPoet ----------
				case 'mailpoet':
					if ( ! empty($arq_options['social']['mailpoet']['list']) && class_exists( 'MailPoet\WP\Functions' ) ){
						$include = true;
						$text    = empty( $arq_options['social']['mailpoet']['text'] ) ? __('Subscribers', 'arq') : $arq_options['social']['mailpoet']['text'];
						$count   = arq_mailpoet_count();
						$icon    = '<i class="counter-icon arqicon-envelope"></i>';
						$url     = esc_url($arq_options['social']['mailpoet']['url']);
					}
					break;


				# MyMail ----------
				case 'mymail':
					if ( !empty($arq_options['social']['mymail']['list']) && class_exists('mailster') ){
						$include = true;
						$text    = empty( $arq_options['social']['mymail']['text'] ) ? __('Subscribers', 'arq') : $arq_options['social']['mymail']['text'];
						$count   = arq_mymail_count();
						$icon    = '<i class="counter-icon arqicon-envelope"></i>';
						$url     = esc_url($arq_options['social']['mymail']['url']);
					}
					break;


				# Foursquare ----------
				case 'foursquare':
					if ( !empty($arq_options['social']['foursquare']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['foursquare']['text'] ) ? __('Friends', 'arq') : $arq_options['social']['foursquare']['text'];
						$count   = arq_foursquare_count();
						$icon    = '<i class="counter-icon arqicon-foursquare"></i>';
						$url     = 'http://foursquare.com/' . $arq_options['social']['foursquare']['id'];
					}
					break;


				# LinkedIn ----------
				case 'linkedin':
					if ( (!empty($arq_options['social']['linkedin']['type']) && $arq_options['social']['linkedin']['type'] == 'Company' && !empty($arq_options['social']['linkedin']['company']) ) ||
							 (!empty($arq_options['social']['linkedin']['type']) && $arq_options['social']['linkedin']['type'] == 'Profile' && !empty($arq_options['social']['linkedin']['profile'])) ){

						$include = true;
						$text    = empty( $arq_options['social']['linkedin']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['linkedin']['text'];
						$count   = arq_linkedin_count();
						$icon    = '<i class="counter-icon arqicon-linkedin"></i>';

						$linkedin_link = '';
						if( ! empty($arq_options['social']['linkedin']['type']) ){
							if ( $arq_options['social']['linkedin']['type'] == 'Profile' && $arq_options['social']['linkedin']['profile'] ){
								$linkedin_link = $arq_options['social']['linkedin']['profile'];
							}
							if ( $arq_options['social']['linkedin']['type'] == 'Company' && $arq_options['social']['linkedin']['company'] ){
								$linkedin_link = 'https://www.linkedin.com/company/'.$arq_options['social']['linkedin']['company'];
							}
						}

						$url = esc_url( $linkedin_link );
					}
					break;


				# Vk ----------
				case 'vk':
					if ( ! empty($arq_options['social']['vk']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['vk']['text'] ) ? __('Members', 'arq') : $arq_options['social']['vk']['text'];
						$count   = arq_vk_count();
						$icon    = '<i class="counter-icon arqicon-vk"></i>';
						$url     = 'http://vk.com/' . $arq_options['social']['vk']['id'];
					}
					break;


				# Tumblr ----------
				case 'tumblr':
					if ( !empty($arq_options['social']['tumblr']['hostname']) ){
						$include = true;
						$text    = empty( $arq_options['social']['tumblr']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['tumblr']['text'];
						$count   = arq_tumblr_count();
						$icon    = '<i class="counter-icon arqicon-tumblr"></i>';
						$url     = esc_url( $arq_options['social']['tumblr']['hostname'] );
					}
					break;


				# 500PX ----------
				case '500px':
					if ( !empty($arq_options['social']['500px']['username']) ){
						$include = true;
						$text    = empty( $arq_options['social']['500px']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['500px']['text'];
						$count   = arq_500px_count();
						$icon    = '<i class="counter-icon arqicon-500px"></i>';
						$url     = 'http://500px.com/' . $arq_options['social']['500px']['username'];
					}
					break;


				# Pinterest ----------
				case 'pinterest':
					if ( !empty($arq_options['social']['pinterest']['username']) ){
						$include = true;
						$text    = empty( $arq_options['social']['pinterest']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['pinterest']['text'];
						$count   = arq_pinterest_count();
						$icon    = '<i class="counter-icon arqicon-pinterest"></i>';
						$url     = 'http://www.pinterest.com/' . $arq_options['social']['pinterest']['username'];
					}
					break;


				# Flickr ----------
				case 'flickr':
					if ( !empty($arq_options['social']['flickr']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['flickr']['text'] ) ? __('Members', 'arq') : $arq_options['social']['flickr']['text'];
						$count   = arq_flickr_count();
						$icon    = '<i class="counter-icon arqicon-flickr"></i>';
						$url     = 'https://www.flickr.com/groups/' . $arq_options['social']['flickr']['id'];
					}
					break;


				# Steam ----------
				case 'steam':
					if ( !empty($arq_options['social']['steam']['group']) ){
						$include = true;
						$text    = empty( $arq_options['social']['steam']['text'] ) ? __('Members', 'arq') : $arq_options['social']['steam']['text'];
						$count   = arq_steam_count();
						$icon    = '<i class="counter-icon arqicon-steam"></i>';
						$url     = 'http://steamcommunity.com/groups/' . $arq_options['social']['steam']['group'];
					}
					break;


				# Spotify ----------
				case 'spotify':
					if ( ! empty($arq_options['social']['spotify']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['spotify']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['spotify']['text'];
						$count   = arq_spotify_count();
						$icon    = '<i class="counter-icon arqicon-spotify"></i>';
						$url     = esc_url($arq_options['social']['spotify']['id']);
					}
					break;


				# Goodreads ----------
				case 'goodreads':
					if ( !empty($arq_options['social']['goodreads']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['goodreads']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['goodreads']['text'];
						$count   = arq_goodreads_count();
						$url     = esc_url($arq_options['social']['goodreads']['id']);
						$icon    = '
						<i class="counter-icon arqicon-goodreads">
						  <svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="45" height="29" viewBox="0 0 430.117 430.118" xml:space="preserve">
							<path id="Goodreads" d="M213.901,302.077c46.55-0.388,79.648-23.671,99.288-69.843h1.026v70.422c0,5.25-0.346,13.385-1.026,24.445
							  c-1.4,11.444-5.144,23.766-11.216,36.959c-6.081,12.414-15.9,22.995-29.435,31.718c-13.391,9.502-32.063,14.449-56.047,14.841
							  c-23.102,0-42.638-6.016-58.63-18.043c-16.344-11.835-25.893-31.045-28.665-57.619h-20.32c2.084,34.527,13.105,59.169,33.08,73.917
							  c19.453,14.16,44.132,21.244,74.02,21.244c29.522,0,52.549-5.525,69.051-16.591c16.326-10.669,28.05-23.966,35.181-39.871
							  c7.122-15.905,11.379-31.045,12.76-45.393c1.055-14.365,1.568-24.642,1.568-30.849V6.987h-20.33v64.021h-1.026
							  c-7.827-23.468-20.764-41.22-38.84-53.254C256.102,5.922,235.949,0,213.892,0c-38.41,0.779-67.591,15.619-87.563,44.529
							  c-20.507,28.707-30.747,64.121-30.747,106.218c0,43.266,9.724,79.056,29.176,107.38
							  C144.409,287.044,174.11,301.689,213.901,302.077z M140.414,60.245c15.971-26.194,40.463-39.771,73.488-40.741
							  c33.874,0.975,58.964,14.165,75.308,39.582c16.326,25.419,24.493,55.972,24.493,91.67c0,35.701-8.167,66.058-24.493,91.083
							  c-16.344,26.589-41.434,40.165-75.308,40.744c-31.967-0.588-56.304-13.782-72.972-39.577
							  c-16.855-25.029-25.277-55.778-25.277-92.254C115.648,116.605,123.901,86.434,140.414,60.245z"/>
						  </svg>
						</i>';
					}
					break;


				# Twitch ----------
				case 'twitch':
					if ( ! empty($arq_options['social']['twitch']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['twitch']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['twitch']['text'];
						$count   = arq_twitch_count();
						$icon    = '<i class="counter-icon arqicon-twitch"></i>';
						$url     = 'http://www.twitch.tv/' . $arq_options['social']['twitch']['id'] . '/profile';
					}
					break;


				# Mixcloud ----------
				case 'mixcloud':
					if ( ! empty($arq_options['social']['mixcloud']['id']) ){
						$include = true;
						$text    = empty( $arq_options['social']['mixcloud']['text'] ) ? __('Followers', 'arq') : $arq_options['social']['mixcloud']['text'];
						$count   = arq_mixcloud_count();
						$url     = 'https://www.mixcloud.com/' . $arq_options['social']['mixcloud']['id'] . '/';
						$icon    = '<i class="counter-icon arqicon-mixcloud"></i>';
					}
					break;


				# Rss ----------
				case 'rss':
					if ( ! empty($arq_options['social']['rss']['url']) ){
						$include = true;
						$text    = empty( $arq_options['social']['rss']['text'] ) ? __('Subscribers', 'arq') : $arq_options['social']['rss']['text'];
						$count   = arq_rss_count();
						$icon    = '<i class="counter-icon arqicon-feed"></i>';
						$url     = esc_url($arq_options['social']['rss']['url']);
					}
					break;


				# Posts ----------
				case 'posts':
					if ( isset($arq_options['social']['posts']['active']) ){
						$include = true;
						$text    = empty( $arq_options['social']['posts']['text'] ) ? __('Posts', 'arq') : $arq_options['social']['posts']['text'];
						$count   = arq_posts_count();
						$icon    = '<i class="counter-icon arqicon-file-text"></i>';
						$url     = empty( $arq_options['social']['posts']['url'] ) ? '' : $arq_options['social']['posts']['url'];
					}
					break;


				# Comments ----------
				case 'comments':
					if ( isset($arq_options['social']['comments']['active']) ){
						$include = true;
						$text    = empty( $arq_options['social']['comments']['text'] ) ? __('Comments', 'arq') : $arq_options['social']['comments']['text'];
						$count   = arq_comments_count();
						$icon    = '<i class="counter-icon arqicon-comments"></i>';
						$url     = empty( $arq_options['social']['comments']['url'] ) ? '' : $arq_options['social']['comments']['url'];
					}
					break;


				# Members ----------
				case 'members':
					if ( isset($arq_options['social']['members']['active']) ){
						$include = true;
						$text    = empty( $arq_options['social']['members']['text'] ) ? __('Members', 'arq') : $arq_options['social']['members']['text'];
						$count   = arq_members_count();
						$icon    = '<i class="counter-icon arqicon-user"></i>';
						$url     = empty( $arq_options['social']['members']['url'] ) ? '' : $arq_options['social']['members']['url'];
					}
					break;


				# Groups ----------
				case 'groups':
					if ( isset($arq_options['social']['groups']['active']) ){
						$include = true;
						$text    = empty( $arq_options['social']['groups']['text'] ) ? __('Groups', 'arq') : $arq_options['social']['groups']['text'];
						$count   = arq_groups_count();
						$icon    = '<i class="counter-icon arqicon-group"></i>';
						$url     = empty( $arq_options['social']['groups']['url'] ) ? '' : $arq_options['social']['groups']['url'];
					}
					break;


				# Forums ----------
				case 'forums':
					if ( isset($arq_options['social']['forums']['active']) ){
						$include = true;
						$text    = empty( $arq_options['social']['forums']['text'] ) ? __('Forums', 'arq') : $arq_options['social']['forums']['text'];
						$count   = arq_bbpress_count('forums');
						$icon    = '<i class="counter-icon arqicon-folder-open"></i>';
						$url     = empty( $arq_options['social']['forums']['url'] ) ? '' : $arq_options['social']['forums']['url'];
					}
					break;


				# Topics ----------
				case 'topics':
					if ( isset($arq_options['social']['topics']['active']) ){
						$include = true;
						$text    = empty( $arq_options['social']['topics']['text'] ) ? __('Topics', 'arq') : $arq_options['social']['topics']['text'];
						$count   = arq_bbpress_count('topics');
						$icon    = '<i class="counter-icon arqicon-copy"></i>';
						$url     = empty( $arq_options['social']['topics']['url'] ) ? '' : $arq_options['social']['topics']['url'];
					}
					break;


				# Replies ----------
				case 'replies':
					if ( isset($arq_options['social']['replies']['active']) ){
						$include = true;
						$text    = empty( $arq_options['social']['replies']['text'] ) ? __('Replies', 'arq') : $arq_options['social']['replies']['text'];
						$count   = arq_bbpress_count('replies');
						$icon    = '<i class="counter-icon arqicon-commenting"></i>';
						$url     = empty( $arq_options['social']['replies']['url'] ) ? '' : $arq_options['social']['replies']['url'];
					}
					break;
			}

			# Add to the counters Array ----------
			if ( $include ) {
				$arqam_data[ $arq_item ] = array(
					'text'  => $text,
					'count' => $count,
					'icon'  => $icon,
					'url'   => $url,
				);
			}

		} //End Foreach

		# Update the counters cache ----------
		if( ! empty( $arq_data) ){
			arq_update_count( $arq_data );
		}

		return $arqam_data;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Get Social Counters for default widget and shortcode
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_get_counters' ) ) :
	function arq_get_counters( $layout='', $columns = 3, $dark = false, $width = '', $block_width = '', $inside_widget = false ){

		# Box Layout ----------
		if( $layout == 'gray' ){
			$class = " arq-outer-frame";
		}
		elseif ( $layout == 'colored' ){
			$class = " arq-outer-frame arq-colored";
		}
		elseif ( $layout == 'colored_border' ){
			$class = " arq-outer-frame arq-border-colored";
		}
		elseif ( $layout == 'flat' ){
			$class = " arq-flat";
		}
		else{
			$class = " arq-metro arq-flat arq-col3";
		}


		# Number of columns ----------
		if( $layout != 'metro' ){
			$class .= ' arq-col'.$columns;
		}


		# Dark skin ----------
		if( $dark ){
			$class .= ' arq-dark';
		}


		# Check if the box is inside widget or not ----------
		if( ! empty($inside_widget) ){
			$class .= ' inside-widget';
		}


		# Set custom width for the counters ----------
		if( !empty($width) ){
			if( strpos( $width, 'px') === false && strpos( $width, '%') === false ){
				$width .= 'px';
			}
			$width = ' style="width:'.$width.';"';
		}

		# set custom width for the box ----------
		if( !empty($block_width) ){
			if( strpos( $block_width, 'px') === false && strpos( $block_width, '%') === false ){
				$block_width .= 'px';
			}
			$block_width = ' style="width:'.$block_width.';"';
		}


		# Open links in a new tab? ----------
		$new_window = ' target="_blank" ';


		# Get the counters data ----------
		$arq_counters = arq_counters_data();

		?>
		<div class="arqam-widget-counter<?php echo $class ?>"<?php echo $block_width; ?>>
			<ul>
				<?php

				foreach ( $arq_counters as $social => $counter ) {
					?>

					<li class="arq-<?php echo $social ?>"<?php echo $width ?>>
						<a href="<?php echo $counter['url'] ?>"<?php echo $new_window ?>>
							<?php echo $counter['icon'] ?>
							<span><?php echo arq_format_num( $counter['count'] ) ?></span>
							<small><?php echo $counter['text']; ?></small>
						</a>
					</li>

					<?php
				}
				?>
			</ul>
		</div>
		<!-- Arqam Social Counter Plugin : http://codecanyon.net/user/TieLabs/portfolio?ref=TieLabs -->
		<?php

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Custom Css and cystom Colors
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arqam_custom_css' ) ) :
	function arqam_custom_css() {

		global $arq_options, $arq_social_items;

		if( !empty( $arq_options['css'] ) || !empty( $arq_options['color'] ) ){ ?>

		<style type="text/css" media="screen">
			<?php

			$css_code =  str_replace( '<pre>', '', htmlspecialchars_decode( $arq_options['css'] ));
			echo str_replace("</pre>", "", $css_code ), "\n";

		 	foreach( $arq_social_items as $item ){

		 		if( !empty( $arq_options['color'][$item] ) ){
		 			if( $item == '500px' ){
		 				$arq_options['color']['fivehundredpx'] = $arq_options['color'][$item];
		 				$item = 'fivehundredpx';
		 			}
					?>

					.arqam-widget-counter.arq-colored li.arq-<?php echo $item ?> a i,
					.arqam-widget-counter.arq-flat li.arq-<?php echo $item ?> a,
					.arqam-widget-counter.arq-outer-frame.arq-border-colored li.arq-<?php echo $item ?>:hover a i{
						background-color:<?php echo $arq_options['color'][$item] ?> !important;
					}
					.arqam-widget-counter.arq-outer-frame.arq-border-colored li.arq-<?php echo $item ?> a i{
						border-color:<?php echo $arq_options['color'][$item] ?>;
						color: <?php echo $arq_options['color'][$item] ?>;
					}

					<?php
				}
			}
			?>
		</style>
		<?php
		}

	}
	add_action('wp_head', 'arqam_custom_css');
endif;

