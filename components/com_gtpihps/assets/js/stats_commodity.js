jQuery.noConflict();
(function($) {
	$(window).load(function() {
		$('#filter_province_ids').change(function() {
			loadOptions($('#filter_regency_ids'), 'stats_province.loadRegencies', { 'filter_province_ids[]' : $('#filter_province_ids').val(), 'price_type_id' : $('#price_type_id').val() });
		});

		toggleRegency($('#filter_layout').val());
		$('#filter_layout').change(function() {
			toggleRegency($(this).val());
			toogleMarket();
		});

		function toggleRegency(reportType) {
			if(reportType == 'chart') {
				$('#regency_ids').hide();
			} else {
				$('#regency_ids').show();
			}
		}

		toogleMarket();
		$('#filter_province_ids').change(function() {
			toogleMarket();
		});
		
		function toogleMarket() {
			var layout = $('#filter_layout').val();
			var provIds = $('#filter_province_ids').val();
			var length = provIds instanceof Array ? provIds.length : 0;
			if(length >= 1 && length <= 10 && layout != 'chart') {
				$('#showMarket').show();
			} else {
				$('#showMarket').hide();
			}
		}
	});
})(jQuery);