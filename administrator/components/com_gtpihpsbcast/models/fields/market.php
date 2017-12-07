<?php
defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldMarket extends JFormFieldList
{
	
	protected $type = 'Market';
	
	protected function getOptions() {
		// DB Objects
		$cities = $this->getCities();
		$markets = $this->getMarkets();
		$options = array();
		foreach ($cities as $city) {
			$options[] = JHtml::_('select.option', '<OPTGROUP>', $city->name);
			if(isset($markets[$city->id])) {
				foreach ($markets[$city->id] as $market) {
					$options[] = JHtml::_('select.option', $market->id, $market->name);
				}
			}
			$options[] = JHtml::_('select.option', '</OPTGROUP>', null);
		}
		
		// Merge any additional options in the XML definition.
		$options	= array_merge(parent::getOptions(), $options);

		return $options;
	}

	protected function getMarkets() {
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.regency_id', 'a.name')));
		$query->from($db->quoteName('#__gtpihps_markets', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->order($db->quoteName('a.id'));
		
		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->regency_id][$item->id] = $item;
		}

		return $data;
	}

	public function getCities() {
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.name')));
		$query->from($db->quoteName('#__gtpihps_regencies', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->order($db->quoteName('a.id'));
		
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
?>
