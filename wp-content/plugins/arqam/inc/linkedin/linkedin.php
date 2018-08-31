<?php

/*-----------------------------------------------------------------------------------*/
# Get LinkedIn Authorization Code
/*-----------------------------------------------------------------------------------*/
function tie_linkedin_getAuthorizationCode( $api_key ) {
	$params = array(
		'response_type' => 'code',
		'client_id'     => $api_key,
		'scope'         => 'rw_company_admin r_basicprofile',
		'state'         => uniqid( '', true ), // unique long string
		'redirect_uri'  => admin_url().'admin.php?page=arqam&service=arq-linkedin',
	);

	$url = 'https://www.linkedin.com/uas/oauth2/authorization?' . http_build_query($params);

	set_transient( 'linkedin_state', $params['state'] , 60*60 );

	header("Location: $url");
	exit;
}


/*-----------------------------------------------------------------------------------*/
# Get LinkedIn Access Token
/*-----------------------------------------------------------------------------------*/
function tie_linkedin_getAccessToken($api_key , $api_secret) {
	$params = array(
		'grant_type'    => 'authorization_code',
		'client_id'     => $api_key,
		'client_secret' => $api_secret,
		'code'          => $_GET['code'],
		'redirect_uri'  => admin_url().'admin.php?page=arqam&service=arq-linkedin',
	);

	$url   = 'https://www.linkedin.com/uas/oauth2/accessToken?' . http_build_query($params);
	$token = arq_remote_get( $url, true );

	set_transient( 'linkedin_expires_in',   $token['expires_in'], 60*60 );
	set_transient( 'linkedin_expires_at',   time() + $token['expires_in'], 60*60 );
	update_option( 'linkedin_access_token', $token['access_token'] );

	echo "<script type='text/javascript'>window.location='".admin_url()."admin.php?page=arqam#linkedin';</script>";
	exit;
}

?>
