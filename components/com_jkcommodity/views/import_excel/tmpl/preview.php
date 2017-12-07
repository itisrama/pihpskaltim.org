<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
?>

<div id="com_gtpihps" class="item-page<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1><?php echo $this->page_title; ?></h1>
	</div>
	<?php endif; ?>
	<?php echo $this->loadTemplate('table'); ?>
	<form action="<?php echo JRoute::_('index.php?option=com_jkcommodity'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validation" enctype="multipart/form-data">		
		<input type="hidden" name="json" value="<?php echo $this->json?>" />
		<input type="hidden" name="city_id" value="<?php echo $this->city->id?>" />
		<input type="hidden" name="date" value="<?php echo JHtml::date($this->data->date, 'Y-m-d'); ?>" />
		<input type="hidden" name="task" value="" />
		<input type="hidden" name="layout" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
