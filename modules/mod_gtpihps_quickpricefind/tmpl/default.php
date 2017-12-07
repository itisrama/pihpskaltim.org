<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
?>
<div id="mod_gtpihps_quickpricefind-<?php echo $module->id?>" class="mod_gtpihps_quickpricefind">
	<h3><?php echo JText::_('MOD_GTPIHPS_QUICKPRICEFIND_H3')?></h3>
	<form action="<?php echo $componentURL; ?>" method="post" name="adminForm" id="adminForm" role="form">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="filter_commodity_ids[]" value="0" />
		<input type="hidden" name="filter_regency_ids[]" value="0" />
		<input type="hidden" name="filter_market_ids[]" value="0" />
		<input type="hidden" name="filter_start_date" value="<?php echo end($dates)?>" />
		<input type="hidden" name="filter_end_date" value="<?php echo reset($dates)?>" />
		<input type="hidden" name="filter_all_commodities" value="0" />
		<?php echo JHtml::_('form.token'); ?>

		<div class="form-group">
			<label for="filter_regency_id"><?php echo JText::_('MOD_GTPIHPS_QUICKPRICEFIND_FIELD_REGENCY'); ?></label>
			<?php echo JHtml::_('select.genericlist', $regencyOptions, 'filter_regency_ids[]', 'class="form-control"', 'value', 'text', null, 'filter_qp_regency_ids');?>
		</div>
		<div class="form-group">
			<label for="filter_market_id"><?php echo JText::_('MOD_GTPIHPS_QUICKPRICEFIND_FIELD_MARKET'); ?></label>
			<?php echo JHtml::_('select.genericlist', array(), 'filter_market_ids[]', 'class="form-control"', 'value', 'text', null, 'filter_qp_market_ids');?>
		</div>
		<div class="form-group">
			<label for="filter_commodity_ids"><?php echo JText::_('MOD_GTPIHPS_QUICKPRICEFIND_FIELD_COMMODITY'); ?></label>
			<?php echo JHtml::_('select.genericlist', $commodityOptions, 'filter_commodity_ids[]', 'class="form-control" size="8" multiple="multiple"', 'value', 'text', null, 'filter_qp_commodity_ids');?>
		</div>
		<button type="submit" class="btn btn-primary btn-md btn-block"><i class="fa fa-file-text"></i> <?php echo JText::_('MOD_GTPIHPS_QUICKPRICEFIND_TOOLBAR_VIEW_REPORT');?></button>
	</form>
</div>