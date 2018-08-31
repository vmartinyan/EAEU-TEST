jQuery(document).ready(function() {

	jQuery("div.arq-metro").each(function(){
		var elementsNumber = jQuery( this ).find( "li" ).length;
		var remainColumns = elementsNumber%5;
		
		//alert( remainColumns );
		if( remainColumns == 4 ){
			jQuery(this).addClass( 'arq-metro-last-2' ); 
		}else if( remainColumns == 1 || remainColumns == 3 ){
			jQuery(this).addClass( 'arq-metro-last-1' ); 
		}

	});

});