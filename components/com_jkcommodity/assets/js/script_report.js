jQuery.noConflict();
(function($) { 
	$(function() {
		// Horizontal Table Nav
		var hlimit = 6;
		var hoffset = 0;
		var hstart = 1;
		var hstop = hstart + hlimit;
		var col_len = $('#report tr th').length;
		hide_column();
		function hide_column() {
			hstart = (hoffset * hlimit) + 1
			hstop = hstart + hlimit;
			if(hstop > col_len) {
				hstart = col_len - hlimit;
				hstop = col_len;
				hoffset--;
			}
			if(hstart < 1) {
				hstart = 1;
				hstop = hlimit + 1;
				hoffset++;
			}
			$('#report td, #report th').hide();
			$('#report th:nth-child(1), #report td:nth-child(1)').show();
			for(var i=hstart+1;i<=hstop;i++) {
				$('#report th:nth-child('+i+'), #report td:nth-child('+i+')').show();
			};
		}
		
		$('.table-next').on({
			mousedown: function(){
				scroll = setInterval(function(){
					hoffset++;
					hide_column();
				}, 70);
			},
			mouseup: function(){
				clearInterval(scroll);
			}
		});
		
		$('.table-prev').on({
			mousedown: function(){
				scroll = setInterval(function(){
					hoffset--;
					hide_column();
				}, 70);
			},
			mouseup: function(){
				clearInterval(scroll);
			}
		});
	
		function toogleSelectCommodity(obj) {
			if(obj.attr('checked')) {
				obj.closest('label').prev('select').attr('readonly', true);
				obj.closest('label').prev('select').find('option').removeAttr('selected');
			} else {
				obj.closest('label').prev('select').removeAttr('readonly');
			}
		}
		
		toogleSelectCommodity($('#all_commodity'));
		$('#all_commodity').click(function(){
			toogleSelectCommodity($(this));
		});
	
		$("optgroup").click(function(){
			if($(this).closest('select').attr('readonly')) return false;
			if($('option:selected', this).length === $('option', this).length){
				$(this).children('option').removeAttr('selected');
				return false;
			}
			$(this).children('option').attr('selected', function(id, oldAttr) {
				return !oldAttr;
			});
		});
	
		$("option").click(function(e){
			e.stopPropagation();
		});
	});	
})(jQuery);