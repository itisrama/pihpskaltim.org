<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
?>
<div id="com_jkcommodity" class="item-page<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1><?php echo $this->page_title . ' - ' . $this->item_title; ?></h1>
	</div>
	<?php endif; ?>
	<form action="<?php echo JRoute::_('index.php?option=com_jkcommodity'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate">
		<?php if($this->buttons):?>
			<div class="well"><?php echo $this->buttons ?></div>
		<?php endif;?>

		<fieldset>
			<legend><?php echo JText::_('COM_JKCOMMODITY_FIELDSET_MAIN');?></legend>
			<?php echo JKHelper::showFieldsetEdit($this->form->getFieldset('main')); ?>
		</fieldset>
		<fieldset>
			<legend><?php echo JText::_('COM_JKCOMMODITY_FIELDSET_COMMODITIES');?></legend>
			<table id="report" class="table table-striped table-bordered table-condensed">
				<thead>
					<tr>
						<th style="text-align: center; vertical-align: middle; width: 60% !important;"><?php echo JText::_('COM_JKCOMMODITY_LABEL_COMMODITY') ?></th>
						<th style="text-align: center; vertical-align: middle;">Satuan</th>
						<th style="text-align: center; vertical-align: middle;">Harga Sebelumnya</th>
						<th style="text-align: center; vertical-align: middle;">Harga</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($this->commodity_list as $commodity):?>
					<?php if(is_numeric($commodity->value)):?>
					<?php
						$last_price_val	= isset($this->last_prices[$commodity->id])? $this->last_prices[$commodity->id]->price : null;
						$last_price 	= $last_price_val ? JKHelperDocument::toCurrency($last_price_val) : null
					?>
					<tr>
						<td style="height: 30px"><?php echo $commodity->text ?></td>
						<td style="text-align: center;"><?php echo $commodity->denomination ?></td>
						<td style="text-align: right;">
							<!-- Prev price -->
							<span id="commodity_<?php echo $commodity->id; ?>" class="commodity_prices"><?php echo $last_price ?></span>
							<?php echo str_replace(
								array('_last_details', '[last_details]', 'value="0"'), 
								array(
									'_last_details_'.$commodity->id,
									'[last_details]['.$commodity->id.']',
									'value="'. ($last_price_val > 0 ? $last_price_val : '') .'"'
								), 
								$this->form->getField('last_details')->input
							);?>
							<button class="btn btn-mini copy-price" style="margin-left: 5px" type="button">
								<?php echo JText::_('COM_JKCOMMODITY_BUTTON_COPY');?> <i class="icon-chevron-right"></i>
							</button>
						</td>
						<td style="text-align: center;">
							<?php echo str_replace(
								array('_details', '[details]', 'value="0"'), 
								array(
									'_details_'.$commodity->value,
									'[details]['.$commodity->value.']',
									'value="'. (@$this->item_detail[$commodity->value]->price > 0 ? JKHelperDocument::toCurrency($this->item_detail[$commodity->value]->price) : '') .'"'
								), 
								$this->form->getField('details')->input
							);?>
							<button class="btn btn-mini clear-price" style="margin-left: 5px" type="button">
								<i class="icon-remove"></i> <?php echo JText::_('COM_JKCOMMODITY_BUTTON_CLEAR');?>
							</button>
						</td>
					</tr>
					<?php else:?>
					<tr>
						<td style="height: 30px"><strong><?php echo $commodity->text ?></strong></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
					<?php endif;?>
					<?php endforeach;?>
				</tbody>
			</table>
			<div style="text-align: center">
				<button type="button" id="copy-prices" class="btn btn-primary btn-large" type="button">
					<i class="icon-copy"></i> <?php echo JText::_('COM_JKCOMMODITY_BUTTON_COPY_PREV_PRICES');?>
				</button>
			</div>
		</fieldset>

		<br/>
		<?php if($this->buttons):?>
			<div class="well"><?php echo $this->buttons ?></div>
		<?php endif;?>
			
		<input type="hidden" name="id" value="<?php echo isset($this->item->id) ? $this->item->id : 0 ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
