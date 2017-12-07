<?php
defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldRegency extends JFormFieldList
{
	protected $type = 'Regency';
	
	protected function getOptions() {
		// Get Feeder ID
		$input		= JFactory::getApplication()->input;
		$province_id	= $this->form->getValue('province_id', 0);

		$this->value = is_numeric($this->value) ? array($this->value) : JArrayHelper::fromObject($this->value);
		$this->value = $this->value ? $this->value : array(0);
		$this->value = $this->multiple ? $this->value : reset($this->value);

		// DB Objects
		$db			= JFactory::getDBO();
		$query		= $db->getQuery(true);
		$user		= JFactory::getUser();

		// Queries
		$query->select($db->quoteName(array('a.id', 'a.name', 'a.type')));
		$query->from($db->quoteName('#__gtpihps_regencies', 'a'));
		$query->where($db->quoteName('a.province_id') . ' = ' .intval($province_id));
		$query->where($db->quoteName('a.published') . ' = 1');
		
		if(!$user->authorise('core.admin')){
			$province_list = GTHelperPrivilege::getPrivileges();
			if(!empty($province_list)){
				$query->where('province_id IN ('.$province_list.')');
			}
			else{
				$query->where('province_id = 0');
			}
		}
		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;
		$db->setQuery($query);
		$items 		= $db->loadObjectlist();
		foreach ($items as &$item) {
			$item->name = sprintf(JText::_('COM_GTPIHPS_'.strtoupper($item->type)), trim($item->name));
		}

		$options	= array();
		
		if ($items) {
			foreach ($items as $item) {
				$options[] = JHtml::_('select.option', $item->id, $item->name);
			}
		}
		
		// Merge any additional options in the XML definition.
		$options	= array_merge(parent::getOptions(), $options);

		return $options;
	}
}
?>
