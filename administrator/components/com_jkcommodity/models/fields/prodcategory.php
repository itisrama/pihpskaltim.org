<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldProdCategory extends JFormFieldList {

	public $type = 'prodcategory';
	
	protected function getOptions()
	{
		// Initialize variables.
		$options = array();
		$query = "
			SELECT `id`, `name` 
			FROM #__jkcommodity_category 
			WHERE published = '1'
			AND `type` = 'producer'
		";

		// Get the database object.
		$db = JFactory::getDBO();

		// Set the query and get the result list.
		$db->setQuery($query);
		$items = $db->loadObjectlist();

		// Check for an error.
		if ($db->getErrorNum())
		{
			JError::raiseWarning(500, $db->getErrorMsg());
			return $options;
		}
				
		// Build the field options.
		if (!empty($items))
		{
			foreach ($items as $item)
			{
				$options[] = JHtml::_('select.option', $item->id, $item->name);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

}