<?php
defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldRegion extends JFormFieldList{

	protected $type = 'Region';
	
	protected function getOptions() {
		// Get Feeder ID
		$input		= JFactory::getApplication()->input;
		$province_id	= $this->form->getValue('province_id', 0);

		$this->value = is_numeric($this->value) ? array($this->value) : JArrayHelper::fromObject($this->value);
		$this->value = $this->value ? $this->value : array(0);

		// DB Objects
		$db			= JFactory::getDBO();
		$query		= $db->getQuery(true);

		// Queries
		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihps_regions', 'a'));
		if(!empty($province_id)) $query->where($db->quoteName('a.province_id') . ' = ' .intval($province_id));
		$query->where($db->quoteName('a.published') . ' = 1');
		
		if(!JFactory::getUser()->authorise('core.admin')){
			$region_list = GTHelperPrivilege::getPrivileges();
			if(!empty($region_list)){
				$query->where('a.id IN ('.$region_list.')');
			}
			else{
				$query->where('a.id = 0');
			}
		}
		
		$db->setQuery($query);
		$items 		= $db->loadObjectlist();
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
