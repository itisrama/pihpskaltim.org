<?php
/**
 * @package   AdminTools
 * @copyright 2010-2016 Akeeba Ltd / Nicholas K. Dionysopoulos
 * @license   GNU General Public License version 3, or later
 */

namespace Akeeba\AdminTools\Admin\View\BlacklistedAddresses;

defined('_JEXEC') or die;

use FOF30\View\DataView\Html as BaseView;

class Html extends BaseView
{
	protected function onBeforeImport()
	{
		$this->addJavascriptFile('admin://components/com_admintools/media/js/BlacklistedAddresses.min.js');
	}

}