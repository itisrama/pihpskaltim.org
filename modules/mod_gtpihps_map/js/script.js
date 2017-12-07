jQuery.noConflict();

var tableRegency = null;

(function($) {
	var placeholder = {
		legend : {
			area : {
				display : false
				, mode : "horizontal"
				, marginLeft : 0
				, marginLeftTitle : 0
				, marginLeftLabel : 0
				, titleAttrs : {
					"font-family" : "RobotoCondensed, sans-serif"
					, "font-size" : "19pt"
					, "font-weight" : "bold"
					, "fill" : "#fff"
				}

				, slices : [
					{ min : 1, max : 1, label : ' ', attrs : { fill : "#1C8246" }, attrsHover : { fill : "#03692D", "stroke-width" : 3 }, legendSpecificAttrs : { "stroke-width" : 0, height : 10, width : 50 } }
					, { min : 2, max : 2, label : ' ', attrs : { fill : "#27AE65" }, attrsHover : { fill : "#0E954C", "stroke-width" : 3 }, legendSpecificAttrs : { "stroke-width" : 0, height : 10, width : 50 } }
					, { min : 3, max : 3, label : ' ', attrs : { fill : "#8CB938" }, attrsHover : { fill : "#73A01F", "stroke-width" : 3 }, legendSpecificAttrs : { "stroke-width" : 0, height : 10, width : 50 } }
					, { min : 4, max : 4, label : ' ', attrs : { fill : "#C0392B" }, attrsHover : { fill : "#A72012", "stroke-width" : 3 }, legendSpecificAttrs : { "stroke-width" : 0, height : 10, width : 50 } }
					, { min : 5, max : 5, label : ' ', attrs : { fill : "#7C1D15" }, attrsHover : { fill : "#630400", "stroke-width" : 3 }, legendSpecificAttrs : { "stroke-width" : 0, height : 10, width : 50 } }
					, { min : 6, max : 6, label : ' ', attrs : { fill : "#BBB" }, attrsHover : { fill : "#BBB", stroke : "#CCC", "stroke-width" : 1 }, legendSpecificAttrs : { "stroke-width" : 0, height : 10, width : 50 } }
					, { min : 0, max : 0, label : ' ', attrs : { fill : "#FFF" }, attrsHover : { fill : "#FFFFFF", stroke : "#A3A3A3", "stroke-width" : 1 }, legendSpecificAttrs : { "stroke-width" : 0, height : 10, width : 50 } }
				] 
			} 
		}
		, areas: {
			"Malaysia" : {
				value : -1
				, attrs : {
					fill : "#2c3e50"
				}
				, attrsHover : "#2c3e50"
			}
			, "Kaltara" : {
				value : -1
				, attrs : {
					fill : "#2c3e50"
				}
				, attrsHover : "#2c3e50"
			}
			, "Kalteng" : {
				value : -1
				, attrs : {
					fill : "#2c3e50"
				}
				, attrsHover : "#2c3e50"
			}
			, "Kalsel" : {
				value : -1
				, attrs : {
					fill : "#2c3e50"
				}
				, attrsHover : "#2c3e50"
			}
		}
	}

	$(function() {

		function checkDataType() {
			if($('#data_type').val() == 'price') {
				$('#period_type').attr('disabled', true);
				$('#period_type').val('');
				$('#period_type option').hide();
			} else {
				$('#period_type').removeAttr('disabled');
				$('#period_type').val('dtd');
				$('#period_type option').show();
			}
		}
		checkDataType();
		$('#data_type').change(function() {
			checkDataType();
		});
		var tableData = $(".table-container table").DataTable( {
			"scrollY":"310px",
			"scrollCollapse": true,
			"sorting":false,
			"paging":false,
			"filter":false,
			"info":false,
			"columns":[
				{"title": Joomla.JText._('MOD_GTPIHPS_MAP_REGENCY')},
				{"title": "", "class": "text-right"}
			]
		});

		$(".map-container").mapael($.extend(placeholder, {
			map : {
				name : "kaltim"
				, defaultArea: {
					eventHandlers: {
						click: function (e, id, mapElem, textElem, elemOptions) {
							var area = mapData.areas[id];
							if(typeof(area) != "undefined" && area.value > 0) {
								$('#regencyModal .modal-title').html(area.name+' - '+mapData.title);
								$('#regencyModal').modal('show');
								$('#contentLoad').show();
								getRegencyDetail(area.reg_id, mapData.commodity_id, mapData.price_type_id, area.date);
							}
						}
					}
					, attrs : {
						fill : "#D7EDAD"
						, stroke: "#2D6D9B"
						, "stroke-width" : 1
					}
					, attrsHover : {
						fill : "#D7EDAD"
					}
					, text : {
						attrs : {
							fill : "#505444"
						}
						, attrsHover : {
							fill : "#000"
						}
					}
					
				}
			}
		}));

		updateMap();
		$('#mapForm').submit(function(e){
			updateMap();
			e.preventDefault();
		});

		function updateMap() {
			var legend	= $(".map-container .areaLegend");
			var table	= $(".table-container");
			
			$('select, button', '#mapForm').attr('readonly', true);
			$('#mapForm #viewBtn i').attr('class', 'fa fa-fw fa-refresh fa-spin');
			$('#mapForm #viewBtn span').text(Joomla.JText._('MOD_GTPIHPS_MAP_UPDATING'));

			$(".map-container").trigger('update', [{
				mapOptions: placeholder,
				animDuration: 400,
				replaceOptions: true, 
				afterUpdate: function(){
					table.animate({ opacity: 0 }, 400);
					legend.animate({ opacity: 0 }, 400);
				}
			}]);

			$('.areaLegendDesc > div').fadeOut();

			$.ajax({
				url: component_url,
				data: $("#mapForm").serialize() + "&task=json.getData",
				dataType: 'json',
				cache: false
			}).done(function(data) {
				//var isPrice = $('#data_type option:checked').val() == 'price';
				var isPrice = true;
				$('select, button', '#mapForm').removeAttr('readonly');
				$('#mapForm #viewBtn i').attr('class', 'fa fa-fw fa-arrow-down');
				$('#mapForm #viewBtn span').text(Joomla.JText._('MOD_GTPIHPS_MAP_VIEW'));
				$('.chart-container h5').html(data.title);
				
				if(isPrice) {
					$('.areaLegendDesc .price').fadeIn();
				} else {
					$('.areaLegendDesc .fluc').fadeIn();
				}
				
				mapData = $.extend(data, {
					legend : {
						area : {
							display : true,
							title : data.title,
						}
					}
				});
				$(".map-container").trigger('update', [{
						mapOptions: mapData,
						animDuration: 400, 
						afterUpdate: function(){
							table.animate({ opacity: 1 }, 400);
							legend.animate({ opacity: 1 }, 400);
						}
				}]);

				tableData.clear().draw();
				$(tableData.columns(1).header()).html(isPrice ? Joomla.JText._('MOD_GTPIHPS_MAP_PRICE') : Joomla.JText._('MOD_GTPIHPS_MAP_CHANGE'));
				$.each(data.tableData, function(reg_id, reg) {
					tableData.row.add([reg.name, reg.display]).draw(false);
				});
				setFlot(data.is_percent, data.regData, data.regencies);

				$(".table-container").css("opacity", 1);
			});
		}

		function setFlot(isPercent, data, ticks) {
			var dataSet = [
				{data: data[0] || [], color: "#333"},
				{data: data[1] || [], color: "#27AE60"},
				{data: data[2] || [], color: "#8CB938"},
				{data: data[3] || [], color: "#F1C40F"},
				{data: data[4] || [], color: "#D97F1D"},
				{data: data[5] || [], color: "#C0392B"}
			];

			var options = {
				series: {
					bars: {
						show: true
					}
				},
				bars: {
					align: "center",
					barWidth: 0.6
				},
				xaxis: {
					axisLabelUseCanvas: false,
					ticks: ticks,
					color: '#2c3e50'
				},
				yaxis: {
					axisLabelUseCanvas: false,
					axisLabelPadding: 3,
					tickFormatter: function (v, axis) {
						return isPercent ? $.number(v, 2) : $.number(v, 0, ',', '.');
					},
					color: '#2c3e50'
				},
				legend: {
					show: false
				},
				grid: {
					hoverable: true,
					borderWidth: 2,
					backgroundColor: 'rgba(44, 62, 80, 0.4)',
					borderColor: '#2c3e50',
				}
			};

			$('.chart-container > div').empty();
			$.plot($('.chart-container > div'), dataSet, options);

			function showTooltip(x, y, contents) {
				$('<div id="tooltip">' + contents + '</div>').css( {
					position: 'absolute',
					display: 'none',
					top: y + 5,
					left: x + 5,
					border: '1px solid #333',
					padding: '2px 4px',
					'background-color': '#555',
					opacity: 0.80,
					zIndex: 200,
					color: '#fff',
					fontSize: '110%',
					borderRadius: '3px'
				}).appendTo('body').fadeIn('normal');
			}
			var previousPoint = null;
			$('.chart-container > div').bind('plothover', function (event, pos, item) {
				if (item) {
					if (previousPoint != item.dataIndex) {
						previousPoint = item.dataIndex;

						$('#tooltip').remove();
						var x = item.datapoint[0],
							y = item.datapoint[1];
						var xaxis = item.series.xaxis;
						showTooltip(item.pageX, item.pageY, '<strong>' + ticks[x-1][2] + '</strong><br/>' + (isPercent ? $.number(y, 2) + "%" : 'Rp ' + $.number(y, 0, ',', '.')));
					}
				}
				else {
					$('#tooltip').fadeOut('slow', function() { $(this).remove(); });
					previousPoint = null;
				}
			});
		}

		tableRegency = $("#contentTable .table-regency").DataTable( {
			"scrollX": "100%",
			"scrollY": "310px",
			"scrollCollapse": true,
			"sorting":false,
			"paging":false,
			"filter":false,
			"info":false,
			"autoWidth":false,
			"columns":[
				{"title": Joomla.JText._('MOD_GTPIHPS_MAP_MARKET'), "width": "40%"},
				{"title": "#1", "class": "text-right date-col", "width": "12%"},
				{"title": "#2", "class": "text-right date-col", "width": "12%"},
				{"title": "#3", "class": "text-right date-col", "width": "12%"},
				{"title": "#4", "class": "text-right date-col", "width": "12%"},
				{"title": "#5", "class": "text-right date-col", "width": "12%"},
				{"visible": false},
				{"visible": false}
			],
			"fnRowCallback": function (nRow, aData, iDisplayIndex) {
				var rowClass = aData[aData.length - 2];
				var rowValue = aData[aData.length - 1];

				$(nRow).addClass(rowClass);
				$(nRow).attr('value', rowValue);

				if(rowClass == 'regency') {
					$('td:first-child', $(nRow)).css('cursor', 'pointer');
					$('td:first-child', $(nRow)).prepend('<span class="fa fa-minus-circle" style="color:red"></span>&nbsp;&nbsp;');
				}

				if(iDisplayIndex == 0) {
					$('#contentTable .dataTables_scrollHead thead tr').eq(1).remove();
					$('#contentTable .dataTables_scrollHead thead').append($(nRow).clone());
					$(nRow).hide();
				}
			},
		});

		
	});
})(jQuery);


function getRegencyDetail(regency_id, commodity_id, price_type_id, date) {
	jQuery('#contentTable').css('opacity', 0);
	jQuery('#contentTable').css('height', 0);
	jQuery('#contentLoad').show();
	jQuery('#contentDownload').hide();
	tableRegency.clear();
	jQuery.ajax({
		url: component_url,
		data: {
			"task": "json.getRegencyDetail",
			"regency_id": regency_id,
			"commodity_id": commodity_id,
			"price_type_id": price_type_id,
			"date": date
		},
		dataType: 'json',
		cache: false
	}).done(function(data) {
		
		setTimeout(function(){
			tableRegency.rows.add(data.prices);
			tableRegency.draw();
			jQuery('#contentTable').css('opacity', 1);
			jQuery('#contentTable').css('height', 'auto');
			jQuery('#contentLoad').hide();
			jQuery('#contentDownload').attr('href', data.download_url);
			jQuery('#contentDownload').show();

			jQuery.each(data.dates, function(k, date){
				jQuery('#contentTable .dataTables_scrollHead thead th.date-col').eq(k).html(date.sdate2);
			});

			jQuery('#contentTable .regency td:first-child').click(function() {
				var rowValue = jQuery(this).parent().val();
				jQuery('.fa', jQuery(this)).toggleClass('fa-minus-circle');
				jQuery('.fa', jQuery(this)).toggleClass('fa-plus-circle');

				if(jQuery('.fa', jQuery(this)).hasClass('fa-plus-circle')) {
					jQuery('.fa', jQuery(this)).css('color', 'green');
					jQuery('#contentTable .market-'+rowValue).hide();
				} else {
					jQuery('.fa', jQuery(this)).css('color', 'red');
					jQuery('#contentTable .market-'+rowValue).show();
				}
			});
		}, 200);
	});
}
