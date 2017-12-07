<form action="<?php echo JRoute::_('index.php?option=com_gtpihps'); ?>" method="post" name="adminForm" id="adminForm" class="form-filter" role="form">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_region_ids[]" value="0" />
	<input type="hidden" id="integration_url" name="integration_url" value="<?php echo $this->integrationUrl ?>" />
	<input type="hidden" id="integration_market_url" name="integration_market_url" value="<?php echo $this->integrationMarketUrl ?>" />
	<?php echo JHtml::_('form.token'); ?>

	<div class="form-group">
		<label for="filter_province_ids"><?php echo JText::_('COM_GTPIHPS_FIELD_PROVINCE'); ?></label>
		<?php echo JHtml::_('select.genericlist', $this->provinceOptions, 'filter_province_ids[]', 'class="form-control" size="8" multiple="multiple"', 'value', 'text');?>
	</div>
	<div class="form-group">
		<label for="filter_start_date"><?php echo JText::_('COM_GTPIHPS_FIELD_START_DATE'); ?></label>
		<?php echo GTHelperDate::getDatePicker('filter_start_date', $this->state->get('filter.start_date'), 'class="form-control"', '%d-%m-%Y');?>
	</div>
	<div class="form-group">
		<label for="filter_end_date"><?php echo JText::_('COM_GTPIHPS_FIELD_END_DATE'); ?></label>
		<?php echo GTHelperDate::getDatePicker('filter_end_date', $this->state->get('filter.end_date'), 'class="form-control"', '%d-%m-%Y');?>
	</div>
	
	<button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fa fa-file-text"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_SYNC_DATA');?></button>
</form>