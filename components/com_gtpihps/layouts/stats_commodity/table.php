<?php if($displayData->itemsProv): ?>
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
					<th width="85" style="text-align: center; vertical-align: middle; display:none; white-space:nowrap;"><?php echo $date->sdate; ?></th>
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
					<strong><?php echo strtoupper(JText::_('COM_GTPIHPS_FIELD_ALL_PROVINCES')) ?></strong>
				</td>
				<?php $item = (array) @$displayData->itemsAll[0]; ?>
				<?php foreach($displayData->periods as $date):?>
					<?php 
						$price		= @$item[$date->unix];
						$display	= $price > 0 ? $price : '-';
						$display	= $price > 0 ? '<div class="text-right">'.$display.'</div>' : '<div class="text-center">'.$display.'</div>';
					?>
					<td style="text-align: right; display:none" title="<?php echo JText::_('COM_GTPIHPS_FIELD_ALL_PROVINCES') ?>" class="hasTooltip"><strong><?php echo $display; ?></strong></td>
				<?php endforeach;?>
			</tr>
			<?php foreach($displayData->provinceList as $province_id => $province):?>
			<?php 
				$tooltip = ltrim($province, '&nbsp;');
				$regencies = (array) $displayData->regencyList[$province_id];
				$showMarket = $displayData->showMarket && count($regencies) > 0;
			?>
			<tr>
				<td class="text-center">
					<strong><?php echo GTHelperNumber::toRoman($i); $i++; ?></strong>
				</td>
				<td>
					<strong><?php echo strtoupper($province) ?></strong>
				</td>
				<?php $item = (array) @$displayData->itemsProv[$province_id]; ?>
				<?php foreach($displayData->periods as $date):?>
					<?php 
						$price		= @$item[$date->unix];
						$display	= $price > 0 ? $price : '-';
						$display	= '<strong>'.$display.'</strong>';
						$display	= $price > 0 ? '<div class="text-right">'.$display.'</div>' : '<div class="text-center">'.$display.'</div>';
					?>
					<td style="text-align: right; display:none" title="<?php echo $tooltip ?>" class="hasTooltip"><?php echo $display; ?></td>
				<?php endforeach;?>
			</tr>
				<?php $j = 1;?>
				<?php foreach($regencies as $regency_id => $regency):?>
					<tr>
						<td class="text-center">
							<strong><?php echo $j; $j++; ?></strong>
						</td>
						<td>
							<?php if($displayData->showMarket):?>
								<div style="margin-left:1em"><strong><?php echo $regency; ?></strong><div>
							<?php else:?>
								<div style="margin-left:1em"><?php echo $regency; ?><div>
							<?php endif;?>
						</td>
						<?php $item = (array) @$displayData->itemsReg[$regency_id]; ?>
						<?php foreach($displayData->periods as $date):?>
							<?php 
								$price		= @$item[$date->unix];
								$display	= $price > 0 ? $price : '-';
								$display	= $displayData->showMarket ? '<strong>'.$display.'</strong>' : $display;
								$display	= $price > 0 ? '<div class="text-right">'.$display.'</div>' : '<div class="text-center">'.$display.'</div>';
							?>
							<td style="text-align: right; display:none" title="<?php echo $regency ?>" class="hasTooltip"><?php echo $display; ?></td>
						<?php endforeach;?>
					</tr>
					<?php $k = a;?>
					<?php if($displayData->showMarket):?>
						<?php foreach((array) $displayData->marketList[$regency_id] as $market_id => $market):?>
							<tr>
								<td class="text-center">
									<span><?php echo $k; $k++; ?></span>
								</td>
								<td>
									<div style="margin-left:2em"><span><?php echo $market; ?></span></div>
								</td>
								<?php $item = (array) @$displayData->itemsMar[$market_id]; ?>
								<?php foreach($displayData->periods as $date):?>
									<?php 
										$price		= @$item[$date->unix];
										$display	= $price > 0 ? $price : '-';
										$display	= $price > 0 ? '<div class="text-right">'.$display.'</div>' : '<div class="text-center">'.$display.'</div>';
									?>
									<td style="text-align: right; display:none" title="<?php echo $market ?>" class="hasTooltip"><?php echo $display; ?></td>
								<?php endforeach;?>
							</tr>
						<?php endforeach;?>
					<?php endif;?>
				<?php endforeach;?>
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