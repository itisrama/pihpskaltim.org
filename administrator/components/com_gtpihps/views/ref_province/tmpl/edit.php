<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task) {
		if (task == "banner.cancel" || document.formvalidator.isValid(document.getElementById("adminForm"))) {
			Joomla.submitform(task, document.getElementById("adminForm"));
		}
	};
');
?>

<form action="<?php echo JRoute::_('index.php?option=com_gtpihps&view=ref_province&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

	<div class="form-horizontal">
		<div class="row-fluid">
			<div class="span9">
				<?php echo $this->form->renderFieldset('province'); ?>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
