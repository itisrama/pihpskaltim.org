<div class="command form-inline">
	<?php if($this->canCreate):?>
		<button type="button" class="btn btn-success" onclick="Joomla.submitbutton('contact.add')">
			<i class="fa fa-plus-circle"></i> <?php echo str_replace('%s', JText::_('COM_GTPIHPSBCAST_PT_CONTACT'), JText::_('COM_GTPIHPSBCAST_PT_NEW'))?>
		</button>
	<?php endif;?>

	<?php if($this->canEditState):?>
		<button type="button" class="btn btn-default" onclick="submitbuttonlist('contacts.publish')">
			<i class="fa fa-check"></i> <?php echo JText::_('COM_GTPIHPSBCAST_TOOLBAR_PUBLISH')?>
		</button>
			<button type="button" class="btn btn-default" onclick="submitbuttonlist('contacts.unpublish')">
			<i class="fa fa-times-circle"></i> <?php echo JText::_('COM_GTPIHPSBCAST_TOOLBAR_UNPUBLISH')?>
		</button>
		<?php if($this->user->authorise('core.admin')):?>
			<button type="button" class="btn btn-default" onclick="submitbuttonlist('contacts.archive')">
				<i class="fa fa-inbox"></i> <?php echo JText::_('COM_GTPIHPSBCAST_TOOLBAR_ARCHIVE')?>
			</button>
		<?php endif;?>
	<?php endif;?>

	<div class="pull-right">
		<?php if($this->canDelete):?>
			<?php if($this->state->get('filter.published') == -2):?>
				<button type="button" class="btn btn-danger" onclick="submitbuttonlist('contacts.delete')">
					<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_GTPIHPSBCAST_TOOLBAR_TRASH_PERMANENTLY')?>
				</button>
			<?php else:?>
				<button type="button" class="btn btn-danger" onclick="submitbuttonlist('contacts.trash')">
					<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_GTPIHPSBCAST_TOOLBAR_TRASH')?>
				</button>
			<?php endif;?>
		<?php endif;?>
	</div>
</div>