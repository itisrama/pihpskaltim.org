<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

defined('_JEXEC') or die;

class GTPIHPSViewPrice extends GTView {

	var $item;
	var $itemView;
	var $form;
	var $state;
	var $canDo;
	var $params;
	var $buttons;
	var $item_title;

	public function ___construct($config = array()) {
		parent::__construct($config);
	}

	public function display($tpl = null) {
		// Get model data.
		$this->state		= $this->get('State');
		$this->params		= $this->state->params;
		
		$layout 			= $this->getLayout();
		switch($layout) {
			case 'view':
				$this->item 	= $this->get('ItemView');
				break;
			default:
				$this->item		= $this->get('Item');
				break;
		}
		
		$this->region 			= $this->get('Region');
		$this->details 			= $this->get('ItemDetails');
		$this->form				= $this->get('Form');
		$this->commodityList	= $this->get('CommodityList');

		$this->isNew			= intval((isset($this->item->id) && $this->item->id > 0) == 0);
		$this->isTrashed		= $this->item->published == -2;
		$this->checkedOut		= $this->isNew ? 0 : isset($this->item->checked_out) && (!($this->item->checked_out == 0 || $this->item->checked_out == $this->user->id));
		
		// Load Script
		$this->document->addScript(GT_GLOBAL_JS . '/price.js');
		
		// Set page title
		if($layout == 'edit') {
			$this->page_title = $this->isNew ? JText::_('COM_GTPIHPS_PT_NEW') : JText::_('COM_GTPIHPS_PT_EDIT');
			$this->page_title = str_replace('%s', JText::_('COM_GTPIHPS_PT_PRICE'), $this->page_title);
		} else {
			$this->page_title = JText::_('COM_GTPIHPS_PT_PRICE');
		}

		$this->page_title .= ' <small>' . $this->region->name . '</small>';
		GTHelperHTML::setTitle($this->page_title);

		// Assign additional data
		if (isset($this->item->id) && $this->item->id) {
			$this->canDo = GTHelperAccess::getActions($this->item->id, $this->getName());
		} else {
			$this->canDo = GTHelperAccess::getActions();
		}

		// Add pathway
		$pathway	= $this->app->getPathway();
		$pathway->addItem($this->item_title);
		
		// Check permission and display
		$created_by	= isset($this->item->created_by) ? $this->item->created_by : 0;
		GTHelperAccess::checkPermission($this->canDo, $created_by);

		parent::display($tpl);
	}

}
