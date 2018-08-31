<?php

/*-----------------------------------------------------------------------------------*/
# Plugin DB Update
/*-----------------------------------------------------------------------------------*/
if( get_option('arqam_active') && get_option('arqam_active') < '1.7.0' ){
	global $arq_options;

	if( !empty( $arq_options['social']['instagram']['api'] ) && !get_option( 'instagram_access_token' ) ){
		update_option( 'instagram_access_token',  $arq_options['social']['instagram']['api'] );
		unset( $arq_options['social']['instagram']['api'] );
	}

	if( !empty( $arq_options['social']['foursquare']['api'] ) && !get_option( 'foursquare_access_token' ) ){
		update_option( 'foursquare_access_token',  $arq_options['social']['foursquare']['api'] );
		unset( $arq_options['social']['foursquare']['api'] );
	}

	if( !empty( $arq_options['social']['linkedin']['id'] ) ){
		$arq_options['social']['linkedin']['id'] = 'https://www.linkedin.com/company/'.$arq_options['social']['linkedin']['id'];
	}

	if( !empty( $arq_options['social']['linkedin']['group'] ) ){
		$arq_options['social']['linkedin']['group'] = 'https://www.linkedin.com/groups/'.$arq_options['social']['linkedin']['group'];
	}

	update_option( 'arq_options' , $arq_options);
	update_option( 'arqam_active' , '1.7.0' );

} elseif( get_option('arqam_active') && get_option('arqam_active') < '2.0.0' ){
	global $arq_options;

	$sort 		= $arq_options[ 'sort' ];
	if( !empty( $sort ) && is_array( $sort ) ){
		$google 	= array_search('google+', $sort);
		$forrst 	= array_search('forrst',  $sort);

		$arq_options['sort'][ $google ] = 'google';

		unset( $arq_options['sort'][ $forrst ] );

		if( $arq_options['sort'][ $google ] == 'google' && !isset( $arq_options['sort'][ $forrst ] ) ){
			$update_sort_array = update_option( 'arq_options', $arq_options);
		}
	}

	update_option( 'arqam_active' , '2.0.0' );
}

?>
