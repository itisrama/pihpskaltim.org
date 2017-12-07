<?php

/**
 * @package		JK 
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityViewSupplies extends JKView {

	var $items;
	var $pagination;
	var $state;
	var $canDo;
	var $buttons;

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	function display($tpl = null) {
		// Get model data.
		$this->items		= $this->get('Items');
		$this->pagination	= $this->get('Pagination');
		$this->state		= $this->get('State');
		$this->canDo		= JKHelper::getActions();
		$this->buttons		= $this->makeButtons();
		
		$this->cities		= $this->get('City');

		parent::display($tpl);
	}

	/**
	 * Prepares the document
	 */
	protected function _prepareDocument() {

	}

	protected function makeButtons() {
		$buttons		= array();
		$canCreate		= $this->canDo->get('core.create');
		$canEdit		= $this->canDo->get('core.edit') || $this->canDo->get('core.edit.own');
		$canEditState	= $this->canDo->get('core.edit.state');
		$canDelete		= $this->state->get('filter.published') == -2 && $this->canDo->get('core.delete');

		if ($canCreate) {
			$buttons[] = JKHelper::makeButton('addNew', 'supply.add');
		}

		if ($canEdit) {
			$buttons[] = JKHelper::makeButton('editList', 'supply.edit');
		}

		if ($canEditState) {
			$buttons[] = JKHelper::makeButton('publish', 'supplies.publish');
			$buttons[] = JKHelper::makeButton('unpublish', 'supplies.unpublish');
			$buttons[] = JKHelper::makeButton('archiveList', 'supplies.archive');
			$buttons[] = JKHelper::makeButton('checkin', 'supplies.checkin');
		}

		if ($canDelete) {
			$buttons[] = JKHelper::makeButton('deleteList', 'supplies.delete');
		}
		elseif ($canEditState) {
			$buttons[] = JKHelper::makeButton('trash', 'supplies.trash');
		}

		return implode('', $buttons);
	}

}
