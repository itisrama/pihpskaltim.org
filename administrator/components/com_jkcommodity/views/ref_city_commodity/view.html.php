<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

defined('_JEXEC') or die;

class JKCommodityViewRef_City_Commodity extends JKView {

	var $form;
	var $item;
	var $state;
	
	public function display($tpl = null) {
		// Initialiase variables.
		$this->item			= $this->get('Item');
		$this->translations	= $this->get('Translations');
		$this->form			= $this->get('Form');
		$this->state		= $this->get('State');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode("\n", $errors));

			return false;
		}
		$this->addToolbar();

		parent::display($tpl);
	}
	protected function addToolbar()	{
		JFactory::getApplication()->input->set('hidemainmenu', true);

		$user		= JFactory::getUser();
		$userId		= $user->get('id');
		$isNew		= ($this->item->id == 0);

		JToolbarHelper::title(JText::_('COM_JKCOMMODITY_MANAGER_CITY_EDIT'), 'list menus');

		// If not checked out, can save the item.
		if ($this->canEdit)
		{
			JToolbarHelper::apply('ref_city_commodity.apply');
			JToolbarHelper::save('ref_city_commodity.save');
		}

		JToolbarHelper::cancel('ref_city_commodity.cancel');
	}

}
