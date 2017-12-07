<?php

/**
 * @package		GT JSON
 * @author		Herwin Pradana
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2014 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityViewRef_Categories extends JKView {

	protected $items;
	protected $pagination;
	protected $state;

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	function display($tpl = null) {
		// Get model data.
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		
		JKHelper::addSubmenu('ref_categories');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		foreach ($this->items as &$item)
		{
			$item->order_up = true;
			$item->order_dn = true;
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		
		parent::display($tpl);
	}

	protected function addToolbar() {
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_JKCOMMODITY_PT_CATEGORY'), 'list menu');

		if ($this->canCreate)
		{
			JToolbarHelper::addNew('ref_category.add');
		}

		if ($this->canEdit)
		{
			JToolbarHelper::editList('ref_category.edit');
		}

		if ($this->canEditState)
		{
			JToolbarHelper::publish('ref_categories.publish', 'JTOOLBAR_PUBLISH', true);
			JToolbarHelper::unpublish('ref_categories.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			JToolbarHelper::archiveList('ref_categories.archive');
			JToolbarHelper::checkin('ref_categories.checkin');
		}

		if ($this->state->get('filter.published') == -2 && $this->canDelete)
		{
			JToolbarHelper::deleteList('', 'ref_categories.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canEditState)
		{
			JToolbarHelper::trash('ref_categories.trash');
		}

		if ($this->isAdmin)
		{
			JToolbarHelper::preferences('com_jkcommodity');
		}

		JToolbarHelper::help('JHELP_COMPONENTS_CONTACTS_CONTACTS');

		JHtmlSidebar::setAction('index.php?option=com_jkcommodity');

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
		);
	}

	protected function getSortFields() {
		return array(
			'a.id'=> JText::_('JGRID_HEADING_ID'), 
			'a.name'=> JText::_('COM_JKCOMMODITY_FIELD_NAME'), 
			'b.name'=> JText::_('COM_JKCOMMODITY_FIELD_PARENT'), 
		);
	}
}
