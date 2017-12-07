<div id="progress" class="progress" style="display:none">
	<div class="progress-bar progress-bar-striped active" role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
		<span class="percent" style="display:none"><span></span>% Complete</span>
	</div>
</div>
<table id="report" class="table table-striped table-bordered table-condensed" style="display:none">
	<thead>
		<tr>
			<th class="text-center" width="45px"><?php echo JText::_('COM_GTPIHPS_FIELD_NUM') ?></th>
			<th class="text-center" width="35%"><?php echo JText::_('COM_GTPIHPS_FIELD_PROVINCE') ?></th>
			<th class="text-center" width="80px"><?php echo JText::_('COM_GTPIHPS_FIELD_DATE') ?></th>
			<th class="text-center" valign="top"><?php echo JText::_('COM_GTPIHPS_FIELD_STATUS') ?></th>
		</tr>
	</thead>
	<tbody class="template" style="display:none">
		<tr>
			<td class="text-center num" ></td>
			<td class="province"></td>
			<td class="date text-center"></td>
			<td class="status"></td>
		</tr>
	</tbody>
	<tbody class="result">
		
	</tbody>
</table>

<div id="alert" class="alert alert-warning text-center" role="alert">
	<i class="fa fa-warning" style="font-size: 8em"></i>
	<h3><?php echo JText::_('COM_GTPIHPS_REPORT_NO_DATA');?></h3>
	<?php echo JText::_('COM_GTPIHPS_REPORT_NO_DATA_DESC');?>
	<br/><br/>
</div>
