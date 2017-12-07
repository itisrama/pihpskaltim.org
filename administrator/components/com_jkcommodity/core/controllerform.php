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

jimport('joomla.application.component.controllerform');

class JKControllerForm extends JControllerForm {

	protected $app;
	protected $user;
	protected $input;
	//NEW
	protected $context2;
	protected $prevContext;


	public function __construct($config = array()) {
		parent::__construct($config);

		// Set variables
		$this->app		= JFactory::getApplication();
		$this->user		= JFactory::getUser();
		$this->input	= $this->app->input;
		
    	//NEW
		$layout				= $this->app->getUserStateFromRequest($this->context . '.layout', 'layout');
		$this->context2		= implode('.', array($this->option, $layout, $this->context));
		$this->prevContext	= $this->context;
	}

	function display($cachable = false, $urlparams = false) {
		parent::display($cachable, $urlparams);
	}

	function read($key = null, $urlVar = null) {
		// Initialise variables.
		$model = $this->getModel();
		$table = $model->getTable();
		$cid = $this->input->post->get('cid', array(), 'ARRAY');

		// Determine the name of the primary key for the data.
		if (empty($key)) {
			$key = $table->getKeyName();
		}

		// To avoid data collisions the urlVar may be different from the primary key.
		if (empty($urlVar)) {
			$urlVar = $key;
		}

		// Get the previous record id (if any) and the current record id.
		$recordId = (int) (count($cid) ? $cid[0] : $this->input->get($urlVar, FALSE, 'INT'));
		if ($recordId) {
			$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_item . '&id=' . $recordId, false
					)
			);
		} else {
			$this->setRedirect(
					JRoute::_(
						'index.php?option=' . $this->option . '&view=' . $this->view_list
						. $this->getRedirectToListAppend(), false
					)
			);
		}
	}

    /* OLD
	function back() {
		$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
		);
	}
	*/
	
	// NEW
	public function back($toItem = false) {
		$model		= $this->getModel();
		$table		= $model->getTable();
		$urlVar		= $table->getKeyName();
		$recordId	= $this->input->get('id');

		// set layout to view layout
		$this->input->set('layout', 'view');

		// check redirection
		if($toItem) {
			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_item
					. $this->getRedirectToItemAppend($recordId, $urlVar), false
				)
			);
		} else {
			$this->setRedirect(
				JRoute::_(
					'index.php?option=' . $this->option . '&view=' . $this->view_list
					. $this->getRedirectToListAppend(), false
				)
			);
		}
	}

	protected function allowEdit($data = array(), $key = 'id')
	{
		$model		= $this->getModel();
		$table		= $model->getTable();
		$key		= $table->getKeyName();
		//$id			= intval($this->jinput->get($key)); OLD
		$id         = $this->input->getInt($key);
		$item		= $model->getItem($id);
		$canEdit	= $this->user->authorise('core.edit', $this->option) || ($this->user->authorise('core.edit.own', $this->option) && $item->created_by == $this->user->id);
		return $canEdit;
	}

    // NEW
	public function saveExternal($redirect = true, $key = null, $urlVar = null) {
		$task		= $this->getTask();
		$task		= strtolower(str_replace('save', '', $task));
		
		$lang  		= JFactory::getLanguage();
		$model		= $this->getModel();
		$data		= $this->input->post->get('jform', array(), 'array');		
		$form		= $model->getFormExternal($task, $data);
		$table 		= $model->getTable($task);
		$checkin 	= property_exists($table, 'checked_out');
		
		$layout		= $this->app->getUserStateFromRequest($this->context . '.layout', 'layout');
		$context	= implode('.', array($this->option, $layout, $task));

		if (!$form) {
			$this->app->enqueueMessage($model->getError(), 'error');
			return false;
		}
		// Test whether the data is valid.
		$validData = $model->validate($form, $data);
		if ($validData === false) {
			$errors = $model->getErrors();
			// Push up to three validation messages out to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++) {
				if ($errors[$i] instanceof Exception) {
					$this->app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				} else {
					$this->app->enqueueMessage($errors[$i], 'warning');
				}
			}
			$this->app->setUserState($context . '.data', $data);
			return false;
		}

		// Attempt to save the data.
		$validData = JArrayHelper::toObject($validData);
		if (!$model->saveExternal($validData, $task)) {
			// Save the data in the session.
			$this->app->setUserState($context . '.data', $validData);

			// Redirect back to the edit screen.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			return false;
		}

		$validData = JArrayHelper::fromObject($validData);
		// Save succeeded, so check-in the record.
		if ($checkin && $model->checkin($validData[$key]) === false) {
			// Save the data in the session.
			$this->app->setUserState($context . '.data', $validData);

			// Check-in failed, so go back to the record and display a notice.
			$this->setError(JText::sprintf('JLIB_APPLICATION_ERROR_CHECKIN_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			return false;
		}

		// Unset data in the session
		$this->app->setUserState($context . '.data', null);
		
		$this->setMessage(
			JText::_(
				($lang->hasKey($this->text_prefix . ($recordId == 0 && $this->app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS')
					? $this->text_prefix
					: 'JLIB_APPLICATION') . ($recordId == 0 && $this->app->isSite() ? '_SUBMIT' : '') . '_SAVE_SUCCESS'
			)
		);

		// Redirect page if redirection enabled
		if($redirect) {
			$this->back(true);
		}

		return true;
	}

	public function getViewItem($urlQueries = array()) {
		foreach($urlQueries as $query) {
			$queryVal = $this->input->get($query);
			if($queryVal) {
				$this->view_item .= '&'.$query.'='.$queryVal;
			}
		}
		return true;
	}

}
