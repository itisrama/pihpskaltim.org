<?php

// No direct access
defined('_JEXEC') or die('Restricted access');

JHtml::addIncludePath(JPATH_COMPONENT . '/helpers/html');
?>

<div id="com_gtpihps" class="widget item-page<?php echo $this->params->get('pageclass_sfx'); ?>">
	<h3><?php echo sprintf(JText::_('COM_GTPIHPS_FIELDSET_WIDGET'), $this->location) ?></h3>
	<table class="table table-condensed">
		<tbody>
		<?php foreach ($this->commodityList as $commodity):
			$price = $commodity->type == 'commodity' ? $this->items[$commodity->id] : $this->itemsCat[$commodity->id];
			switch ($price->status) {
				case 'up':
					$status = '<i class="fa fa-arrow-up" style="color:#c0392b"></i>';
					break;
				case 'down':
					$status = '<i class="fa fa-arrow-down" style="color:#27ae60"></i>';
					break;
				default:
					$status = '<i class="fa fa-exchange" style="color:#2980b9"></i>';
					break;
			}
		?>
		<tr class="<?php echo $commodity->type ?>">
			<?php if(!$price->current) continue;?>
			<td class="name"><div style="margin-left:<?php echo $commodity->level?>em"><?php echo $commodity->name?></div></td>
			<td class="price text-right"><?php echo $price->current?></td>
			<td class="denom text-left">/ <?php echo $commodity->denomination?></td>
			<td class="flucs spark text-center"><?php echo $price->flucs?></td>
			<td class="status text-center"><?php echo $status?></td>
		</tr>
		<?php endforeach;?>
		</tbody>
	</table>
	<footer>
		<a href="http://hargapangan.id"><img src="images/logo/site/logoinv.png" height="20px"/><span>hargapangan.id</span></a>
		<div class="pull-right"><?php echo JHtml::date(reset($this->dates), 'd F Y')?></div>
	</footer>
</div>
