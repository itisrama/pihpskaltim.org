jQuery.noConflict();
(function($) {
	$(window).load(function() {

		// Vertical Table Nav
		var vlimit = 10;
		var vstart = 1;
		var vstop = vstart + vlimit;
		var row_len = $('.mod_gtpihps_table #report tr').length;
		
		hide_row();
		function hide_row() {
			$('.mod_gtpihps_table .table-up').removeAttr('disabled');
			$('.mod_gtpihps_table .table-down').removeAttr('disabled');
			
			$('.mod_gtpihps_table #report tr').hide();
			$('.mod_gtpihps_table #report tr').eq(0).show();
			$('.mod_gtpihps_table #report tr').slice(vstart, vstop).show();

			if(vstart == 1) {
				$('.mod_gtpihps_table .table-up').attr('disabled', true);
				return false;
			}
			if(vstop == row_len) {
				$('.mod_gtpihps_table .table-down').attr('disabled', true);
				return false;
			}
		}
		
		var scroll;
		$('.mod_gtpihps_table .table-up').on({
			mousedown: function(){
				vstart--;
				vstop--;
				hide_row();
			}
		});
	
		$('.mod_gtpihps_table .table-down').on({
			mousedown: function(){
				vstart++;
				vstop++;
				hide_row();
			}
		});
		
		// Horizontal Table Nav
		var hlimit = 3;
		var hoffset = 0;
		var hstart = 1;
		var hstop = hstart + hlimit;
		var col_len = $('.mod_gtpihps_table #report tr th').length;
		hide_column();
		function hide_column() {
			$('.mod_gtpihps_table .table-next').removeAttr('disabled');
			$('.mod_gtpihps_table .table-prev').removeAttr('disabled');
			hstart = (hoffset * hlimit) + 2;
			hstop = hstart + hlimit;
			var max_offset = (col_len / hlimit) - 2;
			if(hoffset >= max_offset) {
				hstart = col_len - hlimit;
				hstop = col_len;
				$('.mod_gtpihps_table .table-next').attr('disabled', true);
			}
			
			if(hoffset < 1) {
				hstart = 1;
				hstop = hlimit + 2;
				$('.mod_gtpihps_table .table-prev').attr('disabled', true);
			}
			$('.mod_gtpihps_table #report td, #report th').hide();
			$('.mod_gtpihps_table #report th:nth-child(1), #report td:nth-child(1)').show();
			$('.mod_gtpihps_table #report th:nth-child(2), #report td:nth-child(2)').show();
			for(var i=hstart+1;i<=hstop;i++) {
				$('#report th:nth-child('+i+'), #report td:nth-child('+i+')').show();
			};
		}
		
		$('.mod_gtpihps_table .table-next').on({
			mousedown: function(){
				hoffset++;
				hide_column();
			}
		});
		
		$('.mod_gtpihps_table .table-prev').on({
			mousedown: function(){
				hoffset--;
				hide_column();
			}
		});
	});
})(jQuery);