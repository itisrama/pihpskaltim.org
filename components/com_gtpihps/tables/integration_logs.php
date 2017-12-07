<?php

/**
 * @package		GT PIHPS
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

class TableIntegration_Logs extends GTTable
{

	public $icon;
	public $province;
	/**
	 * Constructor
	 *
	 * @param object Database connector object
	 * @since 1.0
	 */
	function __construct(&$db) {
		parent::__construct('#__gtpihps_integration_logs', 'id', $db);
	}
	
	public function bind($array, $ignore = '') {
		$row = JArrayHelper::toObject($array);
		
		if(!@$row->id) {
			return parent::bind($array, $ignore);
		}

		switch($row->status) {
			case 'danger':
				$row->icon = '<i class="fa fa-times"></i>';
				break;
			case 'warning':
				$row->icon = '<i class="fa fa-exclamation-triangle"></i>';
				break;
			case 'success':
				$row->icon = '<i class="fa fa-check"></i>';
				break;
		}

		$row->date		= JHtml::date($row->date, 'd/m/Y', 'UTC');
		$row->created	= JHtml::date($row->created, 'd/m/Y H:i', 'UTC');
		
		$array			= JArrayHelper::fromObject($row);
		return parent::bind($array, $ignore);
	}
}
