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
	protected $menu;
	
	public function __construct($config = array()) {
		parent::__construct($config);
		
		// Set variables
		$this->app		= JFactory::getApplication();
		$this->user		= JFactory::getUser();
		$this->input	= $this->app->input;
		$this->menu		= $this->app->getMenu()->getActive();

		// Adjust the context to support modal layouts.
		$layout = $this->input->get('layout', 'default');
		$this->context	= implode('.', array($this->option, $this->getName(), $layout));

		// Add table path
		$this->addTablePath(GT_TABLES);
	}
	
	protected function populateState() {
		parent::populateState();
	}

	public function getItemExternal($pk = null, $name) {
		$this->name	= $name;
		$return		= JArrayHelper::fromObject(parent::getItem($pk));
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

	public function saveExternal($data, $name, $return_id = false) {
		$data	= is_object($data) ? JArrayHelper::fromObject($data) : $data;
		$table	= $this->getTable($name);
		$key	= $table->getKeyName();
		$pk		= intval(@$data[$key]);
		$isNew	= $pk > 0;

		foreach ($data as $k => $dat) {
			if(trim($dat) === '') {
				unset($data->$k);
			}
		}
		if (!$isNew) {
			$table->load($pk);
		}

		// Bind the data.
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		}

		// Check the data.
		if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		}

		// Store the data.
		if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		}

		if (isset($table->$key) && $return_id) {
			return $table->$key;
		} else {
			return true;
		}
	}
	
	public function saveBulk($items, $table = null, $meta = true, $is_table = false) {
		$date		= JFactory::getDate()->toSql();
		$user_id	= JFactory::getUser()->get('id');
		$user_id	= $this->input->get('user_id', $user_id);

		if($is_table) {
			$table = $this->getTable($table);
			$table = $table->getTableName();
		} else {
			$table = $table ? $table : $this->getName();
			$table = GTHelper::pluralize($table);
			$table = '#__gtpihps_'.$table;
		}
		

		$items = is_object($items) ? JArrayHelper::fromObject($items) : $items;
		if(!count($items) > 0) {
			return true;
		}

		$db = JFactory::getDbo();
 
		$query = $db->getQuery(true);

		// Insert columns.
		$columns = reset($items);
		$columns = is_object($columns) ? JArrayHelper::fromObject($columns) : $columns;

		unset($columns['created']);
		$columns = array_keys($columns);

		foreach ($items as &$item) {
			$item = is_object($item) ? JArrayHelper::fromObject($item) : $item;
			$created = @$item['created'];
			unset($item['created']);
			foreach ($item as &$val) {
				$val = $val ? $db->quote($val) : 'NULL';
			}
			if($meta) {
				$item[]	= $created ? $db->quote($created) : $db->quote($date);
				$item[]	= $db->quote($user_id);
			}
			$item	= implode(', ', $item);
		}

		// Prepare the insert query.
		$insert_cols = $meta ? array_merge($columns, array('created', 'created_by')) : $columns;
		$query->insert($db->quoteName($table));
		$query->columns($db->quoteName($insert_cols));
		$query->values($items);

		foreach ($columns as &$column) {
			$column = $db->quoteName($column).' = VALUES('.$db->quoteName($column).')';
		}
		if($meta) {
			$columns[]	= $db->quoteName('modified').' = '.$db->quote($date);
			$columns[]	= $db->quoteName('modified_by').' = '.$db->quote($user_id);
		}
		
		$columns	= implode(', ', $columns);

		$query = $query . ' ON DUPLICATE KEY UPDATE ' . $columns;

		//echo nl2br(str_replace('#__','pihpsnas_',$query)); die;

		// Set the query using our newly populated query object and execute it.
		$db->setQuery($query);

		return $db->execute();
	}
	
	public function deleteExternal(&$pks, $table, $key = 'id') {
		JArrayHelper::toInteger($pks, 0);
		
		if(!count($pks) > 0) return false;

		$table = $table ? $table : $this->getName();
		$table = GTHelper::pluralize($table);

		$db = $this->_db;
 
		$query = $db->getQuery(true);

		$query->delete($db->quoteName('#__gtpihps_'.$table));
		$query->where($db->quoteName($key).' IN ('.implode(',', $pks).')');

		$db->setQuery($query);

		return $db->execute();
	}
}
