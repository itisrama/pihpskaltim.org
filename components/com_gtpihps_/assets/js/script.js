jQuery.noConflict();
(function($) {
	$(function() {
		$(document).on('click', '.nav-tabs a', function(e) {
			$('#tab_position').val($(this).attr('href').replace('#', ''));
		});

		$('.form-filter select[multiple=multiple] option').click(function() {
			$(this).closest('.form-group').find('input[type=checkbox]').prop('checked', false);
		});

		var unselectSelectOptFilter = function(e) {
			if(e.prop('checked')) {
				e.closest('.form-group').find('select[multiple=multiple] option').prop('selected', false);
			}
		}

		$('.form-filter input[type=checkbox]').click(function() {
			unselectSelectOptFilter($(this));
		});

		unselectSelectOptFilter($('.form-group input[type=checkbox]'));


	});

})(jQuery);

var loadOptions = function(el, task, options) {
	var options = jQuery.extend({ 'task' : task }, options);
	el.prev('i').remove();
	el.prev().after('&nbsp;&nbsp;<i class="fa fa-refresh fa-spin fa-lg"></i>');
	el.attr('disabled', true);
	el.load(component_url, options, function() {
		el.removeAttr('disabled');
		el.prev('i').remove();
	});
}

var loadRegions = function(el, task, options, regency) {
	var options = jQuery.extend({ 'task' : task }, options);
	el.prev('i').remove();
	el.prev().after('&nbsp;&nbsp;<i class="fa fa-refresh fa-spin fa-lg"></i>');
	el.attr('disabled', true);
	el.load(component_url, options, function() {
		el.removeAttr('disabled');
		el.prev('i').remove();
		loadOptions(regency, 'ref_regency.loadRegencies', { 'region_id' : el.val() });
	});
}
