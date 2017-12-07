<?php 
// No direct access
defined('_JEXEC') or die('Restricted access');

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root(true).'modules/mod_gtpihps_widget_commodities/css/gtpihps-theme.css');
$document->addStyleSheet(JURI::root(true).'modules/mod_gtpihps_widget_commodities/css/gtpihps-widget.css');
$document->addStyleSheet(JURI::root(true).'modules/mod_gtpihps_widget_commodities/css/font-awesome.min.css');

$component_url = JURI::base(true) . '/index.php?option=com_gtpihps';
$document->addScriptDeclaration("
	var date	= new Date();
	var months	= ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
					'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
					];

	var theme			= '".$theme."';
	var date			= date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear(); 
	var show_province	= ".$show_province.";
	var show_category	= ".$show_category.";
	var show_commodity	= ".$show_commodity.";
	var province		= '".$province."';
	var province_id		= ".$province_id.";
	var category		= '".$category."';
	var category_id		= ".$category_id.";
	var commodity		= '".$commodity."';
	var commodity_id	= ".$commodity_id.";
");


$document->addScript(JURI::root(true).'modules/mod_gtpihps_widget_commodities/jquery.sparkline.min.js');
$document->addScript(JURI::root(true).'modules/mod_gtpihps_widget_commodities/script.js'); 
?>

<div class="gtpihps-commodity">

<div class="cleanslate">
	<div class="gtpihps-pricelist <?php echo $theme; ?>">
		<h3 class="gtpihps-header">
			<b>Harga Rata-Rata dan Perubahan</b><small class="gtpihps-date"><?php echo $date; ?></small>
			<span class="slidecontrol">
				<i class="gtpihps-up fa fa-chevron-up" style="cursor: pointer"></i>
				<div class="gtpihps-down fa fa-chevron-down" style="cursor: pointer"></div>
			</span>
		</h3>
		<div class="gtpihps-filter">
			<?php if ($show_province == "true"): ?>
			<select class="province-id">
				<option value="0">Semua Provinsi</option>
			</select>
			<?php endif; ?>

			<?php if ($show_category == "true"): ?>
			<select class="category-id">
				<option value="0">Semua Kategori</option>
			</select>
			<?php endif; ?>

			<?php if ($show_commodity == "true"): ?>
			<select class="commodity-id">
				<option value="0">Semua Komoditas</option>
			</select>
			<?php endif; ?>

		</div>
		<div class="gtpihps-items">
			<input type="hidden" class="gtpihps-count-up" value=""/>
			<input type="hidden" class="gtpihps-count-down" value=""/>
			<input type="hidden" class="gtpihps-count-still" value=""/>

			<ul class="gtpihps-template">
				<li>
					<a commodityID="" on-click="updateFilter('category')">
						<div>
							<div class="gtpihps-title"></div>
							<div class="gtpihps-spark"></div>
							<div class="gtpihps-desc">
								<div class="gtpihps-price-now"><span></span><div></div></div>
								<div class="gtpihps-price-desc">
									<i class=""></i>
									<span></span>
								</div>
							</div>
						</div>
					</a>
				</li>
			</ul>
			<ul class="gtpihps-list-all"></ul>
			<ul class="gtpihps-list-price-still"></ul>
			<ul class="gtpihps-list-price-up"></ul>
			<ul class="gtpihps-list-price-down"></ul>
			
		</div>
	</div>
</div>

</div>
