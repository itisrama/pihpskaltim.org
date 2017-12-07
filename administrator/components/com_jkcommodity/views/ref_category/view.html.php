<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */

defined('_JEXEC') or die;

class JKCommodityViewRef_Category extends JKView {

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

		JToolbarHelper::title($isNew ? JText::_('COM_JKCOMMODITY_MANAGER_CATEGORY_NEW') : JText::_('COM_JKCOMMODITY_MANAGER_CATEGORY_EDIT'), 'list menus');

		// If not checked out, can save the item.
		if ($this->canEdit)
		{
			JToolbarHelper::apply('ref_category.apply');
			JToolbarHelper::save('ref_category.save');

			if ($this->canCreate)
			{
				JToolbarHelper::save2new('ref_category.save2new');
			}
		}

		// If an existing item, can save to a copy.
		if (!$isNew && $this->canCreate)
		{
			JToolbarHelper::save2copy('ref_category.save2copy');
		}

		if (empty($this->item->id))
		{
			JToolbarHelper::cancel('ref_category.cancel');
		}
		else
		{
			if ($this->state->params->get('save_history', 0) && $this->canEdit)
			{
				JToolbarHelper::versions('com_menus.menu', $this->item->id);
			}

			JToolbarHelper::cancel('ref_category.cancel', 'JTOOLBAR_CLOSE');
		}
	}

}
