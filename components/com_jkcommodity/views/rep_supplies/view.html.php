<?php

/**
 * @package		JK 
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityViewRep_Supplies extends JKView {

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
		
		$this->cities		= $this->get('City');

		parent::display($tpl);
	}

}
