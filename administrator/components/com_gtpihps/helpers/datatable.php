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

class GTHelperDataTable {

	public static function load()
	{
		$document = JFactory::getDocument();
		$document->addScript(GT_ADMIN_JS . '/datatables.min.js');
		$document->addStyleSheet(GT_ADMIN_CSS . '/datatables.min.css');
		$document->addStyleSheet(GT_ADMIN_CSS . '/dataTables.fontAwesome.css');
	}
	
	public static function server($id, $url, $columns, $fixedLeft = 0, $fixedRight = 0) {
		$document = JFactory::getDocument();

		foreach ($columns as $k => $column) {
			list($column, $class, $width, $orderable) = explode(':', $column);
			$columns[$k] = "{ data: '$column', className: '$class', width: '$width', orderable: $orderable }";
		}
		$columns = implode(', ', $columns);

		$document->addScriptDeclaration("
			jQuery.noConflict();
			(function($) {
				$(function() {
					$('#$id').DataTable( {
						processing: true,
						serverSide: true,
						scrollX: true,
						autoWidth: true,
						ajax: {
							url: '$url',
							type: 'POST'
						},
						columns: [$columns],
						fixedColumns: {
							leftColumns: $fixedLeft,
							rightColumns: $fixedRight
						}
					});
				});
			})(jQuery);
		");
	}
}
