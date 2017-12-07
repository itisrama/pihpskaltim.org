<?php
/**
 * @version    SVN: <svn_id>
 * @package    PdfEmbed
 * @author     Techjoomla <extensions@techjoomla.com>
 * @copyright  Copyright (c) 2009-2015 TechJoomla. All rights reserved.
 * @license    GNU General Public License version 2, or later
 */

// No direct access.
defined('_JEXEC') or die('Restricted access');

// Load language file for plugin frontend.
$lang = JFactory::getLanguage();
$lang->load('plg_editors-xtd_pdf_btn', JPATH_ADMINISTRATOR);
jimport('joomla.plugin.plugin');

/**
 * Class for PlgButtonPdf_Btn plugin
 *
 * @package  Editor-xtd
 * @since    1.0
 */
class PlgButtonPdf_Btn extends JPlugin
{
	/**
	 * Takes the parameter for
	 *
	 * @param   string  &$subject  The subject being passed to the plugin.
	 * @param   string  $config    The
	 *
	 * @return   html for the pdf
	 *
	 * @since   1.0
	 */
	public function plgButtonPdf_btn(& $subject, $config)
	{
		parent::__construct($subject, $config);
		JFactory::getLanguage()->load('plg_editors-xtd_pdf_btn');
	}

	/**
	 * Takes the parameter for
	 *
	 * @param   string  $name  The subject being passed to the plugin.
	 *
	 * @return   html for the pdf
	 *
	 * @since   1.0
	 */
	public function onDisplay($name)
	{
		$js = "
			function buttonPdf_btnClick(editor)
			{
				txt = prompt('" . JText::_('PLG_PDF_BTN_PDF_NAME_PROMPT') . "','example.pdf');
				if (!txt) return;
				if(txt)
				{
					txt1 = prompt('" . JText::_('PLG_PDF_BTN_PDF_VIEWER_PROMPT') . "','');
				}
				if(!txt1)
				{
					txt1 = 'native';
				}
				jInsertEditorText('{pdf='+txt+'|100%|300|'+txt1+'}', editor);
			}";
			$doc = JFactory::getdocument();
			$doc ->addScriptDeclaration($js);
			$button = new JObject;
			$button->set('modal', false);
			$button->set('class', 'btn');
			$button->set('onclick', 'buttonPdf_btnClick(\'' . $name . '\');return false;');
			$button->set('text', JText::_('PLG_PDF_BTN_BUTTON'));
			$button->set('name', 'file');
			$button->set('link', '#');

			return $button;
	}
}
