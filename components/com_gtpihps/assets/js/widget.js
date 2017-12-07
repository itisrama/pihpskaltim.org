jQuery.noConflict();
(function($) {
	$(function() {
		$('.spark', $(this)).sparkline('html', {
			type: 'line',
			width: '50',
			height: '20',
			lineColor: '#ffffff',
			fillColor: 'transparent',
			lineWidth: 2,
			spotColor: '#ffffff',
			minSpotColor: '#27ae60',
			maxSpotColor: '#c0392b',
			highlightSpotColor: '#ffffff',
			highlightLineColor: '#ffffff',
			spotRadius: 5
		});

		$('.widget .table').css('margin-top', $('.widget h3').outerHeight());
		$('.widget .table').css('margin-bottom', $('.widget footer').outerHeight());
	});
})(jQuery);