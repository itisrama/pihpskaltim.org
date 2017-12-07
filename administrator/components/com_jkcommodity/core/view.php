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

jimport('joomla.application.component.view');

class JKView extends JViewLegacy
{
	var $app;
	var $user;
	var $jinput;
	var $document;
	var $params;
	var $page_title;
	var $canDo; // NEW


	public function __construct($config = array()) {
		parent::__construct($config);

		// Set variables
		$this->app		= JFactory::getApplication();
		$this->user		= JFactory::getUser();
		$this->document	= JFactory::getDocument();
		$this->jinput	= $this->app->input;
		$this->params	= $this->app->isSite() ? $this->app->getParams() : NULL;

		// Privilege NEW
		$this->canDo		= JKHelperAccess::getActions();
		$this->canCreate	= $this->canDo->get('core.create');
		$this->canEdit		= $this->canDo->get('core.edit') || $this->canDo->get('core.edit.own');
		$this->canEditOwn 	= $this->canDo->get('core.edit.own');
		$this->canEditState	= $this->canDo->get('core.edit.state');
		$this->canDelete	= $this->canDo->get('core.delete');
		$this->isAdmin 		= $this->user->authorise('core.admin', 'com_jkcommodity');

		// Set Title
		if($this->app->isSite()) {
			$this->page_title = $this->params->get('show_page_heading', 1) && $this->params->get('page_heading') ? $this->params->get('page_heading') : $this->document->getTitle();
			JKHelperDocument::setTitle($this->page_title);
		}
	}
}
