jQuery.noConflict();
(function($) { 
	$(function() {
		$("#jform_market_id").change(function (){
			loadLatestPrice();
		});

		$("#jform_date").change(function (){
			loadLatestPrice();
		});

		$("#jform_unit_id").change(function (){
			loadLatestPrice();
		});

		function loadLatestPrice() {
			var data = $.getJSON(
				component_url, { 
					task: 'price.loadLatestPrices',
					date: $('#jform_date').val() || 0,
					city_id: $('#jform_city_id').val() || 0,
					market_id: $('#jform_market_id').val() || 0,
				}, 
				function(data){
					if(!jQuery.isEmptyObject(data)){
						$(".commodity_prices").html('');
						for(var i in data){
							$("#commodity_"+data[i].id).html(data[i].price).formatCurrency({ region: 'custom' });
							$("#jform_last_details_"+data[i].id).val(data[i].price);
						}
					}
				}
			);
		}

		$('#jform_city_id').change(function(){

			// Populate the market selectbox
			$(this).data('val', $(this).val());
			$.getJSON(component_url, { task: 'price.loadMarket', city_id: $(this).val() || 0 }, function(data){
				updatelist($('#jform_market_id'), data);
			});
			$('#jform_market_id').prop('selectedIndex',0);
			loadLatestPrice();
		});
		
		$('.copy-price').click(function(){
			var price = $(this).prev('span').html();
			$('.inputbox', $(this).closest('tr')).val(price);
		});
		
		$('#copy-prices').click(function(){
			$('.copy-price').each(function(){
				var price = $(this).prev('span').html();
				$('.inputbox', $(this).closest('tr')).val(price);
			});
		});
		
		$('.clear-price').click(function(){
			$(this).prev('input').val('');
		});
	})
})(jQuery);
