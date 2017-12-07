<?php

/**
 * @package		GT Component 
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSViewPrices extends GTView {

	protected $items;
	protected $pagination;
	protected $state;

	public function __construct($config = array()) {
		parent::__construct($config);
	}

	function display($tpl = null) {
		// Load Script
		$this->document->addScript(GT_GLOBAL_JS . '/prices.js');

		// Get model data.
		$this->items			= $this->get('Items');
		$this->pagination		= $this->get('Pagination');
		$this->state			= $this->get('State');
		$this->provinceOptions	= $this->get('ProvinceOptions');
		$this->regencyOptions	= $this->get('RegencyOptions');
		$this->marketOptions	= $this->get('MarketOptions');
		$this->ordering			= $this->escape($this->state->get('list.ordering'));
		$this->direction		= $this->escape($this->state->get('list.direction'));
		
		$this->editUrl		= 'index.php?option=com_gtpihps&task=price.edit&id=%d';

		parent::display($tpl);
	}

}
