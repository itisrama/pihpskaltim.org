<?php
/**
 * @package		Joomlaku Component
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

jimport('joomla.application.component.controller');

class JKController extends JControllerLegacy
{
	protected $app;
	protected $user;
	protected $jinput;

	public function __construct($config = array()) {
	    $document = new JKHelperDocument();
		$document->loadHeaders();
		JKHelperCurrency::loadScripts(); // NEW
		parent::__construct($config);

		// Set variables
		$this->app		= JFactory::getApplication();
		$this->user		= JFactory::getUser();
		$this->jinput	= $this->app->input;
	}

	function display($cachable = false, $urlparams = false)
	{
		$view   = $this->input->get('view', 'banners');
		$layout = $this->input->get('layout', 'default');
		$id     = $this->input->getInt('id');

		// Check for edit form.
		if ($layout == 'edit' && !$this->checkEditId('com_jkcommodity.edit.'.$view, $id)) {

			// Somehow the person just went to the form - we don't allow that.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_UNHELD_ID', $id));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect(JRoute::_('index.php?option=com_jkcommodity&view='.$view, false));

			return false;
		}
		
		parent::display($cachable, $urlparams);
		
		
	}
}
