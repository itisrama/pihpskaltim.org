<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

$width = round(700 / (count($this->markets) * 1));

JHtml::_('behavior.tooltip');
?>
<div id="com_jkcommodity" class="item-page<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
		<div class="page-header">
			<h1> <?php echo $this->escape($this->params->get('page_heading')); ?></h1>
		</div>
	<?php endif; ?>

	<img class="videotron-logo" src="<?php echo $this->logo1?>" />
	<div class="title">
		<h3 style="text-align:center; margin:0">
			<?php echo JText::_('COM_JKCOMMODITY_HEADER_REPORT2')?>
		</h3>
		<h1 style="text-align:center; margin:0 0 20px">
			<?php echo $this->city->name?> 
		</h1>
	</div>
	<img class="videotron-logo" src="<?php echo $this->logo2?>" />

	<table id="videotronTable" class="table table-striped table-bordered">
		<thead>
			<tr>
				<th style="text-align:center"><?php echo JText::_('COM_JKCOMMODITY_LABEL_COMMODITY'); ?></th>
				<?php foreach ($this->markets as $market):?>
					<th style="text-align:center; width:<?php echo $width;?>px;"><?php echo $market->short_name?></th>
				<?php endforeach;?>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($this->items as $columns):?>
				<tr>
				<?php foreach ($columns as $k => $column):?>
					<?php $align = $k > 0 ? 'right' : 'left';?>
					<td style="text-align:<?php echo $align?>;"><span><?php echo $column ?></span></td>
				<?php endforeach;?>
				</tr>
			<?php endforeach;?>
		</tbody>
	</table>

	<input id="priceData" type="hidden" value='<?php echo $this->json ?>' />
	<input id="pricePage" type="hidden" value="0" />

	<div id="runningText">
		<?php foreach ($this->news as $news):?>
			<?php echo '&nbsp;&nbsp&nbsp;-&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;' . JHtml::date($news->created, 'd/m') . '&nbsp;&nbsp;' .  trim($news->title);?>
		<?php endforeach;?>
	</div>
	<div id="videotronFooter">
		<?php echo sprintf(JText::_('COM_JKCOMMODITY_PT_PRICE_UPDATE'), JHtml::date($this->date->now, 'd F Y')); ?>
	</div>
	
	
</div>
