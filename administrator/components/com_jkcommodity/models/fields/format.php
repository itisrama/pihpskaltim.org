<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die('Restricted access');

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldFormat extends JFormFieldList {

	public $type = 'Format';
	
	protected function getOptions(){
		$this->value = is_numeric($this->value) ? array($this->value) : JArrayHelper::fromObject($this->value);
		$this->value = $this->value ? $this->value : array(0);

		// DB Objects
		$db			= JFactory::getDBO();
		$query		= $db->getQuery(true);

		// Queries
		$query->select($db->quoteName(array('b.id', 'b.name')));
		$query->from($db->quoteName('#__jkcommodity_city', 'a'));
		$query->join('INNER', $db->quoteName('#__jkcommodity_excel_format', 'b').' ON '.
			$db->quoteName('a.id').' = '.$db->quoteName('b.city_id')
		);
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.type') . ' = '.$db->quote('consumer'));
		$query->order($db->quoteName('a.id'));

		if(!JKHelperAccess::isAdmin()){
    		$city_list = JKHelperPrivilege::getPrivileges();
			$query->where($db->quoteName('a.id').' IN ('.$city_list.')');
		}
		
		$query->order($db->quoteName('key'));
		$query->order($db->quoteName('id'));
		//echo nl2br(str_replace('#__','tpid_',$query));
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
				$options[] = JHtml::_('select.option', $item->id, $item->name);
			}
		}

		// Merge any additional options in the XML definition.
		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}

}
