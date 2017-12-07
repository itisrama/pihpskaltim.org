<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

defined('_JEXEC') or die;

class JKCommodityViewRef_Market extends JKView {

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

		JToolbarHelper::title($isNew ? JText::_('COM_JKCOMMODITY_MANAGER_MARKET_NEW') : JText::_('COM_JKCOMMODITY_MANAGER_MARKET_EDIT'), 'list menus');

		// If not checked out, can save the item.
		if ($this->canEdit)
		{
			JToolbarHelper::apply('ref_market.apply');
			JToolbarHelper::save('ref_market.save');

			if ($this->canCreate)
			{
				JToolbarHelper::save2new('ref_market.save2new');
			}
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $this->canCreate)
		{
			JToolbarHelper::save2copy('ref_market.save2copy');
		}

		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('ref_market.cancel');
		}
		else
		{
			if ($this->state->params->get('save_history', 0) && $this->canEdit)
			{
				JToolbarHelper::versions('com_menus.menu', $this->item->id);
			}

			JToolbarHelper::cancel('ref_market.cancel', 'JTOOLBAR_CLOSE');
		}
	}

}
