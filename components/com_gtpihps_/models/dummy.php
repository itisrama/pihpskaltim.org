<?php

/**
 * @package		JK Docuno
 * @author		Yudhistira Ramadhan
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSModelDummy extends GTModelAdmin {
	public function getReferences($type) {
		$db = $this->_db;
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from($db->quoteName('#__gtpihpssurvey_ref_'.GTHelper::pluralize($type), 'a'));
		$query->where($db->quoteName('a.published').' = 1');
		$this->_db->setQuery($query);

		return $this->_db->loadObjectList();
	}
}