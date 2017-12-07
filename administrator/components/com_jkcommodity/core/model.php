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

jimport('joomla.application.component.model');

class JKModel extends JModelLegacy {

	protected $app;
	protected $user;
	protected $jinput;

	public function __construct($config = array()) {
		parent::__construct($config);

		// Set variables
		$this->app		= JFactory::getApplication();
		$this->user		= JFactory::getUser();
		$this->jinput	= $this->app->input;
	}

	protected function populateState() {
		$offset = $this->jinput->get('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $this->app->getParams();
		$this->setState('params', $params);
	}

}