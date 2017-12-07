<?php
	$layoutOpts = array(
		'default' => JText::_('COM_GTPIHPS_OPTION_LAYOUT_DEFAULT'),
		'weekly' => JText::_('COM_GTPIHPS_OPTION_LAYOUT_WEEKLY'),
		'monthly' => JText::_('COM_GTPIHPS_OPTION_LAYOUT_MONTHLY'),
		'chart' => JText::_('COM_GTPIHPS_OPTION_LAYOUT_CHART'),
	);
?>

<form action="<?php echo JRoute::_('index.php?option=com_gtpihps'); ?>" method="post" name="adminForm" id="adminForm" class="form-filter" role="form">
	<input type="hidden" name="task" value="" />
	<input type="hidden" name="filter_commodity_ids[]" value="0" />
	<input type="hidden" name="filter_regency_ids[]" value="0" />
	<input type="hidden" name="filter_market_ids[]" value="0" />
	<input type="hidden" name="filter_all_commodities" value="0" />
	<input type="hidden" name="format" id="format" value="" />
	<input type="hidden" name="price_type_id" id="price_type_id" value="<?php echo $displayData->price_type_id?>" />
	<?php echo JHtml::_('form.token'); ?>

	<div class="form-group">
		<label for="filter_commodity_ids"><?php echo JText::_('COM_GTPIHPS_FIELD_COMMODITY'); ?></label>
		<?php echo JHtml::_('select.genericlist', $displayData->commodityOptions, 'filter_commodity_ids[]', 'class="form-control" size="8" multiple="multiple"', 'value', 'text', array_filter($displayData->state->get('filter.commodity_ids')));?>
		<?php /*
		<label for="filter_all_commodities" class="checkbox">
			<input type="checkbox" id="filter_all_commodities" name="filter_all_commodities" value="1" <?php echo $displayData->state->get('filter.all_commodities') ? 'checked' : ''; ?> />
			<?php echo JText::_('COM_GTPIHPS_FIELD_ALL_COMMODITIES'); ?>
		</label>
		*/?>
	</div>
	<div class="form-group">
		<label for="filter_regency_ids"><?php echo JText::_('COM_GTPIHPS_FIELD_REGENCY'); ?></label>
		<?php echo JHtml::_('select.genericlist', $displayData->regencyOptions, 'filter_regency_ids[]', 'class="form-control" size="3" multiple="multiple"', 'value', 'text', $displayData->state->get('filter.regency_ids'));?>
	</div>
	<div class="form-group">
		<label for="filter_market_ids"><?php echo JText::_('COM_GTPIHPS_FIELD_MARKET'); ?></label>
		<?php echo JHtml::_('select.genericlist', $displayData->marketOptions, 'filter_market_ids[]', 'class="form-control" size="3" multiple="multiple"', 'value', 'text', $displayData->state->get('filter.market_ids'));?>
	</div>
	<div class="form-group">
		<label for="filter_regency_ids"><?php echo JText::_('COM_GTPIHPS_FIELD_REPORT_TYPE'); ?></label>
		<?php echo JHtml::_('select.genericlist', $layoutOpts, 'layout', 'class="form-control"', 'value', 'text', $displayData->state->get('filter.layout'));?>
	</div>
	<div class="form-group">
		<label for="filter_start_date"><?php echo JText::_('COM_GTPIHPS_FIELD_START_DATE'); ?></label>
		<?php echo GTHelperHtml::calendar($displayData->state->get('filter.start_date'), 'filter_start_date', 'filter_start_date', '%d-%m-%Y', 'class="form-control"');?>
	</div>
	<div class="form-group">
		<label for="filter_end_date"><?php echo JText::_('COM_GTPIHPS_FIELD_END_DATE'); ?></label>
		<?php echo GTHelperHtml::calendar($displayData->state->get('filter.end_date'), 'filter_end_date', 'filter_end_date', '%d-%m-%Y', 'class="form-control"');?>
	</div>
	
	<button type="submit" class="btn btn-primary btn-lg btn-block" onclick="jQuery('#format').val('html')">
		<i class="fa fa-file-text"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_VIEW_REPORT');?>
	</button>
	<button type="submit" class="btn btn-success btn-lg btn-block" onclick="jQuery('#format').val('xls')">
		<i class="fa fa-download"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_DOWNLOAD');?>
	</button>
</form>
