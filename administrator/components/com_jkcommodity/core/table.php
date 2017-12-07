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

jimport('joomla.table');

class JKTable extends JTable
{
	public function __construct($table, $key, $db) {
		parent::__construct($table, $key, $db);
	}

	/**
	 * Stores a contact
	 *
	 * @param	boolean	True to update fields even if they are null.
	 * @return	boolean	True on success, false on failure.
	 * @since	1.6
	 */
	public function store($updateNulls = false) {
		// Prevent to save field that is not available in the table
		$tablefields = array_keys($this->getFields());
		$customfields = array_keys($this->getProperties());
		$customfields = array_diff($customfields, $tablefields);

		foreach ($customfields as $field) {
			unset($this->$field);
		}

		// Attempt to store the data.
		return parent::store($updateNulls);
	}

	public function getTable($name) {
		return parent::getInstance($name, 'Table');
	}

	public function getInput($field, $default = null, $type = null) {
		return JFactory::getApplication()->input->get($field, $default, $type);
	}
	
	public function getList($keys = null, $excludes = array(), $selfields = array(), $exclude_meta = true) {
		if(!is_array($keys)) return false;

		// Initialise the query.
		$metas = array(
			'published', 
			'ordering', 
			'checked_out', 
			'checked_out_time', 
			'modified', 
			'modified_by'
		);
		$metas = array_diff($metas, $selfields);
		$fields = array_keys($this->getFields());
		$selects = array_diff($fields, $metas);
		$excludes = array_merge($excludes, $metas, array('created', 'created_by'));

		$query = $this->_db->getQuery(true)->select(implode(',', $selects))->from($this->_tbl);
		$result = array();
		foreach ($keys as $field => $value) {
			
			// Check that $field is in the table.
			if (!in_array($field, $fields)) {
				throw new UnexpectedValueException(sprintf('Missing field in database: %s &#160; %s.', get_class($this), $field));
			}
			
			// Add the search tuple to the query.
			if(is_array($value) && count($value) > 0) {
				JArrayHelper::toInteger($value);
				$query->where($this->_db->quoteName($field) . ' IN (' . implode(',', array_merge($value)) . ')');
			} elseif(is_string($value) && strlen($value) > 0) {
				$query->where($this->_db->quoteName($field) . ' = ' . $this->_db->quote($value));
			} elseif(is_numeric($value)) {
				$query->where($this->_db->quoteName($field) . ' = ' . $value);
			} else {
				return $result;
			}
		}
		
		//echo nl2br(str_replace('#__','eburo_',$query));
		$this->_db->setQuery($query);
		
		$rows = $this->_db->loadAssocList();
		
		// Check that we have a result.
		
		if (!empty($rows)) {
			
			// Bind the object with the row and return.
			foreach ($rows as $row) {
				// Convert to the JObject before adding other data.
				$this->bind($row);
				$item = $this->getProperties(1);

				if($selfields) {
					if(count($selfields) < 2) {
						$selfield = reset($selfields);
						$result[] = $item[$selfield];
					} 
					elseif(count($selfields) < 3) {
						$itemSel = array();
						foreach ($excludes as $exclude) {
							unset($item[$exclude]);
						}
						foreach ($selfields as $selfield) {
							if(isset($item[$selfield])) {
								$itemSel[$selfield] = $item[$selfield];
							}
						}
						$result[] = $itemSel;
					} 
				} else {
					foreach ($excludes as $exclude) {
						unset($item[$exclude]);
					}
					$result[] = $item;
				}				
			}
		}
		return $result;
	}

	public function escape($output) {
		// Escape the output.
		return htmlspecialchars($output, ENT_COMPAT, 'UTF-8');
	}
}
