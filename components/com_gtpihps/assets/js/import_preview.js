jQuery.noConflict();
(function($) {
	$(window).load(function() {
		
		// Horizontal Table Nav
		var hlimit = typeof global_hlimit !== 'undefined' ? global_hlimit : 6;
		var hoffset = 0;
		var hstart = 1;
		var hstop = hstart + hlimit;
		var col_len = $('#report tr th').length;
		hide_column();
		function hide_column() {
			hstart = (hoffset * hlimit) + 2;
			hstop = hstart + hlimit;
			if(hstop > col_len) {
				hstart = col_len - hlimit;
				hstop = col_len;
				hoffset--;
			}
			if(hstart < 1) {
				hstart = 1;
				hstop = hlimit + 2;
				hoffset++;
			}
			$('#report td, #report th').hide();
			$('#report th:nth-child(1), #report td:nth-child(1)').show();
			$('#report th:nth-child(2), #report td:nth-child(2)').show();
			for(var i=hstart+1;i<=hstop;i++) {
				$('#report th:nth-child('+i+'), #report td:nth-child('+i+')').show();
			};
		}
		
		$('.table-next').on({
			mousedown: function(){
				hoffset++;
				hide_column();
			}
		});
		
		$('.table-prev').on({
			mousedown: function(){
				hoffset--;
				hide_column();
			}
		});
	});
})(jQuery);