<?php
defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldCommodity extends JFormFieldList
{
	
	protected $type = 'Commodity';
	
	protected function getOptions() {
		// DB Objects
		$commodities	= $this->getCommodities();
		$categories		= $this->getCategories();

		$items 			= GTHelperHtml::setCommodities($categories[0], $categories, $commodities);
		$options	= array();
		
		if ($items) {
			foreach ($items as $item) {
				$options[] = JHtml::_('select.option', $item->value, $item->text);
			}
		}
		
		// Merge any additional options in the XML definition.
		$options	= array_merge(parent::getOptions(), $options);

		return $options;
	}

	protected function getCommodities() {
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.category_id')));
		$query->select('CONCAT('.$db->quoteName('a.name').', " (",'.$db->quoteName('a.denomination').', ")") name');
		$query->from($db->quoteName('#__gtpihps_commodities', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->where($db->quoteName('a.short_name') . ' IS NOT NULL');
		$query->order($db->quoteName('a.id'));
		
		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->category_id][$item->id] = $item->name;
		}

		return $data;
	}

	public function getCategories() {
		// Get a db connection.
		$db = JFactory::getDBO();

		// Create a new query object.
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.id', 'a.parent_id', 'a.name')));
		$query->from($db->quoteName('#__gtpihps_categories', 'a'));
		
		$query->where($db->quoteName('a.published') . ' = 1');
		$query->order($db->quoteName('a.id'));
		
		$db->setQuery($query);
		$raw = $db->loadObjectList();
		$data = array();
		foreach ($raw as $item) {
			$data[$item->parent_id][$item->id] = $item->name;
		}
		return $data;
	}
}
?>
