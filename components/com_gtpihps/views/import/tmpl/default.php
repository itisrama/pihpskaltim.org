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
	<form action="<?php echo JRoute::_('index.php?option=com_gtpihps'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validation" enctype="multipart/form-data">		
		<?php echo GTHelperFieldset::renderEdit($this->form->getFieldset('main'), 1); ?>
		<div class="form-group ">
			<div class="col-sm-3"></div>
			<div class="col-sm-9">
				<button class="btn btn-primary" type="submit"><i class="fa fa-upload"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_IMPORT_FILE')?></button>
				<button class="btn btn-warning" type="reset"><i class="fa fa-upload"></i> <?php echo JText::_('COM_GTPIHPS_RESET')?></button>
			</div>
		</div>
		<input type="hidden" name="task" value="import.send" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
