<?php 
	$publishedOptions = JHtml::_('jgrid.publishedOptions');
	if(!$this->user->authorise('core.admin')) {
		unset($publishedOptions[2]);
	}
?>
<div class="form-inline">
	<div class="form-group">
		<div class="input-group input-xlarge">
			<input class="form-control" name="filter_search" type="text" value="<?php echo $this->escape($this->state->get('filter.search')); ?>"  placeholder="<?php echo JText::_('COM_GTPIHPSBCAST_FIELD_FILTER_SEARCH') ?>" id="filter_search">
			<div class="input-group-btn">
				<button class="btn btn-default" onclick="document.getElementById('filter_search').value='';this.form.submit();">
					<i class="fa fa-times"></i>
				</button>
				<button class="btn btn-info" type="submit">
					<i class="fa fa-search"></i> <?php echo JText::_('COM_GTPIHPSBCAST_FIND')?>
				</button>
			</div>
		</div>
	</div>
	<div class="pull-right">
		<div class="form-group">
			<label for="limit"><?php echo JText::_('JGLOBAL_DISPLAY_NUM'); ?>&#160;</label>
			<?php echo $this->pagination->getLimitBox(); ?>
		</div>
		<div class="form-group">
			<select name="filter_published" class="inputbox" onchange="this.form.submit()" style="width:auto">
				<option><?php echo JText::_('JOPTION_SELECT_PUBLISHED');?></option>
				<?php echo JHtml::_('select.options', $publishedOptions, 'value', 'text', $this->state->get('filter.published'), true);?>
			</select>
		</div>
	</div>
</div>