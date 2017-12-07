<?php
defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldRegionFormat extends JFormFieldList
{
	protected $type = 'RegionFormat';
	
	protected function getOptions() {
		// Get Feeder ID
		$input		= JFactory::getApplication()->input;

		$this->value = is_numeric($this->value) ? array($this->value) : JArrayHelper::fromObject($this->value);
		$this->value = $this->value ? $this->value : array(0);

		// DB Objects
		$db			= JFactory::getDBO();
		$query		= $db->getQuery(true);

		// Queries
		$query->select('CONCAT('.$db->quoteName('a.region_id').',":",IF('.$db->quoteName('a.regency_id').' > 0, '.$db->quoteName('a.regency_id').', 0),":",'.$db->quoteName('a.id').') id');
		$query->from($db->quoteName('#__gtpihps_region_formats', 'a'));

		$query->select('IF( '. $db->quoteName('b.name') . 'IS NOT NULL, CONCAT('.$db->quoteName('c.name').'," - ",'.$db->quoteName('b.name').'), '.$db->quoteName('c.name').') name');
		$query->join('LEFT', $db->quoteName('#__gtpihps_regencies', 'b') . ' ON ' . $db->quoteName('a.regency_id') . ' = ' .$db->quoteName('b.id'));
		$query->join('LEFT', $db->quoteName('#__gtpihps_provinces', 'c') . ' ON ' . $db->quoteName('a.province_id') . ' = ' .$db->quoteName('c.id'));

		$query->where($db->quoteName('a.published') . ' = 1');

		
		if(!JFactory::getUser()->authorise('core.admin')){
			$region_list = GTHelperPrivilege::getPrivileges();
			if(!empty($region_list)){
				$query->where('a.region_id IN ('.$region_list.')');
			}
			else{
				$query->where('a.region_id IS NULL');
			}
		}

		$query->order($db->quoteName('a.province_id'));
		$query->order($db->quoteName('b.name') . 'IS NOT NULL');
		
		//echo nl2br(str_replace('#__','pihpsnas_',$query));
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
