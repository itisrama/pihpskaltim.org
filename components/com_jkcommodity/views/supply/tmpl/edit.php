<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');
?>
<style>
	.row-strip > td{
		background-color: #bddcf1;
	}
</style>
<div id="com_jkcommodity" class="item-page<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<div class="page-header">
		<h1><?php echo $this->page_title . ' - ' . $this->item_title; ?></h1>
	</div>
	<?php endif; ?>
	<form action="<?php echo JRoute::_('index.php?option=com_jkcommodity'); ?>" method="post" name="adminForm" id="adminForm" class="form-horizontal form-validate">
		<?php if($this->buttons):?>
			<div class="well"><?php echo $this->buttons ?></div>
		<?php endif;?>

		<fieldset>
			<legend><?php echo JText::_('COM_JKCOMMODITY_FIELDSET_MAIN');?></legend>
			<?php echo JKHelper::showFieldsetEdit($this->form->getFieldset('main')); ?>
		</fieldset>
		<fieldset>
			<legend><?php echo JText::_('COM_JKCOMMODITY_FIELDSET_COMMODITIES_SUPPLY');?></legend>
			<table id="report" class="table table-custom-strip table-bordered table-condensed">
				<thead>
					<tr>
						<th style="text-align: center;vertical-align: middle; width: 60% !important;">
							<?php echo JText::_('COM_JKCOMMODITY_LABEL_COMMODITY') ?>
						</th>
						<th style="text-align: center;vertical-align: middle;">
							<?php echo JText::_('COM_JKCOMMODITY_LABEL_DENOMINATION'); ?>
						</th>
						<th></th>
						<th style="text-align: center;vertical-align: middle;">
							<?php echo JText::_('COM_JKCOMMODITY_LABEL_PREVIOUS_SUPPLY'); ?>
						</th>
						<th style="text-align: center;vertical-align: middle;">
							<?php echo JText::_('COM_JKCOMMODITY_LABEL_SUPPLY'); ?>
						</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach($this->commodity_list as $i => $commodity):?>
					<?php if(is_numeric($commodity->value)):?>
					<?php
						$production  = isset($this->last_supplies[$commodity->id])? $this->last_supplies[$commodity->id]->production  : null;
						$consumption = isset($this->last_supplies[$commodity->id])? $this->last_supplies[$commodity->id]->consumption : null;
						$traded 	 = isset($this->last_supplies[$commodity->id])? $this->last_supplies[$commodity->id]->traded : null;
						$even = ($i + 1) % 2 == 0;
					?>
					<!-- Commodity -->
					<tr <?php if($even) echo 'class="row-strip"'; ?>>
						<td rowspan="4"><?php echo $commodity->text; ?></td>
						<td rowspan="4" style="text-align: center;border-right: 1px solid #93c6e7">
							<?php echo $commodity->denomination ?>
						</td>
					</td>
					<tr <?php if($even) echo 'class="row-strip"'; ?>>
						<td><?php echo JText::_('COM_JKCOMMODITY_LABEL_PRODUCTION'); ?></td>
						<td style="text-align: right;">
							<!-- Prev production -->
							<span id="commodity_<?php echo $commodity->id; ?>_production" class="commodity_supplies">
								<?php echo $production ?>
							</span>
							<button class="btn btn-mini copy-supply" style="margin-left: 5px" type="button">
								<?php echo JText::_('COM_JKCOMMODITY_BUTTON_COPY');?> <i class="icon-chevron-right"></i>
							</button>
						</td>
						<td style="text-align: center;">
							<!-- Production -->
							<?php
							echo str_replace(
								array('_details', '[details]', 'value="0"'), 
								array(
									'_details_'.$commodity->value.'_production',
									'[details]['.$commodity->value.'][production]',
									'value="'.(@$this->item_detail[$commodity->value]->production > 0 ? $this->item_detail[$commodity->value]->production : '').'"'
								), 
								$this->form->getField('details')->input
							); ?>
							<button class="btn btn-mini clear-supply" style="margin-left: 5px" type="button">
								<i class="icon-remove"></i> <?php echo JText::_('COM_JKCOMMODITY_BUTTON_CLEAR');?>
							</button>
						</td>
					</tr>
					<tr <?php if($even) echo 'class="row-strip"'; ?>>
						<td><?php echo JText::_('COM_JKCOMMODITY_LABEL_CONSUMPTION'); ?></td>
						<td style="text-align: right;">
							<!-- Prev consumption -->
							<span id="commodity_<?php echo $commodity->id; ?>_consumption" class="commodity_supplies">
								<?php echo $consumption ?>
							</span>
							<button class="btn btn-mini copy-supply" style="margin-left: 5px" type="button">
								<?php echo JText::_('COM_JKCOMMODITY_BUTTON_COPY');?> <i class="icon-chevron-right"></i>
							</button>
						</td>
						<td style="text-align: center;">
							<!-- Consumption -->
							<?php
							echo str_replace(
								array('_details', '[details]', 'value="0"'), 
								array(
									'_details_'.$commodity->value.'_consumption',
									'[details]['.$commodity->value.'][consumption]',
									'value="'. (@$this->item_detail[$commodity->value]->consumption > 0 ? $this->item_detail[$commodity->value]->consumption : '') .'"'
								), 
								$this->form->getField('details')->input
							); ?>
							<button class="btn btn-mini clear-supply" style="margin-left: 5px" type="button">
								<i class="icon-remove"></i> <?php echo JText::_('COM_JKCOMMODITY_BUTTON_CLEAR');?>
							</button>
						</td>
					</tr>
					<tr <?php if($even) echo 'class="row-strip"'; ?>>
						<td><?php echo JText::_('COM_JKCOMMODITY_LABEL_TRADED'); ?></td>
						<td style="text-align: right;">
							<!-- Prev traded -->
							<span id="commodity_<?php echo $commodity->id; ?>_traded" class="commodity_supplies">
								<?php echo $traded ?>
							</span>
							<button class="btn btn-mini copy-supply" style="margin-left: 5px" type="button">
								<?php echo JText::_('COM_JKCOMMODITY_BUTTON_COPY');?> <i class="icon-chevron-right"></i>
							</button>
						</td>
						<td style="text-align: center;">
							<!-- Traded -->
							<?php
							$value 	= (@$this->item_detail[$commodity->value]->traded != 0 ? $this->item_detail[$commodity->value]->traded : '');
							$name 	= 'jform[details]['.$commodity->id.'][traded]';
							$htid	= 'jform_details_'.$commodity->id.'_traded';
							?>
							<input type="text" name="<?php echo $name; ?>" id="<?php echo $htid; ?>" value="<?php echo $value; ?>" class="input-small form-control" readonly="true" aria-invalid="false">
							<div class="btn-group" style="margin-left:8px">
								<!-- Trade detail modal button -->
								<a href="#trade_modal_<?php echo $commodity->id; ?>" role="button" class="btn btn-small" data-toggle="modal">
									<i class="icon-list-alt" style="margin:0px;"></i>
								</a>
								<button class="btn btn-small clear-trade" type="button" data-toggle="tooltip" title="<?php echo JText::_('COM_JKCOMMODITY_BUTTON_CLEAR');?>">
									<i class="icon-remove" style="margin:0px;"></i>
								</button>
							</div>
							
							<!-- Modal -->
							<div id="trade_modal_<?php echo $commodity->id; ?>" class="modal hide" role="dialog" aria-hidden="true" style="width:auto;top:10%;left:calc(100% - 740px);">
								<?php
									$tradeTypes = array(0 => JText::_('COM_JKCOMMODITY_LABEL_CITY_TRADE'), JText::_('COM_JKCOMMODITY_LABEL_PROVINCE_TRADE'));
								?>
								<div class="modal-header">
									<button type="button" class="close commit-trade" data-dismiss="modal" aria-hidden="true">×</button>
									<h3><?php echo JText::_('COM_JKCOMMODITY_BUTTON_TRADE_DETAILS');?> <?php echo $commodity->name;?></h3>
								</div>
								<div class="modal-body">
									<table class="table table-responsive">
										<thead>
											<tr>
												<th style="text-align:center;width:auto !important;">
													<?php echo JText::_('COM_JKCOMMODITY_LABEL_TRADE_TYPE'); ?>
												</th>
												<th style="text-align:center;">
													<?php echo JText::_('COM_JKCOMMODITY_LABEL_SOURCE_DESTINATION'); ?>
												</th>
												<th style="text-align:center;">
													<?php echo JText::_('COM_JKCOMMODITY_LABEL_TRADE_IN').' ('.$commodity->denomination.')'; ?>
												</th>
												<th style="text-align:center;">
													<?php echo JText::_('COM_JKCOMMODITY_LABEL_TRADE_OUT').' ('.$commodity->denomination.')'; ?>
												</th>
												<th style="text-align:center;">
													<?php echo JText::_('COM_JKCOMMODITY_LABEL_SURPLUS').'/'.
															JText::_('COM_JKCOMMODITY_LABEL_DEFICIT').' ('.$commodity->denomination.')'; ?>
												</th>
												<th></th>
											</tr>
										</thead>
										<tbody class="trades">
											<?php if(!empty($this->item_trades[$commodity->id])): ?>
												<?php foreach($this->item_trades[$commodity->id] as $j => $trade): ?>
												<?php $nprefix = 'trades.'.$commodity->id.'.'.$j; ?>
												<tr class="trade-row" data-number="<?php echo $j; ?>">
													<td>
														<?php echo JKHelperHTML::getSelect($nprefix.'.type', $tradeTypes, $trade->type); ?>
													</td>
													<td>
														<?php
															echo JKHelperHTML::getSelect($nprefix.'.partner_city_id',
																$this->cities,
																$trade->partner_city_id,
																($trade->type == 1)? 'display:none;' : ''.'width:100%;'
															);
															
															echo JKHelperHTML::getSelect($nprefix.'.partner_province_id',
																$this->provinces,
																$trade->partner_province_id,
																($trade->type == 0)? 'display:none;' : ''.'width:100%;'
															);
														?>
													</td>
													<td>
														<?php echo JKHelperHTML::getInput($nprefix.'.traded_in', 'text', $trade->traded_in); ?>
													</td>
													<td>
														<?php echo JKHelperHTML::getInput($nprefix.'.traded_out', 'text', $trade->traded_out); ?>
													</td>
													<td><input type="text" class="input-small inputbox trade-net" readonly="true" value="<?php echo $trade->traded_in - $trade->traded_out; ?>"></td>
													<td>
														<button class="btn btn-small delete-trade" type="button" data-toggle="tooltip" title="Hapus">
															<i class="icon-remove" style="margin:0px;"></i>
														</button>
													</td>
												</tr>
												<?php endforeach; ?>
											<?php else: ?>
												<tr>
													<td colspan="6" style="text-align:center;">
														-- <?php echo JText::_('COM_JKCOMMODITY_TEXT_NO_DATA'); ?> --
													</td>
												</tr>
											<?php endif; ?>
										</tbody>
									</table>
								</div>
								<div class="modal-footer">
									<button class="btn btn-success add-trade" data-commodity="<?php echo $commodity->id; ?>">
										<i class="icon-plus"></i> <?php echo JText::_('COM_JKCOMMODITY_BUTTON_ADD_TRADE'); ?>
									</button>
									<button class="btn btn-primary commit-trade" data-dismiss="modal" aria-hidden="true">
										<i class="icon-ok"></i> <?php echo JText::_('COM_JKCOMMODITY_BUTTON_DONE'); ?>
									</button>
								</div>
							</div>
						</td>
					</tr>
					<?php else:?>
					<tr <?php if(($i + 1) % 2 == 0) echo 'class="row-strip"'; ?>>
						<td colspan="5" style="height: 30px"><strong><?php echo $commodity->text ?></strong></td>
					</tr>
					<?php endif;?>
					<?php endforeach;?>
				</tbody>
			</table>
		</fieldset>

		<br/>
		<?php if($this->buttons):?>
			<div class="well"><?php echo $this->buttons ?></div>
		<?php endif;?>
			
		<input type="hidden" name="id" value="<?php echo isset($this->item->id) ? $this->item->id : 0 ?>" />
		<input type="hidden" name="task" value="" />
		<?php echo JHtml::_('form.token'); ?>
	</form>
</div>

<script>
var provinces = [];

<?php foreach($this->provinces as $id => $name): ?>
provinces[<?php echo $id; ?>] = "<?php echo $name; ?>";
<?php endforeach; ?>

var cities = [];
<?php foreach($this->cities as $id => $name): ?>
cities[<?php echo $id; ?>] = "<?php echo $name; ?>";
<?php endforeach; ?>

</script>

