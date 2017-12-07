<div class="command form-inline">
	<?php if($this->isNew):?>
		<?php if($this->canCreate):?>
			<button type="button" class="btn btn-success" onclick="submitbutton('price.apply')">
				<i class="fa fa-save"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_APPLY')?>
			</button>
			<button type="button" class="btn btn-default" onclick="submitbutton('price.save')">
				<i class="fa fa-check"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_SAVE')?>
			</button>
			<button type="button" class="btn btn-default" onclick="submitbutton('price.save2new')">
				<i class="fa fa-plus"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_SAVE_AND_NEW')?>
			</button>
		<?php endif;?>
		<div class="pull-right">
			<button type="button" class="btn btn-warning" onclick="submitform('price.cancel')">
				<i class="fa fa-times-circle"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_CLOSE')?>
			</button>
		</div>
	<?php else:?>
		<?php if(!$this->checkedOut && $this->canEdit):?>
			<button type="button" class="btn btn-success" onclick="submitbutton('price.apply')">
				<i class="fa fa-save"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_APPLY')?>
			</button>
			<button type="button" class="btn btn-default" onclick="submitbutton('price.save')">
				<i class="fa fa-check"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_SAVE')?>
			</button>
			<button type="button" class="btn btn-default" onclick="submitbutton('price.save2new')">
				<i class="fa fa-plus"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_SAVE_AND_NEW')?>
			</button>
		<?php endif;?>
		<div class="pull-right">
			<button type="button" class="btn btn-warning" onclick="submitform('price.cancel')">
				<i class="fa fa-times-circle"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_CLOSE')?>
			</button>
			<?php if($this->isTrashed  && $this->canDelete):?>
				<button type="button" class="btn btn-danger" onclick="submitbuttonDelete('prices.deleteList')">
					<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_TRASH_PERMANENTLY')?>
				</button>
			<?php elseif($this->canEditState):?>
				<button type="button" class="btn btn-danger" onclick="submitbuttonDelete('prices.trash')">
					<i class="fa fa-trash-o"></i> <?php echo JText::_('COM_GTPIHPS_TOOLBAR_TRASH')?>
				</button>
			<?php endif;?>
		</div>
	<?php endif;?>
</div>