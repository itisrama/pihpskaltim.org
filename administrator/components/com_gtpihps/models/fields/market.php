<?php
defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldMarket extends JFormFieldList
{
	
	protected $type = 'Market';
	
	protected function getOptions() {
		$app 		= JFactory::getApplication();
		// Get Feeder ID
		$input		= $app->input;
		$regency_id	= $this->form->getValue('regency_id', 0);

		$this->value = is_numeric($this->value) ? array($this->value) : JArrayHelper::fromObject($this->value);
		$this->value = $this->value ? $this->value : array(0);
		$this->value = $this->multiple ? $this->value : reset($this->value);

		// DB Objects
		$db			= JFactory::getDBO();
		$query		= $db->getQuery(true);
		$user		= JFactory::getUser();

		$menu 		= $app->getMenu()->getActive();

		// Queries
		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihps_markets', 'a'));
		$query->where($db->quoteName('a.regency_id') . ' = ' .intval($regency_id));
		$query->where($db->quoteName('a.published') . ' = 1');

		$price_type_id = $menu->params->get('price_type_id');
		if($price_type_id) {
			$query->where($db->quoteName('a.price_type_id') . ' = '.$db->quote($price_type_id));
		}
		
		
		if(!$user->authorise('core.admin')){
			$province_list = GTHelperPrivilege::getPrivileges();
			if(!empty($province_list)){
				$query->where('province_id IN ('.$province_list.')');
			}
			else{
				$query->where('province_id = 0');
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
