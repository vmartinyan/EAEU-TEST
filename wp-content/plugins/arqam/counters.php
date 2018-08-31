<?php

/*-----------------------------------------------------------------------------------*/
# Twitter Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_twitter_count' ) ) :
	function arq_twitter_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['twitter']) ){
			$result = $arq_transient['twitter'];
		}
		elseif( empty($arq_transient['twitter']) && !empty($arq_data) && !empty( $arq_options['data']['twitter'] )  ){
			$result = $arq_options['data']['twitter'];
		}
		else{
			$id    = $arq_options['social']['twitter']['id'];
			$token = get_option('arqam_TwitterToken');

			$args = array(
				'httpversion' => '1.1',
				'blocking' 		=> true,
				'timeout'     => 10,
				'headers'     => array(
					'Authorization' => "Bearer $token"
				)
			);

			add_filter('https_ssl_verify', '__return_false');
			$api_url  = "https://api.twitter.com/1.1/users/show.json?screen_name=$id";
			$response = arq_remote_get( $api_url, true, $args );

			if( !empty( $response['followers_count'] ) )
				$result = $response['followers_count'];

			if( !empty( $result ) ) //To update the stored data
				$arq_data['twitter'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['twitter'] ) ) //Get the stored data
				$result = $arq_options['data']['twitter'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Facebook Fans
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_facebook_count' ) ) :
	function arq_facebook_count(){

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['facebook']) ){
			$result = $arq_transient['facebook'];
		}
		elseif( empty($arq_transient['facebook']) && !empty($arq_data) && !empty( $arq_options['data']['facebook'] ) ){
			$result = $arq_options['data']['facebook'];
		}
		else{
			$id = $arq_options['social']['facebook']['id'];
			try {
				$access_token = get_option( 'facebook_access_token' ) ;
				$data         = @arq_remote_get( "https://graph.facebook.com/v2.6/$id?access_token=$access_token&fields=fan_count" );
				$result       = (int) $data['fan_count'];
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['facebook'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['facebook'] ) ) //Get the stored data
				$result = $arq_options['data']['facebook'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Google+ Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_google_count' ) ) :
	function arq_google_count(){

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['google']) ){
			$result = $arq_transient['google'];
		}
		elseif( empty($arq_transient['google']) && !empty($arq_data) && !empty( $arq_options['data']['google'] ) ){
			$result = $arq_options['data']['google'];
		}
		else{
			$id  = $arq_options['social']['google']['id'];
			$key = $arq_options['social']['google']['key'];
			try {
				// Get googleplus data.
				$googleplus_data = arq_remote_get( 'https://www.googleapis.com/plus/v1/people/'. $id .'?key=' . $key );

				if ( isset( $googleplus_data['circledByCount'] ) ) {
					$result = (int) $googleplus_data['circledByCount'] ;
				}
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['google'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['google'] ) ) //Get the stored data
				$result = $arq_options['data']['google'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Youtube Subscribers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_youtube_count' ) ) :
	function arq_youtube_count(){

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['youtube']) ){
			$result = $arq_transient['youtube'];
		}
		elseif( empty($arq_transient['youtube']) && !empty($arq_data) && !empty( $arq_options['data']['youtube'] )  ){
			$result = $arq_options['data']['youtube'];
		}
		else{
			$id  = $arq_options['social']['youtube']['id'];
			$api = $arq_options['social']['youtube']['key'];
			try {
				if( !empty($arq_options['social']['youtube']['type']) && $arq_options['social']['youtube']['type'] == 'Channel' ){
					$data = @arq_remote_get("https://www.googleapis.com/youtube/v3/channels?part=statistics&id=$id&key=$api");
				}else{
					$data = @arq_remote_get("https://www.googleapis.com/youtube/v3/channels?part=statistics&forUsername=$id&key=$api");
				}
				$result = (int) $data['items'][0]['statistics']['subscriberCount'];

			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['youtube'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['youtube'] ) ) //Get the stored data
				$result = $arq_options['data']['youtube'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Vimeo Subscribers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_vimeo_count' ) ) :
	function arq_vimeo_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['vimeo']) ){
			$result = $arq_transient['vimeo'];
		}
		elseif( empty($arq_transient['vimeo']) && !empty($arq_data) && !empty( $arq_options['data']['vimeo'] )  ){
			$result = $arq_options['data']['vimeo'];
		}
		else{
			$id = $arq_options['social']['vimeo']['id'];
			try {
				$data 	= @arq_remote_get( "http://vimeo.com/api/v2/channel/$id/info.json" );
				$result = (int) $data['total_subscribers'];
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['vimeo'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['vimeo'] ) ) //Get the stored data
				$result = $arq_options['data']['vimeo'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Dribbble Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_dribbble_count' ) ) :
	function arq_dribbble_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['dribbble']) ){
			$result = $arq_transient['dribbble'];
		}
		elseif( empty($arq_transient['dribbble']) && !empty($arq_data) && !empty( $arq_options['data']['dribbble'] )  ){
			$result = $arq_options['data']['dribbble'];
		}else{
			$id 	= $arq_options['social']['dribbble']['id'];
			$api 	= $arq_options['social']['dribbble']['api'];
			try {
				$data 	= @arq_remote_get("https://api.dribbble.com/v1/users/$id?access_token=$api");
				$result = (int) $data['followers_count'];
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['dribbble'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['dribbble'] ) ) //Get the stored data
				$result = $arq_options['data']['dribbble'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Github Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_github_count' ) ) :
	function arq_github_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['github']) ){
			$result = $arq_transient['github'];
		}
		elseif( empty($arq_transient['github']) && !empty($arq_data) && !empty( $arq_options['data']['github'] )  ){
			$result = $arq_options['data']['github'];
		}
		else{
			$id = $arq_options['social']['github']['id'];
			try {
				$data 	= @arq_remote_get("https://api.github.com/users/$id");
				$result = (int) $data['followers'];
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['github'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['github'] ) ) //Get the stored data
				$result = $arq_options['data']['github'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Envato Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_envato_count' ) ) :
	function arq_envato_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['envato']) ){
			$result = $arq_transient['envato'];
		}
		elseif( empty($arq_transient['envato']) && !empty($arq_data) && !empty( $arq_options['data']['envato'] )  ){
			$result = $arq_options['data']['envato'];
		}
		else{
			$id = $arq_options['social']['envato']['id'];
			try {
				$data 	= @arq_remote_get("http://marketplace.envato.com/api/edge/user:$id.json");
				$result = (int) $data['user']['followers'];
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['envato'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['envato'] ) ) //Get the stored data
				$result = $arq_options['data']['envato'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# SoundCloud Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_soundcloud_count' ) ) :
	function arq_soundcloud_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['soundcloud']) ){
			$result = $arq_transient['soundcloud'];
		}
		elseif( empty($arq_transient['soundcloud']) && !empty($arq_data) && !empty( $arq_options['data']['soundcloud'] )  ){
			$result = $arq_options['data']['soundcloud'];
		}
		else{
			$id 	= $arq_options['social']['soundcloud']['id'];
			$api 	= $arq_options['social']['soundcloud']['api'];
			try {
				$data 	= @arq_remote_get("http://api.soundcloud.com/users/$id.json?consumer_key=$api");
				$result = (int) $data['followers_count'];
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['soundcloud'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['soundcloud'] ) ) //Get the stored data
				$result = $arq_options['data']['soundcloud'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Behance Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_behance_count' ) ) :
	function arq_behance_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['behance']) ){
			$result = $arq_transient['behance'];
		}
		elseif( empty($arq_transient['behance']) && !empty($arq_data) && !empty( $arq_options['data']['behance'] )  ){
			$result = $arq_options['data']['behance'];
		}
		else{
			$id 	= $arq_options['social']['behance']['id'];
			$api 	= $arq_options['social']['behance']['api'];
			try {
				$data 	= @arq_remote_get("http://www.behance.net/v2/users/$id?api_key=$api");
				$result = (int) $data['user']['stats']['followers'];
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['behance'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['behance'] ) ) //Get the stored data
				$result = $arq_options['data']['behance'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Instagram Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_instagram_count' ) ) :
	function arq_instagram_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['instagram']) ){
			$result = $arq_transient['instagram'];
		}
		elseif( empty($arq_transient['instagram']) && !empty($arq_data) && !empty( $arq_options['data']['instagram'] )  ){
			$result = $arq_options['data']['instagram'];
		}
		else{
			$api = get_option( 'instagram_access_token' );
			$id = explode(".", $api);
			try {
				$data 	= @arq_remote_get("https://api.instagram.com/v1/users/$id[0]/?access_token=$api");
				$result = (int) $data['data']['counts']['followed_by'];
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['instagram'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['instagram'] ) ) //Get the stored data
				$result = $arq_options['data']['instagram'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Foursquare Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_foursquare_count' ) ) :
	function arq_foursquare_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['foursquare']) ){
			$result = $arq_transient['foursquare'];
		}
		elseif( empty($arq_transient['foursquare']) && !empty($arq_data) && !empty( $arq_options['data']['foursquare'] )  ){
			$result = $arq_options['data']['foursquare'];
		}
		else{
			$api 	= get_option('foursquare_access_token');
			$date = date("Ymd");
			try {
				$data 	= @arq_remote_get("https://api.foursquare.com/v2/users/self?oauth_token=$api&v=$date");
				$result = (int) $data['response']['user']['friends']['count'];
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['foursquare'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['foursquare'] ) ) //Get the stored data
				$result = $arq_options['data']['foursquare'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Mailchimp Subscribers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_mailchimp_count' ) ) :
	function arq_mailchimp_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['mailchimp']) ){
			$result = $arq_transient['mailchimp'];
		}
		elseif( empty($arq_transient['mailchimp']) && !empty($arq_data) && !empty( $arq_options['data']['mailchimp'] )  ){
			$result = $arq_options['data']['mailchimp'];
		}
		else{
			if (!class_exists('MCAPI')) require_once 'inc/mailchimp/MCAPI.class.php';

			$apikey = $arq_options['social']['mailchimp']['api'];
			$listId = $arq_options['social']['mailchimp']['id'];

			$api 	= new MCAPI($apikey);
			$retval = $api->lists();
			$result = 0;

			foreach ($retval['data'] as $list){
				if($list['id'] == $listId){
					$result = $list['stats']['member_count'];
					break;
				}
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['mailchimp'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['mailchimp'] ) ) //Get the stored data
				$result = $arq_options['data']['mailchimp'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# MailPoet Subscribers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_mailpoet_count' ) ) :
	function arq_mailpoet_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['mailpoet']) ){
			$result = $arq_transient['mailpoet'];
		}
		elseif( empty($arq_transient['mailpoet']) && !empty($arq_data) && !empty( $arq_options['data']['mailpoet'] )  ){
			$result = $arq_options['data']['mailpoet'];
		}
		else{

			$list = $arq_options['social']['mailpoet']['list'];

			if( !empty( $list )){
				if( $list == 'all' ){
					$result	= do_shortcode( '[mailpoet_subscribers_count]' );
				}else{
					$result	= do_shortcode( '[mailpoet_subscribers_count segments="'. $list .'"]' );
				}
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['mailpoet'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['mailpoet'] ) ) //Get the stored data
				$result = $arq_options['data']['mailpoet'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# myMail Subscribers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_mymail_count' ) ) :
	function arq_mymail_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['mymail']) ){
			$result = $arq_transient['mymail'];
		}
		elseif( empty($arq_transient['mymail']) && !empty($arq_data) && !empty( $arq_options['data']['mymail'] )  ){
			$result = $arq_options['data']['mymail'];
		}
		else{

			$list = $arq_options['social']['mymail']['list'];

			if( !empty( $list )){
				if( $list == 'all' ){
					$counts = mailster('subscribers')->get_count_by_status();
					$result	= $counts[1];
				}else{
					$result	= mailster('lists')->get_member_count( $list, 1) ;
				}
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['mymail'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['mymail'] ) ) //Get the stored data
				$result = $arq_options['data']['mymail'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# LinkedIn Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_linkedin_count' ) ) :
	function arq_linkedin_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['linkedin']) ){
			$result = $arq_transient['linkedin'];
		}
		elseif( empty($arq_transient['linkedin']) && !empty($arq_data) && !empty( $arq_options['data']['linkedin'] )  ){
			$result = $arq_options['data']['linkedin'];
		}
		else{

			$token = get_option( 'linkedin_access_token' );

			if( ! empty( $arq_options['social']['linkedin']['type'] ) && !empty( $token )){

		    $args  = array(
					'headers' => array('Authorization' => sprintf('Bearer %s', $token))
				);

				if( $arq_options['social']['linkedin']['type'] == 'Profile' && ! empty( $arq_options['social']['linkedin']['profile'] )){

					try {
						$data   = arq_remote_get('https://api.linkedin.com/v1/people/~:(num-connections)?format=json', true, $args);
						$result = (int) $data['numConnections'];
					}
					catch (Exception $e) {
						$result = 0;
					}

				}
				elseif( $arq_options['social']['linkedin']['type'] == 'Company' && ! empty( $arq_options['social']['linkedin']['company'] )){

					$page_id = sprintf('https://api.linkedin.com/v1/companies/%s/num-followers?format=json', $arq_options['social']['linkedin']['company'] );

					try {
	          $data = arq_remote_get( $page_id, true, $args);
	          if( !is_array( $data )){
	          	$result = $data;
	          }
					}
					catch (Exception $e) {
						$result = 0;
					}
				}

				if( !empty( $result ) ){ //To update the stored data
					$arq_data['linkedin'] = $result;
				}

				if( empty( $result ) && !empty( $arq_options['data']['linkedin'] ) ){ //Get the stored data
					$result = $arq_options['data']['linkedin'];
				}

			}
		}
		return $result;
	}
endif;



/*-----------------------------------------------------------------------------------*/
# Vk Members
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_vk_count' ) ) :
	function arq_vk_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['vk']) ){
			$result = $arq_transient['vk'];
		}
		elseif( empty($arq_transient['vk']) && !empty($arq_data) && !empty( $arq_options['data']['vk'] )  ){
			$result = $arq_options['data']['vk'];
		}
		else{
			$id = $arq_options['social']['vk']['id'];
			try {
				$data 	= @arq_remote_get( "http://api.vk.com/method/groups.getById?gid=$id&fields=members_count");
				$result = (int) $data['response'][0]['members_count'];
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['vk'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['vk'] ) ) //Get the stored data
				$result = $arq_options['data']['vk'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Tumblr Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_tumblr_count' ) ) :
	function arq_tumblr_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['tumblr']) ){
			$result = $arq_transient['tumblr'];
		}
		elseif( empty($arq_transient['tumblr']) && !empty($arq_data) && !empty( $arq_options['data']['tumblr'] )  ){
			$result = $arq_options['data']['tumblr'];
		}
		else{
			$base_hostname = str_replace( array( 'http://','https://' ) , '', $arq_options['social']['tumblr']['hostname'] );

			try {
				$consumer_key		    = get_option( 'tumblr_api_key' );
				$consumer_secret    = get_option( 'tumblr_api_secret' );
				$oauth_token		    = get_option( 'tumblr_oauth_token' );
				$oauth_token_secret	= get_option( 'tumblr_token_secret' );
				$tumblr_api_URI		  = 'http://api.tumblr.com/v2/blog/'.$base_hostname.'/followers';

				$tum_oauth 	= new TumblrOAuthTie($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
				$tumblr_api = $tum_oauth->post($tumblr_api_URI, '');

				if( $tumblr_api->meta->status == 200 && !empty($tumblr_api->response->total_users) )
					$result = (int) $tumblr_api->response->total_users ;

			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['tumblr'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['tumblr'] ) ) //Get the stored data
				$result = $arq_options['data']['tumblr'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# 500px Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_500px_count' ) ) :
	function arq_500px_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['500px']) ){
			$result = $arq_transient['500px'];
		}
		elseif( empty($arq_transient['500px']) && !empty($arq_data) && !empty( $arq_options['data']['500px'] )  ){
			$result = $arq_options['data']['500px'];
		}
		else{
			$px500_username = $arq_options['social']['500px']['username'];
			try {
				$consumer_key       = get_option( '500px_api_key' );
				$consumer_secret    = get_option( '500px_api_secret' );
				$oauth_token        = get_option( '500px_oauth_token' );
				$oauth_token_secret = get_option( '500px_token_secret' );

				$px500_oauth = new tie500pxOAuth($consumer_key, $consumer_secret, $oauth_token, $oauth_token_secret);
				$px500_api   = $px500_oauth->get('users/show', array('username' => $px500_username ));

				if( !empty( $px500_api->user->followers_count ) )
					$result = (int) $px500_api->user->followers_count ;

			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['500px'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['500px'] ) ) //Get the stored data
				$result = $arq_options['data']['500px'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Pinterest Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_pinterest_count' ) ) :
	function arq_pinterest_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['pinterest']) ){
			$result = $arq_transient['pinterest'];
		}
		elseif( empty($arq_transient['pinterest']) && !empty($arq_data) && !empty( $arq_options['data']['pinterest'] )  ){
			$result = $arq_options['data']['pinterest'];
		}
		else{
			$username = $arq_options['social']['pinterest']['username'];
			try {
				$html 	= arq_remote_get( "https://www.pinterest.com/$username/" , false);
				$doc    = new DOMDocument();
				@$doc->loadHTML($html);
				$metas 	= $doc->getElementsByTagName('meta');
				for ($i = 0; $i < $metas->length; $i++){
					$meta = $metas->item($i);
					if($meta->getAttribute('name') == 'pinterestapp:followers'){
						$result = $meta->getAttribute('content');
						break;
					}
				}
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['pinterest'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['pinterest'] ) ) //Get the stored data
				$result = $arq_options['data']['pinterest'];
		}
		return $result;
	}
endif;



/*-----------------------------------------------------------------------------------*/
# Flickr Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_flickr_count' ) ) :
	function arq_flickr_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['flickr']) ){
			$result = $arq_transient['flickr'];
		}
		elseif( empty($arq_transient['flickr']) && !empty($arq_data) && !empty( $arq_options['data']['flickr'] )  ){
			$result = $arq_options['data']['flickr'];
		}
		else{
			$id 	= $arq_options['social']['flickr']['id'];
			$api 	= $arq_options['social']['flickr']['api'];
			try {
				$data 	= @arq_remote_get( "https://api.flickr.com/services/rest/?method=flickr.groups.getInfo&api_key=$api&group_id=$id&format=json&nojsoncallback=1");
				$result = (int) $data['group']['members']['_content'];
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['flickr'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['flickr'] ) ) //Get the stored data
				$result = $arq_options['data']['flickr'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Steam Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_steam_count' ) ) :
	function arq_steam_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['steam']) ){
			$result = $arq_transient['steam'];
		}
		elseif( empty($arq_transient['steam']) && !empty($arq_data) && !empty( $arq_options['data']['steam'] )  ){
			$result = $arq_options['data']['steam'];
		}
		else{
			$id = $arq_options['social']['steam']['group'];
			try {
				$data 	= @arq_remote_get( "http://steamcommunity.com/groups/$id/memberslistxml?xml=1" , false );
				$data 	= @new SimpleXmlElement( $data );
				$result = (int) $data->groupDetails->memberCount;
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['steam'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['steam'] ) ) //Get the stored data
				$result = $arq_options['data']['steam'];
		}
		return $result;
	}
endif;



/*-----------------------------------------------------------------------------------*/
# Rss Subscribers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_rss_count' ) ) :
	function arq_rss_count() {

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['rss']) ){
			$result = $arq_transient['rss'];
		}
		elseif( empty($arq_transient['rss']) && !empty($arq_data) && !empty( $arq_options['data']['rss'] )  ){
			$result = $arq_options['data']['rss'];
		}
		else{
			if( ( $arq_options['social']['rss']['type'] == 'feedpress.it' ) && !empty($arq_options['social']['rss']['feedpress']) ){
				try {
					$feedpress_url 	= esc_url($arq_options['social']['rss']['feedpress']);
					$feedpress_url 	= str_replace( 'feedpress.it', 'feed.press', $feedpress_url);
					//$feedpress_url 	= str_replace( 'http', 'https', $feedpress_url);

					$data   = @arq_remote_get( $feedpress_url );
					$result = (int) $data[ 'subscribers' ];
				} catch (Exception $e) {
					$result = 0;
				}
			}
			elseif( ( $arq_options['social']['rss']['type'] == 'Manual' ) && !empty($arq_options['social']['rss']['manual']) ){
				$result = $arq_options['social']['rss']['manual'] ;
			}
			else{
				$result = 0;
			}
			if( !empty( $result ) ) //To update the stored data
				$arq_data['rss'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['rss'] ) ) //Get the stored data
				$result = $arq_options['data']['rss'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Spotify Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_spotify_count' ) ) :
	function arq_spotify_count(){

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['spotify']) ){
			$result = $arq_transient['spotify'];
		}
		elseif( empty($arq_transient['spotify']) && !empty($arq_data) && !empty( $arq_options['data']['spotify'] )  ){
			$result = $arq_options['data']['spotify'];
		}
		else{
			$id = $url = $arq_options['social']['spotify']['id'];
			$id = rtrim( $id , "/");
			$id = urlencode( str_replace( array(  'https://play.spotify.com/', 'https://player.spotify.com/', 'artist/', 'user/' ) , '', $id) );

			try {
				if( !empty( $url ) && strpos( $url, 'artist') !== false ){
					$data = @arq_remote_get("https://api.spotify.com/v1/artists/$id");
				}else{
					$data = @arq_remote_get("https://api.spotify.com/v1/users/$id");
				}
				$result = (int) $data['followers']['total'];

			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['spotify'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['spotify'] ) ) //Get the stored data
				$result = $arq_options['data']['spotify'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Goodreads Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_goodreads_count' ) ) :
	function arq_goodreads_count(){

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['goodreads']) ){
			$result = $arq_transient['goodreads'];
		}
		elseif( empty($arq_transient['goodreads']) && !empty($arq_data) && !empty( $arq_options['data']['goodreads'] )  ){
			$result = $arq_options['data']['goodreads'];
		}
		else{
			$id  = $url = $arq_options['social']['goodreads']['id'];
			$key = $arq_options['social']['goodreads']['key'];

			$id = rtrim( $id , "/");
			$id = @parse_url($id);
			$id = $id['path'];
			$id = str_replace( array( '/user/show/', '/author/show/' ) , '', $id);
			if( strpos( $id, '-') !== false ){
				$id = explode( '-', $id);
			}else{
				$id = explode( '.', $id);
			}
			$id = $id[0];
			try {
				if( !empty( $url ) && strpos( $url, 'author') !== false ){
					$data 	= @arq_remote_get("https://www.goodreads.com/author/show/$id.xml?key=$key", false);
					$data 	= @new SimpleXmlElement( $data );
					$result = (int) $data->author->author_followers_count;
				}else{
					$data 	= @arq_remote_get("https://www.goodreads.com/user/show/$id.xml?key=$key", false);
					$data 	= @new SimpleXmlElement( $data );
					$result = (int) $data->user->friends_count;
				}

			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['goodreads'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['goodreads'] ) ) //Get the stored data
				$result = $arq_options['data']['goodreads'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Twitch Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_twitch_count' ) ) :
	function arq_twitch_count(){

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['twitch']) ){
			$result = $arq_transient['twitch'];
		}
		elseif( empty($arq_transient['twitch']) && !empty($arq_data) && !empty( $arq_options['data']['twitch'] )  ){
			$result = $arq_options['data']['twitch'];
		}
		else{
			$id  = $arq_options['social']['twitch']['id'];
			$api = get_option('twitch_access_token');

			try {
				$data 	= @arq_remote_get("https://api.twitch.tv/kraken/channels/$id?oauth_token=$api");

				$result = (int) $data['followers'];
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['twitch'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['twitch'] ) ) //Get the stored data
				$result = $arq_options['data']['twitch'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Mixcloud Followers
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_mixcloud_count' ) ) :
	function arq_mixcloud_count(){

		global $arq_data, $arq_options, $arq_transient;

		if( !empty($arq_transient['mixcloud']) ){
			$result = $arq_transient['mixcloud'];
		}
		elseif( empty($arq_transient['mixcloud']) && !empty($arq_data) && !empty( $arq_options['data']['mixcloud'] )  ){
			$result = $arq_options['data']['mixcloud'];
		}
		else{
			$id  = $arq_options['social']['mixcloud']['id'];
			try {
				$data 	= @arq_remote_get("http://api.mixcloud.com/$id/");
				$result = (int) $data['follower_count'];
			} catch (Exception $e) {
				$result = 0;
			}

			if( !empty( $result ) ) //To update the stored data
				$arq_data['mixcloud'] = $result;

			if( empty( $result ) && !empty( $arq_options['data']['mixcloud'] ) ) //Get the stored data
				$result = $arq_options['data']['mixcloud'];
		}
		return $result;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Posts Number
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_posts_count' ) ) :
	function arq_posts_count() {

		$count_posts   = wp_count_posts();
		return $result = $count_posts->publish;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Comments number
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_comments_count' ) ) :
	function arq_comments_count() {

		$comments_count = wp_count_comments();
		return $result  = $comments_count->approved;

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Members number
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_members_count' ) ) :
	function arq_members_count() {

		$members_count = count_users();
		return $result = $members_count['total_users'];

	}
endif;



/*-----------------------------------------------------------------------------------*/
# Groups number
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_groups_count' ) ) :
	function arq_groups_count() {

		return $result = groups_get_total_group_count();

	}
endif;



/*-----------------------------------------------------------------------------------*/
# bbPress Counters
/*-----------------------------------------------------------------------------------*/
if ( ! function_exists( 'arq_bbpress_count' ) ) :
	function arq_bbpress_count( $count ) {

		$arg = array (
			'count_users'           => false,
			'count_forums'          => false,
			'count_topics'          => false,
			'count_private_topics'  => false,
			'count_spammed_topics'  => false,
			'count_trashed_topics'  => false,
			'count_replies'         => false,
			'count_private_replies' => false,
			'count_spammed_replies' => false,
			'count_trashed_replies' => false,
			'count_tags'            => false,
			'count_empty_tags'      => false,
		);

		$arg[ 'count_' . $count ]	= true;

		$counters = bbp_get_statistics( $arg );
		if( $count == 'forums' ){
			$result = $counters[ 'forum_count' ];
		}
		elseif( $count == 'topics' ){
			$result = $counters[ 'topic_count' ];
		}
		elseif( $count == 'replies' ){
			$result = $counters[ 'reply_count' ];
		}
		return $result;

	}
endif;
