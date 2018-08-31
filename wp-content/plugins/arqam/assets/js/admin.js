jQuery(function() {

	//Color Picker
	var arqColorsOptions = {
		change: function(event, ui){
			var newColor = ui.color.toString();
			jQuery(this).closest( '.postbox-arq' ).find( 'i' ).attr('style', 'background-color: '+newColor+' !important');
		},
		clear: function() {
			jQuery(this).closest( '.postbox-arq' ).find( 'i' ).attr('style', '');
		},
		palettes: false,
	};
	jQuery('.arq-color-field').wpColorPicker( arqColorsOptions );

	//Open blocks via links
	var hash = window.location.hash;
	hash = hash.replace(/[^a-z0-9\s]/gi, '').replace(/[_\s]/g, '-');
	if ( jQuery( hash ).length ) {
		jQuery( hash ).removeClass( 'closed' );
	}

	jQuery(document).ready(function() {

		jQuery( '.tie-get-api-key' ).click(function() {
			if( confirm( tiearqam_js.save_warning )){
				return true;
			}else{
				return false;
			}
		});

		//Social Blocks
		var isMobile = jQuery(document.body).hasClass('mobile');
		jQuery('.meta-box-sortables').sortable({
			placeholder: 'sortable-placeholder',
			connectWith: '.meta-box-sortables',
			items: '.postbox-arq',
			handle: '.hndle',
			cursor: 'move',
			delay: ( isMobile ? 200 : 0 ),
			distance: 2,
			tolerance: 'pointer',
			forcePlaceholderSize: true,
			helper: 'clone',
			opacity: 0.65,
		});

		// Linkedin
		var arq_linkedin = jQuery("select[name='social[linkedin][type]'] option:selected").val();
		if ( arq_linkedin == 'Company' ) {
			jQuery('#tie_linkedin_company').show();
			jQuery('#tie_linkedin_profile').hide();
		}
		else{
			jQuery('#tie_linkedin_profile').show();
			jQuery('#tie_linkedin_company').hide();
		}

		jQuery("select[name='social[linkedin][type]']").change(function(){
			var arq_linkedin = jQuery("select[name='social[linkedin][type]'] option:selected").val();

			if ( arq_linkedin == 'Company' ) {
				jQuery( '#tie_linkedin_company' ).fadeIn();
				jQuery('#tie_linkedin_profile').hide();
			}
			else{
				jQuery( '#tie_linkedin_profile' ).fadeIn();
				jQuery('#tie_linkedin_company').hide();
			}
		});

		//Rss
		var arq_rss = jQuery("select[name='social[rss][type]'] option:selected").val();
		if ( arq_rss == 'Manual' ) {
			jQuery('#tie_rss_manual').show();
		}
		if ( arq_rss == 'feedpress.it' ) {
			jQuery('#tie_rss_feedpress').show();
		}
		jQuery("select[name='social[rss][type]']").change(function(){
			var arq_rss = jQuery("select[name='social[rss][type]'] option:selected").val();
			if ( arq_rss == 'feedpress.it' ) {
				jQuery( '#tie_rss_manual'    ).hide();
				jQuery( '#tie_rss_feedpress' ).fadeIn();
			}
			if ( arq_rss == 'Manual' ) {
				jQuery( '#tie_rss_feedpress' ).hide();
				jQuery( '#tie_rss_manual'    ).fadeIn();
			}
		});
	});

});





