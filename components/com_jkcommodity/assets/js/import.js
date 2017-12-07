jQuery.noConflict();
(function($) { 
	$(function() {
		$('.item-delete').click(function(){
			if(confirm(Joomla.JText._('COM_JKCOMMODITY_CONFIRM_ITEM_DELETE'))) {
				$('input[type=checkbox]', $(this).closest('tbody')).removeAttr('checked');
				$('#item_id').val($('input[type=checkbox]', $(this).closest('tr')).val());
				$('#task').val('import.delete');
			} else {
				return false;
			}
		});
		$('#delete-items').click(function(){
			if(confirm(Joomla.JText._('COM_JKCOMMODITY_CONFIRM_ITEM_DELETE'))) {
				$('#item_id').val('');
				$('#task').val('import.delete');
			} else {
				return false;
			}
		});
	});	
})(jQuery);