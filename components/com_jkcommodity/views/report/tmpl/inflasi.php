
<?php
// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::_('behavior.keepalive');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.calendar');
JHtml::_('behavior.formvalidation');

$session = JFactory::getSession();
$context = JRequest::getVar('option') . '.' . JRequest::getVar('view');

$db = JFactory::getDBO();
$query = "SELECT id AS `value`, ref AS `text` FROM " . $db->nameQuote('#__jkcommodity_inflasi_ref') . " WHERE `legend`='1' ORDER BY id ASC";
$rows = $db->setQuery($query);
$row = $rows->loadObjectList();
?>
<div id="com_jkcommodity" class="item-page<?php echo $this->params->get('pageclass_sfx'); ?>">
	<?php if ($this->params->get('show_page_heading', 1)) : ?>
		<div class="page-header">
			<h1> <?php echo $this->escape($this->params->get('page_heading')); ?> </h1>
		</div>
	<?php endif; ?>
	<form action="<?php echo JRoute::_('index.php?option=com_jkcommodity&view=report&layout=inflasi'); ?>" method="post" name="adminForm" id="adminForm" class="form-vertical">
		<fieldset>
			<legend>Filter</legend>
			<div class="jkcolumn">
				<div>
					<div class="control-group">
						<div class="controls">
							<?php echo JHtml::_('select.genericlist', $row, 'ref_id', 'size="10" multiple="multiple" style="width:350px"', 'value', 'text', true);?>
						</div>
					</div>
				</div>
				<div>
					<div class="control-group">
						<label title="Output" class="hasTip" for="inflasiOutput" id="inflasiOutput-lbl" aria-invalid="false">Output</label>
						<div class="controls">
						<select name="inflasiOutput" id="inflasiOutput" style="width: auto;">
							<option value="mtm">mtm</option>
							<option value="yoy">yoy</option>
							<option value="ytd">ytd</option>
						</select>
						</div>
					</div>
					<div class="control-group">
						<label title="Mulai" class="hasTip" for="inflasiBulanMulai" id="inflasiBulanMulai-lbl" aria-invalid="false">Mulai</label>
						<div class="controls">
						<select name="inflasiBulanMulai" id="inflasiBulanMulai" style="width: auto;">
							<option value="0">Bulan</option>
							<?php for ($i=1;$i<=12;$i++): ?>
							<option value="<?php echo $i ?>"><?php echo $i ?></option>
							<?php endfor; ?>
						</select>
						<select name="inflasiTahunMulai" id="inflasiTahunMulai" style="width: auto;">
							<option value="0">Tahun</option>
							<?php for ($i=date('Y');$i>=2011;$i--): ?>
							<option value="<?php echo $i ?>"><?php echo $i ?></option>
							<?php endfor; ?>
						</select>
						</div>
					</div>
					<div class="control-group">
						<label title="Sampai" class="hasTip" for="inflasiBulanSelesai" id="inflasiBulanSelesai-lbl" aria-invalid="false">Sampai</label>
						<div class="controls">
						<select name="inflasiBulanSelesai" id="inflasiBulanSelesai" style="width: auto;">
							<option value="0">Bulan</option>
							<?php for ($i=1;$i<=12;$i++): ?>
							<option value="<?php echo $i ?>"><?php echo $i ?></option>
							<?php endfor; ?>
						</select>
						<select name="inflasiTahunSelesai" id="inflasiTahunSelesai" style="width: auto;">
							<option value="0">Tahun</option>
							<?php for ($i=date('Y');$i>=2011;$i--): ?>
							<option value="<?php echo $i ?>"><?php echo $i ?></option>
							<?php endfor; ?>
						</select>
						</div>
					</div>
				</div>
			</div>
			<div style="text-align: center">
				<button class="btn btn-primary" type="button" onclick="updateImg();">Cari</button>
			</div>
			<!--
			<span>Output</span>
			<select name="inflasiOutput" id="inflasiOutput" style="width: auto;">
				<option value="mtm">mtm</option>
				<option value="yoy">yoy</option>
				<option value="ytd">ytd</option>
			</select>
			<br /><br />
			<span>Mulai</span>
			<select name="inflasiBulanMulai" id="inflasiBulanMulai" style="width: auto;">
				<option value="0">Bulan</option>
				<?php for ($i=1;$i<=12;$i++): ?>
				<option value="<?php echo $i ?>"><?php echo $i ?></option>
				<?php endfor; ?>
			</select>
			<select name="inflasiTahunMulai" id="inflasiTahunMulai" style="width: auto;">
				<option value="0">Tahun</option>
				<?php for ($i=date('Y');$i>=2011;$i--): ?>
				<option value="<?php echo $i ?>"><?php echo $i ?></option>
				<?php endfor; ?>
			</select>
			<span style="margin-left: 20px;">Sampai</span>
			<select name="inflasiBulanSelesai" id="inflasiBulanSelesai" style="width: auto;">
				<option value="0">Bulan</option>
				<?php for ($i=1;$i<=12;$i++): ?>
				<option value="<?php echo $i ?>"><?php echo $i ?></option>
				<?php endfor; ?>
			</select>
			<select name="inflasiTahunSelesai" id="inflasiTahunSelesai" style="width: auto;">
				<option value="0">Tahun</option>
				<?php for ($i=date('Y');$i>=2011;$i--): ?>
				<option value="<?php echo $i ?>"><?php echo $i ?></option>
				<?php endfor; ?>
			</select>
			<br /><br />
			<input onclick="updateImg();" type="button" name="cari" value="Cari" />
			-->
		</fieldset>
	</form>
	
	<fieldset>
		<img id="inflasi" src="<?php echo JURI::base() . 'components/com_jkcommodity/lib/yoy.php?inflasiOutput=yoy&inflasiBulanMulai='.date('m').'&inflasiTahunMulai='. (date('Y')-1) . '&refid=1' ?>" alt="Chart" />
	</fieldset>
</div>

<script language="javascript">
var clicks = 500;

function updateImg()
{
	var doc = document.getElementById("inflasi");
	var inflasiOutput = document.getElementById("inflasiOutput");
	var inflasiOutputValue = inflasiOutput.options[inflasiOutput.selectedIndex].value;
	var inflasiBulanMulai = document.getElementById("inflasiBulanMulai");
	var inflasiBulanMulaiValue = inflasiBulanMulai.options[inflasiBulanMulai.selectedIndex].value;
	var inflasiTahunMulai = document.getElementById("inflasiTahunMulai");
	var inflasiTahunMulaiValue = inflasiTahunMulai.options[inflasiTahunMulai.selectedIndex].value;
	var inflasiBulanSelesai = document.getElementById("inflasiBulanSelesai");
	var inflasiBulanSelesaiValue = inflasiBulanSelesai.options[inflasiBulanSelesai.selectedIndex].value;
	var inflasiTahunSelesai = document.getElementById("inflasiTahunSelesai");
	var inflasiTahunSelesaiValue = inflasiTahunSelesai.options[inflasiTahunSelesai.selectedIndex].value;
	
	var refid = document.getElementById("ref_id");
	var result = [];
	var options = refid && refid.options;
	var opt;

	for (var i=0, iLen=options.length; i<iLen; i++) {
		opt = options[i];

		if (opt.selected) {
			result.push(opt.value || opt.text);
		}
	}
	
	switch (inflasiOutputValue) {
		case 'mtm':
			doc.src = "<?php echo JURI::base() . 'components/com_jkcommodity/lib/mtm.php' ?>"
					+ "?inflasiOutput=" + inflasiOutputValue
					+ "&inflasiBulanMulai=" + inflasiBulanMulaiValue
					+ "&inflasiTahunMulai=" + inflasiTahunMulaiValue
					+ "&inflasiBulanSelesai=" + inflasiBulanSelesaiValue
					+ "&inflasiTahunSelesai=" + inflasiTahunSelesaiValue
					+ "&refid=" + result;
			break;
		case 'yoy':
			doc.src = "<?php echo JURI::base() . 'components/com_jkcommodity/lib/yoy.php' ?>"
					+ "?inflasiOutput=" + inflasiOutputValue
					+ "&inflasiBulanMulai=" + inflasiBulanMulaiValue
					+ "&inflasiTahunMulai=" + inflasiTahunMulaiValue
					+ "&inflasiBulanSelesai=" + inflasiBulanSelesaiValue
					+ "&inflasiTahunSelesai=" + inflasiTahunSelesaiValue
					+ "&refid=" + result;
			break;
		case 'ytd':
			doc.src = "<?php echo JURI::base() . 'components/com_jkcommodity/lib/ytd.php' ?>"
					+ "?inflasiOutput=" + inflasiOutputValue
					+ "&inflasiBulanMulai=" + inflasiBulanMulaiValue
					+ "&inflasiTahunMulai=" + inflasiTahunMulaiValue
					+ "&inflasiBulanSelesai=" + inflasiBulanSelesaiValue
					+ "&inflasiTahunSelesai=" + inflasiTahunSelesaiValue
					+ "&refid=" + result;
			break;
		default:
			doc.src = "<?php echo JURI::base() . 'components/com_jkcommodity/lib/mtm.php' ?>"
					+ "?inflasiOutput=" + inflasiOutputValue
					+ "&inflasiBulanMulai=" + inflasiBulanMulaiValue
					+ "&inflasiTahunMulai=" + inflasiTahunMulaiValue
					+ "&inflasiBulanSelesai=" + inflasiBulanSelesaiValue
					+ "&inflasiTahunSelesai=" + inflasiTahunSelesaiValue
					+ "&refid=" + result;
			break;
	}
}
</script>
