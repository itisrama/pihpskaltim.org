<?php
/**
 * @package     Joomla.Platform
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('calendar');

defined('JPATH_PLATFORM') or die;

/**
 * Form Field class for the Joomla Platform.
 * Provides a hidden field
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/input.hidden.html#input.hidden
 * @since       11.1
 */
class JFormFieldAltCalendar extends JFormFieldCalendar
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'AltCalendar';

	protected function getInput() {
		$calendar = parent::getInput();

		$class = explode(' ', $this->element['class']);
		$inputclass = array();
		foreach ($class as $k => $cl) {
			if(is_numeric(strpos($cl, 'input'))) {
				$inputclass[] = $cl;
				unset($class[$k]);
			}
		}
		$inputclass = implode(' ', $inputclass);
		$search = array(
			$this->element['class'],
			'<div class="input-append">',
			'hasTooltip',
			'btn',
			'<button',
			'icon-calendar',
			'</button>'
		);

		$replace = array(
			implode(' ', $class),
			'<div class="input-group '.$inputclass.'">',
			'hasTooltip form-control',
			'btn btn-info',
			'<div class="input-group-btn"><button',
			'fa fa-calendar',
			'</button></div>'
		);
		return str_replace($search, $replace, $calendar);
	} 
}
