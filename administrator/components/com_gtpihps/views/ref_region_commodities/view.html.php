<?php

/**
 * @package		GT JSON
 * @author		Herwin Pradana
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2014 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSViewRef_Region_Commodities extends GTView {

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
		
		GTHelper::addSubmenu('ref_region_commodities');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}

		$this->addToolbar();
		$this->sidebar = JHtmlSidebar::render();
		
		parent::display($tpl);
	}

	protected function addToolbar() {
		// Get the toolbar object instance
		$bar = JToolBar::getInstance('toolbar');

		JToolbarHelper::title(JText::_('COM_GTPIHPS_PT_REGION_COMMODITY'), 'list menu');

		if ($this->canEdit)
		{
			JToolbarHelper::editList('ref_region_commodity.edit');
		}


		if ($this->isAdmin)
		{
			JToolbarHelper::preferences('com_gtpihps');
		}

		JToolbarHelper::help('JHELP_COMPONENTS_CONTACTS_CONTACTS');

		JHtmlSidebar::setAction('index.php?option=com_gtpihps');

		JHtmlSidebar::addFilter(
			JText::_('JOPTION_SELECT_PUBLISHED'),
			'filter_published',
			JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true)
		);
	}

	protected function getSortFields() {
		return array(
			'a.id'=> JText::_('JGRID_HEADING_ID'), 
			'a.name'=> JText::_('COM_GTPIHPS_FIELD_NAME'), 
			'b.name'=> JText::_('COM_GTPIHPS_FIELD_PROVINCE'), 
			'a.type'=> JText::_('COM_GTPIHPS_FIELD_TYPE')
		);
	}
}
