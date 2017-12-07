<form action="<?php echo JRoute::_('index.php?option=com_gtpihps'); ?>" method="post" name="adminForm" id="adminForm" class="form-filter" role="form">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="format" id="format" value="" />
	<input type="hidden" name="filter_province_ids[]" value="0" />
	<input type="hidden" name="filter_all_provinces" value="0" />
	<?php echo JHtml::_('form.token'); ?>

	<div class="form-group">
		<label for="filter_province_ids"><?php echo JText::_('COM_GTPIHPS_FIELD_PROVINCE'); ?></label>
		<?php echo JHtml::_('select.genericlist', $this->provinces, 'filter_province_ids[]', 'class="form-control" size="8" multiple="multiple"', 'value', 'text', $this->state->get('filter.province_ids'));?>
	</div>
	<div class="form-group">
		<label for="filter_price_type_id"><?php echo JText::_('COM_GTPIHPS_FIELD_PRICE_TYPE_ID'); ?></label>
		<?php echo JHtml::_('select.genericlist', $this->priceTypes, 'filter_price_type_id', 'class="form-control"', 'id', 'name', $this->state->get('filter.price_type_id'));?>
	</div>
	<div class="form-group">
		<label for="filter_start_date"><?php echo JText::_('COM_GTPIHPS_FIELD_START_DATE'); ?></label>
		<?php echo GTHelperDate::getDatePicker('filter_start_date', $this->state->get('filter.start_date'), 'class="form-control"', '%d-%m-%Y');?>
	</div>
	<div class="form-group">
		<label for="filter_end_date"><?php echo JText::_('COM_GTPIHPS_FIELD_END_DATE'); ?></label>
		<?php echo GTHelperDate::getDatePicker('filter_end_date', $this->state->get('filter.end_date'), 'class="form-control"', '%d-%m-%Y');?>
	</div>
	
	<button type="submit" class="btn btn-primary btn-lg btn-block" onclick="jQuery('#format').val('html')">
		<i class="fa fa-file-text"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_VIEW_REPORT');?>
	</button>
	<button type="submit" class="btn btn-success btn-lg btn-block" onclick="jQuery('#format').val('xls')">
		<i class="fa fa-download"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_DOWNLOAD');?>
	</button>
</form>