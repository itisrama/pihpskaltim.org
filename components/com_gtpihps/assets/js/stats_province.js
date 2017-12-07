jQuery.noConflict();
(function($) {
	$(window).load(function() {
		$('#filter_province_ids').change(function() {
			loadOptions($('#filter_regency_ids'), 'stats_province.loadRegencies', { 'filter_province_ids[]' : $('#filter_province_ids').val(), 'price_type_id' : $('#price_type_id').val() });
		});

		$('#filter_regency_ids').change(function() {
			loadOptions($('#filter_market_ids'), 'stats_province.loadMarkets', { 'filter_regency_ids[]' : $('#filter_regency_ids').val(), 'price_type_id' : $('#price_type_id').val() });
		});
	});
})(jQuery);