jQuery.noConflict();
(function($) {
	$(window).load(function() {
		$('#jform_province_id').change(function(){
			loadOptions($('#jform_region_id'), 'ref_province.loadRegions', { 'province_id' : $('#jform_province_id').val() });
		});
	});
})(jQuery);
