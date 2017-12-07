jQuery.noConflict();
(function($) {
	inputmask.extendDefinitions({
		'Q': {
			validator: "[-a-zA-Z0-9]",
			cardinality: 1,
			casing: "lower"
		}
	});
		
	$(document).ready(function(){
		$('#jform_pelapor_id_sipil_wn').prop('disabled', $('#jform_pelapor_is_wni0').prop('checked'));
		$('#jform_terlapor_id_sipil_wn').prop('disabled', $('#jform_terlapor_is_wni0').prop('checked'));
		$('#jform_korban_id_sipil_wn').prop('disabled', $('#jform_korban_is_wni0').prop('checked'));
		$('#jform_saksi_id_sipil_wn').prop('disabled', $('#jform_saksi_is_wni0').prop('checked'));
	});
	
	$(function() {
		$('#jform_pasal_kasus_pasal').addClass('inputmask');
		$('#jform_pasal_id_kasus_pasal_tipe').change(function() {
			var pasal = $('#jform_pasal_kasus_pasal').val();
			$('#jform_pasal_kasus_pasal').val('');
			switch($(this).val()) {
				case '1':
					$('#jform_pasal_kasus_pasal').removeAttr('readonly');
					$('#jform_pasal_kasus_pasal').inputmask({ mask: 'KUHP P\\as\\al Q{1,10}[ \\Ay\\at 9{1,4}]', greedy: false, clearIncomplete: true });
					if(pasal.indexOf('KUHP') > -1) {
						$('#jform_pasal_kasus_pasal').val(pasal);
					}
					break;
				case '2':
					$('#jform_pasal_kasus_pasal').removeAttr('readonly');
					$('#jform_pasal_kasus_pasal').inputmask({ mask: 'UU No. 9{1,4} T\\ahun 9{4}[ P\\as\\al Q{1,10}][ \\Ay\\at 9{1,4}]', greedy: false, clearIncomplete: true });
					if(pasal.indexOf('UU No.') > -1) {
						$('#jform_pasal_kasus_pasal').val(pasal);
					}
					break;
				case '3':
					$('#jform_pasal_kasus_pasal').removeAttr('readonly');
					$('#jform_pasal_kasus_pasal').inputmask({ mask: 'Perpu No. 9{1,4} T\\ahun 9{4}[ P\\as\\al Q{1,10}][ \\Ay\\at 9{1,4}]', greedy: false, clearIncomplete: true });
					if(pasal.indexOf('Perpu No.') > -1) {
						$('#jform_pasal_kasus_pasal').val(pasal);
					}
					break;
				case '4':
					$('#jform_pasal_kasus_pasal').removeAttr('readonly');
					$('#jform_pasal_kasus_pasal').inputmask({ mask: 'UU D\\arur\\at No. 9{1,4} T\\ahun 9{4}[ P\\as\\al Q{1,10}][ \\Ay\\at 9{1,4}]', greedy: false, clearIncomplete: true });
					if(pasal.indexOf('UU Darurat No.') > -1) {
						$('#jform_pasal_kasus_pasal').val(pasal);
					}
					break;
				default:
					$('#jform_pasal_kasus_pasal').inputmask('remove');
					$('#jform_pasal_kasus_pasal').attr('readonly');
					break;
			}
		});

		$('#jform_pelapor_is_wni input[type=radio]').change(function() {
			$('#jform_pelapor_id_sipil_wn').prop('disabled', $(this).val() == 1);
		});

		$('#jform_terlapor_is_wni input[type=radio]').change(function() {
			$('#jform_terlapor_id_sipil_wn').prop('disabled', $(this).val() == 1);
		});

		$('#jform_korban_is_wni input[type=radio]').change(function() {
			$('#jform_korban_id_sipil_wn').prop('disabled', $(this).val() == 1);
		});

		$('#jform_saksi_is_wni input[type=radio]').change(function() {
			$('#jform_saksi_id_sipil_wn').prop('disabled', $(this).val() == 1);
		});
	});

	
})(jQuery);
