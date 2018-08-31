(function($) {
	$(function() {

		$('.upcoming_event_widget_type').live('change', function(){
			var parent = $(this).parents('.widget-inside');
			if ($(this).val() === '1') {
				parent.find('.featured_event_block').show();
				parent.find('.upcoming_event_block').hide();
			} else {
				parent.find('.featured_event_block').hide();
				parent.find('.upcoming_event_block').show();
			}
		});

	});
}(jQuery));