jQuery.noConflict();
(function($) {
	$(window).load(function() {
		$('#filter_regency_ids').change(function() {
			loadOptions($('#filter_market_ids'), 'regency_statistics.loadMarkets', { 'filter_regency_ids[]' : $('#filter_regency_ids').val(), 'price_type_id' : $('#price_type_id').val() });
		});
	});
})(jQuery);