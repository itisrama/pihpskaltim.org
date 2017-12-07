jQuery.noConflict();
(function($) {
	$(document).ready(function (){
		checkStatusRanmor();
		$("[id^=jform_status]").click(function() {
			checkStatusRanmor();
		});

		function checkStatusRanmor() {
			var statusRanmor = $("[id^=jform_status]:checked").val();
			if(statusRanmor == 'hilang') {
				$('#jform_tgl_ketemu').val('');
				$('#jform_tgl_ketemu').attr('disabled', true);
				$('#jform_tgl_ketemu_container .btn').attr('disabled', true);
			} else {
				$('#jform_tgl_ketemu').removeAttr('disabled');
				$('#jform_tgl_ketemu_container .btn').removeAttr('disabled');
			}
		}
	});

	
})(jQuery);