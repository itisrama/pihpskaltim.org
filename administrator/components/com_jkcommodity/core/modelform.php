<?php
/**
 * @package		Joomlaku Component
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

// no direct access
defined( '_JEXEC' ) or die('Restricted access');

jimport('joomla.application.component.modelform');

class JKModelForm extends JModelForm
{
	public function __construct()
	{
		parent::__construct();
	}
	
	protected function populateState() {
		$app = JFactory::getApplication('site');

		$offset = JRequest::getUInt('limitstart');
		$this->setState('list.offset', $offset);

		// Load the parameters.
		$params = $app->getParams();
		$this->setState('params', $params);
	}
	
	public function getForm($data = array(), $loadData = true, $control = 'jform') 
	{
		$component_name = JRequest::getCmd('option');
		$model_name = $this->getName();
		// Get the form.
		$form = $this->loadForm($component_name.'.'.$model_name, $model_name, array('control' => $control, 'load_data' => $loadData));
		if (empty($form)) {
			return false;
		}
		return $form;
	}
}
