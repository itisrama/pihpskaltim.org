<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT.'/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');

$listOrder = $this->escape($this->state->get('list.ordering'));
$listDirn = $this->escape($this->state->get('list.direction'));
?>

<div id="com_jkcommodity" class="item-page<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1><?php echo $this->page_title; ?></h1>
	</div>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_jkcommodity&view=supplies'); ?>" method="post" name="adminForm" id="adminForm" class="form-vertical">
		<?php if($this->buttons):?>
			<div class="well">
				<?php echo $this->buttons ?>
			</div>
		<?php endif;?>
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
		<div class="clearfix">
			<div class="filter-search btn-group pull-left">
				<input type="text" title="<?php echo JText::_('COM_JKCOMMODITY_FIELD_FILTER_SEARCH_DESC')?>" value="<?php echo $this->escape($this->state->get('filter.search')); ?>" placeholder="<?php echo JText::_('COM_JKCOMMODITY_FIELD_FILTER_SEARCH_DESC')?>" id="filter_search" name="filter_search" class="input-xlarge">
			</div>
			<div class="btn-group pull-left">
				<button type="submit" class="btn hasTooltip" data-original-title="Search"><i class="icon-search"></i></button>
				<button onclick="document.id('filter_search').value='';this.form.submit();" type="button" class="btn hasTooltip" data-original-title="Clear"><i class="icon-remove"></i></button>
			</div>

			<div class="pull-right form-search">
				<div style="display:inline-block;">
					<span style="margin-bottom:5px"><?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;</span>
					<?php echo str_replace('size="1"', 'style="width:auto"', $this->pagination->getLimitBox()) ?>
				</div>
				<?php if($this->canDo->get('core.edit.state')):?>
				<select name="filter_published" class="inputbox" onchange="this.form.submit()" style="width:auto">
					<option value=""><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
					<?php echo JHtml::_('select.options', JHtml::_('jgrid.publishedOptions'), 'value', 'text', $this->state->get('filter.published'), true);?>
				</select>
				<?php endif;?>
			</div>
		</div>
		<table class="adminlist table table-striped table-bordered table-hover">
			<thead>
				<tr>
					<th width="1%">
						<input type="checkbox" name="checkall-toggle" value="" title="<?php echo JText::_('COM_JKCOMMODITY_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
					</th>
					<th width="17%" style="text-align:center">
						<?php echo JHtml::_('grid.sort', 'COM_JKCOMMODITY_FIELD_CITY_LABEL', 'ct.name', $listDirn, $listOrder); ?>
					</th>
					<th width="17%" style="text-align:center">
						<?php echo JHtml::_('grid.sort', 'COM_JKCOMMODITY_FIELD_DATE_LABEL', 'pc.date', $listDirn, $listOrder); ?>
					</th>
					<!--
					<th width="15%" style="text-align:center">
						<?php echo JHtml::_('grid.sort', 'JAUTHOR', 'us.name', $listDirn, $listOrder); ?>
					</th>
					-->
					<th width="5%" style="text-align:center">
						<?php echo JHtml::_('grid.sort', 'JGRID_HEADING_ID', 'pc.id', $listDirn, $listOrder); ?>
					</th>
					<th width="5%" style="text-align:center">
						<?php echo JText::_('COM_JKCOMMODITY_ACTION'); ?>
					</th>
				</tr>
			</thead>
			<tbody>
				<?php if(!count($this->items)) :?>
					<tr class="row0">
						<td style="text-align:center" colspan="8">
							<?php echo JText::_('COM_JKCOMMODITY_NO_DATA');?>
						</td>
					</tr>
				<?php endif;?>
				<?php foreach ($this->items as $i => $item) : ?>
					<?php $editUrl = JRoute::_('index.php?option=com_jkcommodity&task=supply.edit&id='.(int) $item->id); ?>
					<tr class="row<?php echo $i % 2; ?>">
						<td style="text-align:center">
							<?php echo JHtml::_('grid.id', $i, $item->id); ?>
						</td>
						<td>
							<a href="<?php echo $editUrl; ?>">
								<?php echo $this->escape($item->city); ?>
							</a>
						</td>
						<td style="text-align:center">
							<a href="<?php echo $editUrl; ?>">
								<?php echo JFactory::getDate($item->date)->format('d/m/Y'); ?>
							</a>
						</td>
						<!--
						<td style="text-align:center">
							<?php echo $item->author; ?>
						</td>
						-->
						<td style="text-align:center">
							<?php echo $item->id; ?>
						</td>
						<td style="text-align:center">
							<div class="btn-group">
								<a title="<?php echo JText::_('COM_JKCOMMODITY_TOOLBAR_EDIT')?>" href="<?php echo $editUrl ?>" class="btn btn-small"><i class="icon-edit"></i></a>
							</div>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<div class="pagination">
			<?php echo $this->pagination->getListFooter(); ?>
		</div>
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $listOrder; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $listDirn; ?>" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
