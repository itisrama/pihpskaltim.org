<?php
/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

jimport('joomla.application.component.view');

class GTView extends JViewLegacy
{
	var $app;
	var $user;
	var $input;
	var $document;
	var $params;
	var $page_title;
	var $canDo;


	public function __construct($config = array()) {
		parent::__construct($config);

		// Set variables
		$this->app			= JFactory::getApplication();
		$this->user			= JFactory::getUser();
		$this->document		= JFactory::getDocument();
		$this->input		= $this->app->input;
		$this->params		= $this->app->isSite() ? $this->app->getParams() : NULL;

		// Privilege
		$this->canDo		= GTHelperAccess::getActions();
		$this->canCreate	= $this->canDo->get('core.create');
		$this->canEdit		= $this->canDo->get('core.edit') || $this->canDo->get('core.edit.own');
		$this->canEditState	= $this->canDo->get('core.edit.state');
		$this->canDelete	= $this->canDo->get('core.delete');
		
		// Set Title
		if($this->app->isSite()) {
			$this->page_title = $this->params->get('show_page_heading', 1) && $this->params->get('page_heading') ? $this->params->get('page_heading') : $this->document->getTitle();
			GTHelperHTML::setTitle($this->page_title);
		}
	}
}