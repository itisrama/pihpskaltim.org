<?php

/**
 * @package		JK 
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityViewPrices extends JKView {

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
		$this->markets		= $this->get('Market');

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
			$buttons[] = JKHelper::makeButton('addNew', 'price.add');
		}

		if ($canEdit) {
			$buttons[] = JKHelper::makeButton('editList', 'price.edit');
		}

		if ($canEditState) {
			$buttons[] = JKHelper::makeButton('publish', 'prices.publish');
			$buttons[] = JKHelper::makeButton('unpublish', 'prices.unpublish');
			$buttons[] = JKHelper::makeButton('archiveList', 'prices.archive');
			$buttons[] = JKHelper::makeButton('checkin', 'prices.checkin');
		}

		if ($canDelete) {
			$buttons[] = JKHelper::makeButton('deleteList', 'prices.delete');
		}
		elseif ($canEditState) {
			$buttons[] = JKHelper::makeButton('trash', 'prices.trash');
		}

		return implode('', $buttons);
	}

}
