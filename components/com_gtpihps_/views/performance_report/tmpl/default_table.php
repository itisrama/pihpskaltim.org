<?php
	$period = JHtml::date($this->state->get('filter.start_date'), 'd F Y').' - ';
	$period .= JHtml::date($this->state->get('filter.end_date'), 'd F Y');

	$countProvinces	= 0;
	$countRegencies	= 0;
	$countMarkets	= 0;
	foreach($this->items as $province) {
		$countProvinces++;
		foreach ($province->children as $regency) {
			$countRegencies ++;
			$countMarkets += $regency->count;
		}
	}

	$selectedProv = array_intersect_key($this->provinces, array_keys($this->items));
?>

<?php if($this->items):?>
	<div id="report-header">
		<div class="row-fluid clearfix">
			<div class="col-sm-12">
				<h3 style="margin-top:0"><?php echo JText::_('COM_GTPIHPS_HEADER_PERFORMANCE_REPORT')?></h3>
			</div>
			<div class="col-sm-3 col-md-2"><?php echo JText::_('COM_GTPIHPS_FIELD_PROVINCE') ?><div class="pull-right">:</div></div>
			<div class="col-sm-9 col-md-10"><?php echo implode(', ', $selectedProv);?></div>
			<div class="col-sm-3 col-md-2"><?php echo JText::_('COM_GTPIHPS_FIELD_PERIOD') ?><div class="pull-right">:</div></div>
			<div class="col-sm-9 col-md-10"><?php echo $period ?></div>
			<div class="col-sm-3 col-md-2"><?php echo JText::_('COM_GTPIHPS_FIELD_COUNTED_PERIOD') ?><div class="pull-right">:</div></div>
			<div class="col-sm-9 col-md-10"><?php echo sprintf(JText::_('COM_GTPIHPS_N_DAYS'), $this->dayCount) ?></div>
		</div>
		<hr/>
		<div class="row-fluid clearfix">
			<div class="col-sm-12">
				<h4 style="margin-top:0"><?php echo JText::_('COM_GTPIHPS_HEADER_PERFORMANCE_REPORT_META')?></h4>
			</div>
			<div class="col-sm-3 col-md-2"><?php echo JText::_('COM_GTPIHPS_FIELD_PROVINCE') ?><div class="pull-right">:</div></div>
			<div class="col-sm-9 col-md-10"><?php echo $countProvinces ?></div>
			<div class="col-sm-3 col-md-2"><?php echo JText::_('COM_GTPIHPS_FIELD_REGENCY') ?><div class="pull-right">:</div></div>
			<div class="col-sm-9 col-md-10"><?php echo $countRegencies ?></div>
			<div class="col-sm-3 col-md-2"><?php echo JText::_('COM_GTPIHPS_FIELD_MARKET') ?><div class="pull-right">:</div></div>
			<div class="col-sm-9 col-md-10"><?php echo $countMarkets ?></div>
		</div>
	</div>
	<hr/>
	<table id="report" class="table table-bordered table-condensed valign-top">
		<thead>
			<tr>
				<th class="text-center" style="width:45px"><?php echo JText::_('COM_GTPIHPS_FIELD_NUM') ?></th>
				<th class="text-center" style="width:auto"><?php echo JText::_('COM_GTPIHPS_FIELD_PROVINCE_PERFORMANCE') ?></th>
				<th class="text-center" style="width:auto">
					<i class="fa fa-thumbs-up"></i>
				</th>
				<th class="text-center" style="width:auto">
					<i class="fa fa-thumbs-down"></i>
				</th>
				<th class="text-center" style="width:auto"><?php echo JText::_('COM_GTPIHPS_FIELD_REGENCY_PERFORMANCE') ?></th>
				<th class="text-center" style="width:auto">
					<i class="fa fa-thumbs-up"></i>
				</th>
				<th class="text-center" style="width:auto">
					<i class="fa fa-thumbs-down"></i>
				</th>
				<th class="text-center" style="width:auto"><?php echo JText::_('COM_GTPIHPS_FIELD_MARKET_PERFORMANCE') ?></th>
				<th class="text-center" style="width:auto">Σ</th>
				<th class="text-center" style="width:auto">
					<i class="fa fa-thumbs-up"></i>
				</th>
				<th class="text-center" style="width:auto">
					<i class="fa fa-thumbs-down"></i>
				</th>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1;?>
			<?php foreach($this->items as $province):?>
			<?php $trClass = $i % 2 ? 'even' : 'odd';?>
			<tr class="<?php echo $trClass?>">
				<td class="text-center" rowspan="<?php echo $province->count?>">
					<?php echo $i; $i++;?>
				</td>
				<td class="text-left" rowspan="<?php echo $province->count?>">
					<strong><?php echo $province->name; $j++;?></strong></br>
					<small><?php echo $province->desc;?></small>
				</td>
				<td class="text-center" rowspan="<?php echo $province->count?>">
					<?php echo $province->ontime;?>%
				</td>
				<td class="text-center" rowspan="<?php echo $province->count?>">
					<?php echo $province->late;?>%
				</td>

				<?php $j = 0;?>
				<?php foreach($province->children as $regency):?>
					<?php if($j>0) echo '<tr class="'.$trClass.'">'?>
					<td class="text-left" rowspan="<?php echo $regency->count?>">
						<strong><?php echo $regency->name; $j++;?></strong></br>
						<small><?php echo $regency->desc;?></small>
					</td>
					<td class="text-center" rowspan="<?php echo $regency->count?>">
						<?php echo $regency->ontime;?>%
					</td>
					<td class="text-center" rowspan="<?php echo $regency->count?>">
						<?php echo $regency->late;?>%
					</td>

					<?php $k = 0;?>
					<?php foreach($regency->children as $market):?>
						<?php if($k>0) echo '<tr class="'.$trClass.'">'?>
						<td class="text-left">
						<?php echo $market->name; $k++;?>
						</td>
						<td class="text-center">
							<small><?php echo $market->desc;?></small>
						</td>
						<td class="text-center">
							<?php echo $market->ontime;?>%
						</td>
						<td class="text-center">
							<?php echo $market->late;?>%
						</td></tr>
					<?php endforeach;?>
				<?php endforeach;?>
			<?php endforeach;?>
		</tbody>
	</table>
<?php else:?>
	<div class="alert alert-warning text-center" role="alert">
		<i class="fa fa-warning" style="font-size: 8em"></i>
		<h3><?php echo JText::_('COM_GTPIHPS_REPORT_NO_DATA');?></h3>
		<?php echo JText::_('COM_GTPIHPS_REPORT_NO_DATA_DESC');?>
		<br/><br/>
	</div>
<?php endif;?>