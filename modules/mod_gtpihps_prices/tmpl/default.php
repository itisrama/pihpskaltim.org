<?php
// No direct access
defined('_JEXEC') or die('Restricted access');
?>
<div id="pricelist-<?php echo $module->id?>" class="pricelist">
	<h3>Harga Rata-Rata dan Perubahan <small id="pricelist_date" class="date" date=""></small>&nbsp;&nbsp;<?php echo JHtml::_('select.genericlist', $regencies, 'pricelist_regency_id', 'style="margin:0"');?>
		<?php //echo JHtml::_('select.genericlist', $priceTypes, 'pricelist_price_type_id', 'style="margin:0"');?>
		&nbsp;&nbsp;<span class="slidecontrol"><i class="fa fa-chevron-up" style="cursor: pointer"></i>  <i class="fa fa-chevron-down" style="cursor: pointer"></i></span>
	</h3>
	<input type="hidden" id="up_count" value=""/>
	<input type="hidden" id="down_count" value=""/>
	<input type="hidden" id="still_count" value=""/>

	<ul class="pricelist template" style="display:none">
		<li><a href="javascript:void(0)" commodityID=""><div>
			<div class="title"></div>
			<div class="spark"></div>
			<div class="desc">
				<div class="price_now"><span></span><div></div></div>
				<div class="price_desc">
					<i class=""></i>&nbsp;
					<span></span>
				</div>
			</div>
		</div></a></li>
	</ul>
	<ul class="pricelist list_all"></ul>
	<ul class="pricelist list_price_still" style="display:none"></ul>
	<ul class="pricelist list_price_up" style="display:none"></ul>
	<ul class="pricelist list_price_down" style="display:none"></ul>
</div>