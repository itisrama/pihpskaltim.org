<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldCommodity extends JFormFieldList {

	public $type = 'commodity';
	
	protected function getOptions()
	{
		$this->value = is_numeric($this->value) ? array($this->value) : JArrayHelper::fromObject($this->value);
		$this->value = $this->value ? $this->value : array(0);

		// DB Objects
		$db			= JFactory::getDBO();
		$query		= $db->getQuery(true);

		// Queries
		$query->select($db->quoteName(array('a.id', 'a.name', 'a.key')));
		$query->from($db->quoteName('#__jkcommodity_commodity', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.type') . ' = '.$db->quote('consumer'));
		
		$db->setQuery($query);
		$items 		= $db->loadObjectlist();
		$options	= array();

		// Check for an error.
		if ($db->getErrorNum()){
			JError::raiseWarning(500, $db->getErrorMsg());
			return $options;
		}
				
		// Build the field options.
		if (!empty($items)){
			foreach ($items as $item){
			    $item->name = (!empty($item->key))? $item->name.' - '.$item->key : $item->name;
				$options[] = JHtml::_('select.option', $item->id, $item->name);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

}
