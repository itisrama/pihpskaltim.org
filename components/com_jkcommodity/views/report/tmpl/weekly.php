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
			<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
		</div>
	<?php endif; ?>
	<form action="<?php echo JRoute::_(JK_COMPONENT); ?>" method="post" name="adminForm" id="adminForm" class="form-vertical">
		<fieldset class="filter open">
			<legend style="cursor: pointer">Filter</legend>
			<div class="container-fluid row-fluid">
				<div class="span5">
					<div class="control-group"><?php echo $this->form->getLabel('commodity_id'); ?>
						<div class="controls">
							<?php echo JHtml::_('select.genericlist', $this->commodity_select, 'commodity_id[]', 'size="10" multiple="multiple" style="width:300px"', 'value', 'text', $this->commodity_ids);?>
							<label class="checkbox">
								<?php echo $this->form->getInput('all_commodity'); ?> <?php echo JText::_('COM_JKCOMMODITY_FIELD_ALL_COMMODITY');?>
							</label>
						</div>
					</div>
				</div>
				<div class="span4">
					<div class="control-group"><?php echo $this->form->getLabel('city_id'); ?>
						<div class="controls"><?php echo $this->form->getInput('city_id'); ?></div>
					</div>
					<div class="control-group"><?php echo $this->form->getLabel('market_id'); ?>
						<div class="controls"><?php echo $this->form->getInput('market_id'); ?></div>
					</div>
				</div>
				<div class="span3">
					<div class="control-group"><?php echo $this->form->getLabel('start_date'); ?>
						<div class="controls"><?php echo $this->form->getInput('start_date'); ?></div>
					</div>
					<div class="control-group"><?php echo $this->form->getLabel('end_date'); ?>
						<div class="controls"><?php echo $this->form->getInput('end_date'); ?></div>
					</div>
					<div class="control-group"><?php echo $this->form->getLabel('layout'); ?>
						<div class="controls"><?php echo $this->form->getInput('layout'); ?></div>
					</div>
				</div>
			</div>
			<hr/>
			<div style="text-align: center">
				<input type="hidden" name="format" id="format" value="" />
				<button class="btn btn-primary" type="submit" onclick="jQuery('#format').val('html')"><?php echo JText::_('COM_JKCOMMODITY_BUTTON_FIND_REPORT') ?></button>
				<button class="btn btn-primary" type="submit" onclick="jQuery('#format').val('xls')"><?php echo JText::_('COM_JKCOMMODITY_BUTTON_DOWNLOAD_REPORT') ?></button>
				<button class="btn btn-warning" type="reset" ><?php echo JText::_('COM_JKCOMMODITY_BUTTON_RESET') ?></button>
			</div>
		</fieldset>
	</form>
	<div style="margin-bottom: 10px">
		<h4><?php echo JText::_('COM_JKCOMMODITY_HEADER_REPORT')?></h4>
		<div><span style="display: inline-block; width: 80px;"><?php echo JText::_('COM_JKCOMMODITY_LABEL_PERIOD') ?></span> : <?php echo $this->header->period ?></div>
		<div><span style="display: inline-block; width: 80px;"><?php echo JText::_('COM_JKCOMMODITY_LABEL_CITY') ?></span> : <?php echo $this->header->city ?></div>
		<div><span style="display: inline-block; width: 80px;"><?php echo JText::_('COM_JKCOMMODITY_LABEL_MARKET') ?></span> : <?php echo $this->header->market ?></div>
		<div><span style="display: inline-block; width: 80px;"><?php echo JText::_('COM_JKCOMMODITY_FIELD_REPORT_TYPE') ?></span> : <?php echo $this->header->layout ?></div>
	</div>
	<?php if(count($this->commodity) > 0):?>
	<div style="text-align: right">
		<button type="button" class="btn table-prev"><i class="icon-chevron-left"></i> <?php echo JText::_('COM_JKCOMMODITY_BUTTON_LEFT');?></button>
		<button type="button" class="btn table-next"><?php echo JText::_('COM_JKCOMMODITY_BUTTON_RIGHT');?> <i class="icon-chevron-right"></i></button>
	</div><br/>
	<table id="report" class="table table-striped table-bordered">
		<thead>
			<tr>
				<th style="text-align: center; vertical-align: middle;"><?php echo JText::_('COM_JKCOMMODITY_LABEL_COMMODITY') ?> (dalam Rp)</th>
				<?php foreach($this->period as $date):?>
					<th style="display:none; text-align: center; vertical-align: middle;"><?php echo $date->sdate; ?></th>
				<?php endforeach;?>
			</tr>
		</thead>
		<tbody>
			<?php foreach($this->commodity_list as $commodity):?>
			<tr>
				<td>
					<?php if($commodity->value == 'category'):?>
					<strong><?php echo $commodity->text ?></strong>
					<?php else:?>
					<span><?php echo $commodity->text ?></span>
					<?php endif;?>
				</td>
				<?php $item = isset($this->data[$commodity->value]) && $commodity->value != 'category' ? $this->data[$commodity->value] : array() ?>
				<?php foreach($this->period as $date):?>
					<?php if($commodity->value == 'category'):?>
						<td style="display:none"></td>
					<?php elseif(isset($item[$date->unix])): ?>				
						<td style="display:none; text-align: right"><?php echo $item[$date->unix]; ?></td>
					<?php else:?>
						<td style="display:none; text-align: center">-</td>
					<?php endif;?>
				<?php endforeach;?>
			</tr>
			<?php endforeach;?>
		</tbody>
	</table>
	<div style="text-align: right">
		<button type="button" class="btn table-prev"><i class="icon-chevron-left"></i> <?php echo JText::_('COM_JKCOMMODITY_BUTTON_LEFT');?></button>
		<button type="button" class="btn table-next"><?php echo JText::_('COM_JKCOMMODITY_BUTTON_RIGHT');?> <i class="icon-chevron-right"></i></button>
	</div>
	<?php else:?>
	<div class="alert alert-warning" role="alert" style="text-align:center; font-size:1.3em">
		<strong><?php echo JText::_('COM_JKCOMMODITY_NO_DATA_HEAD')?></strong><br/>
		<?php echo JText::_('COM_JKCOMMODITY_NO_DATA_DESC')?>
	</div>
	<?php endif;?>
</div>