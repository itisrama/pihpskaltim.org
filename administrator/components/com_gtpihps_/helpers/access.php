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

class GTHelperAccess
{

	public static function isAdmin($userid = NULL) {
		$user = JFactory::getUser($userid);
		$admin_groups = array(7, 8);

		foreach ($admin_groups as $group_id) {
			if (in_array($group_id, $user->groups)) {
				return true;
				break;
			}
		}
		return false;
	}

	/**
	 * Get the actions
	 */
	public static function getActions()
	{
		jimport('joomla.access.access');
		$user		= JFactory::getUser();
		$result		= new JObject;

		$assetName = 'com_gtpihps';
		$level = 'component';

		$actions = JAccess::getActions('com_gtpihps', $level);

		foreach ($actions as $action) {
			$result->set($action->name,	$user->authorise($action->name, $assetName));
		}

		return $result;
	}

	/**
	 * Check user permission for accessing edit view directly.
	 */
	public static function checkPermission($canDo, $created_by = 0) {
		$app		= JFactory::getApplication();
		$user		= JFactory::getUser();
		$jinput		= $app->input;

		$id			= $jinput->get('id');
		$option		= $jinput->get('option');
		$view		= $jinput->get('view');
		$viewList	= GTHelper::pluralize($view);
		$layout		= $jinput->get('layout');
		$canEdit	= $canDo->get('core.edit') || ($canDo->get('core.edit.own') && $created_by == $user->id);
		$canCreate	= $canDo->get('core.create');

		if ($layout == 'edit' && !$canEdit && $id) {
			$app->redirect(
				JRoute::_('index.php?option=' . $option . '&view=' . $viewList), JText::_('JLIB_APPLICATION_ERROR_EDIT_NOT_PERMITTED'), 'error'
			);
		} else if($layout == 'edit' && !$canCreate && !$id) {
			$app->redirect(
				JRoute::_('index.php?option=' . $option . '&view=' . $viewList), JText::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'), 'error'
			);
		}
	}

	/**
	 * Check user permission for accessing a view
	 */
	public static function checkViewPermission($view, $layout)
	{
		$admin_only = array('');
		$user_only = array('upload');
		$user = JFactory::getUser();
		$view = $layout ? implode(';', array($view, $layout)) : $view;
		if(in_array($view, $admin_only)) {
			if(GTHelperAccess::isAdmin()) {
				return true;
			} else {
				return false;
			}
		} else if(in_array($view, $user_only)) {
			if($user->id) {
				return true;
			} else {
				return false;
			}
		} else {
			return true;
		}
	}

}
