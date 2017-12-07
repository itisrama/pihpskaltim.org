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
				</div>
			</div>
			<hr/>
			<div style="text-align: center">
				<input type="hidden" name="format" id="format" value="" />
				<button class="btn btn-primary" type="submit" onclick="jQuery('#format').val('html')"><?php echo JText::_('COM_JKCOMMODITY_BUTTON_FIND_REPORT') ?></button>
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
	<?php echo $this->data ?>
	<?php else:?>
	<div class="alert alert-warning" role="alert" style="text-align:center; font-size:1.3em">
		<strong><?php echo JText::_('COM_JKCOMMODITY_NO_DATA_HEAD')?></strong><br/>
		<?php echo JText::_('COM_JKCOMMODITY_NO_DATA_DESC')?>
	</div>
	<?php endif;?>
</div>