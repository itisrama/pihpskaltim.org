<h3><?php echo @$this->format->name ?> <small><?php echo JHtml::date($this->data->date, 'j F Y');?></small></h3>
<?php if($this->items):?>
	<div class="well">
		<button type="button" class="btn btn-warning" onclick="Joomla.submitbutton('import_excel.back')">
			<i class="icon-arrow-left"></i> <?php echo JText::_('COM_JKCOMMODITY_TOOLBAR_BACK')?>
		</button>
		<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('import_excel.send')">
			<i class="icon-upload"></i> <?php echo JText::_('COM_JKCOMMODITY_TOOLBAR_SEND_DATA')?>
		</button>
		<div style="float: right">
			<button type="button" class="table-prev btn btn-default"><i class="icon-chevron-left"></i> <?php echo JText::_('COM_JKCOMMODITY_BUTTON_LEFT');?></button>
			<button type="button" class="table-next btn btn-default"><?php echo JText::_('COM_JKCOMMODITY_BUTTON_RIGHT');?> <i class="icon-chevron-right"></i></button>
		</div>
	</div>
	<br/>
	<table id="report" class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<th style="text-align:center" width="45px"><?php echo JText::_('COM_JKCOMMODITY_LABEL_NUM') ?></th>
				<th style="text-align: center; vertical-align: middle;"><?php echo JText::_('COM_JKCOMMODITY_LABEL_COMMODITY') ?> (Rp)</th>
				<?php foreach($this->markets as $market):?>
					<th style="width:120px; text-align: center; vertical-align: middle; display:none;"><?php echo $market->name; ?></th>
				<?php endforeach;?>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1;?>
			<?php foreach($this->commodityList as $commodity):?>
			<?php $tooltip = ltrim($commodity->text, '&nbsp;');?>
			<tr>
				<td style="text-align:center">
					<?php if($commodity->value):?>
					<span><?php echo $i; $i++; ?></span>
					<?php endif;?>
				</td>
				<td>
					<?php if(!$commodity->value):?>
					<strong><?php echo $commodity->text ?></strong>
					<?php else:?>
					<span><?php echo $commodity->text ?></span>
					<?php endif;?>
				</td>
				<?php $item = isset($this->items[$commodity->value]) && $commodity->value ? $this->items[$commodity->value] : array() ?>
				<?php foreach($this->markets as $market_id => $market):?>
					<?php if(!$commodity->value):?>
						<td style="display:none"></td>
					<?php elseif(isset($item[$market_id]) && $item[$market_id] > 0): ?>				
						<td style="text-align: right; display:none" title="<?php echo $tooltip ?>" class="hasTooltip"><?php echo JKHelperDocument::toCurrency($item[$market_id], ''); ?></td>
					<?php else:?>
						<td style="text-align: center; display:none; background-color:#FCD9D9" title="<?php echo $tooltip ?>" class="hasTooltip">-</td>
					<?php endif;?>
				<?php endforeach;?>
			</tr>
			<?php endforeach;?>
		</tbody>
	</table>
	<div class="well">
		<div style="float: right">
			<button type="button" class="table-prev btn btn-default"><i class="icon-chevron-left"></i> <?php echo JText::_('COM_JKCOMMODITY_BUTTON_LEFT');?></button>
			<button type="button" class="table-next btn btn-default"><?php echo JText::_('COM_JKCOMMODITY_BUTTON_RIGHT');?> <i class="icon-chevron-right"></i></button>
		</div>
	</div>
<?php else:?>
	<button type="button" class="btn btn-warning" onclick="Joomla.submitbutton('import_excel.back')">
		<i class="icon-arrow-left"></i> <?php echo JText::_('COM_JKCOMMODITY_TOOLBAR_BACK')?>
	</button><br/><br/>
			
	<div class="alert alert-warning text-center" role="alert">
		<i class="icon-warning" style="font-size: 8em"></i>
		<h3><?php echo JText::_('COM_JKCOMMODITY_EXCEL_NO_DATA');?></h3>
		<?php echo JText::_('COM_JKCOMMODITY_EXCEL_NO_DATA_DESC');?>
		<br/><br/>
	</div>
<?php endif;?>