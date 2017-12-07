<?php
defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldSelectize extends JFormFieldList
{
	
	protected $type = 'Selectize';
	
	protected function getOptions() {
		$this->value = (array) $this->value;

		$db		= JFactory::getDBO();
		
		$id			= $this->id;
		$query		= (string) $this->element['query'];
		$task		= (string) $this->element['task'];
		$requests	= (string) $this->element['requests'];
		
		$db->setQuery(str_replace('%s', '"'.implode('","', $this->value).'"', $query));
		$items 		= $db->loadObjectlist();
		$options	= array();
		
		if ($items) {
			foreach ($items as $item) {
				$options[] = JHtml::_('select.option', $item->id, $item->name);
			}
		}
		
		// Merge any additional options in the XML definition.
		$options	= array_merge(parent::getOptions(), $options);
		
		// Load JSs
		$document	= JFactory::getDocument();
		$document->addScript(GT_ADMIN_JS . '/selectize.min.js');
		$document->addStylesheet(GT_ADMIN_CSS . '/selectize.bootstrap3.css');;
		
		$component_url = GT_GLOBAL_COMPONENT;

		$script		= "
			(function ($){
				$(document).ready(function (){
					$('#$id').selectize({
						persist: false,
						valueField: 'id',
						labelField: 'name',
						searchField: 'name',
						sortField: 'name',
						create: false,
						preload: true,
						load: function(query, callback) {
							data = $requests;
							data.search = query;
							data.task = '$task';
							$.ajax({
								url: '$component_url',
								data: data,
								type: 'GET',
								error: function() {
									callback();
								},
								success: function(result) {
									callback($.parseJSON(result));
								}
							});
						},
					});
				});
			})(jQuery);
		";
		$document->addScriptDeclaration($script);

		return $options;
	}


}
?>
