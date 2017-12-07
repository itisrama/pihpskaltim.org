jQuery.noConflict();
(function($) {
	$(window).load(function() {
		function checkDocNumType() {
			$('*[name*="doc_num]"]').attr('readonly', $('*[name*="doc_num_type]"]').filter(':checked').val() == 'auto');
		}

		$('*[name*="doc_num_type]"]').change(function() {
			checkDocNumType();
		});
	});
})(jQuery);