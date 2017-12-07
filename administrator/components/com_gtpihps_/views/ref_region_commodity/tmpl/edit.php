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
//JHtml::_('formbehavior.chosen', 'select');

JFactory::getDocument()->addScriptDeclaration('
	Joomla.submitbutton = function(task) {
		if (task == "banner.cancel" || document.formvalidator.isValid(document.getElementById("adminForm"))) {
			Joomla.submitform(task, document.getElementById("adminForm"));
		}
	};
');
?>

<form action="<?php echo JRoute::_('index.php?option=com_gtpihps&view=ref_region_commodity&layout=edit&id=' . (int) $this->item->id); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">

	<div class="form-horizontal">
		<div class="row-fluid">
			<div class="span9">
				<?php echo $this->form->renderFieldset('edit'); ?>

				<legend><?php echo JText::_('COM_GTPIHPS_PT_REGION_COMMODITY'); ?></legend>

				<table class="table table-striped table-bordered table-hover table-condensed table-sorted">
					<thead>
						<tr>
							<th width="20px" style="text-align:center"><?php echo JText::_('JGRID_HEADING_ID')?></th>
							<th style="text-align:center"><?php echo JText::_('COM_GTPIHPS_PT_NATIONAL_COMMODITY')?></th>
							<th width="10%" style="text-align:center"><?php echo JText::_('COM_GTPIHPS_FIELD_DENOMINATION_NATIONAL')?></th>
							<th width="25%" style="text-align:center"><?php echo JText::_('COM_GTPIHPS_PT_REGION_COMMODITY')?></th>
						</tr>
					</thead>

					<?php
						$commodityQuery	= "SELECT id, CONCAT(name,' - ',denomination) name FROM #__gtpihps_province_commodities WHERE region_id IN (%s) ORDER BY id";
						$commodityReqs	= "{type:'ref_province_commodity', order:'category_id', code_field: 'name', name_field: 'denomination'}";
					?>
					<tbody>
						<?php foreach ($this->item->commodities as $commodity):?>
							<tr>
								<td style="text-align:center"><?php echo $commodity->commodity_national_id?></td>
								<td style="text-align:left"><?php echo $commodity->name?></td>
								<td style="text-align:center"><?php echo $commodity->denomination?></td>
								<td><?php echo GTHelperHTML::getSelectize('commodities['.$commodity->commodity_national_id.'][id]', $commodity->id, $commodityQuery, $commodity->region_id, 'input-xlarge', $commodityReqs);?></td>
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
