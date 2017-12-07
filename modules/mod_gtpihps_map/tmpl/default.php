<?php

$periodTypes = array(
	'dtd' => JText::_('MOD_GTPIHPS_MAP_OPT_DTD_COMPARE'),
	'wtw' => JText::_('MOD_GTPIHPS_MAP_OPT_WTW_COMPARE'),
	'mtm' => JText::_('MOD_GTPIHPS_MAP_OPT_MTM_COMPARE')
);
$dataTypes = array(
	'price' => JText::_('MOD_GTPIHPS_MAP_OPT_PRICE_COMPARE'),
	'fluctuation' => JText::_('MOD_GTPIHPS_MAP_OPT_FLUCTUATION'),
	'fluctuation2' => JText::_('MOD_GTPIHPS_MAP_OPT_FLUCTUATION2')
);
?>

<div id="mod_gtpihps_map-<?php echo $module->id?>" class="mod_gtpihps_map">
	<h3><?php echo JText::_('MOD_GTPIHPS_MAP_H3')?></h3>
	<form role="form" id="mapForm" name="mapForm" method="post" action="<?php echo JURI::root(true)?>">
		<div class="form-inline">
			<div class="form-group">
				<label class="hasTip" for="commodity_id"><?php echo JText::_('MOD_GTPIHPS_MAP_FIELD_COMMODITY')?></label>
				<?php echo JHtml::_('select.genericlist', $commodityOptions, 'commodity_id', 'class="form-control"', 'value', 'text', $commodity_id);?>
			</div>
			<div class="form-group">
				<label class="hasTip" for="date"><?php echo JText::_('MOD_GTPIHPS_MAP_FIELD_DATE')?></label>
				<?php echo GTHelperHtml::calendar(JHtml::date($date, 'd-m-Y'), 'date', 'date', '%d-%m-%Y', 'class="form-control"');?>
			</div>
			<div class="form-group">
				<button id="viewBtn" class="btn btn-success" type="submit"><i class="fa fa-fw fa-arrow-down"></i> <span><?php echo JText::_('MOD_GTPIHPS_MAP_VIEW')?></span></button>
			</div>
		</div>
	</form>
	<div class="row data-display" style="position:relative;">
		<div class="col-md-7">
			<?php echo JHtml::_('bootstrap.startTabSet', 'mapTab', array('active' => 'map')); ?>
			<?php echo JHtml::_('bootstrap.addTab', 'mapTab', 'map', '<i class="fa fa-globe"></i>&nbsp;&nbsp;'.JText::_('MOD_GTPIHPS_MAP_MAP_VIEW', true)); ?>
			<div class="map-container" style="width:90%;">
				<div class="map"></div>
				<div class="areaLegend"></div>
				<div class="areaLegendDesc">
					<div class="fluc" style="display:none;">
						<div class="pull-left" style="width:83.3px"><?php echo JText::_('MOD_GTPIHPS_MAP_DOWN')?></div>
						<div class="pull-left text-center" style="width:83.3px"><?php echo JText::_('MOD_GTPIHPS_MAP_STILL')?></div>
						<div class="pull-left text-right" style="width:83.3px"><?php echo JText::_('MOD_GTPIHPS_MAP_UP')?></div>
						<div class="pull-left text-center" style="width:50px"><?php echo JText::_('MOD_GTPIHPS_MAP_OUTDATE')?></div>
						<div class="pull-left text-center" style="width:50px"><?php echo JText::_('MOD_GTPIHPS_MAP_EMPTY')?></div>
					</div>
					<div class="price" style="display:none;">
						<div class="pull-left" style="width:125px"><?php echo JText::_('MOD_GTPIHPS_MAP_LOWEST')?></div>
						<div class="pull-left text-right" style="width:125px"><?php echo JText::_('MOD_GTPIHPS_MAP_HIGHEST')?></div>
						<div class="pull-left text-center" style="width:50px"><?php echo JText::_('MOD_GTPIHPS_MAP_OUTDATE')?></div>
						<div class="pull-left text-center" style="width:50px"><?php echo JText::_('MOD_GTPIHPS_MAP_EMPTY')?></div>
					</div>
				</div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>

			<?php echo JHtml::_('bootstrap.addTab', 'mapTab', 'chart', '<i class="fa fa-bar-chart"></i>&nbsp;&nbsp;'.JText::_('MOD_GTPIHPS_MAP_HISTOGRAM', true)); ?>
			<div class="chart-container">
				<h5></h5>
				<div></div>
			</div>
			<?php echo JHtml::_('bootstrap.endTab'); ?>
			<?php echo JHtml::_('bootstrap.endTabSet'); ?>
		</div>
		<div class="col-md-5">
			<div class="table-container">
				<table class="table table-bordered table-condensed"></table>
			</div>
		</div>
	</div>
	<div class="modal fade" tabindex="-1" role="dialog" id="regencyModal">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close hasTooltip" data-dismiss="modal" aria-label="Close" title="<?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE')?>"><span aria-hidden="true">&times;</span></button>
					<h4 class="modal-title"></h4>
				</div>
				<div class="modal-body">
					<div id="contentLoad" class="content text-center" style="padding:50px 0">
						<span class="fa fa-spinner fa-spin" style="font-size:4em"></span>
						<div style="font-size:2em"><?php echo JText::_('MOD_GTPIHPS_MAP_LOADING')?></div>
					</div>
					<div id="contentTable" class="content" style="opacity:0; height:0px; overflow:hidden">
						<table class="table table-bordered table-condensed table-regency"></table>
					</div>
				</div>
				<div class="modal-footer">
					<a href="#" class="btn btn-success" id="contentDownload" style="display:none"><i class="fa fa-download"></i> <?php echo JText::_('MOD_GTPIHPS_MAP_DOWNLOAD')?></a>
					<button type="button" class="btn btn-danger" data-dismiss="modal"><?php echo JText::_('JLIB_HTML_BEHAVIOR_CLOSE')?></button>
				</div>
			</div>
		</div>
	</div>
</div>

