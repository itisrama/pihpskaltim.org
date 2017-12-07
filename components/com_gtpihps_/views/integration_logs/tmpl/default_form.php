<div class="form-filter">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->ordering; ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->direction; ?>" />
	<input type="hidden" name="filter_all_provinces" value="0" />
	<input type="hidden" name="filter_province_ids[]" value="0" />
	<input type="hidden" id="filter_integration_url" name="filter_integration_url" value="<?php echo $this->integrationUrl ?>" />
	<?php echo JHtml::_('form.token'); ?>

	<div class="form-group">
		<label for="filter_province_ids"><?php echo JText::_('COM_GTPIHPS_FIELD_PROVINCE'); ?></label>
		<?php echo JHtml::_('select.genericlist', $this->provinceOptions, 'filter_province_ids[]', 'class="form-control" size="8" multiple="multiple"', 'value', 'text', $this->state->get('filter.province_ids'));?>
		<label for="filter_all_provinces" class="checkbox">
			<input type="checkbox" id="filter_all_provinces" name="filter_all_provinces" value="1" <?php echo $this->state->get('filter.all_provinces') ? 'checked' : ''; ?> />
			<?php echo JText::_('COM_GTPIHPS_FIELD_ALL_PROVINCES'); ?>
		</label>
	</div>
	<div class="form-group">
		<label for="filter_start_date"><?php echo JText::_('COM_GTPIHPS_FIELD_START_DATE'); ?></label>
		<?php echo GTHelperHtml::calendar($this->state->get('filter.start_date'), 'filter_start_date', 'filter_start_date', '%d-%m-%Y', 'class="form-control"');?>
	</div>
	<div class="form-group">
		<label for="filter_end_date"><?php echo JText::_('COM_GTPIHPS_FIELD_END_DATE'); ?></label>
		<?php echo GTHelperHtml::calendar($this->state->get('filter.end_date'), 'filter_end_date', 'filter_end_date', '%d-%m-%Y', 'class="form-control"');?>
	</div>
	<div class="form-group">
		<label for="filter_status_log"><?php echo JText::_('COM_GTPIHPS_FIELD_STATUS_LOG'); ?></label>
		<?php echo JHtml::_('select.genericlist', $this->statusLogOptions, 'filter_status_log', 'class="form-control"', 'value', 'text', $this->state->get('filter.status_log'));?>
	</div>

	<button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fa fa-file-text"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_VIEW_REPORT');?></button>
</div>