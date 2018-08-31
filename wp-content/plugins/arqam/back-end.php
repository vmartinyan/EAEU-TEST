<?php

/*-----------------------------------------------------------------------------------*/
# Register main Scripts and Styles
/*-----------------------------------------------------------------------------------*/
add_action( 'admin_enqueue_scripts', 'arqam_admin_register' );
function arqam_admin_register() {

	if ( isset( $_GET['page'] ) && $_GET['page'] == 'arqam' ) {
		wp_register_script( 'arqam-admin-scripts', plugins_url('assets/js/admin.js', __FILE__) , array( 'jquery', 'jquery-ui-core', 'jquery-ui-widget', 'jquery-ui-mouse', 'jquery-ui-sortable', 'postbox', 'post' ), false, true );

		wp_enqueue_script( 'arqam-admin-scripts' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_enqueue_style ( 'wp-color-picker' );
	}
}


/*-----------------------------------------------------------------------------------*/
# Add Panel Page
/*-----------------------------------------------------------------------------------*/
add_action('admin_menu', 'arqam_add_admin');
function arqam_add_admin() {
	global $arq_options;

	$current_page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';

	add_menu_page(ARQAM_Plugin.' '. __( 'Settings', 'arq' ), ARQAM_Plugin ,'install_plugins', 'arqam' , 'arqam_options', 'dashicons-heart'  );
	add_submenu_page('arqam', ARQAM_Plugin.' '. __( 'Settings', 'arq' ), __( 'Settings', 'arq' ),'install_plugins', 'arqam' , 'arqam_options');

	if( isset( $_REQUEST['action'] ) ){

		if( 'save' == $_REQUEST['action']  && $current_page == 'arqam' ) {
			$arq_options['social'] 	= $_REQUEST['social'];
			$arq_options['sort']    = $_REQUEST['sort'];
			$arq_options['color'] 	= $_REQUEST['color'];
			$arq_options['cache'] 	= (int) $_REQUEST['cache'];
			$arq_options['css']     = htmlspecialchars(stripslashes( $_REQUEST['css'] ) );
			$arq_options['data']    = '';

			update_option( 'arq_options', $arq_options );
			delete_transient( 'arq_counters' );

			do_action( 'arqam_options_updated' );

			header("Location: admin.php?page=arqam&saved=true");
			die;

		}

		elseif( 'facebook' == $_REQUEST['action'] && $current_page == 'arqam' ){

			$facebook_app_id     = $_REQUEST['app_id'];
			$facebook_app_secret = $_REQUEST['app_secret'];

			$url   = "https://graph.facebook.com/oauth/access_token?client_id=$facebook_app_id&client_secret=$facebook_app_secret&grant_type=client_credentials";
			$token = arq_remote_get( $url );

			if( ! empty( $token['access_token'] )){
				$token = $token['access_token'];

				// Store access token
				update_option( 'facebook_access_token', $token );
			}


			echo "<script type='text/javascript'>window.location='".admin_url()."admin.php?page=arqam#facebook';</script>";
			exit;

		}

		elseif( 'twitter' == $_REQUEST['action'] && $current_page == 'arqam' ){

			$consumerKey 		= $_REQUEST['app_id'];
			$consumerSecret = $_REQUEST['app_secret'];

			// preparing credentials
			$credentials = $consumerKey . ':' . $consumerSecret;
			$toSend 	 = base64_encode($credentials);

			// http post arguments
			$args = array(
				'method'      => 'POST',
				'httpversion' => '1.1',
				'blocking' 		=> true,
				'headers' 		=> array(
					'Authorization' => 'Basic ' . $toSend,
					'Content-Type' 	=> 'application/x-www-form-urlencoded;charset=UTF-8'
				),
				'body' 				=> array( 'grant_type' => 'client_credentials' )
			);

			add_filter('https_ssl_verify', '__return_false');
			$response = wp_remote_post('https://api.twitter.com/oauth2/token', $args);

			$keys = json_decode(wp_remote_retrieve_body($response));

			if( !empty($keys->access_token) ) {
				// saving token to wp_options table
				update_option('arqam_TwitterToken', $keys->access_token);
			}

			echo "<script type='text/javascript'>window.location='".admin_url()."admin.php?page=arqam#twitter';</script>";
			exit;

		}

		elseif( 'tumblr' == $_REQUEST['action']  && $current_page == 'arqam' ){

			session_start();

			update_option( 'tumblr_api_key',  	$_REQUEST['api_key'] );
			update_option( 'tumblr_api_secret', $_REQUEST['api_secret'] );

			$callback_url  = admin_url().'admin.php?page=arqam&service=arq-tumblr';
			$tum_oauth     = new TumblrOAuthTie( $_REQUEST['api_key'] , $_REQUEST['api_secret'] );
			$request_token = $tum_oauth->getRequestToken($callback_url);

			$_SESSION['request_token']        = $token = $request_token['oauth_token'];
			$_SESSION['request_token_secret'] = $request_token['oauth_token_secret'];

			switch ($tum_oauth->http_code) {
			  case 200:
				$url = $tum_oauth->getAuthorizeURL($token);
				header('Location: ' . $url);
				break;
				default:
				_e( 'Could not connect to Tumblr. Refresh the page or try again later.', 'arq' );
			}
			exit();

		}

		elseif( '500px' == $_REQUEST['action'] && $current_page == 'arqam' ){

			session_start();

			update_option( '500px_api_key',    $_REQUEST['api_key'] );
			update_option( '500px_api_secret', $_REQUEST['api_secret'] );

			$callback_url  = admin_url().'admin.php?page=arqam&service=arq-500px';
			$px500_oauth   = new tie500pxOAuth( $_REQUEST['api_key'] , $_REQUEST['api_secret'] );
			$request_token = $px500_oauth->getRequestToken($callback_url);

			$_SESSION['request_token']        = $token = $request_token['oauth_token'];
			$_SESSION['request_token_secret'] = $request_token['oauth_token_secret'];

			switch ($px500_oauth->http_code) {
			  case 200:
				$url = $px500_oauth->getAuthorizeURL($token);
				header('Location: ' . $url);
				break;
				default:
				_e( 'Could not connect to 500px. Refresh the page or try again later.', 'arq' );
			}
			exit();

		}

		elseif( 'Instagram' == $_REQUEST['action'] && $current_page == 'arqam' ){

			$Instagram_client_id     = $_REQUEST['client_id'];
			$Instagram_client_secret = $_REQUEST['client_secret'];

			$cur_page =  urlencode ( admin_url().'admin.php?page=arqam&service=arq-Instagram' );

			set_transient( 'arq_instagram_client_id',     $Instagram_client_id, 	60*60 );
			set_transient( 'arq_instagram_client_secret', $Instagram_client_secret, 60*60 );

			if( !empty( $_REQUEST['follow_us'] ) && $_REQUEST['follow_us'] == 'true' ){
				set_transient( 'arq_instagram_follow_us', 'true'  , 60*60 );
			}
			else{
				delete_transient( 'arq_instagram_follow_us' );
			}

			$url = "https://api.instagram.com/oauth/authorize/?client_id=$Instagram_client_id&redirect_uri=$cur_page&response_type=code&scope=basic relationships";

			header( "Location: $url" );

		}

		elseif( 'foursquare' == $_REQUEST['action'] && $current_page == 'arqam' ){

			$foursquare_client_id     = $_REQUEST['client_id'];
			$foursquare_client_secret = $_REQUEST['client_secret'];

			$cur_page =  urlencode ( admin_url().'admin.php?page=arqam&service=arq-foursquare' );

			set_transient( 'arq_foursquare_client_id',     $foursquare_client_id,     60*60 );
			set_transient( 'arq_foursquare_client_secret', $foursquare_client_secret, 60*60 );

			$url = "https://foursquare.com/oauth2/authenticate?client_id=$foursquare_client_id&response_type=code&redirect_uri=$cur_page";

			header( "Location: $url" );
		}

		elseif( 'twitch' == $_REQUEST['action'] && $current_page == 'arqam' ){

			$twitch_client_id     = $_REQUEST['client_id'];
			$twitch_client_secret = $_REQUEST['client_secret'];

			$cur_page =  urlencode ( admin_url().'admin.php?page=arqam&service=arq-twitch' );

			set_transient( 'arq_twitch_client_id',     $twitch_client_id,     60*60 );
			set_transient( 'arq_twitch_client_secret', $twitch_client_secret, 60*60 );

			$url = "https://api.twitch.tv/kraken/oauth2/authorize?response_type=code&client_id=$twitch_client_id&redirect_uri=$cur_page&scope=channel_read&state=$twitch_client_secret";

			header( "Location: $url" );
		}

		elseif( 'linkedin' == $_REQUEST['action']  && $current_page == 'arqam' ){

			set_transient( 'linkedin_api_key',    $_REQUEST['api_key'],    60*60 );
			set_transient( 'linkedin_api_secret', $_REQUEST['api_secret'], 60*60 );

			tie_linkedin_getAuthorizationCode( $_REQUEST['api_key'] );
		}

	}
}


/*-----------------------------------------------------------------------------------*/
# arqam Panel
/*-----------------------------------------------------------------------------------*/
function arqam_options() {
	global $arq_options, $arq_social_items;
	$current_page = isset( $_REQUEST['page'] ) ? $_REQUEST['page'] : '';

if( isset( $_REQUEST['service'] ) && 'arq-facebook' == $_REQUEST['service'] && $current_page == 'arqam' ){
?>

<div class="wrap">
	<h1><?php _e( 'Facebook App info' , 'arq' ) ?></h1>
	<br />
	<form method="post">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content" class="arq-content">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Facebook App info' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="app_id"><?php _e( 'App ID' , 'arq' ) ?></label></th>
										<td><input type="text" name="app_id" id="app_id" value=""></td>
									</tr>
									<tr>
										<th scope="row"><label for="app_secret"><?php _e( 'App Secret' , 'arq' ) ?></label></th>
										<td><input type="text" name="app_secret" id="app_secret" value=""></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your App ID and App Secret, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#facebook' ) ?> </em></p></div>
							<div class="clear"></div>
						</div>
					</div> <!-- Box end /-->
				</div> <!-- Post Body COntent -->

				<div id="publishing-action">
					<input type="hidden" name="action" value="facebook" />
					<input name="save" type="submit" class="button-large button-primary" id="publish" value="<?php _e( 'Submit' , 'arq' ) ?>">
				</div>
				<div class="clear"></div>

			</div><!-- post-body /-->
		</div><!-- poststuff /-->
	</form>
</div>
<?php
}

elseif( isset( $_REQUEST['service'] ) && 'arq-twitter' == $_REQUEST['service'] && $current_page == 'arqam' ){
?>

<div class="wrap">
	<h1><?php _e( 'Twitter App info' , 'arq' ) ?></h1>
	<br />
	<form method="post">
		<div id="poststuff">
			<div id="post-body" class="metabox-holder columns-2">
				<div id="post-body-content" class="arq-content">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Twitter App info' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="app_id"><?php _e( 'Consumer key' , 'arq' ) ?></label></th>
										<td><input type="text" name="app_id" id="app_id" value=""></td>
									</tr>
									<tr>
										<th scope="row"><label for="app_secret"><?php _e( 'Consumer secret' , 'arq' ) ?></label></th>
										<td><input type="text" name="app_secret" id="app_secret" value=""></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter your APP Consumer key and Consumer secret, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#twitter' ) ?> </em></p>
							<div class="clear"></div>
						</div>
					</div> <!-- Box end /-->
				</div> <!-- Post Body COntent -->

				<div id="publishing-action">
					<input type="hidden" name="action" value="twitter" />
					<input name="save" type="submit" class="button-large button-primary" id="publish" value="<?php _e( 'Submit' , 'arq' ) ?>">
				</div>
				<div class="clear"></div>

			</div><!-- post-body /-->
		</div><!-- poststuff /-->
	</form>
</div>
<?php
}

elseif( isset( $_REQUEST['service'] ) && 'arq-Instagram' == $_REQUEST['service'] && $current_page == 'arqam' ){

	if( !empty( $_REQUEST['code'] ) ){
		$code                    = $_REQUEST['code'];
		$cur_page                = admin_url().'admin.php?page=arqam&service=arq-Instagram' ;
		$instagram_client_id     = get_transient( 'arq_instagram_client_id' );
		$instagram_client_secret = get_transient( 'arq_instagram_client_secret' );
		$instagram_follow_us     = get_transient( 'arq_instagram_follow_us ');

		// http post arguments
		$args = array(
			'body'            => array(
				'client_id'     => $instagram_client_id,
				'client_secret' => $instagram_client_secret ,
				'grant_type'    => 'authorization_code',
				'redirect_uri'  => $cur_page,
				'code'          => $code,
			)
		);

		add_filter('https_ssl_verify', '__return_false');
		$response 		= wp_remote_post('https://api.instagram.com/oauth/access_token', $args);
		$response 		= json_decode(wp_remote_retrieve_body($response) );
		$access_token = $response->access_token;

		update_option( 'instagram_access_token' , $access_token );

		$instagram_client_id     = delete_transient( 'arq_instagram_client_id' );
		$instagram_client_secret = delete_transient( 'arq_instagram_client_secret' );
		$instagram_follow_us     = delete_transient( 'arq_instagram_follow_us ');

		echo "<script type='text/javascript'>window.location='".admin_url()."admin.php?page=arqam';</script>";

		exit;
	}
?>
<div class="wrap">
	<h1><?php _e( 'Instagram App info' , 'arq' ) ?></h1>
	<br />
	<form method="post">
		<div id="poststuff">
			<div id="post-body" class="columns-2">
				<div id="post-body-content" class="arq-content">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Instagram App info' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="client_id"><?php _e( 'Client ID' , 'arq' ) ?></label></th>
										<td><input type="text" name="client_id" id="client_id" value=""></td>
									</tr>
									<tr>
										<th scope="row"><label for="client_secret"><?php _e( 'Client Secret' , 'arq' ) ?></label></th>
										<td><input type="text" name="client_secret" id="client_secret" value=""></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong>
								<p><em><?php printf( __( 'Enter Your App Client ID and App Client Secret, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#instagram' ) ?></em></p>
							</div>

							<div class="clear"></div>
						</div>
					</div> <!-- Box end /-->
				</div> <!-- Post Body COntent -->

				<div id="publishing-action">
					<input type="hidden" name="action" value="Instagram" />
					<input name="save" type="submit" class="button-large button-primary" id="publish" value="<?php _e( 'Submit' , 'arq' ) ?>">
				</div>
				<div class="clear"></div>

			</div><!-- post-body /-->
		</div><!-- poststuff /-->
	</form>
</div>
<?php
}

elseif( isset( $_REQUEST['service'] ) && 'arq-foursquare' == $_REQUEST['service'] && $current_page == 'arqam' ){

	if( !empty( $_REQUEST['code'] ) ){
		$code                     = $_REQUEST['code'];
		$cur_page                 = admin_url().'admin.php?page=arqam&service=arq-foursquare' ;
		$foursquare_client_id     = get_transient( 'arq_foursquare_client_id' );
		$foursquare_client_secret = get_transient( 'arq_foursquare_client_secret' );

		// http post arguments
		$args = array(
			'body' => array(
				'client_id'     => $foursquare_client_id,
				'client_secret' => $foursquare_client_secret ,
				'grant_type'    => 'authorization_code',
				'redirect_uri'  => $cur_page,
				'code'          => $code,
			)
		);

		add_filter('https_ssl_verify', '__return_false');
		$response      = wp_remote_post('https://foursquare.com/oauth2/access_token', $args);
		$response      = json_decode(wp_remote_retrieve_body($response) );
		$access_token  = $response->access_token;

		update_option( 'foursquare_access_token' , $access_token );

		delete_transient( 'arq_foursquare_client_id' );
		delete_transient( 'arq_foursquare_client_secret' );

		echo "<script type='text/javascript'>window.location='".admin_url()."admin.php?page=arqam';</script>";

		exit;
	}
?>
<div class="wrap">
	<h1><?php _e( 'Foursquare App info' , 'arq' ) ?></h1>
	<br />
	<form method="post">
		<div id="poststuff">
			<div id="post-body" class="columns-2">
				<div id="post-body-content" class="arq-content">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Foursquare App info' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="client_id"><?php _e( 'Client ID' , 'arq' ) ?></label></th>
										<td><input type="text" name="client_id" id="client_id" value=""></td>
									</tr>
									<tr>
										<th scope="row"><label for="client_secret"><?php _e( 'Client Secret' , 'arq' ) ?></label></th>
										<td><input type="text" name="client_secret" id="client_secret" value=""></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong>
								<p><em><?php printf( __( 'Enter Your App Client ID and App Client Secret, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ) , 'http://plugins.tielabs.com/docs/arqam/#foursquare' ) ?></em></p></div>
							<div class="clear"></div>
						</div>
					</div> <!-- Box end /-->
				</div> <!-- Post Body COntent -->


				<div id="publishing-action">
					<input type="hidden" name="action" value="foursquare" />
					<input name="save" type="submit" class="button-large button-primary" id="publish" value="<?php _e( 'Submit' , 'arq' ) ?>">
				</div>
				<div class="clear"></div>

			</div><!-- post-body /-->
		</div><!-- poststuff /-->
	</form>
</div>
<?php
}


elseif( isset( $_REQUEST['service'] ) && 'arq-twitch' == $_REQUEST['service'] && $current_page == 'arqam' ){

	if( !empty( $_REQUEST['code'] ) ){
		$code                 = $_REQUEST['code'];
		$cur_page             = admin_url().'admin.php?page=arqam&service=arq-twitch' ;
		$twitch_client_id     = get_transient( 'arq_twitch_client_id' );
		$twitch_client_secret = get_transient( 'arq_twitch_client_secret' );

		// http post arguments
		$args = array(
			'body' => array(
				'client_id'     => $twitch_client_id,
				'client_secret' => $twitch_client_secret,
				'grant_type'    => 'authorization_code',
				'redirect_uri'  => $cur_page,
				'code'          => $code,
			)
		);

		add_filter('https_ssl_verify', '__return_false');
		$response      = wp_remote_post('https://api.twitch.tv/kraken/oauth2/token', $args);
		$response      = json_decode(wp_remote_retrieve_body($response) );
		$access_token  = $response->access_token;

		update_option( 'twitch_access_token' , $access_token );

		delete_transient( 'arq_twitch_client_id' );
		delete_transient( 'arq_twitch_client_secret' );

		echo "<script type='text/javascript'>window.location='".admin_url()."admin.php?page=arqam';</script>";

		exit;
	}
?>
<div class="wrap">
	<h1><?php _e( 'Twitch App info' , 'arq' ) ?></h1>
	<br />
	<form method="post">
		<div id="poststuff">
			<div id="post-body" class="columns-2">
				<div id="post-body-content" class="arq-content">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Twitch App info' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="client_id"><?php _e( 'Client ID' , 'arq' ) ?></label></th>
										<td><input type="text" name="client_id" id="client_id" value=""></td>
									</tr>
									<tr>
										<th scope="row"><label for="client_secret"><?php _e( 'Client Secret' , 'arq' ) ?></label></th>
										<td><input type="text" name="client_secret" id="client_secret" value=""></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong>
								<p><em><?php printf( __( 'Enter Your App Client ID and App Client Secret, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ) , 'http://plugins.tielabs.com/docs/arqam/#twitch' ) ?></em></p></div>
							<div class="clear"></div>
						</div>
					</div> <!-- Box end /-->
				</div> <!-- Post Body COntent -->


				<div id="publishing-action">
					<input type="hidden" name="action" value="twitch" />
					<input name="save" type="submit" class="button-large button-primary" id="publish" value="<?php _e( 'Submit' , 'arq' ) ?>">
				</div>
				<div class="clear"></div>

			</div><!-- post-body /-->
		</div><!-- poststuff /-->
	</form>
</div>
<?php
}

elseif( isset( $_REQUEST['service'] ) && 'arq-tumblr' == $_REQUEST['service'] && $current_page == 'arqam' ){

	if (isset($_REQUEST['oauth_verifier'])) {

		session_start();

		$consumer_key    = get_option( 'tumblr_api_key' );
		$consumer_secret = get_option( 'tumblr_api_secret' );
		$tum_oauth       = new TumblrOAuthTie($consumer_key, $consumer_secret, $_SESSION['request_token'], $_SESSION['request_token_secret']);
		$access_token    = $tum_oauth->getAccessToken($_REQUEST['oauth_verifier']);

		unset($_SESSION['request_token']);
		unset($_SESSION['request_token_secret']);

		update_option( 'tumblr_oauth_token',  $access_token['oauth_token'] );
		update_option( 'tumblr_token_secret', $access_token['oauth_token_secret'] );

		echo "<script type='text/javascript'>window.location='".admin_url()."admin.php?page=arqam#tumblr';</script>";
		exit;
	}
?>
<div class="wrap">
	<h1><?php _e( 'Tumblr App info' , 'arq' ) ?></h1>
	<br />
	<form method="post">
		<div id="poststuff">
			<div id="post-body" class="columns-2">
				<div id="post-body-content" class="arq-content">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Tumblr App info' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="api_key"><?php _e( 'Consumer Key' , 'arq' ) ?></label></th>
										<td><input type="text" name="api_key" id="api_key" value=""></td>
									</tr>
									<tr>
										<th scope="row"><label for="api_secret"><?php _e( 'Secret Key' , 'arq' ) ?></label></th>
										<td><input type="text" name="api_secret" id="api_secret" value=""></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your APP Consumer Key and Secret Key, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#tumblr' ) ?></em></p></div>
							<div class="clear"></div>
						</div>
					</div> <!-- Box end /-->
				</div> <!-- Post Body COntent -->

				<div id="publishing-action">
					<input type="hidden" name="action" value="tumblr" />
					<input name="save" type="submit" class="button-large button-primary" id="publish" value="<?php _e( 'Submit' , 'arq' ) ?>">
				</div>
				<div class="clear"></div>

			</div><!-- post-body /-->
		</div><!-- poststuff /-->
	</form>
</div>
<?php
}

elseif( isset( $_REQUEST['service'] ) && 'arq-500px' == $_REQUEST['service'] && $current_page == 'arqam' ){

	if (isset($_REQUEST['oauth_verifier'])) {

		session_start();

		$consumer_key    = get_option( '500px_api_key' );
		$consumer_secret = get_option( '500px_api_secret' );
		$px500_oauth     = new tie500pxOAuth($consumer_key, $consumer_secret, $_SESSION['request_token'], $_SESSION['request_token_secret']);
		$access_token    = $px500_oauth->getAccessToken($_REQUEST['oauth_verifier']);

		unset($_SESSION['request_token']);
		unset($_SESSION['request_token_secret']);

		update_option( '500px_oauth_token',  $access_token['oauth_token'] );
		update_option( '500px_token_secret', $access_token['oauth_token_secret'] );

		echo "<script type='text/javascript'>window.location='".admin_url()."admin.php?page=arqam#px500';</script>";
		exit;
	}
?>
<div class="wrap">
	<h1><?php _e( '500px App info' , 'arq' ) ?></h1>
	<br />
	<form method="post">
		<div id="poststuff">
			<div id="post-body" class="columns-2">
				<div id="post-body-content" class="arq-content">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( '500px App info' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="api_key"><?php _e( 'Consumer Key' , 'arq' ) ?></label></th>
										<td><input type="text" name="api_key" id="api_key" value=""></td>
									</tr>
									<tr>
										<th scope="row"><label for="api_secret"><?php _e( 'Secret Key' , 'arq' ) ?></label></th>
										<td><input type="text" name="api_secret" id="api_secret" value=""></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your APP Consumer Key and Secret Key, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#px500' ) ?></em></p></div>
							<div class="clear"></div>
						</div>
					</div> <!-- Box end /-->
				</div> <!-- Post Body COntent -->

				<div id="publishing-action">
					<input type="hidden" name="action" value="500px" />
					<input name="save" type="submit" class="button-large button-primary" id="publish" value="<?php _e( 'Submit' , 'arq' ) ?>">
				</div>
				<div class="clear"></div>

			</div><!-- post-body /-->
		</div><!-- poststuff /-->
	</form>
</div>
<?php
}

elseif( isset( $_REQUEST['service'] ) && 'arq-linkedin' == $_REQUEST['service'] && $current_page == 'arqam' ){

	if (isset($_GET['error'])) {
		print $_GET['error'] . ': ' . $_GET['error_description'];
	}

	elseif (isset($_GET['code'])) {

		if ( get_transient( 'linkedin_state' ) == $_GET['state']) {

			$api_key    = get_transient( 'linkedin_api_key' );
			$api_secret = get_transient( 'linkedin_api_secret' );

			tie_linkedin_getAccessToken( $api_key, $api_secret );
		}
		else {
			echo 'ERROR';
		}
	}
?>
<div class="wrap">
	<div id="icon-options-general" class="icon32"><br></div>
	<h2><?php _e( 'LinkedIn App info' , 'arq' ) ?></h2>
	<br />
	<form method="post">
		<div id="poststuff">
			<div id="post-body" class="columns-2">
				<div id="post-body-content" class="arq-content">
					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'LinkedIn App info' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="api_key"><?php _e( 'API Key' , 'arq' ) ?></label></th>
										<td><input type="text" name="api_key" class="code" id="api_key" value=""></td>
									</tr>
									<tr>
										<th scope="row"><label for="api_secret"><?php _e( 'Secret Key' , 'arq' ) ?></label></th>
										<td><input type="text" name="api_secret" class="code" id="api_secret" value=""></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php _e( 'Enter Your APP API Key and Secret Key ,' , 'arq' ) ?> <a href="http://plugins.tielabs.com/docs/arqam/#linkedin" target="_blank"><?php _e( 'Click Here' , 'arq' ) ?></a> <?php _e( 'For More Details.' , 'arq' ) ?></em></p></div>
							<div class="clear"></div>
						</div>
					</div> <!-- Box end /-->
				</div> <!-- Post Body COntent -->

				<div id="publishing-action">
					<input type="hidden" name="action" value="linkedin" />
					<input name="save" type="submit" class="button-large button-primary" id="publish" value="<?php _e( 'Submit' , 'arq' ) ?>">
				</div>
				<div class="clear"></div>

			</div><!-- post-body /-->
		</div><!-- poststuff /-->
	</form>
</div>
<?php
}

elseif( !empty( $_REQUEST['debug'] ) && $current_page == 'arqam' ){
	global $arq_options, $arq_transient, $arq_social_items ;

	$arq_options[ 'data' ] = false;
	$arq_transient         = false;
	$i = 0;

	$excluded_array = array( 'mailchimp', 'pinterest', 'mailpoet', 'mymail', 'members', 'groups', 'comments', 'posts', '500px', 'tumblr');

	foreach( $arq_social_items as $item ){
		$function_name = 'arq_'. $item .'_count';
		if(function_exists( $function_name ) && !in_array( $item, $excluded_array)) {
			$i++;
			?>
			<h3><?php echo $i .'- '. $item ?></h3>
			<pre style="margin: 10px 10px 25px; padding: 20px; word-wrap: break-word; background-color: #FFF; border: 1px solid #ccc;">
				<?php $function_name(); ?>
			</pre>
			<?php
		}
	}
}

else{

	global $arq_options, $arq_social_items;

	if(isset($_REQUEST['saved'])){
		echo '<div id="setting-error-settings_updated" class="updated settings-error"><p><strong>'. __( 'Settings saved.' , 'arq' ) .'</strong></p></div>';
	}
?>

<div class="wrap">
	<h1><?php _e( 'Arqam Settings' , 'arq' ) ?></h1>
	<br />
	<form method="post">
		<div id="poststuff">
			<div id="post-body" class="columns-2 meta-box-sortables">
				<div id="post-body-content" class="arq-content">

				<?php
				if( !empty( $arq_options['sort'] ) && is_array( $arq_options['sort'] )){
					$arq_sort_items = $arq_options['sort'];
					$arq_new_items 	= array_diff ( $arq_social_items, $arq_sort_items );

					if( !empty( $arq_new_items ) ){
						$arq_sort_items = array_merge( $arq_sort_items, $arq_new_items );
					}
				}
				else{
					$arq_sort_items		= $arq_social_items;
				}

				foreach ( $arq_sort_items as $arq_item ){
					$arq_title = $arq_item;
					if( $arq_item == '500px' ){
						$arq_title = 'px500';
					}

					$color_style = '';
					if( !empty( $arq_options['color'][$arq_item] ) ){
						$color_style = ' style="background-color:'.$arq_options['color'][$arq_item].' !important"';
					}

				if( in_array( $arq_item, $arq_social_items )){ ?>
					<div id="<?php echo $arq_title ?>" class="postbox postbox-arq closed">
						<button type="button" class="handlediv button-link" aria-expanded="true">
							<span class="screen-reader-text"><?php _e( 'Toggle panel:' , 'arq' ) ?> <?php echo $arq_title ?></span>
							<span class="toggle-indicator" aria-hidden="true"></span>
						</button>
					<?php
				}

					switch ( $arq_item ) {
					case 'facebook':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-facebook"></i><span><?php _e( 'Facebook' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[facebook][id]"><?php _e( 'Page ID/Name' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[facebook][id]" id="social[facebook][id]" value="<?php if( !empty($arq_options['social']['facebook']['id']) ) echo $arq_options['social']['facebook']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[facebook][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[facebook][text]" id="social[facebook][text]" value="<?php if( !empty($arq_options['social']['facebook']['text']) ) echo $arq_options['social']['facebook']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[facebook][api]"><?php _e( 'Access Token Key' , 'arq' ) ?></label></th>
										<td>
											<input type="text" style="color: #999;" name="social[facebook][api]" disabled="disabled" id="social[facebook][api]" value="<?php if( get_option( 'facebook_access_token' ) ) echo get_option( 'facebook_access_token' ) ?>">
											<a class="button-large button-primary tie-get-api-key" href="<?php echo admin_url().'admin.php?page=arqam&service=arq-facebook' ?>"><?php _e( 'Get Access Token' , 'arq' ) ?></a>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your Facebook Page Name or ID and click on Get Access Token to get your App Access Token, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#facebook' ) ?> </em></p>
							</div>
				<?php
					break;
					case 'twitter':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-twitter"></i><span><?php _e( 'Twitter' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[twitter][id]"><?php _e( 'UserName' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[twitter][id]" id="social[twitter][id]" value="<?php if( !empty($arq_options['social']['twitter']['id']) ) echo $arq_options['social']['twitter']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[twitter][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[twitter][text]" id="social[twitter][text]" value="<?php if( !empty($arq_options['social']['twitter']['text']) ) echo $arq_options['social']['twitter']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[twitter][api]"><?php _e( 'Access Token Key' , 'arq' ) ?></label></th>
										<td>
											<input type="text" style="color: #999;" name="social[twitter][api]" disabled="disabled" id="social[twitter][api]" value="<?php if( get_option( 'arqam_TwitterToken' ) ) echo get_option( 'arqam_TwitterToken' ) ?>">
											<a class="button-large button-primary tie-get-api-key" href="<?php echo admin_url().'admin.php?page=arqam&service=arq-twitter' ?>"><?php _e( 'Get Access Token' , 'arq' ) ?></a>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your Twitter Account Username and click on Get Access Token to get your App Access Token. <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#twitter' ) ?> </em></p>
							</div>
				<?php
					break;
					case 'google':
				?>

						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-google-plus"></i><span><?php _e( 'Google+' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[google][id]"><?php _e( 'Google+ ID' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[google][id]" id="social[google][id]" value="<?php if( !empty($arq_options['social']['google']['id']) ) echo $arq_options['social']['google']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[google][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[google][text]" id="social[google][text]" value="<?php if( !empty($arq_options['social']['google']['text']) ) echo $arq_options['social']['google']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[google][key]"><?php _e( 'Google API Key' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[google][key]" id="social[google][key]" value="<?php if( !empty($arq_options['social']['google']['key']) ) echo $arq_options['social']['google']['key'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your Google+ page or profile ID and Google API Key, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#google' ) ?></em></p>
							</div>
				<?php
					break;
					case 'youtube':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-youtube"></i><span><?php _e( 'Youtube' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[youtube][id]"><?php _e( 'Username or Channel ID' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[youtube][id]" id="social[youtube][id]" value="<?php if( !empty($arq_options['social']['youtube']['id']) ) echo $arq_options['social']['youtube']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[youtube][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[youtube][text]" id="social[youtube][text]" value="<?php if( !empty($arq_options['social']['youtube']['text']) ) echo $arq_options['social']['youtube']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[youtube][key]"><?php _e( 'Youtube API Key' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[youtube][key]" id="social[youtube][key]" value="<?php if( !empty($arq_options['social']['youtube']['key']) ) echo $arq_options['social']['youtube']['key'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[youtube][type]"><?php _e( 'Type' , 'arq' ) ?></label></th>
										<td>
											<select name="social[youtube][type]" id="social[youtube][type]">
											<?php
											$youtube_type = array('User', 'Channel');
											foreach ( $youtube_type as $type ){ ?>
												<option <?php if( !empty($arq_options['social']['youtube']['type']) && $arq_options['social']['youtube']['type'] == $type ) echo'selected="selected"' ?> value="<?php echo $type ?>"><?php echo $type ?></option>
											<?php } ?>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your Youtube username or Channel ID, API Key and choose User or Channel from Type menu, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#youtube' ) ?></em></p>
							</div>
				<?php
					break;
					case 'vimeo':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-vimeo"></i><span><?php _e( 'Vimeo' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[vimeo][id]"><?php _e( 'Channel Name' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[vimeo][id]" id="social[vimeo][id]" value="<?php if( !empty($arq_options['social']['vimeo']['id']) ) echo $arq_options['social']['vimeo']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[vimeo][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[vimeo][text]" id="social[vimeo][text]" value="<?php if( !empty($arq_options['social']['vimeo']['text']) ) echo $arq_options['social']['vimeo']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php _e( 'Enter Your Vimeo Channel Name.' , 'arq' ) ?> </em></p>
							</div>
				<?php
					break;
					case 'dribbble':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-dribbble"></i> <span><?php _e( 'Dribbble' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[dribbble][id]"><?php _e( 'UserName' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[dribbble][id]" id="social[dribbble][id]" value="<?php if( !empty($arq_options['social']['dribbble']['id']) ) echo $arq_options['social']['dribbble']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[dribbble][api]"><?php _e( 'Access Token Key' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[dribbble][api]" id="social[dribbble][api]" value="<?php if( !empty($arq_options['social']['dribbble']['api']) ) echo $arq_options['social']['dribbble']['api'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[dribbble][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[dribbble][text]" id="social[dribbble][text]" value="<?php if( !empty($arq_options['social']['dribbble']['text']) ) echo $arq_options['social']['dribbble']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div><strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your Dribbble Account Username and the Access Token Key, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#dribbble' )  ?></em></p></div>
				<?php
					break;
					case 'github':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-github"></i><span><?php _e( 'Github' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[github][id]"><?php _e( 'UserName' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[github][id]" id="social[github][id]" value="<?php if( !empty($arq_options['social']['github']['id']) ) echo $arq_options['social']['github']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[github][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[github][text]" id="social[github][text]" value="<?php if( !empty($arq_options['social']['github']['text']) ) echo $arq_options['social']['github']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php _e( 'Enter Your Github Account Username .' , 'arq' ) ?></em></p>
							</div>
				<?php
					break;
					case 'envato':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-envato"></i><span><?php _e( 'Envato' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[envato][id]"><?php _e( 'UserName' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[envato][id]" id="social[envato][id]" value="<?php if( !empty($arq_options['social']['envato']['id']) ) echo $arq_options['social']['envato']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[envato][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[envato][text]" id="social[envato][text]" value="<?php if( !empty($arq_options['social']['envato']['text']) ) echo $arq_options['social']['envato']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[envato][site]"><?php _e( 'Marketplace' , 'arq' ) ?></label></th>
										<td>
											<select name="social[envato][site]" id="social[envato][site]">
											<?php
											$envato_markets = array('3docean', 'activeden', 'audiojungle', 'codecanyon', 'graphicriver', 'photodune', 'themeforest', 'videohive');
											foreach ( $envato_markets as $market ){ ?>
												<option <?php if( !empty($arq_options['social']['envato']['site']) && $arq_options['social']['envato']['site'] == $market ) echo'selected="selected"' ?> value="<?php echo $market ?>"><?php echo $market ?></option>
											<?php } ?>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php _e( 'Enter Your Envato Account Username .' , 'arq' ) ?></em></p>
							</div>
				<?php
					break;
					case 'soundcloud':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-soundcloud"></i><span><?php _e( 'SoundCloud' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[soundcloud][id]"><?php _e( 'UserName' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[soundcloud][id]" id="social[soundcloud][id]" value="<?php if( !empty($arq_options['social']['soundcloud']['id']) ) echo $arq_options['social']['soundcloud']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[soundcloud][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[soundcloud][text]" id="social[soundcloud][text]" value="<?php if( !empty($arq_options['social']['soundcloud']['text']) ) echo $arq_options['social']['soundcloud']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[soundcloud][api]"><?php _e( 'API Key' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[soundcloud][api]" id="social[soundcloud][api]" value="<?php if( !empty($arq_options['social']['soundcloud']['api']) ) echo $arq_options['social']['soundcloud']['api'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your SoundCloud Account Username and the API Key, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#soundcloud' ) ?></em></p>
							</div>
				<?php
					break;
					case 'behance':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-behance"></i><span><?php _e( 'Behance' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[behance][id]"><?php _e( 'UserName' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[behance][id]" id="social[behance][id]" value="<?php if( !empty($arq_options['social']['behance']['id']) ) echo $arq_options['social']['behance']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[behance][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[behance][text]" id="social[behance][text]" value="<?php if( !empty($arq_options['social']['behance']['text']) ) echo $arq_options['social']['behance']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[behance][api]"><?php _e( 'API Key' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[behance][api]" id="social[behance][api]" value="<?php if( !empty($arq_options['social']['behance']['api']) ) echo $arq_options['social']['behance']['api'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your Behance Account Username and the API Key, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#behance' ) ?></em></p>
							</div>
				<?php
					break;
					case 'instagram':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-instagram"></i><span><?php _e( 'Instagram' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[instagram][id]"><?php _e( 'UserName' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[instagram][id]" id="social[instagram][id]" value="<?php if( !empty($arq_options['social']['instagram']['id']) ) echo $arq_options['social']['instagram']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[instagram][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[instagram][text]" id="social[instagram][text]" value="<?php if( !empty($arq_options['social']['instagram']['text']) ) echo $arq_options['social']['instagram']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[instagram][api]"><?php _e( 'Access Token Key' , 'arq' ) ?></label></th>
										<td>
											<input type="text" disabled="disabled" name="social[instagram][api]" id="social[instagram][api]" value="<?php if( get_option( 'instagram_access_token' ) ) echo get_option( 'instagram_access_token' ) ?>">
											<a class="button-large button-primary tie-get-api-key" href="<?php echo admin_url().'admin.php?page=arqam&service=arq-Instagram' ?>"><?php _e( 'Get Access Token' , 'arq' ) ?></a>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your Instagram Username and click on Get Access Token to get your App Access Token, <a target="_blank" href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#instagram' ) ?></em></p>
							</div>
				<?php
					break;
					case 'mailchimp':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-envelope"></i><span><?php _e( 'MailChimp' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[mailchimp][id]"><?php _e( 'List ID' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[mailchimp][id]" id="social[mailchimp][id]" value="<?php if( !empty($arq_options['social']['mailchimp']['id']) ) echo $arq_options['social']['mailchimp']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[mailchimp][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[mailchimp][text]" id="social[mailchimp][text]" value="<?php if( !empty($arq_options['social']['mailchimp']['text']) ) echo $arq_options['social']['mailchimp']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[mailchimp][url]"><?php _e( 'List URL' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[mailchimp][url]" id="social[mailchimp][url]" value="<?php if( !empty($arq_options['social']['mailchimp']['url']) ) echo $arq_options['social']['mailchimp']['url'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[mailchimp][api]"><?php _e( 'API Key' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[mailchimp][api]" id="social[mailchimp][api]" value="<?php if( !empty($arq_options['social']['mailchimp']['api']) ) echo $arq_options['social']['mailchimp']['api'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your <a href="%1$s" target="_blank">List ID</a>, <a href="%2$s" target="_blank">List URL</a> and <a href="%3$s" target="_blank">API Key</a>.' , 'arq' ), 'http://kb.mailchimp.com/article/how-can-i-find-my-list-id', 'http://kb.mailchimp.com/article/how-do-i-share-my-signup-form', 'http://kb.mailchimp.com/article/where-can-i-find-my-api-key' ) ?></em></p>
							</div>
				<?php
					break;
					case 'mailpoet':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-envelope"></i><span><?php _e( 'MailPoet' , 'arq' ) ?></span></h3>
						<div class="inside">
							<?php if( class_exists( 'MailPoet\WP\Functions' ) ){ ?>
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[mailpoet][url]"><?php _e( 'List URL' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[mailpoet][url]" id="social[mailpoet][url]" value="<?php if( !empty($arq_options['social']['mailpoet']['url']) ) echo $arq_options['social']['mailpoet']['url'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[mailpoet][list]"><?php _e( 'Choose a List' , 'arq' ) ?></label></th>
										<td>
										<select name="social[mailpoet][list]" id="social[mailpoet][list]">
											<?php
											$mailpoet_lists = \MailPoet\API\API::MP('v1')->getLists();
											$mailpoet_lists = array_merge( array( array( 'id' => 'all', 'name' => __(' Total Subscribers', 'arq' ))), $mailpoet_lists);
											foreach ( $mailpoet_lists as $list ){ ?>
												<option <?php if( !empty($arq_options['social']['mailpoet']['list']) && !empty( $list[ 'id' ] ) && $arq_options['social']['mailpoet']['list'] == $list[ 'id' ] ) echo'selected="selected"' ?> value="<?php if( !empty( $list[ 'id' ] ) ) echo $list[ 'id' ] ?>"><?php if( !empty( $list[ 'name' ] ) ) echo $list[ 'name' ] ?></option>
											<?php } ?>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="social[mailpoet][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[mailpoet][text]" id="social[mailpoet][text]" value="<?php if( !empty($arq_options['social']['mailpoet']['text']) ) echo $arq_options['social']['mailpoet']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php _e( 'Choose a list and enter the list URL.', 'arq' ) ?></em></p>
							</div>
							<div class="clear"></div>
							<?php }else{ ?>
								<?php printf( __( 'You need to install the <a href="%s" target="_blank">MailPoet Newsletters</a> plugin first to use this section.' , 'arq' ), 'https://wordpress.org/plugins/wysija-newsletters/' ) ?>
							<?php } ?>
				<?php
					break;
					case 'mymail':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-envelope"></i><span><?php _e( 'Mailster' , 'arq' ) ?></span></h3>
						<div class="inside">
							<?php if( class_exists( 'mailster' ) ){ ?>
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[mymail][url]"><?php _e( 'List URL' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[mymail][url]" id="social[mymail][url]" value="<?php if( !empty($arq_options['social']['mymail']['url']) ) echo $arq_options['social']['mymail']['url'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[mymail][list]"><?php _e( 'Choose a List' , 'arq' ) ?></label></th>
										<td>
										<select name="social[mymail][list]" id="social[mymail][list]">
											<?php
											global $wpdb;
											$sql 	= "SELECT CASE WHEN a.parent_id = 0 THEN a.ID*10 ELSE a.parent_id*10+1 END AS _sort, a.* FROM {$wpdb->prefix}mailster_lists AS a WHERE 1=1 GROUP BY a.ID";
											$mymail_lists_items		 = $wpdb->get_results($sql);
											$mymail_lists 			   = array();
											$mymail_lists[1] 		   = new stdClass();
											$mymail_lists[1]->ID 	 = 'all';
											$mymail_lists[1]->name = __(' Total Subscribers', 'arq' );
											$mymail_lists 			   = array_merge($mymail_lists , $mymail_lists_items);
											foreach ( $mymail_lists as $list ){ ?>
												<option <?php if( !empty($arq_options['social']['mymail']['list']) && !empty( $list->ID ) && $arq_options['social']['mymail']['list'] == $list->ID ) echo'selected="selected"' ?> value="<?php if( !empty( $list->ID ) ) echo $list->ID ?>"><?php if( !empty( $list->name ) ) echo $list->name ?></option>
											<?php } ?>
											</select>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="social[mymail][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[mymail][text]" id="social[mymail][text]" value="<?php if( !empty($arq_options['social']['mymail']['text']) ) echo $arq_options['social']['mymail']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php _e( 'Choose a list and enter the list URL.', 'arq' ) ?></em></p>
							</div>
							<div class="clear"></div>
							<?php }else{ ?>
								<?php printf( __( 'You need to install the <a href="%s" target="_blank">Mailster - Email Newsletter Plugin for WordPress</a> first to use this section.' , 'arq' ), 'http://codecanyon.net/item/x/3078294?ref=tielabs' ) ?>
							<?php } ?>
				<?php
					break;
					case 'foursquare':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-foursquare"></i><span><?php _e( 'Foursquare' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[foursquare][id]"><?php _e( 'UserName' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[foursquare][id]" id="social[foursquare][id]" value="<?php if( !empty($arq_options['social']['foursquare']['id']) ) echo $arq_options['social']['foursquare']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[foursquare][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[foursquare][text]" id="social[foursquare][text]" value="<?php if( !empty($arq_options['social']['foursquare']['text']) ) echo $arq_options['social']['foursquare']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[foursquare][api]"><?php _e( 'Access Token Key' , 'arq' ) ?></label></th>
										<td>
											<input type="text" disabled="disabled" name="social[foursquare][api]" id="social[foursquare][api]" value="<?php if( get_option( 'foursquare_access_token' ) ) echo get_option( 'foursquare_access_token' ) ?>">
											<a class="button-large button-primary tie-get-api-key" href="<?php echo admin_url().'admin.php?page=arqam&service=arq-foursquare' ?>"><?php _e( 'Get Access Token' , 'arq' ) ?></a>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your Foursquare Username and click on Get Access Token to get your App Access Token, <a target="_blank" href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#foursquare' ) ?></em></p>
							</div>
				<?php
					break;
					case 'linkedin':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-linkedin"></i><span><?php _e( 'LinkedIn' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[linkedin][type]"><?php _e( 'Type' , 'arq' ) ?></label></th>
										<td>
											<select name="social[linkedin][type]" id="social[linkedin][type]">
											<?php

											$linkedin_type = array(
												'Company' => __( 'Company' , 'arq' ),
												'Profile' => __( 'Profile' , 'arq' ),
											);
											foreach ( $linkedin_type as $type => $type_name ){ ?>
												<option <?php if( !empty($arq_options['social']['linkedin']['type']) && $arq_options['social']['linkedin']['type'] == $type ) echo'selected="selected"' ?> value="<?php echo $type ?>"><?php echo $type_name ?></option>
											<?php } ?>
											</select>
										</td>
									</tr>
									<tr id="tie_linkedin_company">
										<th scope="row"><label for="social[linkedin][id]"><?php _e( 'Your Company ID' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[linkedin][company]" id="social[linkedin][company]" value="<?php if( !empty($arq_options['social']['linkedin']['company']) ) echo $arq_options['social']['linkedin']['company'] ?>"></td>
									</tr>
									<tr id="tie_linkedin_profile">
										<th scope="row"><label for="social[linkedin][profile]"><?php _e( 'Profile Page URL' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[linkedin][profile]" id="social[linkedin][profile]" value="<?php if( !empty($arq_options['social']['linkedin']['profile']) ) echo $arq_options['social']['linkedin']['profile'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[linkedin][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[linkedin][text]" id="social[linkedin][text]" value="<?php if( !empty($arq_options['social']['linkedin']['text']) ) echo $arq_options['social']['linkedin']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[linkedin][api]"><?php _e( 'Access Token Key' , 'arq' ) ?></label></th>
										<td>
											<input type="text" style="color: #999;" name="social[linkedin][api]" disabled="disabled" class="code" id="social[linkedin][api]" value="<?php if( get_option( 'linkedin_access_token' ) ) echo get_option( 'linkedin_access_token' ) ?>">
											<a class="button-large button-primary tie-get-api-key" href="<?php echo admin_url().'admin.php?page=arqam&service=arq-linkedin' ?>"><?php _e( 'Get Access Token' , 'arq' ) ?></a>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Choose the type of the Linkedin counter and click on Get Access Token to get your App Access Token, <a target="_blank" href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#linkedin' ) ?></em></p>

							</div>
				<?php
					break;
					case 'vk':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-vk"></i><span><?php _e( 'VK' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[vk][id]"><?php _e( 'Community ID/Name' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[vk][id]" id="social[vk][id]" value="<?php if( !empty($arq_options['social']['vk']['id']) ) echo $arq_options['social']['vk']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[vk][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[vk][text]" id="social[vk][text]" value="<?php if( !empty($arq_options['social']['vk']['text']) ) echo $arq_options['social']['vk']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php _e( 'Enter Your VK Community Name/ID .' , 'arq' ) ?></em></p>
							</div>
				<?php
					break;
					case 'tumblr':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-tumblr"></i><span><?php _e( 'Tumblr' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[tumblr][hostname]"><?php _e( 'Blog Hostname' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[tumblr][hostname]" id="social[tumblr][hostname]" value="<?php if( !empty($arq_options['social']['tumblr']['hostname']) ) echo $arq_options['social']['tumblr']['hostname'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[tumblr][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[tumblr][text]" id="social[tumblr][text]" value="<?php if( !empty($arq_options['social']['tumblr']['text']) ) echo $arq_options['social']['tumblr']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[tumblr][api]"><?php _e( 'Consumer Key' , 'arq' ) ?></label></th>
										<td>
											<input type="text" style="color: #999;" name="social[tumblr][api]" disabled="disabled" id="social[tumblr][api]" value="<?php if( get_option( 'tumblr_api_key' ) ) echo get_option( 'tumblr_api_key' ) ?>">
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="social[tumblr][SecretKey]"><?php _e( 'Secret Key' , 'arq' ) ?></label></th>
										<td>
											<input type="text" style="color: #999;" name="social[tumblr][SecretKey]" disabled="disabled" id="social[tumblr][SecretKey]" value="<?php if( get_option( 'tumblr_api_secret' ) ) echo get_option( 'tumblr_api_secret' ) ?>">
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="social[tumblr][Token]"><?php _e( 'Access Token Key' , 'arq' ) ?></label></th>
										<td>
											<input type="text" style="color: #999;" name="social[tumblr][Token]" disabled="disabled" id="social[tumblr][Token]" value="<?php if( get_option( 'tumblr_oauth_token' ) ) echo get_option( 'tumblr_oauth_token' ) ?>">
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="social[tumblr][secret]"><?php _e( 'Access Token secret Key' , 'arq' ) ?></label></th>
										<td>
											<input type="text" style="color: #999;" name="social[tumblr][secret]" disabled="disabled" id="social[tumblr][secret]" value="<?php if( get_option( 'tumblr_token_secret' ) ) echo get_option( 'tumblr_token_secret' ) ?>">
											<a class="button-large button-primary tie-get-api-key" href="<?php echo admin_url().'admin.php?page=arqam&service=arq-tumblr' ?>"><?php _e( 'Get the required info' , 'arq' ) ?></a>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your Blog Hostname and click on Get the required info to get your app info, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#tumblr') ?></em></p>
							</div>
				<?php
					break;
					case '500px':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-500px"></i><span><?php _e( '500px' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[500px][username]"><?php _e( 'UserName' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[500px][username]" id="social[500px][username]" value="<?php if( !empty($arq_options['social']['500px']['username']) ) echo $arq_options['social']['500px']['username'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[500px][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[500px][text]" id="social[500px][text]" value="<?php if( !empty($arq_options['social']['500px']['text']) ) echo $arq_options['social']['500px']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[500px][api]"><?php _e( 'Consumer Key' , 'arq' ) ?></label></th>
										<td>
											<input type="text" style="color: #999;" name="social[500px][api]" disabled="disabled" id="social[500px][api]" value="<?php if( get_option( '500px_api_key' ) ) echo get_option( '500px_api_key' ) ?>">
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="social[500px][SecretKey]"><?php _e( 'Secret Key' , 'arq' ) ?></label></th>
										<td>
											<input type="text" style="color: #999;" name="social[500px][SecretKey]" disabled="disabled" id="social[500px][SecretKey]" value="<?php if( get_option( '500px_api_secret' ) ) echo get_option( '500px_api_secret' ) ?>">
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="social[500px][Token]"><?php _e( 'Access Token Key' , 'arq' ) ?></label></th>
										<td>
											<input type="text" style="color: #999;" name="social[500px][Token]" disabled="disabled" id="social[500px][Token]" value="<?php if( get_option( '500px_oauth_token' ) ) echo get_option( '500px_oauth_token' ) ?>">
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="social[500px][secret]"><?php _e( 'Access Token secret Key' , 'arq' ) ?></label></th>
										<td>
											<input type="text" style="color: #999;" name="social[500px][secret]" disabled="disabled" id="social[500px][secret]" value="<?php if( get_option( '500px_token_secret' ) ) echo get_option( '500px_token_secret' ) ?>">
											<a class="button-large button-primary tie-get-api-key" href="<?php echo admin_url().'admin.php?page=arqam&service=arq-500px' ?>"><?php _e( 'Get the required info' , 'arq' ) ?></a>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your username and click on Get the required info to get your app info, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#px500' ) ?></em></p>
							</div>

				<?php
					break;
					case 'pinterest':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-pinterest"></i><span><?php _e( 'Pinterest' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[pinterest][username]"><?php _e( 'UserName' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[pinterest][username]" id="social[pinterest][username]" value="<?php if( !empty($arq_options['social']['pinterest']['username']) ) echo $arq_options['social']['pinterest']['username'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[pinterest][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[pinterest][text]" id="social[pinterest][text]" value="<?php if( !empty($arq_options['social']['pinterest']['text']) ) echo $arq_options['social']['pinterest']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php _e( 'Enter Your Pinterest username' , 'arq' ) ?></em></p>
							</div>
				<?php
					break;
					case 'flickr':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-flickr"></i><span><?php _e( 'Flickr' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[flickr][id]"><?php _e( 'Group ID' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[flickr][id]" id="social[flickr][id]" value="<?php if( !empty($arq_options['social']['flickr']['id']) ) echo $arq_options['social']['flickr']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[flickr][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[flickr][text]" id="social[flickr][text]" value="<?php if( !empty($arq_options['social']['flickr']['text']) ) echo $arq_options['social']['flickr']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[flickr][api]"><?php _e( 'API Key' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[flickr][api]" id="social[flickr][api]" value="<?php if( !empty($arq_options['social']['flickr']['api']) ) echo $arq_options['social']['flickr']['api'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter the Group ID and Your app API key, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#flickr' ) ?></em></p>
							</div>
				<?php
					break;
					case 'steam':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-steam"></i><span><?php _e( 'Steam' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[steam][group]"><?php _e( 'Group Slug' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[steam][group]" id="social[steam][group]" value="<?php if( !empty($arq_options['social']['steam']['group']) ) echo $arq_options['social']['steam']['group'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[steam][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[steam][text]" id="social[steam][text]" value="<?php if( !empty($arq_options['social']['steam']['text']) ) echo $arq_options['social']['steam']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter the Group Slug, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#steam' ) ?></em></p>
							</div>
				<?php
					break;
					case 'rss':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-feed"></i><span><?php _e( 'RSS' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[rss][url]"><?php _e( 'Feed URL' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[rss][url]" id="social[rss][url]" value="<?php if( !empty($arq_options['social']['rss']['url']) ) echo $arq_options['social']['rss']['url'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[rss][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[rss][text]" id="social[rss][text]" value="<?php if( !empty($arq_options['social']['rss']['text']) ) echo $arq_options['social']['rss']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[rss][type]"><?php _e( 'Type' , 'arq' ) ?></label></th>
										<td>
											<select name="social[rss][type]" id="social[rss][type]">
											<?php
											$rss_type = array('feedpress.it', 'Manual');
											foreach ( $rss_type as $type ){ ?>
												<option <?php if( !empty($arq_options['social']['rss']['type']) && $arq_options['social']['rss']['type'] == $type ) echo'selected="selected"' ?> value="<?php echo $type ?>"><?php echo $type ?></option>
											<?php } ?>
											</select>
										</td>
									</tr>
									<tr id="tie_rss_feedpress">
										<th scope="row"><label for="social[rss][feedpress]"><?php _e( 'Feedpress Json file URL' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[rss][feedpress]" id="social[rss][feedpress]" value="<?php if( !empty($arq_options['social']['rss']['feedpress']) ) echo $arq_options['social']['rss']['feedpress'] ?>"></td>
									</tr>
									<tr id="tie_rss_manual">
										<th scope="row"><label for="social[rss][manual]"><?php _e( 'Number of Subscribers' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[rss][manual]" id="social[rss][manual]" value="<?php if( !empty($arq_options['social']['rss']['manual']) ) echo $arq_options['social']['rss']['manual'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div><strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your Feed URl and the Feedpress Json file URL or Number of Subscribers manually, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#rss' ) ?></em></p></div>
				<?php
					break;
					case 'spotify':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-spotify"></i><span><?php _e( 'Spotify' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[spotify][id]"><?php _e( 'Spotify URI' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[spotify][id]" id="social[spotify][id]" value="<?php if( !empty($arq_options['social']['spotify']['id']) ) echo $arq_options['social']['spotify']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[spotify][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[spotify][text]" id="social[spotify][text]" value="<?php if( !empty($arq_options['social']['spotify']['text']) ) echo $arq_options['social']['spotify']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Spotify URI of the user or artist.' , 'arq' ), '' ) ?></em></p>
							</div>
				<?php
					break;
					case 'goodreads':
				?>
						<h3 class="hndle">
							<i<?php echo $color_style?> class="arqicon arqicon-goodreads">
								<svg version="1.1" xmlns="http://www.w3.org/2000/svg" width="35" height="20" style="margin-top: 7px" viewBox="0 0 430.117 430.118" xml:space="preserve">
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
							</i>
							<span><?php _e( 'Goodreads' , 'arq' ) ?></span>
						</h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[goodreads][id]"><?php _e( 'Goodreads profile URI' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[goodreads][id]" id="social[goodreads][id]" value="<?php if( !empty($arq_options['social']['goodreads']['id']) ) echo $arq_options['social']['goodreads']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[goodreads][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[goodreads][text]" id="social[goodreads][text]" value="<?php if( !empty($arq_options['social']['goodreads']['text']) ) echo $arq_options['social']['goodreads']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[goodreads][key]"><?php _e( 'API Key' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[goodreads][key]" id="social[goodreads][key]" value="<?php if( !empty($arq_options['social']['goodreads']['key']) ) echo $arq_options['social']['goodreads']['key'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Goodreads profile URI and the API key, <a href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#goodreads' ) ?></em></p>
							</div>
				<?php
					break;
					case 'twitch':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-twitch"></i><span><?php _e( 'Twitch' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[twitch][id]"><?php _e( 'Channel Name' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[twitch][id]" id="social[twitch][id]" value="<?php if( !empty($arq_options['social']['twitch']['id']) ) echo $arq_options['social']['twitch']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[twitch][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[twitch][text]" id="social[twitch][text]" value="<?php if( !empty($arq_options['social']['twitch']['text']) ) echo $arq_options['social']['twitch']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[twitch][api]"><?php _e( 'Access Token Key' , 'arq' ) ?></label></th>
										<td>
											<input type="text" disabled="disabled" name="social[twitch][api]" id="social[twitch][api]" value="<?php if( get_option( 'twitch_access_token' ) ) echo get_option( 'twitch_access_token' ) ?>">
											<a class="button-large button-primary tie-get-api-key" href="<?php echo admin_url().'admin.php?page=arqam&service=arq-twitch' ?>"><?php _e( 'Get Access Token' , 'arq' ) ?></a>
										</td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Your Twitch channel Name and click on Get Access Token to get your App Access Token, <a target="_blank" href="%s" target="_blank">Click Here</a> For More Details.' , 'arq' ), 'http://plugins.tielabs.com/docs/arqam/#twitch' ) ?></em></p>
							</div>
				<?php
					break;
					case 'mixcloud':
				?>
						<h3 class="hndle">
							<i<?php echo $color_style?> class="arqicon arqicon-mixcloud"></i>
							<span><?php _e( 'Mixcloud' , 'arq' ) ?></span>
						</h3>
						<div class="inside">
							<table class="links-table" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[mixcloud][id]"><?php _e( 'UserName' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[mixcloud][id]" id="social[mixcloud][id]" value="<?php if( !empty($arq_options['social']['mixcloud']['id']) ) echo $arq_options['social']['mixcloud']['id'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[mixcloud][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[mixcloud][text]" id="social[mixcloud][text]" value="<?php if( !empty($arq_options['social']['mixcloud']['text']) ) echo $arq_options['social']['mixcloud']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<div>
								<strong><?php _e( 'Need Help?' , 'arq' ) ?></strong><p><em><?php printf( __( 'Enter Mixcloud username .' , 'arq' ), '' ) ?></em></p>
							</div>
				<?php
					break;
					case 'posts':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-file-text"></i><span><?php _e( 'Posts' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table full-width" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[posts][active]"><?php _e( 'Posts counter' , 'arq' ) ?></label></th>
										<td><input type="checkbox" name="social[posts][active]" id="social[posts][active]" value="" <?php if( isset($arq_options['social']['posts']['active']) ) echo ' checked="checked"' ?>></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[posts][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[posts][text]" id="social[posts][text]" value="<?php if( !empty($arq_options['social']['posts']['text']) ) echo $arq_options['social']['posts']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row" style="min-width: 100px;" ><label for="social[posts][url]"><?php _e( 'URL' , 'arq' ) ?> <small><?php _e( '( Optional )' , 'arq' ) ?></small></label></th>
										<td><input type="text" name="social[posts][url]" id="social[posts][url]" value="<?php if( !empty($arq_options['social']['posts']['url']) ) echo $arq_options['social']['posts']['url'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
				<?php
					break;
					case 'comments':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-commenting"></i><span><?php _e( 'Comments' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table full-width" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[comments][active]"><?php _e( 'Comments counter' , 'arq' ) ?></label></th>
										<td><input type="checkbox" name="social[comments][active]" id="social[comments][active]" value="" <?php if( isset($arq_options['social']['comments']['active']) ) echo ' checked="checked"'?>></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[comments][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[comments][text]" id="social[comments][text]" value="<?php if( !empty($arq_options['social']['comments']['text']) ) echo $arq_options['social']['comments']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row" style="min-width: 100px;" ><label for="social[comments][url]"><?php _e( 'URL' , 'arq' ) ?> <small><?php _e( '( Optional )' , 'arq' ) ?></small></label></th>
										<td><input type="text" name="social[comments][url]" id="social[comments][url]" value="<?php if( !empty($arq_options['social']['comments']['url']) ) echo $arq_options['social']['comments']['url'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
				<?php
					break;
					case 'members':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-user"></i><span><?php _e( 'Members' , 'arq' ) ?></span></h3>
						<div class="inside">
							<table class="links-table full-width" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[members][active]"><?php _e( 'Members counter' , 'arq' ) ?></label></th>
										<td><input type="checkbox" name="social[members][active]" id="social[members][active]" value="" <?php if( isset($arq_options['social']['members']['active']) ) echo ' checked="checked"'?>></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[members][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[members][text]" id="social[members][text]" value="<?php if( !empty($arq_options['social']['members']['text']) ) echo $arq_options['social']['members']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row" style="min-width: 100px;" ><label for="social[members][url]"><?php _e( 'URL' , 'arq' ) ?> <small><?php _e( '( Optional )' , 'arq' ) ?></small></label></th>
										<td><input type="text" name="social[members][url]" id="social[members][url]" value="<?php if( !empty($arq_options['social']['members']['url']) ) echo $arq_options['social']['members']['url'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
					<?php
					break;
					case 'groups':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-group"></i><span><?php _e( 'BuddyPress - Groups' , 'arq' ) ?></span></h3>
						<div class="inside">
							<?php if( class_exists( 'BuddyPress' ) ){ ?>
							<table class="links-table full-width" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[groups][active]"><?php _e( 'Groups counter' , 'arq' ) ?></label></th>
										<td><input type="checkbox" name="social[groups][active]" id="social[groups][active]" value="" <?php if( isset($arq_options['social']['groups']['active']) ) echo ' checked="checked"'?>></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[groups][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[groups][text]" id="social[groups][text]" value="<?php if( !empty($arq_options['social']['groups']['text']) ) echo $arq_options['social']['groups']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row" style="min-width: 100px;" ><label for="social[groups][url]"><?php _e( 'URL' , 'arq' ) ?> <small><?php _e( '( Optional )' , 'arq' ) ?></small></label></th>
										<td><input type="text" name="social[groups][url]" id="social[groups][url]" value="<?php if( !empty($arq_options['social']['groups']['url']) ) echo $arq_options['social']['groups']['url'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<?php }else{
								 printf( __( 'You need to install the <a href="%s" target="_blank">BuddyPress</a> first to use this section.' , 'arq' ), 'https://wordpress.org/plugins/buddypress/' );
							} ?>
				<?php
					break;
					case 'forums':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-folder-open"></i><span><?php _e( 'bbPress - Forums' , 'arq' ) ?></span></h3>
						<div class="inside">
							<?php if( class_exists( 'bbPress' ) ){ ?>
							<table class="links-table full-width" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[forums][active]"><?php _e( 'Forums counter' , 'arq' ) ?></label></th>
										<td><input type="checkbox" name="social[forums][active]" id="social[forums][active]" value="" <?php if( isset($arq_options['social']['forums']['active']) ) echo ' checked="checked"'?>></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[forums][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[forums][text]" id="social[forums][text]" value="<?php if( !empty($arq_options['social']['forums']['text']) ) echo $arq_options['social']['forums']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row" style="min-width: 100px;" ><label for="social[forums][url]"><?php _e( 'URL' , 'arq' ) ?> <small><?php _e( '( Optional )' , 'arq' ) ?></small></label></th>
										<td><input type="text" name="social[forums][url]" id="social[forums][url]" value="<?php if( !empty($arq_options['social']['forums']['url']) ) echo $arq_options['social']['forums']['url'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<?php }else{
								 printf( __( 'You need to install the <a href="%s" target="_blank">bbPress</a> first to use this section.' , 'arq' ), 'https://wordpress.org/plugins/bbpress/' );
							} ?>

				<?php
					break;
					case 'topics':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-copy"></i><span><?php _e( 'bbPress - Topics' , 'arq' ) ?></span></h3>
						<div class="inside">
							<?php if( class_exists( 'bbPress' ) ){ ?>
							<table class="links-table full-width" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[topics][active]"><?php _e( 'Topics counter' , 'arq' ) ?></label></th>
										<td><input type="checkbox" name="social[topics][active]" id="social[topics][active]" value="" <?php if( isset($arq_options['social']['topics']['active']) ) echo ' checked="checked"'?>></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[topics][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[topics][text]" id="social[topics][text]" value="<?php if( !empty($arq_options['social']['topics']['text']) ) echo $arq_options['social']['topics']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row" style="min-width: 100px;" ><label for="social[topics][url]"><?php _e( 'URL' , 'arq' ) ?> <small><?php _e( '( Optional )' , 'arq' ) ?></small></label></th>
										<td><input type="text" name="social[topics][url]" id="social[topics][url]" value="<?php if( !empty($arq_options['social']['topics']['url']) ) echo $arq_options['social']['topics']['url'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<?php }else{
								 printf( __( 'You need to install the <a href="%s" target="_blank">bbPress</a> first to use this section.' , 'arq' ), 'https://wordpress.org/plugins/bbpress/' );
							} ?>
				<?php
					break;
					case 'replies':
				?>
						<h3 class="hndle"><i<?php echo $color_style?> class="arqicon arqicon-comments"></i><span><?php _e( 'bbPress - Replies' , 'arq' ) ?></span></h3>
						<div class="inside">
							<?php if( class_exists( 'bbPress' ) ){ ?>
							<table class="links-table full-width" cellpadding="0">
								<tbody>
									<tr>
										<th scope="row"><label for="social[replies][active]"><?php _e( 'Replies counter' , 'arq' ) ?></label></th>
										<td><input type="checkbox" name="social[replies][active]" id="social[replies][active]" value="" <?php if( isset($arq_options['social']['replies']['active']) ) echo ' checked="checked"'?>></td>
									</tr>
									<tr>
										<th scope="row"><label for="social[replies][text]"><?php _e( 'Text below the number' , 'arq' ) ?></label></th>
										<td><input type="text" name="social[replies][text]" id="social[replies][text]" value="<?php if( !empty($arq_options['social']['replies']['text']) ) echo $arq_options['social']['replies']['text'] ?>"></td>
									</tr>
									<tr>
										<th scope="row" style="min-width: 100px;" ><label for="social[replies][url]"><?php _e( 'URL' , 'arq' ) ?> <small><?php _e( '( Optional )' , 'arq' ) ?></small></label></th>
										<td><input type="text" name="social[replies][url]" id="social[replies][url]" value="<?php if( !empty($arq_options['social']['replies']['url']) ) echo $arq_options['social']['replies']['url'] ?>"></td>
									</tr>
									<tr>
										<th scope="row"><label for="color[<?php echo $arq_item; ?>]"><?php _e( 'Custom color' , 'arq' ) ?> </label></th>
										<td><input type="text" name="color[<?php echo $arq_item; ?>]" value="<?php if( !empty( $arq_options['color'][$arq_item] ) ) echo ( $arq_options['color'][$arq_item] ); ?>" class="arq-color-field" ></td>
									</tr>
								</tbody>
							</table>
							<?php }else{
								 printf( __( 'You need to install the <a href="%s" target="_blank">bbPress</a> first to use this section.' , 'arq' ), 'https://wordpress.org/plugins/bbpress/' );
							} ?>
				<?php
					break;
				}

				if( in_array( $arq_item, $arq_social_items )){ ?>
								<input type="hidden" name="sort[]" id="social[]" value="<?php echo $arq_item; ?>">
							<div class="clear"></div>
						</div><!-- .inside /-->
					</div> <!-- Box end /-->
					<?php
				}
				?>


				<?php } //End of Foreach Sorting ?>



				</div> <!-- Post Body COntent -->


				<div id="postbox-container-1" class="postbox-container">

					<div class="arq-notice info">
						<p>
							<?php _e( 'If you like Arqam please leave us a <a href="http://codecanyon.net/downloads?ref=tielabs" target="_blank">&#9733;&#9733;&#9733;&#9733;&#9733;</a> rating, that will help us a lot on improving our items and the support as well … A huge thank you from TieLabs in advance :)', 'arq' ); ?>
						</p>
					</div>

					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Need Help?' , 'arq' ) ?></span></h3>
						<div class="inside">
						<p>
							<ul>
								<li><a href="http://plugins.tielabs.com/docs/arqam/" target="_blank"><?php _e( 'Plugin Docs' , 'arq' ) ?></a></li>
								<li><a href="http://tielabs.com/help/" target="_blank"><?php _e( 'Support' , 'arq' ) ?></a></li>
							</ul>
						</p>
						</div>
					</div>

					<div class="postbox">
						<h3 class="hndle"><span><?php _e( 'Custom CSS' , 'arq' ) ?></span></h3>
						<div class="inside">
							<p>
								<textarea name="css" rows="10" cols="50" id="css" class="large-text code"><?php if( !empty( $arq_options['css'] ) ) echo htmlspecialchars_decode( $arq_options['css'] ); ?></textarea>
							</p>
						</div>
					</div>

					<div class="postbox">
						<h3 class="hndle" style="cursor: default"><span><?php _e( 'General Settings' , 'arq' ) ?></span></h3>
						<div class="inside">
							<p>
								<label for="cache"><?php _e( 'Cache Time' , 'arq' ) ?></label>
								<select name="cache" id="cache">
									<?php
									for ( $i = 1; $i <= 24 ; $i++ ){ ?>
									<option <?php if( !empty($arq_options['cache']) && $arq_options['cache'] == $i ) echo'selected="selected"' ?> value="<?php echo $i ?>"><?php echo $i ?> <?php _e( 'hours' , 'arq' ) ?> </option>
									<?php } ?>
								</select>
							</p>

							<div class="arq-notice arq-save">
								<?php _e( 'Saving the plugin settings will delete the counters cached data, so you may notice some delay in Arqam widget loading for the first time you visit your website after saving the settings till the plugin gets the counters data and cache it again. ', 'arq' ) ?>
							</div>

							<div id="publishing-action">
								<input type="hidden" name="action" value="save" />
								<input name="save" type="submit" class="button-large button-primary" id="publish" value="<?php _e( 'Save' , 'arq' ) ?>">
							</div>
							<div class="clear"></div>
						</div>
					</div>

				</div><!-- postbox-container /-->
			</div><!-- post-body /-->

		</div><!-- poststuff /-->
	</form>
</div>

<?php
	}
}


/*-----------------------------------------------------------------------------------*/
# ARQAM Shortcodes
/*-----------------------------------------------------------------------------------*/
add_action('admin_init', 'tie_arqam_add_admin_init');
function tie_arqam_add_admin_init(){

	$arqam_translation_array = array(
		'shortcodes_tooltip' => __( 'Arqam', 'arq' ),
		'style'              => __( 'Style :', 'arq' ),
		'metro'              => __( 'Metro Style', 'arq' ),
		'gray'               => __( 'Gray Icons', 'arq' ),
		'colored'            => __( 'Colored Icons', 'arq' ),
		'bordered'           => __( 'Colored Border Icons', 'arq' ),
		'flat'               => __( 'Flat Icons', 'arq' ),
		'columns'            => __( 'Columns :', 'arq' ),
		'column1'            => __( '1 Column', 'arq' ),
		'column2'            => __( '2 Columns', 'arq' ),
		'column3'            => __( '3 Columns', 'arq' ),
		'dark'               => __( 'Dark Skin :', 'arq' ),
		'width'              => __( 'Widget Width :', 'arq' ),
		'items_width'        => __( 'Forced Items Width :', 'arq' ),
		'save_warning'       => __( 'You will be moved to a new window, All unsaved changes will be lost it is recommended to save settings first. Are you sure you want to continue?', 'arq' ),
	);
	wp_localize_script( 'jquery-core', 'tiearqam_js', $arqam_translation_array );
}

add_action('admin_head', 'tie_arqam_add_mce_button');
function tie_arqam_add_mce_button() {
	if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
		return;
	}
	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', 'tie_arqam_add_tinymce_plugin'  );
		add_filter( 'mce_buttons', 'tie_arqam_register_mce_button' );
	}
}

// Declare script for new button
function tie_arqam_add_tinymce_plugin( $plugin_array ) {
	$plugin_array['tie_arqam_mce_button'] = plugins_url('assets/js/mce.js' , __FILE__);
	return $plugin_array;
}

// Register new button in the editor
function tie_arqam_register_mce_button( $buttons ) {
	array_push( $buttons, 'tie_arqam_mce_button' );
	return $buttons;
}



/*-----------------------------------------------------------------------------------*/
# Shortcode action in Front-end
/*-----------------------------------------------------------------------------------*/
add_shortcode('arqam', 'tie_arqam_shortcode');

function tie_arqam_shortcode( $atts, $content = null ) {
	$style = $columns = $dark = $width = $item ='';

	@extract($atts);

	ob_start();
	arq_get_counters( $style, $columns, $dark, $item, $width );
	$output = ob_get_contents();
	ob_end_clean();
	return $output;

}



/*-----------------------------------------------------------------------------------*/
# Visual Composer
/*-----------------------------------------------------------------------------------*/
add_action( 'vc_before_init', 'arqam_add_vc', 8 );
function arqam_add_vc() {
	vc_map( array(
		'name' 			  => ARQAM_Plugin,
		'description' => __( 'Social Counters', 'arq' ),
		'base' 			  => 'arqam',
		'class' 		  => '',
		'icon'			  => 'arqam-vc-icon',
		'category' 		=> 'Social',
		'params'      => array(
			array(
				'type' 			  => 'dropdown',
				'holder' 		  => 'div',
				'class'       => '',
				'heading'     => __( 'Style :', 'arq' ),
				'param_name'  => 'style',
				'save_always'	=> true,
				'value' 		=> array(
					__( 'Metro Style', 'arq' )          => 'metro',
					__( 'Gray Icons', 'arq' )           => 'gray',
					__( 'Colored Icons', 'arq' )        => 'colored',
					__( 'Colored Border Icons', 'arq' ) => 'colored_border',
					__( 'Flat Icons', 'arq' )           => 'flat'
				),
			),
			array(
				'type' 			  => 'dropdown',
				'holder' 		  => 'div',
				'class' 		  => '',
				'heading' 		=> __( 'Columns :', 'arq' ),
				'param_name' 	=> 'columns',
				'save_always'	=> true,
				'value'       => array(
					__( '1 Column',		'arq' )	=> '1',
					__( '2 Columns', 	'arq' )	=> '2',
					__( '3 Columns', 	'arq' )	=> '3'
				),
				'description' 	=> __( "Columns option not available for the METRO style.", "arq" )
			),
			array(
				'type'       => 'checkbox',
				'holder'     => 'div',
				'class'      => '',
				'heading'    => __( 'Dark Skin :', 'arq' ),
				'param_name' => 'dark',
				'value'      => array(
					__( 'Enable Dark Skin?', 'arq' ) => 1
				),
			),
			array(
				'type'       => 'textfield',
				'holder'     => 'div',
				'class'      => '',
				'heading'    => __( 'Widget Width :', 'arq' ),
				'param_name' => 'width'
			),
			array(
				'type'        => 'textfield',
				'holder'      => 'div',
				'class'       => '',
				'heading' 		=> __( 'Forced Items Width :', 'arq' ),
				'param_name' 	=> 'item'
			)
		)
	));
}



/*-----------------------------------------------------------------------------------*/
# Social Counter Widget
/*-----------------------------------------------------------------------------------*/
add_action( 'widgets_init', 'arqam_counter_widget_box' );
function arqam_counter_widget_box() {
	register_widget( 'arqam_counter_widget' );
}
class arqam_counter_widget extends WP_Widget {

	public function __construct(){
		$widget_ops 	= array( 'classname' => 'arqam_counter-widget', 'description' => ''  );
		$control_ops 	= array( 'width' => 250, 'height' => 350, 'id_base' => 'arqam_counter-widget' );
		parent::__construct( 'arqam_counter-widget', ARQAM_Plugin. ' - ' . __( 'Social Counter' ), $widget_ops, $control_ops );
	}

	public function widget( $args, $instance ){

		extract( $args );

		$title 		= $instance['title'];
		$layout 	= $instance['layout'];
		$columns 	= $instance['columns'];
		$dark 		= $instance['dark'];
		$width 		= $instance['width'];
		$box_only = $instance['box_only'];

		$inside_widget = false;

		if( empty($box_only) ){
			echo $before_widget . $before_title . $title . $after_title;
			$inside_widget = true;
		}

		arq_get_counters( $layout, $columns, $dark, $width, '', $inside_widget );

		if( empty($box_only) ){
			echo $after_widget;
		}
	}

	public function update( $new_instance, $old_instance ){
		$instance = $old_instance;
		$instance['layout'] 	= $new_instance['layout'] ;
		$instance['columns'] 	= $new_instance['columns'] ;
		$instance['title'] 		= $new_instance['title'] ;
		$instance['dark'] 		= $new_instance['dark'] ;
		$instance['width'] 		= $new_instance['width'] ;
		$instance['box_only'] = $new_instance['box_only'] ;

		return $instance;
	}

	public function form( $instance ){
		$defaults = array( 'title' => __( 'Follow us' , 'arq' )  , 'layout' => 'flat' ,'columns' => '3' , 'dark' => false, 'box_only' => false );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title :' , 'arq' ) ?> </label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" class="widefat" type="text" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'layout' ); ?>"><?php _e( 'Style :' , 'arq' ) ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'layout' ); ?>" name="<?php echo $this->get_field_name( 'layout' ); ?>" >
				<option value="metro" <?php if( $instance['layout'] == 'metro' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( 'Metro Style' , 'arq' ) ?></option>
				<option value="gray" <?php if( $instance['layout'] == 'gray' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( 'Gray Icons' , 'arq' ) ?></option>
				<option value="colored" <?php if( $instance['layout'] == 'colored' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( 'Colored Icons' , 'arq' ) ?></option>
				<option value="colored_border" <?php if( $instance['layout'] == 'colored_border' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( 'Colored Border Icons' , 'arq' ) ?></option>
				<option value="flat" <?php if( $instance['layout'] == 'flat' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( 'Flat Icons' , 'arq' )?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'columns' ); ?>"><?php _e( 'Columns :' , 'arq' ) ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id( 'columns' ); ?>" name="<?php echo $this->get_field_name( 'columns' ); ?>" >

				<option value="1" <?php if( $instance['columns'] == '1' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( '1 Column'  , 'arq' ) ?></option>
				<option value="2" <?php if( $instance['columns'] == '2' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( '2 Columns' , 'arq' ) ?></option>
				<option value="3" <?php if( $instance['columns'] == '3' ) echo "selected=\"selected\""; else echo ""; ?>><?php _e( '3 Columns' , 'arq' ) ?></option>
			</select>
			<small> <?php _e( 'Columns option not available for the METRO style.', 'arq' ); ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'dark' ); ?>"><?php _e( 'Dark Skin :' , 'arq' ) ?></label>
			<input id="<?php echo $this->get_field_id( 'dark' ); ?>" name="<?php echo $this->get_field_name( 'dark' ); ?>" value="true" <?php if( $instance['dark'] ) echo 'checked="checked"'; ?> type="checkbox" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'box_only' ); ?>"><?php _e( 'Show the Social Box only :' , 'arq' ) ?></label>
			<input id="<?php echo $this->get_field_id( 'box_only' ); ?>" name="<?php echo $this->get_field_name( 'box_only' ); ?>" value="true" <?php if( $instance['box_only'] ) echo 'checked="checked"'; ?> type="checkbox" />
			<br /><small><?php _e( 'Will avoid the theme widget design and hide the widget title .' , 'arq' ) ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'width' ); ?>"><?php _e( 'Forced Items Width :' , 'arq' ) ?></label>
			<input id="<?php echo $this->get_field_id( 'width' ); ?>" name="<?php echo $this->get_field_name( 'width' ); ?>" value="<?php if(isset( $instance['width'] )) echo $instance['width']; ?>" style="width:40px;" type="text" />
		</p>

	<?php
	}
}

?>
