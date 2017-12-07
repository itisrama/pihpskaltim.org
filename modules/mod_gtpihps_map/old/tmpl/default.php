<?php
	$document = JFactory::getDocument();
	$document->addScriptDeclaration(sprintf('
		var map_data = %s
	', json_encode($provinces)));

	$document->addScript('https://www.google.com/jsapi');
	$document->addScript(JURI::root(true).'modules/mod_gtpihps_map/script.js');
?>
<div id="mod_gtpihps_map-<?php echo $module->id?>" class="mod_gtpihps_map" style="background-color: #B4D7FF; height: 525px">
	<div class="row">
		<div class="col-md-9">
			<div id="report-map"></div>
			<div class="map-meta">
				<h5><?php echo JHtml::date($date, 'j F Y')?></h5>
				<h4><?php echo $commodity->name?> <small><?php echo ' per ' . $commodity->denomination?></small></h4>
			</div>
		</div>
		<div class="col-md-3">
			<h3><?php echo JText::_('MOD_GTPIHPS_MAP_H3')?></h3>
			<form class="form-vertical" role="form" name="mapForm" method="post" action="<?php echo JURI::root(true)?>">
				<div class="control-group">
					<label aria-invalid="false" title="" class="hasTip" for="city_id" id="city_id-lbl"><?php echo JText::_('MOD_GTPIHPS_MAP_FIELD_COMMODITY')?></label>
					<div class="controls">
						<?php echo JHtml::_('select.genericlist', $commodityOptions, 'filter_map_commodity_id', 'class="form-control" size="25" style="height:309px"', 'value', 'text', $commodity_id);?>
					</div>
				</div>
				<div class="control-group">
					<label for="filter_mapstart_date"><?php echo JText::_('MOD_GTPIHPS_MAP_FIELD_DATE')?></label>
					<div class="controls">
						<?php echo GTHelperHtml::calendar($date, 'filter_map_date', 'filter_map_date', '%d-%m-%Y', 'class="form-control"');?>
					</div>
				</div>
				<button class="btn btn-primary btn-lg btn-block" type="submit"><i class="fa fa-map-marker"></i> <?php echo JText::_('MOD_GTPIHPS_MAP_TOOLBAR_VIEW_MAP')?></button>
			</form>
		</div>
	</div>
</div>