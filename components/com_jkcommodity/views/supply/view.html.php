<?php

/**
 * @package		JK 
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityViewSupply extends JKView {

	var $item;
	var $form;
	var $state;
	var $canDo;
	var $params;
	var $buttons;
	var $item_title;

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	public function display($tpl = null) {
		// Get model data.
		$this->item			= $this->get('Item');
		$this->item_detail	= $this->get('ItemDetail');
		$this->item_trades	= $this->get('ItemTrades');
		$this->form			= $this->get('Form');
		$this->state		= $this->get('State');
		$this->commodities	= $this->get('Commodity');
		$this->cities		= $this->get('Cities');
		$this->provinces	= $this->get('Provinces');
		
		// Get Last Supplies
		$this->jinput->set('date', $this->item->date);
		$this->jinput->set('city_id', $this->item->city_id);
		$this->last_supplies = $this->get('LastSupplies');
		
		// Set Commodity List
		$categories = $this->get('Category');
		$categories_el = array();
		foreach($categories as $item) {
			$categories_el[intval($item->parent_id)][$item->id] = $item;
		}
		$commodities_el = array();
		$category_ids = array();
		foreach($this->commodities as $item) {
			$commodities_el[intval($item->category_id)][$item->id] = $item;
			$category_ids[] = $item->category_id;
		}
		$selected_category = array();
		foreach(array_unique($category_ids) as $category_id) {
			$category = $categories[$category_id];
			$selected_category[] = $category_id;
			$selected_category[] = $category->parent_id;
			while(isset($categories[$category->parent_id])) {
				$category = $categories[$category->parent_id];
				$selected_category[] = $category->parent_id;
			}
		}
		$selected_category = array_filter(array_unique($selected_category));
		$selected_category = $selected_category ? $selected_category : array(0);
		$this->commodity_list = JKHelperDocument::prepareCommodity($categories_el[0], $categories_el, $commodities_el, 0, NULL, $selected_category);
				
		// Set page title
		$this->item_title = isset($this->item->id) && $this->item->id ? JText::_('Edit') : JText::_('JNEW');
		JKHelperDocument::setTitle($this->page_title . ' - ' . $this->item_title);
		$this->document->addScript( JK_JS . '/script_supply.js' );

		// Assign additional data
		if (isset($this->item->id) && $this->item->id) {
			$this->canDo = JKHelper::getActions($this->item->id, $this->getName());
		} else {
			$this->canDo = JKHelper::getActions();
		}

		// Add pathway
		$pathway = $this->app->getPathway();
		$pathway->addItem($this->item_title);

		$this->params = $this->state->params;
		$this->buttons = $this->makeButtons();
		
		// Check permission and display
		$created_by = isset($this->item->created_by) ? $this->item->created_by : 0;
		JKHelper::checkPermission($this->canDo, $created_by);
		parent::display($tpl);
	}

	/**
	 * Add the page buttons.
	 *
	 * @return	string
	 */
	protected function makeButtons() {
		$buttons = array();
		$isNew = intval((isset($this->item->id) && $this->item->id > 0) == 0);
		$canCreate = $this->canDo->get('core.create');
		$canEdit = $this->canDo->get('core.edit') || ($this->canDo->get('core.edit.own') && $this->item->created_by == $this->user->id);
		$canEditState = $this->canDo->get('core.edit.state');

		// Build the actions for new and existing records.
		if ($isNew) {
			// For new records, check the create permission.
			if ($canCreate) {
				$buttons[] = JKHelper::makeButton('apply', 'supply.apply');
				$buttons[] = JKHelper::makeButton('save', 'supply.save');
				$buttons[] = JKHelper::makeButton('save2new', 'supply.save2new');
			}
			$buttons[] = JKHelper::makeButton('cancel', 'supply.cancel');
		} else {
			// Since it's an existing record, check the edit permission, or fall back to edit own if the owner.
			if ($canEdit) {
				$buttons[] = JKHelper::makeButton('apply', 'supply.apply');
				$buttons[] = JKHelper::makeButton('save', 'supply.save');
				
				// We can save this record, but check the create permission to see if we can return to make a new one.
				if ($canCreate) {
					$buttons[] = JKHelper::makeButton('save2new', 'supply.save2new');
				}
			}

			// If checked out, we can still save
			$buttons[] = JKHelper::makeButton('cancel', 'supply.cancel');
			if ($canEditState) {
				$buttons[] = JKHelper::makeButton('trash', 'supplies.trash', true);
			}
			
		}

		return implode('', $buttons);
	}

}
