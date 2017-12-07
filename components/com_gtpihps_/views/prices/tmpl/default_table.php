<table class="adminlist table table-striped table-bordered table-hover">
	<thead>
		<tr>
			<th width="1%">
				<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('COM_GTPIHPS_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
			</th>
			<th width="45px" class="text-center">
				<?php echo GTHelperHTML::gridSort('JGRID_HEADING_ID', 'a.id', $this->ordering, $this->direction); ?>
			</th>
			<th class="text-center">
				<?php echo GTHelperHTML::gridSort('COM_GTPIHPS_FIELD_REGENCY', 'b.name', $this->ordering, $this->direction); ?>
			</th>
			<th class="text-center">
				<?php echo GTHelperHTML::gridSort('COM_GTPIHPS_FIELD_MARKET', 'c.name', $this->ordering, $this->direction); ?>
			</th>
			<th class="text-center">
				<?php echo GTHelperHTML::gridSort('COM_GTPIHPS_CREATED_DATE', 'a.created', $this->ordering, $this->direction); ?>
			</th>
			<th class="text-center">
				<?php echo GTHelperHTML::gridSort('COM_GTPIHPS_FIELD_DATE', 'a.date', $this->ordering, $this->direction); ?>
			</th>
			<th width="83px" class="text-center">
				<?php echo JText::_('COM_GTPIHPS_ACTION'); ?>
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
		<?php else: foreach ($this->items as $i => $item): ?>
			<tr>
				<td class="text-center">
					<?php echo JHtml::_('grid.id', $i, $item->id); ?>
				</td>
				<td class="text-center">
					<?php echo $item->id; ?>
				</td>
				<td>
					<?php echo $item->regency;?>
				</td>
				<td>
					<?php echo $item->market;?>
				</td>
				<td class="text-center">
					<?php echo JHtml::date($item->created, 'd-m-Y H:i');?>
				</td>
				<td class="text-center">
					<?php echo JHtml::date($item->date, 'd-m-Y');?>
				</td>
				<td class="text-center">
					<div class="btn-group">
						<a title="<?php echo JText::_('COM_GTPIHPS_TOOLBAR_EDIT') ?>" href="<?php echo JRoute::_(sprintf($this->editUrl, $item->id)); ?>" class="btn btn-default btn-sm"><i class="fa fa-edit"></i></a>
					</div>
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