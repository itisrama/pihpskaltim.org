<?php
defined('_JEXEC') or die;

jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldCategories extends JFormFieldList
{
	
	protected $type = 'Categories';
	
	protected function getOptions() {
		$this->value = is_numeric($this->value) ? array($this->value) : JArrayHelper::fromObject($this->value);
		$this->value = $this->value ? $this->value : array(0);

		// DB Objects
		$db			= JFactory::getDBO();
		$query		= $db->getQuery(true);

		// Queries
		$query->select($db->quoteName(array('a.id', 'a.name', 'a.key')));
		$query->from($db->quoteName('#__jkcommodity_category', 'a'));
		$query->where($db->quoteName('a.published') . ' = 1');
		
		$db->setQuery($query);
		$items 		= $db->loadObjectlist();
		$options	= array();
		
		if ($items) {
			foreach ($items as $item) {
			    $item->name = (!empty($item->key))? $item->name.' - '.$item->key : $item->name;
				$options[] = JHtml::_('select.option', $item->id, $item->name);
			}
		}
		
		// Merge any additional options in the XML definition.
		$options	= array_merge(parent::getOptions(), $options);

		return $options;
	}


}
?>
