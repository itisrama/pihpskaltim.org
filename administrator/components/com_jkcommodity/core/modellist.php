<?php

/**
 * @package		Joomlaku Component
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

jimport('joomla.application.component.modellist');

class JKModelList extends JModelList {

	var $app;
	var $user;
	var $input;
	var $jinput;

	public function __construct($config = array()) {
		parent::__construct($config);

		// Set variables
		$this->app		= JFactory::getApplication();
		$this->user		= JFactory::getUser();
		$this->input	= $this->app->input;
		$this->jinput	= $this->app->input;

		// Add table path
		$this->addTablePath(JK_TABLES);
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

	public function getItems($table = true) {
		if(!$table) {
			return parent::getItems();
		}

		$items = parent::getItems();
		$table = $this->getTable();

		foreach ($items as $k => $item) {
			$table->bind(JArrayHelper::fromObject($item));
			$properties = $table->getProperties(1);
			$items[$k] = JArrayHelper::toObject($properties); 
		}
		return $items;
	}
}
