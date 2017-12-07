jQuery.noConflict();
(function($) {
	$(document).ready(function (){
		var no_dokumen1 = $('.doc_template .no_dokumen');
		var no_dokumen2 = $('#jform_no_dokumen');
		var tgl_proses1 = $('.doc_template .tgl_proses .form-control');
		var tgl_proses2 = $('#jform_tgl_proses');
		var text 		= $('.doc_template .textarea, .doc_template .text');
		var date 		= $('.doc_template .date .form-control');

		no_dokumen1.keyup(function(){
			no_dokumen2.val($(this).val());
		});
		no_dokumen2.keyup(function(){
			no_dokumen1.val($(this).val());
		});

		tgl_proses1.change(function(){
			tgl_proses2.val($(this).val());
		});
		tgl_proses2.change(function(){
			tgl_proses1.val($(this).val());
		});

		text.keyup(function(){
			var lastClass = $(this).attr('class').replace(' invalid', '');
			var lastClass = $(this).attr('class').replace(' required', '');
			var lastClass = lastClass.split(' ').pop();
			if (!/text/i.test(lastClass) && !/input/i.test(lastClass)) {
				$('.doc_template .'+lastClass).val($(this).val());
			}
		});

		date.change(function(){
			var lastClass = $(this).parent().attr('class').replace(' invalid', '');
			var lastClass = $(this).parent().attr('class').replace(' required', '');
			var lastClass = lastClass.split(' ').pop();
			if (!/date/i.test(lastClass) && !/input/i.test(lastClass)) {
				$('.doc_template .'+lastClass+' .form-control').val($(this).val());
			}
		});
	});

	
})(jQuery);