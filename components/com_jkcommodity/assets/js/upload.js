jQuery.noConflict();
(function($) { 
	$(function() {
		$('.item-delete').click(function(){
			if(confirm(Joomla.JText._('COM_JKCOMMODITY_CONFIRM_ITEM_DELETE'))) {
				$('input[type=checkbox]', $(this).closest('tbody')).removeAttr('checked');
				$('#item_id').val($('input[type=checkbox]', $(this).closest('tr')).val());
				$('#task').val('upload.delete');
			} else {
				return false;
			}
		});
		
		$('.item-download').click(function(){
			$('input[type=checkbox]', $(this).closest('tbody')).removeAttr('checked');
			$('#item_id').val($('input[type=checkbox]', $(this).closest('tr')).val());
			$('#task').val('upload.download');
		});
		
		$('#delete-items').click(function(){
			if(confirm(Joomla.JText._('COM_JKCOMMODITY_CONFIRM_ITEM_DELETE'))) {
				$('#item_id').val('');
				$('#task').val('upload.delete');
			} else {
				return false;
			}
		});
		
		$('#download-items').click(function(){
			$('#item_id').val('');
			$('#task').val('upload.download');
		});
	});	
})(jQuery);