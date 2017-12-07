<?php if($displayData->items):?>
	<div id="report-header">
		<h4><?php echo JText::_('COM_GTPIHPS_HEADER_REPORT')?></h4>
		<div><span><?php echo JText::_('COM_GTPIHPS_FIELD_PERIOD') ?></span> : <?php echo $displayData->period ?></div>
		<div><span><?php echo JText::_('COM_GTPIHPS_FIELD_COMMODITY') ?></span> : <?php echo $displayData->commodity ?></div>
		<div><span><?php echo JText::_('COM_GTPIHPS_FIELD_REPORT_TYPE') ?></span> : <?php echo $displayData->report_type ?></div>
	</div>
	<hr/>
	
	<div class="text-center">
		<button type="button" class="table-prev btn btn-default"><i class="fa fa-chevron-left"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_LEFT');?></button>
		<button type="button" class="table-next btn btn-default"><?php echo JText::_('COM_GTPIHPS_TOOLBAR_RIGHT');?> <i class="fa fa-chevron-right"></i></button>
	</div>
	<br/>
	<table id="report" class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<th class="text-center" width="45px"><?php echo JText::_('COM_GTPIHPS_FIELD_NUM') ?></th>
				<th style="text-align: center; vertical-align: middle;"><?php echo JText::_('COM_GTPIHPS_FIELD_PROVINCE') ?> (<?php echo trim(GTHelperCurrency::$symbol); ?>)</th>
				<?php foreach($displayData->periods as $date):?>
					<th width="85" style="text-align: center; vertical-align: middle; display:none;"><?php echo $date->sdate; ?></th>
				<?php endforeach;?>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1;?>
			<tr>
				<td class="text-center">
					<strong><?php echo GTHelperNumber::toRoman($i); $i++; ?></strong>
				</td>
				<td>
					<strong><?php echo strtoupper(JText::_('COM_GTPIHPS_FIELD_ALL_REGENCIES')) ?></strong>
				</td>
				<?php $item = (array) @$displayData->itemsAll[0]; ?>
				<?php foreach($displayData->periods as $date):?>
					<?php if(isset($item[$date->unix])): ?>				
						<td style="text-align: right; display:none" title="<?php echo $tooltip ?>" class="hasTooltip"><strong><?php echo $item[$date->unix]; ?></strong></td>
					<?php else:?>
						<td style="text-align: center; display:none">-</td>
					<?php endif;?>
				<?php endforeach;?>
			</tr>
			<?php $j = 2;?>
			<?php foreach((array) $displayData->regencyList as $regency_id => $regency):?>
				<tr>
					<td class="text-center">
						<strong><?php echo GTHelperNumber::toRoman($j); $j++; ?></strong>
					</td>
					<td>
						<strong><?php echo str_repeat('&nbsp;', 0).$regency; ?></strong>
					</td>
					<?php $item = (array) @$displayData->itemsRegency[$regency_id]; ?>
					<?php foreach($displayData->periods as $date):?>
						<?php if(isset($item[$date->unix])): ?>				
							<td style="text-align: right; display:none" title="<?php echo $regency ?>" class="hasTooltip"><strong><?php echo $item[$date->unix]; ?></strong></td>
						<?php else:?>
							<td style="text-align: center; display:none">-</td>
						<?php endif;?>
					<?php endforeach;?>
				</tr>
				<?php $k = 1;?>
				<?php if($displayData->showMarket):?>
					<?php foreach((array) $displayData->marketList[$regency_id] as $market_id => $market):?>
						<tr>
							<td class="text-center">
								<span><?php echo $k; $k++; ?></span>
							</td>
							<td>
								<span><?php echo str_repeat('&nbsp;', 6).$market; ?></span>
							</td>
							<?php $item = (array) @$displayData->itemsMarket[$market_id]; ?>
							<?php foreach($displayData->periods as $date):?>
								<?php if(isset($item[$date->unix])): ?>				
									<td style="text-align: right; display:none" title="<?php echo $regency ?>" class="hasTooltip"><?php echo $item[$date->unix]; ?></td>
								<?php else:?>
									<td style="text-align: center; display:none">-</td>
								<?php endif;?>
							<?php endforeach;?>
						</tr>
					<?php endforeach;?>
				<?php endif;?>
			<?php endforeach;?>
		</tbody>
	</table>
	<div class="text-center">
		<button type="button" class="table-prev btn btn-default"><i class="fa fa-chevron-left"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_LEFT');?></button>
		<button type="button" class="table-next btn btn-default"><?php echo JText::_('COM_GTPIHPS_TOOLBAR_RIGHT');?> <i class="fa fa-chevron-right"></i></button>
	</div>
<?php else:?>
	<div class="alert alert-warning text-center" role="alert">
		<i class="fa fa-warning" style="font-size: 8em"></i>
		<h3><?php echo JText::_('COM_GTPIHPS_REPORT_NO_DATA');?></h3>
		<?php echo JText::_('COM_GTPIHPS_REPORT_NO_DATA_DESC');?>
		<br/><br/>
	</div>
<?php endif;?>