<table id="report" class="table table-striped table-bordered table-condensed">
	<thead>
		<tr>
			<!--<th width="1%">
				<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('COM_GTPIHPS_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
			</th>-->
			<th width="45px" class="text-center">
				<?php echo JText::_('COM_GTPIHPS_FIELD_NUM'); ?>
			</th>
			<th width="25%" class="text-center">
				<?php echo GTHelperHTML::gridSort('COM_GTPIHPS_FIELD_PROVINCE', 'b.name', $this->ordering, $this->direction); ?>
			</th>
			<th width="75px" class="text-center">
				<?php echo GTHelperHTML::gridSort('COM_GTPIHPS_FIELD_DATE', 'a.date', $this->ordering, $this->direction); ?>
			</th>
			<th class="text-center">
				<?php echo GTHelperHTML::gridSort('COM_GTPIHPS_FIELD_STATUS', 'a.status', $this->ordering, $this->direction); ?>
			</th>
			<th width="115px" class="text-center">
				<?php echo GTHelperHTML::gridSort('COM_GTPIHPS_CREATED_DATE', 'a.created', $this->ordering, $this->direction); ?>
			</th>
		</tr>
	</thead>
	<tbody>
		<?php if (!$this->items): ?>
			<tr>
				<td class="text-center" colspan="8">
					<?php echo JText::_('COM_GTPIHPS_NO_DATA'); ?>
				</td>
			</tr>
		<?php else: $num = 0; foreach ($this->items as $i => $item): ?>
			<tr>
				<!--<td class="text-center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>-->
				<td class="text-center">
					<?php echo $i+$this->state->get('list.start')+1; ?>
				</td>
				<td>
					<strong><?php echo $item->province;?></strong>
				</td>
				<td class="text-center">
					<?php echo $item->date;?>
				</td>
				<td class="status">
					<span class="label label-<?php echo $item->status?>">
						<?php echo $item->icon;?>
					</span>
					<span style="display:inline-block; vertical-align:middle; max-width:90%; line-height:normal">
						<small><strong><?php echo $item->url;?></strong></small><br/>
						<small><?php echo $item->log;?></small>
					</span>
				</td>
				<td class="text-center">
					<?php echo $item->created;?>
				</td>
			</tr>
		<?php endforeach; endif; ?>
	</tbody>
</table>
<div class="text-center">
	<div style="display:inline-block">
		<?php echo $this->pagination->getListFooter(); ?>
	</div>
</div>