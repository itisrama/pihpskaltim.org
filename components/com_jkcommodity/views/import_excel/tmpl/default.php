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
	<form action="<?php echo JRoute::_('index.php?option=com_jkcommodity'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validation" enctype="multipart/form-data">		
		<fieldset>
			<?php echo JKHelper::showFieldsetEdit($this->form->getFieldset('main')); ?>
			<hr/>
			<div class="control-group">
				<div class="control-label"></div>
				<div class="controls">
					<button class="btn btn-primary" type="submit"><i class="icon-upload"></i> <?php echo JText::_('COM_JKCOMMODITY_TOOLBAR_IMPORT_FILE')?></button>
					<button class="btn btn-warning" type="reset"><i class="icon-refresh"></i> <?php echo JText::_('COM_JKCOMMODITY_BUTTON_RESET')?></button>
				</div>
			</div>
		</fieldset>	

		<input type="hidden" name="layout" value="preview" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>
