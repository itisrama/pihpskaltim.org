<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
if($this->itemsCom) {
	// Generate chart
	$start_date				= reset($this->tableData->periods)->unix;
	$end_date				= end($this->tableData->periods)->unix;
	$menu_id 				= $this->input->get('Itemid');

	$options				= new stdClass();
	$options->start_date	= $start_date * 1000;
	$options->end_date		= $end_date * 1000;
	$options->show_legend	= 2;
	$options->show_tooltip	= 1;
	$options->show_yaxis	= 1;
	$options->show_xaxis	= 1;
	$options->enable_zoom	= 1;
	$options->zoom_buttons	= 1;
	$options->enable_pan	= 1;
	$options->pan_buttons	= 1;
	$options->date_format	= '%d %b %y';

	foreach ($this->commodities as &$commodity) {
		$commodity = $commodity->name;
	}
	foreach ($this->itemsCom as &$item) {
		$dates = array();
		foreach ($this->periods as $date) {
			$unix = $date->unix;
			$price = @$item[$unix];
			if(!$price > 0) continue;

			$dates[$unix] = $price;
		}
		$item = $dates;
	}

	$chart = GTHelperFlot::loadChart('gtchart'.$menu_id, $this->itemsCom, $this->commodities, $options);
}

?>

<div id="com_gtpihps" class="item-page<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_page_heading', 1)): ?>
	<div class="page-header">
		<h1><?php echo $this->page_title; ?></h1>
	</div>
	<?php endif; ?>

		
	<div class="row">
		<div class="col-md-3">
			<?php echo $this->formLayout->render($this->formData); ?>
		</div>
		<div class="col-md-9">
			<?php if($this->itemsCom):?>
				<div id="report-header">
					<h4><?php echo JText::_('COM_GTPIHPS_HEADER_REPORT')?></h4>
					<div><span><?php echo JText::_('COM_GTPIHPS_FIELD_PERIOD') ?></span> : <?php echo $this->tableData->period ?></div>
					<div><span><?php echo JText::_('COM_GTPIHPS_FIELD_PROVINCE') ?></span> : <?php echo $this->provinces ? $this->provinces : JText::_('COM_GTPIHPS_ALL_PROVINCES'); ?></div>
					<div><span><?php echo JText::_('COM_GTPIHPS_FIELD_REGENCY') ?></span> : <?php echo $this->tableData->regencies ? $this->tableData->regencies : JText::_('COM_GTPIHPS_ALL_REGENCIES'); ?></div>
					<div><span><?php echo JText::_('COM_GTPIHPS_FIELD_MARKET') ?></span> : <?php echo $this->tableData->markets ? $this->tableData->markets : JText::_('COM_GTPIHPS_ALL_MARKETS'); ?></div>
					<div><span><?php echo JText::_('COM_GTPIHPS_FIELD_REPORT_TYPE') ?></span> : <?php echo $this->tableData->report_type ?></div>
				</div>
				<hr/>
				<?php echo $chart; ?>
			<?php else:?>
				<div class="alert alert-warning text-center" role="alert">
					<i class="fa fa-warning" style="font-size: 8em"></i>
					<h3><?php echo JText::_('COM_GTPIHPS_REPORT_NO_DATA');?></h3>
					<?php echo JText::_('COM_GTPIHPS_REPORT_NO_DATA_DESC');?>
					<br/><br/>
				</div>
			<?php endif;?>
		</div>
	</div>
</div>
