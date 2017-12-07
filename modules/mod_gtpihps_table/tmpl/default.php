<div id="mod_gtpihps_table-<?php echo $module->id?>" class="mod_gtpihps_table">
	<h3><?php echo JText::_('MOD_GTPIHPS_TABLE_H3')?> <small><?php echo JHtml::date($date, 'j F Y'); ?></small></h3>
	<div class="wrapper"><table id="report" class="table table-striped table-bordered table-condensed">
		<thead>
			<tr>
				<th style="width:45px;"><?php echo JText::_('MOD_GTPIHPS_TABLE_FIELD_NUM') ?></th>
				<th><?php echo JText::_('MOD_GTPIHPS_TABLE_FIELD_PROVINCE') ?> (<?php echo trim(GTHelperCurrency::$symbol); ?>)</th>
				<?php foreach($commodities as $commodity):?>
					<th style="width:20%; display:none;"><span><?php echo $commodity->name; ?></span></th>
				<?php endforeach;?>
			</tr>
		</thead>
		<tbody>
			<?php $i = 1;?>
			<?php foreach($provinces as $province):?>
			<tr>
				<td style="text-align: center;">
					<span><?php echo $i; $i++; ?></span>
				</td>
				<td>
					<span><?php echo $province->name ?></span>
				</td>
				<?php $item = isset($items[$province->id]) ? $items[$province->id] : array() ?>
				<?php foreach($commodities as $commodity):?>
					<?php if(isset($item[$commodity->id])): ?>
						<?php
							$price = $item[$commodity->id];
							$tooltip = '<strong>'.ltrim(strtoupper($province->name), '&nbsp;') . ' #' . $price->rank . '</strong><br/>'.$commodity->name.' : '.$price->price . '/' . $commodity->denomination;
						?>				
						<td style="text-align: right; display:none;" title="<?php echo $tooltip ?>" class="hasTooltip rank rank<?php echo $price->rank?>"><?php echo $price->price; ?></td>
					<?php else:?>
						<td style="text-align: center; display:none;">-</td>
					<?php endif;?>
				<?php endforeach;?>
			</tr>
			<?php endforeach;?>
		</tbody>
	</table></div>
	<div class="text-center">
		<button type="button" class="table-prev btn btn-default"><i class="fa fa-chevron-left"></i> <?php echo JText::_('MOD_GTPIHPS_TABLE_TOOLBAR_LEFT');?></button>
		<button type="button" class="table-up btn btn-default"><i class="fa fa-chevron-up"></i> <?php echo JText::_('MOD_GTPIHPS_TABLE_TOOLBAR_UP');?></button>
		<button type="button" class="table-down btn btn-default"><i class="fa fa-chevron-down"></i> <?php echo JText::_('MOD_GTPIHPS_TABLE_TOOLBAR_DOWN');?></button>
		<button type="button" class="table-next btn btn-default"><?php echo JText::_('MOD_GTPIHPS_TABLE_TOOLBAR_RIGHT');?> <i class="fa fa-chevron-right"></i></button>
	</div>
</div>