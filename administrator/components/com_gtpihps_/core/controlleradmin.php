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

jimport('joomla.application.component.controlleradmin');

class GTControllerAdmin extends JControllerAdmin
{
	
	protected $app;
	protected $user;
	protected $context;
	protected $contextAction;
	protected $menu;
	
	public function __construct($config = array()) {
		parent::__construct($config);
		
		// Guess the context as the suffix, eg: OptionControllerContent.
		if (empty($this->context)) {
			$r = null;
			if (!preg_match('/(.*)Controller(.*)/i', get_class($this), $r)) {
				throw new Exception(JText::_('JLIB_APPLICATION_ERROR_CONTROLLER_GET_NAME'), 500);
			}
			$this->context = strtolower($r[2]);
		}
		
		// Set variables
		$this->app		= JFactory::getApplication();
		$this->user		= JFactory::getUser();
		$this->menu		= $this->app->getMenu()->getActive();

		$layout			= $this->app->getUserStateFromRequest($this->context . '.layout', 'layout');
		$this->context2	= implode('.', array($this->option, $layout, $this->context));
	}
	
	function display($cachable = false, $urlparams = false) {
		parent::display($cachable, $urlparams);
	}
}
