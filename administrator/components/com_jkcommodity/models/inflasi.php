<?php
jimport('joomla.utilities.arrayhelper');

class JKCommodityModelInflasi extends JKModel {

	function getRef() {
		$query = "SELECT id, ref AS name FROM #__jkcommodity_inflasi_ref ORDER BY id ASC";
		$this->_db->setQuery($query);
		$result = $this->_db->loadObjectList();
		return $result;
	}

	function store($data, $table = '') {
		$table = & $this->getTable($table);
		$table->reset();
		if (!$table->bind($data)) {
			$this->setError($table->getError());
			return false;
		} if (!$table->check()) {
			$this->setError($table->getError());
			return false;
		} if (!$table->store()) {
			$this->setError($table->getError());
			return false;
		} return $table->id;
	}

}