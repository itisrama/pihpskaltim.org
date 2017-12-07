jQuery.noConflict();
(function($) {
	$(window).load(function() {
		
		// Horizontal Table Nav
		var hlimit = typeof global_hlimit !== 'undefined' ? global_hlimit : 6;
		var hoffset = 0;
		var hstart = 1;
		var hstop = hstart + hlimit;
		var hfreeze = typeof global_hfreeze !== 'undefined' ? global_hfreeze : 2;;
		var col_len = $('#report tr th').length;
		hide_column();
		function hide_column() {
			hstart = (hoffset * hlimit) + hfreeze;
			hstop = hstart + hlimit;
			if(hstop > col_len) {
				hstart = col_len - hlimit;
				hstop = col_len;
				hoffset--;
			}
			if(hstart < 1) {
				hstart = 1;
				hstop = hlimit + hfreeze;
				hoffset++;
			}
			$('#report td, #report th').hide();
			for(var i=1;i<=hfreeze;i++) {
				$('#report th:nth-child('+i+'), #report td:nth-child('+i+')').show();
			}
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