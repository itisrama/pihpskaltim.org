<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.multiselect');
?>

<div id="com_gtpihps" class="item-page<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_page_heading', 1)): ?>
	<div class="page-header">
		<h1><?php echo $this->page_title; ?></h1>
	</div>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_gtpihps'); ?>" method="post" name="adminForm" id="adminForm" role="form">
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="filter_regency_ids[]" value="0" />
		<input type="hidden" name="filter_market_ids[]" value="0" />
		<input type="hidden" name="filter_all_commodities" value="0" />
		<input type="hidden" name="boxchecked" value="0" />
		<input type="hidden" name="filter_order" value="<?php echo $this->ordering; ?>" />
		<input type="hidden" name="filter_order_Dir" value="<?php echo $this->direction; ?>" />
		<input type="hidden" name="province_id" value="<?php echo $this->state->get('filter.province_id'); ?>" />
		<input type="hidden" name="region_id" value="<?php echo $this->state->get('filter.region_id'); ?>" />
		<?php echo JHtml::_('form.token'); ?>

		<?php if(!$this->user->guest):?>
			<?php echo $this->loadTemplate('button'); ?>
		<?php endif;?>

		<div class="row">
			<div class="col-md-3">
				<?php echo $this->loadTemplate('form'); ?>
			</div>
			<div class="col-md-9">
				<?php echo $this->loadTemplate('table'); ?>
			</div>
		</div>
	</form>
</div>
