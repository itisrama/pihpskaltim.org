jQuery.noConflict();
(function($) {
	$(function() {
		/* CURRENCY
		 ---------------------- */
		// Format Currency
		$('.currency').formatCurrency({region: 'custom'});
		$('.currency').keyup(function(e) {
			var e = window.event || e;
			var keyUnicode = e.charCode || e.keyCode;
			if (e !== undefined) {
				switch (keyUnicode) {
					case 16:
						break; // Shift
					case 17:
						break; // Ctrl
					case 18:
						break; // Alt
					case 27:
						this.value = '';
						break; // Esc: clear entry
					case 35:
						break; // End
					case 36:
						break; // Home
					case 37:
						break; // cursor left
					case 38:
						break; // cursor up
					case 39:
						break; // cursor right
					case 40:
						break; // cursor down
					case 78:
						break; // N (Opera 9.63+ maps the "." from the number key section to the "N" key too!)
					case 110:
						break; // . number block (Opera 9.63+ maps the "." from the number block to the "N" key (78) !!!)
					case 190:
						break; // .
					default:
						$(this).formatCurrency({region: 'custom'});
				}
			}
		});

		$(".currency").keydown(function(event) {
			// Allow: backspace, delete, tab, escape, enter and .
			if ($.inArray(event.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
					// Allow: Ctrl+A
							(event.keyCode == 65 && event.ctrlKey === true) ||
							// Allow: home, end, left, right
									(event.keyCode >= 35 && event.keyCode <= 39) ||
									(event.keyCode == 188)) {
						// let it happen, don't do anything
						return;
					}
					else {
						// Ensure that it is a number and stop the keypress
						if (event.shiftKey || (event.keyCode < 48 || event.keyCode > 57) && (event.keyCode < 96 || event.keyCode > 105)) {
							event.preventDefault();
						}
					}
				});

		$('#city_id option').click(function() {
			// Populate the market selectbox
			$.getJSON(component_url, {task: 'price.loadMarket', city_id: $(this).closest('select').val()}, function(data) {
				updatelist($('#market_id'), data);
			});
		});

		$('#filter_city_id option').click(function() {
			// Populate the market selectbox
			$.getJSON(component_url, {task: 'price.loadMarket', city_id: $(this).closest('select').val()}, function(data) {
				updatelist($('#filter_market_id'), data);
			});
		});

		$('fieldset.filter legend').click(function() {
			jQuery(this).closest('fieldset').children().not('legend').toggle();
			return false;
		});

		/* DETAIL DATA
		 ---------------------- */
		// Delete Detail
		$('.btn-remove-detail').live('click', function() {
			var rows = $('tr', $(this).closest('tbody'));
			var container = $(this).closest('table');
			if (rows.length == 2) {
				$('.no-data', container).show();
			}
			$(this).closest('tr').remove();
		})

		// Edit Detail
		$('.btn-edit-detail').live('click', function() {
			var row = $(this).closest('tr');
			var rows = $('tr', $(this).closest('tbody'));
			var form = $(this).closest('table').prev('.inputs');
			var row_id = $('.row_id', form);
			var inputboxes = $('.inputbox', form);
			$('input', row).each(function(i) {
				inputboxes.eq(i).val($(this).val());
				if (inputboxes.eq(i).hasClass('currency')) {
					inputboxes.eq(i).formatCurrency({region: 'custom'});
				}
			});
			row_id.val(rows.index(row));
			$('.btn-add-detail', form).hide();
			$('.btn-save-detail', form).show();
			$('.btn-cancel-detail', form).show();
		})

		// Reset+Cancel Detail
		$('.btn-reset-detail, .btn-cancel-detail').click(function() {
			var form = $(this).closest('.inputs');
			var inputboxes = $('.inputbox', form);
			var row_id = $('.row_id', form);
			inputboxes.val('');
			if ($(this).hasClass('btn-cancel-detail')) {
				row_id.val('');
				$('.btn-add-detail', form).show();
				$('.btn-save-detail', form).hide();
				$('.btn-cancel-detail', form).hide();
			}
		})

		// Add+Save Detail
		$('.btn-add-detail, .btn-save-detail').click(function() {
			var form = $(this).closest('.inputs');
			var inputboxes = $('.inputbox', form);
			var container = form.next('table');
			var row_id = $('.row_id', form);
			var obj = null;
			if (row_id.val()) {
				obj = $('.list tr', container).eq(row_id.val());
				$('.btn-add-detail', form).show();
				$('.btn-save-detail', form).hide();
				$('.btn-cancel-detail', form).hide();
			} else {
				obj = $('.list tr', container).eq(0).clone();
				obj.show().appendTo($('.list', container));
			}
			inputboxes.each(function(i) {
				var item_value = $(this).attr('value');
				var item_display = item_value;
				if ($(this).is('select')) {
					item_display = $('option:selected', $(this)).text();
				}
				if ($(this).hasClass('currency')) {
					item_value = $(this).asNumber({region: 'custom'});
				}
				$('input', obj).eq(i).val(item_value);
				$('span', obj).eq(i).text(item_display);
			})
			row_id.val('');
			inputboxes.val('');
			$('.no-data', container).hide();
		});

		$('#jkchart466 canvas').change(function() {
			alert('loaded')
		});



		$('option').live('click', function() {
			resetSelect($(this))
		})
		$('option').click(function() {
			resetSelect($(this))
		})
		function resetSelect(obj) {
			if (!obj.val() || obj.val() == '0' || obj.val() == '') {
				$('option', obj.closest('select')).removeAttr('selected');
				obj.attr('selected', 'selected');
			} else if ($('option[value=0]', obj.closest('select')).attr('selected')) {
				$('option[value=0]', obj.closest('select')).removeAttr('selected');
			}
		}

		function generateCanvas() {
			var canvas = $('<canvas id="canvas" width="900" height="370"></canvas>');
			$('#jkchart466 .legend').css('fontFamily', 'Arial');
			$('#jkchart466 .legend').css('fontSize', '9pt');
			$('#jkchart466 .legend table').css('position', 'static');
			$('#jkchart466 .legend table').appendTo('#jkchart466 .legend > div');
			$('#jkchart466 .legend > div').css('width', 'auto');
			$('#jkchart466 .legend > div').css('height', 'auto');
			rasterizeHTML.drawHTML($('#jkchart466').html(), canvas[0]);
			canvas[0].getContext('2d').drawImage($('.flot-base')[0], 0, 0);
			canvas.appendTo('#com_jkjocommodity');

		}


	});
})(jQuery);

function updatelist(field, data) {
	var j = jQuery.noConflict();
	var obj = j('option', field).eq(0);
	field.empty();
	obj.appendTo(field);
	j.each(data, function(key, item) {
		var option = obj.clone();
		option.val(item.id);
		option.html(item.name);
		option.appendTo(field);
	});
	field.val('');
}

Joomla.submitbutton = function(task) {
	/*
	if (task == '') {
		return false;
	} else {
		var isValid = true;
		var action = task.split('.');
		if (action[1] != 'cancel' && action[1] != 'close') {
			var forms = $$('form.form-validate');
			for (var i = 0; i < forms.length; i++) {
				if (!document.formvalidator.isValid(forms[i])) {
					isValid = false;
					break;
				}
			}
		}

		if (isValid) {
			Joomla.submitform(task);
			return true;
		} else {
			alert(Joomla.JText._('COM_HELLOWORLD_HELLOWORLD_ERROR_UNACCEPTABLE',
					'Some values are unacceptable'));
			return false;
		}
	}
	*/
	Joomla.submitform(task);
	return true;
}