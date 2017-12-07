<?php

/**
 * @package		GT Component 
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSViewIntegration_Logs extends GTView {

	protected $items;
	protected $pagination;
	protected $state;

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	function display($tpl = null) {
		
		// Get model data.
		$this->items 			= $this->get('Items');
		$this->pagination		= $this->get('Pagination');
		$this->state 			= $this->get('State');
		$this->provinceOptions	= $this->get('ProvinceOptions');
		$this->integrationUrl	= JURI::root(true).'?option=com_gtpihps&task=integrations.manual&province_id={province_id}&date={date}';
		$this->ordering			= $this->escape($this->state->get('list.ordering'));
		$this->direction		= $this->escape($this->state->get('list.direction'));
		$this->statusLogOptions	= array(
			'0' => JText::_('COM_GTPIHPS_SELECT_STATUS_LOG'),
			'success' => JText::_('COM_GTPIHPS_OPTION_STATUS_LOG_SUCCESS'),
			'warning' => JText::_('COM_GTPIHPS_OPTION_STATUS_LOG_WARNING'),
			'danger' => JText::_('COM_GTPIHPS_OPTION_STATUS_LOG_DANGER'),
		);

		parent::display($tpl);
	}

}
