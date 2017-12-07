<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

if($this->items) {
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

	$chart = GTHelperFlot::loadChart('gtchart'.$menu_id, $this->items, $this->provinces, $options);
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
			<?php if($this->items):?>
				<div id="report-header">
					<h4><?php echo JText::_('COM_GTPIHPS_HEADER_REPORT')?></h4>
					<div><span><?php echo JText::_('COM_GTPIHPS_FIELD_PERIOD') ?></span> : <?php echo $this->tableData->period ?></div>
					<div><span><?php echo JText::_('COM_GTPIHPS_FIELD_COMMODITY') ?></span> : <?php echo $this->tableData->commodity ?></div>
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
