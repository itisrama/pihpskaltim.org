<?php 
// No direct access
defined('_JEXEC') or die('Restricted access');

$document = JFactory::getDocument();
$document->addStyleSheet(JURI::root(true).'modules/mod_gtpihps_widget_prices/css/gtpihps-theme.css');
$document->addStyleSheet(JURI::root(true).'modules/mod_gtpihps_widget_prices/css/gtpihps-widget.css');
$document->addStyleSheet(JURI::root(true).'modules/mod_gtpihps_widget_prices/css/font-awesome.min.css');

$component_url = JURI::base(true) . '/index.php?option=com_gtpihps';
$document->addScriptDeclaration("
	var date	= new Date();
	var months	= ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
					'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
					];

	var theme			= '".$theme."';
	var date			= date.getDate() + ' ' + months[date.getMonth()] + ' ' + date.getFullYear(); 
	var show_province	= ".($show_province ? 1 : 0).";
	var show_city		= ".($show_city ? 1 : 0).";
	var show_market		= ".($show_market ? 1 : 0).";
	var province		= '".$province."';
	var province_id		= ".$province_id.";
	var city			= '".$city."';
	var city_id			= ".$city_id.";
	var market			= '".$market."';
	var market_id		= ".$market_id.";
");

$document->addScript(JURI::root(true).'modules/mod_gtpihps_widget_prices/script.js'); 
?>

<div class="gtpihps-price">

<div class="cleanslate">
	<div class="gtpihps-pricetable <?php echo $theme; ?>">
		<h3 class="gtpihps-header">
			<b>Harga Rata-Rata dan Perubahan</b><small date="<?php echo $date; ?>"><?php echo $date; ?></small>
		</h3>
		<div class="gtpihps-filter">
			<?php if ($show_province == "true"): ?>
			<select class="province-id">
				<option value="0">Semua Provinsi</option>
			</select>
			<?php endif; ?>

			<?php if ($show_city == "true"): ?>
			<select class="city-id">
				<option value="0">Semua Kota/Kabupaten</option>
			</select>
			<?php endif; ?>

			<?php if ($show_market == "true"): ?>
			<select class="market-id">
				<option value="0">Semua Pasar</option>
			</select>
			<?php endif; ?>
		</div>
		<div class="gtpihps-items">
			<table class="gtpihps-table">
			</table>
		</div>
	</div>
</div>



</div>
