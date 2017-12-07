<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
?>

<div id="com_gtpihps" class="item-page<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1><?php echo $this->page_title; ?></h1>
	</div>
	<?php endif; ?>
	<form action="<?php echo JRoute::_('index.php?option=com_gtpihps'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validation">
		<?php echo $this->loadTemplate('button'); ?>

		<?php echo GTHelperFieldset::renderEdit($this->form->getFieldset('main'), 1); ?>

		<br/>
		<h3 class="clearfix">
			<?php echo JText::_('COM_GTPIHPS_FIELDSET_COMMODITY_PRICE')?>
			<div class="pull-right">
				<button type="button" id="copy-prices" class="btn btn-default" type="button"><i class="fa fa-copy"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_COPY_ALL');?></button>
				<button type="button" id="clear-prices" class="btn btn-default" type="button"><i class="fa fa-times"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_CLEAR_ALL');?></button>
			</div>
		</h3>
		<table id="report" class="table table-striped table-bordered table-condensed">
			<thead>
				<tr>
					<th style="text-align: center; vertical-align: middle;"><?php echo JText::_('COM_GTPIHPS_FIELD_COMMODITY') ?></th>
					<th style="text-align: center; vertical-align: middle; width: 250px;"><?php echo JText::_('COM_GTPIHPS_FIELD_LAST_PRICE') ?></th>
					<th style="text-align: center; vertical-align: middle; width: 234px;"><?php echo JText::_('COM_GTPIHPS_FIELD_CURRENT_PRICE') ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach($this->commodityList as $commodity):?>
				<?php if(is_numeric($commodity->value)):?>
				<tr>
					<td><?php echo $commodity->text ?></td>
					<td style="text-align: right;">
						<span id="price_<?php echo $commodity->value?>" class="price"></span>
						<button class="btn btn-default btn-sm copy-price" style="margin-left: 5px" type="button"><?php echo JText::_('COM_GTPIHPS_TOOLBAR_COPY');?> <i class="fa fa-chevron-right"></i></button>
					</td>
					<td style="text-align: center;">
						<?php echo str_replace(
							array('_details', '[details]', 'value="0"'), 
							array('_details_'.$commodity->value, '[details]['.$commodity->value.']', 'value="'.@$this->details[$commodity->value]->price.'" style="width:100px"'), 
							$this->form->getField('details')->input
						);?>
						<button class="btn btn-default btn-sm clear-price" style="margin-left: 5px" type="button"><i class="fa fa-times"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_CLEAR');?></button>
					</td>
				</tr>
				<?php else:?>
				<tr>
					<td><strong><?php echo $commodity->text ?></strong></td>
					<td></td>
					<td></td>
				</tr>
				<?php endif;?>
				<?php endforeach;?>
			</tbody>
		</table>
		
		<input type="hidden" name="id" value="<?php echo intval(@$this->item->id) ?>" />
		<input type="hidden" name="region_id" id="region_id" value="<?php echo intval(@$this->state->get('filter.region_id')) ?>" />
		<input type="hidden" name="province_id" id="province_id" value="<?php echo intval(@$this->state->get('filter.province_id')) ?>" />
		<input type="hidden" name="cid[]" value="<?php echo intval(@$this->item->id) ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
