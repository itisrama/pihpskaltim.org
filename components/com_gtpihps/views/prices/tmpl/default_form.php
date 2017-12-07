<?php
	$publishedOptions = JHtml::_('jgrid.publishedOptions');
	if(!$this->user->authorise('core.admin')) {
		unset($publishedOptions[2]);
	}
?>

<div class="form-filter">
	<div class="form-group">
		<label for="filter_province_ids"><?php echo JText::_('COM_GTPIHPS_FIELD_PROVINCE'); ?></label>
		<?php echo JHtml::_('select.genericlist', $this->provinceOptions, 'filter_province_ids[]', 'class="form-control" size="5" multiple="multiple"', 'value', 'text', $this->state->get('filter.province_ids'));?>
	</div>
	<div class="form-group">
		<label for="filter_regency_ids"><?php echo JText::_('COM_GTPIHPS_FIELD_REGENCY'); ?></label>
		<?php echo JHtml::_('select.genericlist', $this->regencyOptions, 'filter_regency_ids[]', 'class="form-control" size="3" multiple="multiple"', 'value', 'text', $this->state->get('filter.regency_ids'));?>
	</div>
	<div class="form-group">
		<label for="filter_market_ids"><?php echo JText::_('COM_GTPIHPS_FIELD_MARKET'); ?></label>
		<?php echo JHtml::_('select.genericlist', $this->marketOptions, 'filter_market_ids[]', 'class="form-control" size="3" multiple="multiple"', 'value', 'text', $this->state->get('filter.market_ids'));?>
	</div>
	<div class="form-group">
		<label for="filter_start_date"><?php echo JText::_('COM_GTPIHPS_FIELD_START_DATE'); ?></label>
		<?php echo GTHelperDate::getDatePicker('filter_start_date', $this->state->get('filter.start_date'), 'class="form-control"', '%d-%m-%Y');?>
	</div>
	<div class="form-group">
		<label for="filter_end_date"><?php echo JText::_('COM_GTPIHPS_FIELD_END_DATE'); ?></label>
		<?php echo GTHelperDate::getDatePicker('filter_end_date', $this->state->get('filter.end_date'), 'class="form-control"', '%d-%m-%Y');?>
	</div>
	<div class="form-group">
		<label for="filter_end_date"><?php echo JText::_('JSTATUS'); ?></label>
		<select name="filter_published" class="form-control" onchange="this.form.submit()">
			<option><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
			<?php echo JHtml::_('select.options', $publishedOptions, 'value', 'text', $this->state->get('filter.published'), true);?>
		</select>
	</div>
	<br/>
	<button type="submit" class="btn btn-primary btn-lg btn-block"><i class="fa fa-file-text"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_SHOW_DATA');?></button>
</div>