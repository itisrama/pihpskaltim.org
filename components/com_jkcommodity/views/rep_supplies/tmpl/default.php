<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>
<style>
	.table-center > thead > tr > th,
	.table-center > tbody > tr > td{
		text-align: center;
	}
	
	.surplus > td{
		color: #fff;
		background-color: #7fb348;
		border-color: #fff;
	}
	
	.deficit > td{
		color: #fff;
		background-color: #db6255;
		border-color: #fff;
	}
</style>
<div id="com_jkcommodity" class="item-page<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1><?php echo $this->page_title; ?></h1>
	</div>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_jkcommodity&view=supplies'); ?>" method="post" name="adminForm" id="adminForm" class="form-vertical">
		<fieldset class="filter open">
			<legend style="cursor: pointer">Filter</legend>
			<div class="container-fluid row-fluid">
				<div class="span4">
					<div class="control-group">
						<label for="filter_city_id" class="control-label">
							<?php echo JText::_('COM_JKCOMMODITY_LABEL_CITY')?>
						</label>
						<div class="controls">
							<?php echo JHtml::_('select.genericlist', $this->cities, 'filter_city_id[]', 'size="6" multiple="multiple"', 'value', 'text', $this->state->get('filter.city_id'));?>
						</div>
					</div>
				</div>
				<div class="span3">
					<div class="control-group">
						<label for="filter_start_date" class="control-label">
							<?php echo JText::_('COM_JKCOMMODITY_LABEL_DATE_START')?>
						</label>
						<div class="controls">
							<?php echo JHtml::_('calendar', $this->state->get('filter.start_date'), 'filter_start_date', 'filter_start_date', '%d-%m-%Y', array('style' => 'width:auto'));?>
						</div>
					</div>
					<div class="control-group">
						<label for="filter_end_date" class="control-label">
							<?php echo JText::_('COM_JKCOMMODITY_LABEL_DATE_END')?>
						</label>
						<div class="controls">
							<?php echo JHtml::_('calendar', $this->state->get('filter.end_date'), 'filter_end_date', 'filter_end_date', '%d-%m-%Y', array('style' => 'width:auto'));?>
						</div>
					</div>
				</div>
			</div>
			<hr/>
			<div style="text-align: center">
				<button class="btn btn-primary" type="submit"><?php echo JText::_('COM_JKCOMMODITY_BUTTON_SHOW_DATA') ?></button>
				<button class="btn btn-warning" type="reset" ><?php echo JText::_('COM_JKCOMMODITY_BUTTON_RESET') ?></button>
			</div>
		</fieldset>
		<!-- CONTENT -->
		<div class="row">
			<?php if(!empty($this->items)):
				$indo_months = array("Januari", "Februari", "Maret", "April", "Mei", "Juni",
									 "Juli", "Agustus","September", "Oktober", "November", "Desember");

				foreach($this->items as $item): ?>
					<div style="float:left;margin-right:15px;width:250px;">
						<div>
							<h4><?php echo $item->city; ?></h4>
							<h5><?php echo $item->commodity.' per '.$indo_months[$item->month-1].' '.$item->year; ?></h5>
						</div>
						<table class="table table-center">
							<thead>
								<tr>
									<th colspan="3"><?php echo JText::_('COM_JKCOMMODITY_LABEL_COMMODITY'); ?></th>
								</tr>
								<tr>
									<th><?php echo JText::_('COM_JKCOMMODITY_LABEL_PRODUCTION'); ?></th>
									<th><?php echo JText::_('COM_JKCOMMODITY_LABEL_CONSUMPTION'); ?></th>
									<th><?php echo JText::_('COM_JKCOMMODITY_LABEL_TRANSPORTED'); ?></th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td><?php echo $item->production; ?></td>
									<td><?php echo $item->consumption; ?></td>
									<td><?php echo $item->transported; ?></td>
								</tr>
								<?php
									$net = $item->production - ($item->consumption + $item->transported);
								?>
								<tr class="<?php echo ($net < 0)? 'deficit' : 'surplus'; ?>">
									<?php if($net < 0): ?>
										<td colspan="3"><?php echo JText::_('COM_JKCOMMODITY_LABEL_DEFICIT'); ?></td>
									<?php else: ?>
										<td colspan="3"><?php echo JText::_('COM_JKCOMMODITY_LABEL_SURPLUS'); ?></td>
									<?php endif; ?>
								</tr>
								<tr class="<?php echo ($net < 0)? 'deficit' : 'surplus'; ?>">
									<td colspan="3"><?php echo $net; ?></td>
								</tr>
							</tbody>
						</table>
					</div>
				<?php endforeach; ?>
			<?php else: ?>
				<div class="alert alert-warning" role="alert"><?php echo JText::_('COM_JKCOMMODITY_TEXT_NO_DATA'); ?></div>
			<?php endif; ?>
		</div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
