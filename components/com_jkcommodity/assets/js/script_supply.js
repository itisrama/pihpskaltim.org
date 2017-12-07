jQuery.noConflict();
(function($) { 
	$(document).ready(function() {
		function loadLatestSupplies() {
			var data = $.getJSON(
				component_url, { 
					task: 'supply.loadLatestSupplies',
					date: $('#jform_date').val() || 0,
					city_id: $('#jform_city_id').val() || 0
				}, 
				function(data){
					if(!jQuery.isEmptyObject(data)){
						$(".commodity_supplies").html('');
						for(var i in data){
							$('#commodity_' + data[i].id + '_production').html(data[i].production);
							$('#commodity_' + data[i].id + '_consumption').html(data[i].consumption);
							$('#commodity_' + data[i].id + '_transported').html(data[i].transported);
						}
					}
				}
			);
		}

		$("#jform_date").change(function (){
			loadLatestSupplies();
		});

		$('#jform_city_id').change(function(){
			loadLatestSupplies();
		});
		
		$('.copy-supply').click(function(){
			var supply = $(this).prev('span').html().trim();
			$('.inputbox', $(this).closest('tr')).val(supply);
		});
		
		$('.clear-supply').click(function(){
			$(this).prev('input').val('');
		});
		
		$('.clear-trade').click(function(){
			$(this).parent().prev('input').val('');
			$(this).closest('td').find('.trades').html('');
			
			$(this).closest('td').find('.trades').append('<tr><td colspan="6" style="text-align:center;">-- Belum ada data --</td></tr>');
		});
		
		$(document).delegate('.trade-row select:nth-child(1)', 'change', function(){
			var column = $(this).closest('tr').find('td:nth-child(2)');
			
			if($(this).val() == 1){
				column.find('select:nth-child(1)').hide();
				column.find('select:nth-child(2)').show();
			}
			else{
				column.find('select:nth-child(1)').show();
				column.find('select:nth-child(2)').hide();
			}
		});
		
		$(document).delegate('.trade-row input:nth-child(1), .trade-row input:nth-child(2)', 'keyup', function(){
			var row = $(this).closest('tr');
			
			var tradedIn  = parseInt(row.find('td:nth-child(3)').find('input:first').val());
			var tradedOut = parseInt(row.find('td:nth-child(4)').find('input:first').val());
			
			tradedIn	= (tradedIn)? tradedIn : 0;
			tradedOut	= (tradedOut)? tradedOut : 0;

			row.find('td:nth-child(5)').find('input:first').val(tradedIn - tradedOut);
		});
		
		$(document).delegate('.delete-trade', 'click', function(){
			$(this).closest('tr').remove();
		});
		
		$(document).delegate('.commit-trade', 'click', function(){
			var nets = $(this).parent().parent().find('.trade-net');
			
			var total = 0;
			$.each(nets, function(i, net){
				total += (parseInt($(net).val()) || 0);
			});
			
			$(this).closest('td').children('input').val(total);
		});
		
		function createSelect(elName, options, value, style = ''){
			var html = [];
		
			var names	= elName.split('.');
			var id		= 'jform_' + names.join('_');
			var fName	= 'jform';
			$.each(names, function(i, part){
				fName += '[' + part + ']';
			});
		
			html.push('<select name="' + fName + '" id="' + id + '" class="inputbox" style="max-width:none !important;width:auto;' + style + '">');
			$.each(options, function(id, option){
				if(option != null)
					html.push('<option value="' + id + '"' + ((value == id)? 'selected' : '') + '>' + option + '</option>');
			});
			html.push('</select>');
		
			return html.join('');
		}
		
		function createInput(elName, type, placeholder){
			var html = [];
		
			var names	= elName.split('.');
			var id		= 'jform_' + names.join('_');
			var fName	= 'jform';
			$.each(names, function(i, part){
				fName += '[' + part + ']';
			});
		
			html.push('<input name="' + fName + '" id="' + id + '" type="' + type + '" class="input-small inputbox" placeholder="' + placeholder + '">');
			return html.join('');
		}
		
		$('.add-trade').click(function(event){
			var id = parseInt($(this).parent().parent().find('.modal-body').find('.trade-row:last').data('number')) + 1;
			var commodity = $(this).data('commodity');
			
			if(!id){
				$(this).parent().parent().find('.trades').html('');
				id = 0;
			}
			
			var types = ['Antar Kota', 'Antar Provinsi'];
			
			var html = [];
			html.push('<tr class="trade-row" data-number="' + id + '">');

			html.push('<td>');
			html.push(createSelect('trades.' + commodity + '.' + id + '.type', types, 0));
			html.push('</td>');

			html.push('<td>');
			html.push(createSelect('trades.' + commodity + '.' + id + '.partner_city_id', cities, 0, 'width:100%;'));
			html.push(createSelect('trades.' + commodity + '.' + id + '.partner_province_id', provinces, 0, 'display:none;'));
			html.push('</td>');

			html.push('<td>');
			html.push(createInput('trades.' + commodity + '.' + id + '.traded_in', 'text', 0));
			html.push('</td>');

			html.push('<td>');
			html.push(createInput('trades.' + commodity + '.' + id + '.traded_out', 'text', 0));
			html.push('</td>');

			html.push('<td>');
			html.push('<input type="text" class="input-small inputbox trade-net" readonly="true" aria-invalid="false">');
			html.push('</td>');

			html.push('<td>');
			html.push('<button class="btn btn-small delete-trade" type="button" data-toggle="tooltip" title="Hapus">');
			html.push('<i class="icon-remove" style="margin:0px;"></i>');
			html.push('</button>');
			html.push('</td>');

			html.push('</tr>');
			
			$(this).parent().parent().find('.modal-body').find('.trades').append(html.join(''));
			
			event.preventDefault();
		});
	});
})(jQuery);
