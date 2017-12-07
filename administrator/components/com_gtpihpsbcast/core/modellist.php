<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

class GTModelList extends JModelList {

	var $app;
	var $user;
	var $input;

	public function __construct($config = array()) {
		parent::__construct($config);

		// Set variables
		$this->app		= JFactory::getApplication();
		$this->user		= JFactory::getUser();
		$this->input	= $this->app->input;

		// Add table path
		$this->addTablePath(GT_TABLES);
	}

	protected function populateState($ordering = null, $direction = null) {
		parent::populateState($ordering, $direction);
		// Pre-fill the limits
		$app = JFactory::getApplication();
		$limit = $app->getUserStateFromRequest('global.list.limit', 'limit', $app->getCfg('list_limit'), 'uint');
		if($limit == 0 || $limit > 25) {
			$this->setState('list.limit', 25);
		}

	}

	public function getItems($table = false) {
		if(!$table) {
			return parent::getItems();
		}

		$items = parent::getItems();
		$table = $this->getTable();

		foreach ($items as $k => $item) {
			$table->bind(JArrayHelper::fromObject($item));
			$items[$k] = JArrayHelper::toObject($table->getProperties(1)); 
		}
		return $items;
	}

}