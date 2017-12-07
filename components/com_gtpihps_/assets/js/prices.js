jQuery.noConflict();
(function($) {
	$(window).load(function() {
		$('#filter_province_ids').change(function() {
			loadOptions($('#filter_regency_ids'), 'prices.loadRegencies', { 'filter_province_ids[]' : $('#filter_province_ids').val() });
		});

		$('#filter_regency_ids').change(function() {
			loadOptions($('#filter_market_ids'), 'prices.loadMarkets', { 'filter_regency_ids[]' : $('#filter_regency_ids').val() });
		});
	});
})(jQuery);