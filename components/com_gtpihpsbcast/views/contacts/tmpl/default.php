<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$ordering	= $this->escape($this->state->get('list.ordering'));
$direction	= $this->escape($this->state->get('list.direction'));

?>

<div id="com_gtpihpsbcast" class="contact-page<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_page_heading', 1)): ?>
	<div class="page-header">
		<h1><?php echo $this->page_title; ?></h1>
	</div>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_gtpihpsbcast'); ?>" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $ordering; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $direction; ?>" />
		<?php echo JHtml::_('form.token'); ?>

		<?php if(!$this->user->guest):?>
			<?php echo $this->loadTemplate('button'); ?>
		<?php endif;?>
		<hr/>
		<?php echo $this->loadTemplate('form'); ?>
		<br/>
		<div><table class="adminlist table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th width="1%">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('COM_GTPIHPSBCAST_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="5%" class="text-center">
						<?php echo GTHelperHTML::gridSort('JGRID_HEADING_ID', 'a.id', $ordering, $direction); ?>
					</th>
					<th class="text-center">
						<?php echo GTHelperHTML::gridSort('COM_GTPIHPSBCAST_FIELD_NAME', 'a.name', $ordering, $direction); ?>
					</th>
					<th width="20%"  class="text-center">
						<?php echo GTHelperHTML::gridSort('COM_GTPIHPSBCAST_FIELD_COMPANY', 'a.company', $ordering, $direction); ?>
					</th>
					<th width="89px" class="text-center">
						<?php echo GTHelperHTML::gridSort('COM_GTPIHPSBCAST_FIELD_PHONE', 'a.phone', $ordering, $direction); ?>
					</th>
					<th width="89px" class="text-center">
						<?php echo GTHelperHTML::gridSort('COM_GTPIHPSBCAST_FIELD_EMAIL', 'b.email', $ordering, $direction); ?>
					</th>
					<th width="83px" class="text-center">
						<?php echo JText::_('COM_GTPIHPSBCAST_ACTION'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if (!$this->items): ?>
					<tr class="row0">
						<td class="text-center" colspan="8">
							<?php echo JText::_('COM_GTPIHPSBCAST_NO_DATA'); ?>
						</td>
					</tr>
				<?php else: foreach ($this->items as $i => $item): ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td class="text-center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td class="text-center">
							<?php echo $item->id; ?>
						</td>
						<td>
							<a title="<?php echo JText::_('COM_GTPIHPSBCAST_TOOLBAR_EDIT') ?>" href="<?php echo JRoute::_($this->editUrl . (int)$item->id); ?>">
								<?php echo $item->name;?>
							</a>
						</td>
						<td>
							<?php echo $item->company;?>
						</td>
						<td class="text-center">
							<?php echo $item->phone; ?>
						</td>
						<td class="text-center">
							<?php echo $item->email; ?>
						</td>
						<td class="text-center">
							<a title="<?php echo JText::_('COM_GTPIHPSBCAST_TOOLBAR_EDIT') ?>" href="<?php echo JRoute::_($this->editUrl . (int)$item->id); ?>" class="btn btn-default btn-xs"><i class="fa fa-edit"></i></a>
						</td>
					</tr>
				<?php endforeach; endif; ?>
			</tbody>
		</table></div>

		<div class="text-center">
			<div style="display:inline-block">
				<?php echo $this->pagination->getListFooter(); ?>
			</div>
		</div>
	</form>
</div>
