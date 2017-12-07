jQuery.noConflict();
(function($) {
	var date	= new Date();
	var months	= ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
					'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
					];

	var row_show_count = 12;
	var i = 0;
	
	var themes = {
		light: {
			lineColor: '#568c95',
			fillColor: '#7fcedb',
			spotColor: '#e0e0e0',
			highlightSpotColor: '#e0e0e0',
			highlightLineColor: '#e0e0e0'
		},
		dark: {
			lineColor: '#ffffff',
			fillColor: '#bababa',
			spotColor: '#e0e0e0',
			highlightSpotColor: '#ffffff',
			highlightLineColor: '#ffffff'
		},
		light_blue: {
			lineColor: '#aab2bd',
			fillColor: '#ffffff',
			spotColor: '#5d9cec',
			highlightSpotColor: '#e0e0e0',
			highlightLineColor: '#e0e0e0'
		},
		black_white: {
			lineColor: '#333333',
			fillColor: '#8f8f8f',
			spotColor: '#5d9cec',
			maxSpotColor: '#aaaaaa',
			minSpotColor: '#aaaaaa',
			highlightSpotColor: '#333333',
			highlightLineColor: '#333333'
		}
	}
	
	$(document).ready(function(){
		if(show_province){
			refreshFilter('province', 'provinces');
		}
		
		if(show_category){
			refreshFilter('category', 'categories');
		}
		
		if(show_commodity){
			refreshFilter('commodity', 'commodities');
		}
		
  		refreshData();

		theme = themes[theme];
		
		$('.gtpihps-up').click(slider);
		$('.gtpihps-down').click(sliderDown);

		$('.province-id').change(
			function() {
				updateFilter('province');
			}
		);

		$('.category-id').change(
			function() {
				updateFilter('category')
			}
		);

		$('.commodity-id').change(
			function() {
				updateFilter('commodity')
			}
		);
	});

	function refreshFilter(type, task){
		var ractive = this;
		$.ajax({
			url: 'https://hargapangan.id/?option=com_gtpihps',
			//url: 'http://localhost/hargapangan/?option=com_gtpihps',
			data: {
				'task' : 'json.' + task,
			},
			dataType: 'json'
		}).done(function(data) {
			var filter	= $('.gtpihps-commodity').find('.' + type + '-id');
			var select	= false;

			var id = '';
			switch(type) {
				case 'province': 
					id = province_id; 
					break;

				case 'category': 
					id = category_id; 
					break;

				case 'commodity': 
					id = commodity_id; 
					break;
			}

			$.each(data.result, function(i, item){
				if(item.id){
					select = (id == item.id);
					$(filter).append('<option value="' + item.id + '" ' + (select? 'selected' : '') + '>' + item.name + '</option>');
				}
			});
		});
	}



	function updateFilter(type){

		var filter = $('.gtpihps-commodity').find('.' + type + '-id');

		switch(type) {
			case 'province': 
				province_id = $(filter).val(); 
				break;

			case 'category': 
				category_id = $(filter).val(); 
				break;

			case 'commodity': 
				commodity_id = $(filter).val(); 
				break;
		}

		refreshData();
	}

	function refreshData(){
		$.ajax({
			url: 'https://hargapangan.id/?option=com_gtpihps',
			//url: 'http://localhost/hargapangan/?option=com_gtpihps',
			data: {
				'task'			: 'json.commodityPrices',
				'province'		: province,
				'province_id'	: province_id,
				'category'		: category,
				'category_id'	: category_id,
				'commodity'		: commodity,
				'commodity_id'	: commodity_id,
			},
			dataType: 'json'
		}).done(function(data) {
			$('.gtpihps-date').text(data.date);
			
			$('.gtpihps-commodity').find('ul.gtpihps-list-all').html('');
			$('.gtpihps-commodity').find('ul.gtpihps-list-price-up').html('');
			$('.gtpihps-commodity').find('ul.gtpihps-list-price-down').html('');
			$('.gtpihps-commodity').find('ul.gtpihps-list-price-still').html('');

			var count = {};
			count['price-still']	= 0;
			count['price-down']		= 0;
			count['price-up']		= 0;
			
			var nPrices = 0;

			$.each(data.prices, function(k, v) {
				nPrices++;

				var item = $('.gtpihps-commodity').find('.gtpihps-template li').eq(0).clone();
				var cls	 = v.class.replace('_', '-');

				$('.gtpihps-title', item).html(v.title);
				$('.gtpihps-price-now span', item).html(v.price);
				$('.gtpihps-price-now div', item).html(v.denom);
				$('.gtpihps-price-desc span', item).html(v.status);
				$('.gtpihps-price-desc', item).addClass(cls);
				$('.gtpihps-price-desc i', item).addClass(v.icon);	
				$('.gtpihps-spark', item).html(v.prices);
				$('a', item).attr('commodityID', k);
				
				item.appendTo($('.gtpihps-commodity').find('ul.gtpihps-list-' + cls));
				count[cls]++;
			});

			//hide shuffle button if the item count is 12 or less
			if(nPrices<=12) {
				$('.gtpihps-up').addClass('gtpihps-hidden');
				$('.gtpihps-down').addClass('gtpihps-hidden');
			} else {
				$('.gtpihps-up').removeClass('gtpihps-hidden');
				$('.gtpihps-down').removeClass('gtpihps-hidden');
			}

			var countKeys = Object.keys(count).sort(function(a,b){return count[a]-count[b]})

			var total = 12;
			var i = 0;
			$.each(countKeys, function(k, v) {
				i++;
				if(i == countKeys.length) {
					count[v] = total;
				}
				else {
					count[v] = count[v] > 4 ? 4 : count[v];
					total	-= count[v];
				}
		
			});

			$('.gtpihps-commodity').find('.gtpihps-count-up').val(count['price-up']);
			$('.gtpihps-commodity').find('.gtpihps-count-down').val(count['price-down']);
			$('.gtpihps-commodity').find('.gtpihps-count-still').val(count['price-still']);
			
			sliderDown();
		});
	}

	function slider(){
		shuffleRow();

		list_all = $('.gtpihps-commodity').find('ul.gtpihps-list-all');
		$('li', list_all).slice(0, row_show_count).fadeOut('fast', function(){
			$(this).remove();
		});
		
		setSparkline(theme);
	}

	function shuffleRow() {
		var list_all	= $('.gtpihps-commodity').find('ul.gtpihps-list-all');
		var list_up		= $('.gtpihps-commodity').find('ul.gtpihps-list-price-up');
		var list_down	= $('.gtpihps-commodity').find('ul.gtpihps-list-price-down');
		var list_still	= $('.gtpihps-commodity').find('ul.gtpihps-list-price-still');
		
		var count_up	= $('.gtpihps-commodity').find('.gtpihps-count-up').val();
		var count_down	= $('.gtpihps-commodity').find('.gtpihps-count-down').val();
		var count_still	= $('.gtpihps-commodity').find('.gtpihps-count-still').val();

		$('li', list_up).slice(0, count_up).appendTo(list_up);
		$('li', list_down).slice(0, count_down).appendTo(list_down);
		$('li', list_still).slice(0, count_still).appendTo(list_still);

		$('li', list_up).slice(0, count_up).clone().appendTo(list_all);
		$('li', list_down).slice(0, count_down).clone().appendTo(list_all);
		$('li', list_still).slice(0, count_still).clone().appendTo(list_all);
	}

	function sliderDown(){
		shuffleRowDown();

		var list_all = $('.gtpihps-commodity').find('ul.gtpihps-list-all');
		$('li', list_all).slice(row_show_count).hide().prependTo(list_all).fadeIn('slow', function(){
			$('li', list_all).slice(row_show_count).remove();
		});
		
		setSparkline(theme);
	}

	function shuffleRowDown(){
		var list_all	= $('.gtpihps-commodity').find('ul.gtpihps-list-all');
		var list_up		= $('.gtpihps-commodity').find('ul.gtpihps-list-price-up');
		var list_down	= $('.gtpihps-commodity').find('ul.gtpihps-list-price-down');
		var list_still	= $('.gtpihps-commodity').find('ul.gtpihps-list-price-still');
		
		var count_up	= $('.gtpihps-commodity').find('.gtpihps-count-up').val();
		var count_down	= $('.gtpihps-commodity').find('.gtpihps-count-down').val();
		var count_still	= $('.gtpihps-commodity').find('.gtpihps-count-still').val();
	
		$('li', list_up).slice(-count_up).prependTo(list_up);
		$('li', list_down).slice(-count_down).prependTo(list_down);
		$('li', list_still).slice(-count_still).prependTo(list_still);

		$('li', list_up).slice(0, count_up).clone().appendTo(list_all);
		$('li', list_down).slice(0, count_down).clone().appendTo(list_all);
		$('li', list_still).slice(0, count_still).clone().appendTo(list_all);
	}

	function setSparkline(theme) {
		var list_all = $('.gtpihps-commodity').find('ul.gtpihps-list-all');
		$('li', list_all).each(function(){
			$('.gtpihps-spark', $(this)).sparkline('html', {
				type: 'line',
				width: '100',
				height: '54',
				lineColor: theme.lineColor,
				fillColor: theme.fillColor,
				lineWidth: 2,
				spotColor: theme.spotColor,
				minSpotColor: '#27ae60',
				maxSpotColor: '#c0392b',
				highlightSpotColor: theme.highlightSpotColor,
				highlightLineColor: theme.highlightLineColor,
				spotRadius: 5
			});

			var width = $(this).closest('.gtpihps-commodity').width();
			
			var desc_width = $('.gtpihps-desc', this).outerWidth();
			if(width > 250 && desc_width > 123)
				$('.gtpihps-spark', this).attr('style', 'margin-right: ' + (133 - desc_width) + 'px !important');
		});
	}
})(jQuery);
