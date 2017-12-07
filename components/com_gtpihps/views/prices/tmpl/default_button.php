<?php
$published = $this->state->get('filter.published');
?>
<div class="command form-inline">
	<?php if($this->canCreate):?>
		<button type="button" class="btn btn-success" onclick="Joomla.submitbutton('price.add')">
			<i class="fa fa-plus-circle"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_NEW')?>
		</button>
	<?php endif;?>

	<?php if($this->canEditState):?>
		<?php if($published != '1'):?>
			<button type="button" class="btn btn-default" onclick="submitbuttonlist('prices.publish')">
				<i class="fa fa-check"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_PUBLISH')?>
			</button>
		<?php endif;?>
		<?php if(!in_array($published, array('0', '-2', '2'))):?>
			<button type="button" class="btn btn-default" onclick="submitbuttonlist('prices.unpublish')">
				<i class="fa fa-times-circle"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_UNPUBLISH')?>
			</button>
		<?php endif;?>
		<?php if($published != '2'):?>
			<button type="button" class="btn btn-default" onclick="submitbuttonlist('prices.archive')">
				<i class="fa fa-inbox"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_ARCHIVE')?>
			</button>
		<?php endif;?>
	<?php endif;?>

	<div class="pull-right">
		<?php if($this->canDelete):?>
			<?php if($published == '-2'):?>
				<button type="button" class="btn btn-danger" onclick="submitbuttonlist('prices.delete')">
					<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_TRASH_PERMANENTLY')?>
				</button>
			<?php else:?>
				<button type="button" class="btn btn-danger" onclick="submitbuttonlist('prices.trash')">
					<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_TRASH')?>
				</button>
			<?php endif;?>
		<?php endif;?>
	</div>
</div>
