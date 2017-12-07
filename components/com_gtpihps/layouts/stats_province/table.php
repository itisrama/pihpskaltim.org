<?php if($displayData->itemsCom || $displayData->itemsCat):?>
	<div id="report-header">
		<h4><?php echo JText::_('COM_GTPIHPS_HEADER_REPORT')?></h4>
		<div><span><?php echo JText::_('COM_GTPIHPS_FIELD_PERIOD') ?></span> : <?php echo $displayData->period ?></div>
		<div><span><?php echo JText::_('COM_GTPIHPS_FIELD_PROVINCE') ?></span> : <?php echo $displayData->provinces ? $displayData->provinces : JText::_('COM_GTPIHPS_ALL_PROVINCES'); ?></div>
		<div><span><?php echo JText::_('COM_GTPIHPS_FIELD_REGENCY') ?></span> : <?php echo $displayData->regencies ? $displayData->regencies : JText::_('COM_GTPIHPS_ALL_REGENCIES'); ?></div>
		<div><span><?php echo JText::_('COM_GTPIHPS_FIELD_MARKET') ?></span> : <?php echo $displayData->markets ? $displayData->markets : JText::_('COM_GTPIHPS_ALL_MARKETS'); ?></div>
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
				<th style="text-align: center; vertical-align: middle;"><?php echo JText::_('COM_GTPIHPS_FIELD_COMMODITY') ?> (<?php echo trim(GTHelperCurrency::$symbol); ?>)</th>
				<?php foreach($displayData->periods as $date):?>
					<th style="width:90px; text-align: center; vertical-align: middle; display:none; white-space:nowrap;"><?php echo $date->sdate; ?></th>
				<?php endforeach;?>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1;?>
			<?php $j = 1;?>
			<?php foreach($displayData->commodityList as $commodity):?>
			<?php $tooltip = ltrim($commodity->text, '&nbsp;');?>
			<tr>
				<td class="text-center">
					<?php if($commodity->type == 'category'):?>
						<span><strong><?php echo GTHelperNumber::toRoman($i); $i++; $j = 1; ?></strong></span>
					<?php elseif($commodity->type == 'commodity'):?>
						<span><?php echo $j; $j++; ?></span>
					<?php endif;?>
				</td>
				<td>
					<div style="margin-left:<?php echo $commodity->level?>em">
						<?php if($commodity->type == 'category'):?>
							<strong><?php echo $commodity->name ?></strong>
						<?php elseif($commodity->type == 'commodity'):?>
							<span><?php echo $commodity->name ?></span>
						<?php endif;?>
					</div>
				</td>
				<?php if($commodity->type == 'category') {
					$item = $displayData->itemsCat[$commodity->id];
				} elseif($commodity->type == 'commodity') {
					$item = $displayData->itemsCom[$commodity->id];
				}
				?>
				<?php foreach($displayData->periods as $date):?>
					<?php 
						$price		= @$item[$date->unix];
						$display	= $price > 0 ? $price : '-';
						$display	= $commodity->type == 'commodity' ? $display : '<strong>'.$display.'</strong>';
						$display	= $price > 0 ? '<div class="text-right">'.$display.'</div>' : '<div class="text-center">'.$display.'</div>';
					?>
					<?php if($price > 0): ?>				
						<td style="text-align: right; display:none" title="<?php echo $tooltip ?>" class="hasTooltip" data-placement="top"><?php echo $display; ?></td>
					<?php else:?>
						<td style="text-align: center; display:none"><?php echo $display; ?></td>
					<?php endif;?>
				<?php endforeach;?>
			</tr>
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