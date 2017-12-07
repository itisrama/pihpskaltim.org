<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_banners
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;
JHtml::_('behavior.formvalidation');

/*
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
*/
?>

<form action="<?php echo JRoute::_('index.php?option=com_jkcommodity&view=ref_city_commodity&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

	<div class="form-horizontal">
		<div class="row-fluid">
			<div class="span9">
				<?php //echo $this->form->renderFieldset('edit'); ?>
			    <?php echo JKHelper::showFieldsetEdit($this->form->getFieldset('edit')); ?>

				<legend><?php echo JText::_('COM_JKCOMMODITY_LABEL_CITY_COMMODITY'); ?></legend>

				<table class="table table-striped table-bordered table-hover table-condensed table-sorted">
					<thead>
						<tr>
							<th width="20px" style="text-align:center"><?php echo JText::_('JGRID_HEADING_ID')?></th>
							<th style="text-align:center"><?php echo JText::_('COM_JKCOMMODITY_LABEL_NAME')?></th>
							<th width="10%" style="text-align:center"><?php echo JText::_('COM_JKCOMMODITY_LABEL_DENOMINATION')?></th>
							<th width="10%" style="text-align:center"><?php echo JText::_('COM_JKCOMMODITY_LABEL_MULTIPLIER')?></th>
							<th width="25%" style="text-align:center"><?php echo JText::_('COM_JKCOMMODITY_LABEL_COMMODITY')?></th>
						</tr>
					</thead>

					<?php
						$commodityQuery	= "SELECT id, CONCAT(name,' - ',denomination) name FROM #__jkcommodity_commodity WHERE id IN (%s) ORDER BY id";
						$commodityReqs	= "{type:'commodity', order:'category_id', code_field: 'name', name_field: 'denomination'}";
					?>
					<tbody>
						<?php foreach ($this->item->commodities as $commodity):?>
							<tr>
								<td style="text-align:center"><?php echo $commodity->id?></td>
								<td style="text-align:left"><?php echo $commodity->name?></td>
								<td style="text-align:center"><?php echo $commodity->denomination?></td>
								<td style="text-align:center"><input class="input-small" style="text-align:right;" name="commodities[<?php echo $commodity->id?>][multiplier]" type="text" value="<?php echo floatval($commodity->multiplier) ?>"/></td>
								<td><?php echo JKHelperHTML::getSelectize('commodities['.$commodity->id.'][commodity_id]', $commodity->commodity_id, $commodityQuery, 'input-xlarge', $commodityReqs);?></td>
							</tr>
						<?php endforeach;?>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
