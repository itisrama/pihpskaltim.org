jQuery.noConflict();
(function($) {
	$(window).load(function() {
		$('#jform_province_id').change(function() {
			loadOptions($('#jform_regency_id'), 'price.loadRegencies', { 'province_id' : $('#jform_province_id').val() }, true);
			getLatestPrices();
		});

		$('#jform_regency_id, #jform_price_type_id').change(function() {
			loadOptions($('#jform_market_id'), 'price.loadMarkets', { 'regency_id' : $('#jform_regency_id').val(), 'price_type_id' : $('#jform_price_type_id').val() }, true);
			getLatestPrices();
		});

		$('#jform_date_img').blur(function() {
			getLatestPrices();
		});

		$( "#adminForm #jform_market_id" ).on( "change", function() {
			getLatestPrices();
		});

		getLatestPrices();
		function getLatestPrices() {
			var el = $('.price');
			var options = { 
				'task' : 'price.loadLastPrices',
				'province_id' : $('#jform_province_id').val(),
				'regency_id' : $('#jform_regency_id').val(),
				'market_id' : $('#jform_market_id').val(),
				'date' : $('#jform_date').val()
			};
			el.html('&nbsp;&nbsp;<i class="fa fa-refresh fa-spin fa-lg"></i>');
			$.getJSON(component_url, options).done(function( json ) {
				el.html('');
				$.each(json, function(i, v) {
					$('#price_'+v.commodity_id).html(v.price);
					$('#price_'+v.commodity_id).formatCurrency({
						region: 'custom'
					});
				})
			});
		}

		$('.copy-price').click(function(){
			var price = $(this).prev('span').html();
			$('.currency', $(this).closest('tr')).val(price);
		});
		
		$('#copy-prices').click(function(){
			$('.copy-price').each(function(){
				var price = $(this).prev('span').html();
				$('.currency', $(this).closest('tr')).val(price);
			});
		});
		
		$('.clear-price').click(function(){
			$(this).prev('input').val('');
		});

		$('#clear-prices').click(function(){
			$('.clear-price').each(function(){
				$(this).prev('input').val('');
			});
		});
		
	});
})(jQuery);