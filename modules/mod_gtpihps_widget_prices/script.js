jQuery.noConflict();
(function($) {
	var date	= new Date();
	var months	= ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
					'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
					];

	var row_show_count = 12;
	var i = 0;
	
	$(document).ready(function(){
		if(show_province){
			refreshFilter('province', 'provinces');
		}
		
		console.log(show_province);
		
		if(!show_province && (show_city || show_market)){
			refreshFilter('city', 'regencies');
		}
		
		if(show_market){
			refreshFilter('market', 'markets');
		}
		
  		refreshData();

		$('.province-id').change(
			function() {
				updateFilter('province');
				refreshFilter('city', 'regencies');
				$('.city-id').change();
			}
		);

		$('.city-id').change(
			function() {
				updateFilter('city');
				refreshFilter('market', 'markets');
				$('.market-id').change();
			}
		);

		$('.market-id').change(
			function() {
				updateFilter('market');
			}
		);
	});

	function refreshFilter(type, task){
		var ractive = this;
		var data = {
			'task' : 'json.' + task,
		};

		if(type == 'market')
			data.regency_id = city_id;

		if(type == 'city')
			data.province_id = province_id;

		$.ajax({
			url: 'https://hargapangan.id/?option=com_gtpihps',
			data: data,
			dataType: 'json'
		}).done(function(data) {
			var filter	= $('.gtpihps-price').find('.' + type + '-id');
			var select	= false;

			var id = '';
			switch(type) {
				case 'province': 
					id = province_id; 
					break;

				case 'city': 
					id = city_id; 
					break;

				case 'market': 
					id = market_id; 
					break;
			}

			var options = $(filter).find('option');
			$.each(options, function(i, option){
				if(i > 0)
					$(option).remove();
			});

			$.each(data.result, function(i, item){
				if(item.id){
					select = (id == item.id);
					$(filter).append('<option value="' + item.id + '" ' + (select? 'selected' : '') + '>' + item.name + '</option>');
				}
			});
		});
	}



	function updateFilter(type){

		var filter = $('.gtpihps-price').find('.' + type + '-id');

		switch(type) {
			case 'province': 
				province_id = $(filter).val(); 
				break;

			case 'city': 
				city_id = $(filter).val(); 
				break;

			case 'market': 
				market_id = $(filter).val(); 
				break;
		}

		refreshData();
	}

	function refreshData(){
		$.ajax({
			url: 'https://hargapangan.id/?option=com_gtpihps',
			data: {
				'task'			: 'json.commodityPrices',
				'province'		: province,
				'province_id'	: province_id,
				'regency'		: city,
				'regency_id'	: city_id,
				'market'		: market,
				'market_id'		: market_id,
			},
			dataType: 'json'
		}).done(function(data) {
			$('.gtpihps-date').text(data.date);
			
			var categories = {};
			$.each(data.prices, function(key, value) {
				if(categories[value.category_id] == undefined){
					categories[value.category_id] = {};
					categories[value.category_id].name = value.category;
					categories[value.category_id].commodities = [];
				}
				categories[value.category_id].commodities.push(value);
			});

			var table = $('.gtpihps-table', $('.gtpihps-price'));
			table.html("");
			
			$.each(categories, function(i, category) {
				table.append('<tr class="gtpihps-category"><td colspan="4">' + category.name + '</td></tr>');
					
				$.each(category.commodities, function(j, commodity){
					var html = [];
					
					html.push('<tr class="gtpihps-commodity">');
					html.push('<td>' + commodity.title + '</td>');
					html.push('<td>' + commodity.price + '</td>');
					html.push('<td>' + commodity.denom.replace('Per ', '') + '</td>');
					html.push('<td><i class="' + commodity.icon + '" aria-hidden="true"></i></td>');
					html.push('</tr>');
					
					table.append(html.join(''));
				});
			});
		});
	}

})(jQuery);
