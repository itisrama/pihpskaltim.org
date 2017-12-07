jQuery.noConflict();
(function($) {
	$(function() {
		var all_list = $('.module-ct .pricelist ul.list_all');
		var up_list = $('.module-ct .pricelist ul.list_price_up');
		var down_list = $('.module-ct .pricelist ul.list_price_down');
		var still_list = $('.module-ct .pricelist ul.list_price_still');
		
		var row_show_count = 12;
		var i = 0;
		
		function shuffleRow() {
			var up_count = $('#up_count').val();
			var down_count = $('#down_count').val();
			var still_count = $('#still_count').val();

			$('li', up_list).slice(0, up_count).appendTo(up_list);
			$('li', down_list).slice(0, down_count).appendTo(down_list);
			$('li', still_list).slice(0, still_count).appendTo(still_list);

			$('li', up_list).slice(0, up_count).clone().appendTo(all_list);
			$('li', down_list).slice(0, down_count).clone().appendTo(all_list);
			$('li', still_list).slice(0, still_count).clone().appendTo(all_list);
		}

		function shuffleRowDown() {
			var up_count = $('#up_count').val();
			var down_count = $('#down_count').val();
			var still_count = $('#still_count').val();
			
			$('li', up_list).slice(-up_count).prependTo(up_list);
			$('li', down_list).slice(-down_count).prependTo(down_list);
			$('li', still_list).slice(-still_count).prependTo(still_list);

			$('li', up_list).slice(0, up_count).clone().appendTo(all_list);
			$('li', down_list).slice(0, down_count).clone().appendTo(all_list);
			$('li', still_list).slice(0, still_count).clone().appendTo(all_list);
		}

		function setSparkline() {
			$('li', all_list).each(function(){
				$('.spark', $(this)).sparkline('html', {
					/*type: 'bar',
					height: '20',
					barWidth: 5,
					barColor: '#c0392b',
					negBarColor: '#27ae60',
					zeroColor: '#2980b9'
					*/

					
					type: 'line',
					width: '100',
					height: '30',
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
			});
		}

		$(".pricelist .list_all").on( "click", "a", function() {
			if($('#regencyModal').length > 0) {
				var provName = $('#pricelist_regency_id option:selected').html();
				var provID = $('#pricelist_regency_id').val();
				var priceType = $('#pricelist_price_type_id').val();
				var comID = $(this).attr('commodityID');
				var listDate = $('#pricelist_date').attr('date');
				var comTitle = $('.title', this).html();

				$('#regencyModal .modal-title').html(provName+' - '+comTitle);
				$('#regencyModal').modal('show');
				$('#contentLoad').show();
				getRegencyDetail(provID, comID, priceType, listDate);
			}
			
		});

		function getData(regency_id, price_type_id) {
			$.ajax({
				url: component_url,
				data: {
					"task": "json.commodityPrices",
					"regency_id": regency_id,
					"price_type_id": price_type_id
				},
				dataType: 'json',
				cache: false
			}).done(function(data) {
				all_list.html('');
				up_list.html('');
				down_list.html('');
				still_list.html('');

				var count = {};
				count['price_still'] = 0;
				count['price_down'] = 0;
				count['price_up'] = 0;
		

				$.each(data.prices, function(k, v) {
					var item = $('.pricelist.template li').eq(0).clone();

					$('.title', item).html(v.title);
					$('.price_now span', item).html(v.price);
					$('.price_now div', item).html(v.denom);
					$('.price_desc span', item).html(v.status);
					$('.price_desc', item).addClass(v.class);
					$('.price_desc i', item).addClass(v.icon);	
					$('.spark', item).html(v.prices);
					$('a', item).attr('commodityID', k);

					item.css('background-image', 'url("'+v.image+'")');

					item.appendTo($('.module-ct .pricelist ul.list_'+v.class));
					count[v.class]++;
				});

				countKeys = Object.keys(count).sort(function(a,b){return count[a]-count[b]})

				var total = 12;
				var i = 0;
				$.each(countKeys, function(k, v) {
					i++;
					if(i == countKeys.length) {
						count[v] = total;
					} else {
						count[v] = count[v] > 4 ? 4 : count[v];
						total -= count[v];
					}
					
				});

				$('#up_count').val(count['price_up']);
				$('#down_count').val(count['price_down']);
				$('#still_count').val(count['price_still']);

				$('.pricelist .date').html(data.date);
				$('.pricelist .date').attr('date', data.dateSQL);
				sliderDown();
			});
		}
		getData(0, 1);

		var slider = function(){
			shuffleRow();
			$('li', all_list).slice(0, row_show_count).fadeOut('fast', function(){
				$(this).remove();
			});
			setSparkline();
		};

		var sliderDown = function(){
			shuffleRowDown();
			$('li', all_list).slice(row_show_count).hide().prependTo(all_list).fadeIn('slow', function(){
				$('li', all_list).slice(row_show_count).remove();
			});
			setSparkline();
		};
		
		/*
		var interval_time	= 15000;
		var interval		= setInterval(sliderDown, interval_time);
		*/
		
		/*$('.module-ct .pricelist').hover(function() {
			clearInterval(interval);
		}, function() {
			interval = setInterval(sliderDown, interval_time);
		});*/

		$('#pricelist_regency_id, #pricelist_price_type_id').change(function() {
			var provID		= $('#pricelist_regency_id').val();
			var priceTypeID	= $('#pricelist_price_type_id').val();
			getData(provID, priceTypeID);
		});
		$('.module-ct .pricelist .slidecontrol .fa-chevron-up').click(slider);
		$('.module-ct .pricelist .slidecontrol .fa-chevron-down').click(sliderDown);
	});
})(jQuery);