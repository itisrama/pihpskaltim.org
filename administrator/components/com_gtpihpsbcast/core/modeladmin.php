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

jimport('joomla.application.component.modeladmin');

class GTModelAdmin extends JModelAdmin
{
	
	protected $app;
	protected $user;
	protected $input;
	protected $context;
	protected $prevName;
	protected $item;
	
	public function __construct($config = array()) {
		parent::__construct($config);
		
		// Set variables
		$this->app		= JFactory::getApplication();
		$this->user		= JFactory::getUser();
		$this->input	= $this->app->input;

		// Adjust the context to support modal layouts.
		$layout = $this->input->get('layout', 'default');
		$this->context	= implode('.', array($this->option, $this->getName(), $layout));

		// Add table path
		$this->addTablePath(GT_TABLES);
	}

	public function getItem($pk = null) {
		$data = parent::getItem($pk);
		foreach ($data as $k => $item) {
			if(!is_object($item)) continue;
			$data->$k = JArrayHelper::fromObject($item);
		}
		return $data;
	}
	
	protected function populateState() {
		parent::populateState();
	}

	public function getItemExternal($pk = null, $name) {
		$this->name	= $name;
		$return		= JArrayHelper::fromObject(self::getItem($pk));
		$this->name	= $this->prevName;
		return JArrayHelper::toObject($return);
	}

	protected function loadFormData() {	
		$layout		= $this->app->getUserStateFromRequest($this->getName() . '.layout', 'layout');
		$context	= implode('.', array($this->option, $layout, $this->getName()));
		
		$data		= JFactory::getApplication()->getUserState($context . '.data', array());
		$data		= empty($data) ? $this->item : JArrayHelper::toObject($data);
		
		return $data;
	}
	
	public function getForm($data = array(), $loadData = true, $control = 'jform') {
		$component_name = $this->input->get('option');
		$model_name = $this->getName();
		
		if($data) {
			$this->item = $data;
		}
		// Get the form.
		$form = $this->loadForm($component_name . '.' . $model_name, $model_name, array('control' => $control, 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		
		// Modify the form based on access controls.
		if (!$this->canEditState((object)$data)) {
			// Disable fields for display.
			$form->setFieldAttribute('published', 'disabled', 'true');
			// Disable fields while saving.
			// The controller has already verified this is a record you can edit.
			$form->setFieldAttribute('published', 'filter', 'unset');
		}
		
		return $form;
	}

	public function getFormExternal($name, $data = array(), $loadData = true, $control = 'jform') {
		$this->name	= $name;
		$return		= $this->getForm($data, $loadData, $control);
		$this->name	= $this->prevName;
		return $return;
	}

	public function save($data, $return_num = false) {
		$data = JArrayHelper::fromObject($data);
		$return = parent::save($data);
		return $return_num ? $this->getState($this->getName() . '.id') : $return;
	}

	public function saveExternal($data, $name) {
		$data		= JArrayHelper::fromObject($data);
		$this->name	= $name;
		$return		= parent::save($data);
		$this->name	= $this->prevName;
		return $return;
	}
	

	public function deleteExternal(&$pks, $name) {
		$this->name	= $name;
		$return		= parent::delete($pks);
		$this->name	= $this->prevName;
		return $return;
	}
}
