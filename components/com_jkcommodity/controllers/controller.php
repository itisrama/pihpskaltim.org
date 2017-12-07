<?php
/**
 * @package		JK Commodity
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityController extends JKController
{
	public function __construct($config = array())
	{
		$config['default_view'] = 'report';
		parent::__construct($config);
		
		$view = JRequest::getVar('view');
		$layout = JRequest::getVar('layout');
		$is_allowed = self::checkPermission($view, $layout);
		
		if(!$is_allowed) {
			$this->setRedirect(JK_COMPONENT, 'Anda tidak memiliki hak untuk mengakses halaman ' . ucfirst($view), 'error');
		}
	}
	
	public function checkPermission($view, $layout)
	{
		$admin_only = array('import');
		$user_only = array('upload');
		$user = JFactory::getUser();
		$view = $layout ? implode(';', array($view, $layout)) : $view;
		if(in_array($view, $admin_only)) {
			if(JKHelper::isAdmin()) {
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