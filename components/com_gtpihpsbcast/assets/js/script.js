jQuery.noConflict();
(function($) {
	$(function() {
		$(document).on('click', '.nav-tabs a', function(e) {
			$('#tab_position').val($(this).attr('href').replace('#', ''));
		});
	});

})(jQuery);