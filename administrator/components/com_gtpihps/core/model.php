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

jimport('joomla.application.component.model');

class GTModel extends JModelLegacy {

	protected $app;
	protected $user;
	protected $input;

	public function __construct($config = array()) {
		parent::__construct($config);

		// Set variables
		$this->app		= JFactory::getApplication();
		$this->user		= JFactory::getUser();
		$this->input	= $this->app->input;

		// Add table path
		$this->addTablePath(GT_TABLES);
	}

	protected function populateState() {
		$offset = $this->input->get('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $this->app->getParams();
		$this->setState('params', $params);
	}

}