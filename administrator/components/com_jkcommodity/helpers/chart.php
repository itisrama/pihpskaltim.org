<?php

/**
 * @package		Joomlaku Component
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class JKHelperChart {

	static function loadChart($id, $data, $commodity, $options)
	{
		self::loadLibrary();
		$dataSlice = array_slice($data, 0, 1);
		$dates = $data ? array_keys(array_shift($dataSlice)): array();
		$chart_height = count($data) <= 7 ? '295px' : (25 * count($data)) + 120 . 'px';
		$data = self::setData($data, $commodity, $options);

		$options = (object) $options;
		$options->start_date = isset($options->start_date) ? $options->start_date : array_shift($dates) * 1000;
		$options->end_date = isset($options->end_date) ? $options->end_date : array_pop($dates) * 1000;

		$legend_button = isset($options->show_legend) && $options->show_legend == 2;
		$show_tooltip = isset($options->show_tooltip) && $options->show_tooltip == 1;
		$enable_zoom = isset($options->enable_zoom) && $options->enable_zoom == 1;
		$zoom_buttons = isset($options->zoom_buttons) && $options->zoom_buttons == 1;
		$enable_pan = isset($options->enable_pan) && $options->enable_pan == 1;
		$pan_buttons = isset($options->pan_buttons) && $options->pan_buttons == 1;
		$download_button = TRUE;

		$not_money = isset($options->not_money) && $options->not_money;
		$options = self::setOptions($options);

		$js_chart_init = "
			var data_$id = $data;
			var options_$id = $options;
			var plot_$id = $.plot($('#$id'), data_$id, options_$id);
		";

		$yformatter = $not_money ? "y + '%'" : 'toCurrency(y.toFixed(0))';

		$js_chart_tooltip = $show_tooltip ? "
			function showTooltip_$id(x, y, contents) {
				$('<div id=tooltip-$id>' + contents + '</div>').css( {
					position: 'absolute',
					display: 'none',
					top: y + 5,
					left: x + 5,
					border: '1px solid #333',
					padding: '2px 4px',
					'background-color': '#555',
					opacity: 0.80,
					zIndex: 200,
					color: '#fff',
					fontWeight: 'bold',
					fontSize: '80%',
					borderRadius: '3px'
				}).appendTo('body').fadeIn('normal');
			}
			var previousPoint_$id = null;
			$('#$id').bind('plothover', function (event, pos, item) {
				if (item) {
					if (previousPoint_$id != item.dataIndex) {
						previousPoint_$id = item.dataIndex;

						$('#tooltip-$id').remove();
						var x = item.datapoint[0].toFixed(0),
							y = item.datapoint[1];
						var d = new Date();
						d.setTime(x);
						var xaxis = item.series.xaxis;
						var xformatter = xaxis.tickFormatter;
						showTooltip_$id(item.pageX, item.pageY, item.series.label + ' (' + xformatter(d, xaxis) +') : ' + $yformatter);
					}
				}
				else {
					$('#tooltip-$id').fadeOut('slow', function() { $(this).remove(); });
					previousPoint_$id = null;
				}
			});
		" : NULL;

		$js_chart_zoom_button = $enable_zoom && $zoom_buttons ? "
			$('#chart-zoomout-$id').click(function(e){
				e.preventDefault();
				plot_$id.zoomOut();
			});
			$('#chart-zoomin-$id').click(function(e){
				e.preventDefault();
				plot_$id.zoom();
			});
		" : NULL;

		$js_chart_pan_button = $enable_pan && $pan_buttons ? "
			$('#chart-left-$id').click(function(e){
				e.preventDefault();
				plot_$id.pan({left:-100});
			});
			$('#chart-right-$id').click(function(e){
				e.preventDefault();
				plot_$id.pan({left:100});
			});
		" : NULL;

		$js_chart_legend_button = $legend_button ? "
			$('#chart-toggle-legend-$id').click(function(){
				$('#$id .legend').toggle();
			});
		" : NULL;

		$js_chart_download_button = $download_button ? "
			$('#chart-download-$id').click(function(){
				var obj = $('#$id');

				$('.legend', obj).css('fontFamily', 'Arial');
				$('.legend', obj).css('fontSize', '9pt');
				$('.legend table', obj).css('position', 'static');
				$('.legend table', obj).appendTo('.legend > div', obj);
				$('.legend > div', obj).css('width', 'auto');
				$('.legend > div', obj).css('height', 'auto');

				html2canvas(obj, {
					onrendered: function(canvas){
						var dataURL = canvas.toDataURL('image/png');
						window.open(dataURL);
					}
				});
			});

		" : NULL;

		$document = JFactory::getDocument();
		$document->addScriptDeclaration("
			(function($) {
				$(function() {
					$js_chart_init
					$js_chart_tooltip
					$js_chart_zoom_button
					$js_chart_pan_button
					$js_chart_legend_button
					$js_chart_download_button

					function toCurrency(num) {
						return jQuery('<div>'+num+'</div>').formatCurrency({region:'custom'}).html();
					}
				});
			})(jQuery);
		");

		$output = sprintf('<div id="%s" style="width:%s;height:%s;"></div>', $id, '100%', $chart_height);
		$output .= '<div style="text-align: center; margin-top: 10px;">';
		$output .= $enable_zoom && $zoom_buttons ? sprintf('
			<button type="button" id="chart-zoomin-%s" class="btn"><i class="icon-zoom-in"></i> Zoom In</button>
			<button type="button" id="chart-zoomout-%s" class="btn"><i class="icon-zoom-out"></i> Zoom Out</button>
		', $id, $id) : NULL;
		$output .= $enable_pan && $pan_buttons ? sprintf('
			<button type="button" id="chart-left-%s" class="btn"><i class="icon-chevron-left"></i> Left</button>
			<button type="button" id="chart-right-%s" class="btn">Right <i class="icon-chevron-right"></i></button>
		', $id, $id) : NULL;
		$output .= $legend_button ? sprintf('
			<button type="button" id="chart-toggle-legend-%s" class="btn"><i class="icon-th-list"></i> Toggle Legend</button>
		', $id) : NULL;
		$output .= $download_button ? sprintf('
			<button type="button" id="chart-download-%s" class="btn"><i class="icon-download-alt"></i> Download Chart</button>
			<form id="cdownload-%s" action="index.php" method="post">
				<input type="hidden" name="option" value="com_jkcommodity" />
				<input type="hidden" name="option" value="com_jkcommodity" />
			</form>
		', $id, $id) : NULL;
		$output .= '</div>';

		return $output;
	}

	static function loadLibrary()
	{
		$document = JFactory::getDocument();
		$component_assets_uri = JURI::root( true ) . '/administrator/components/com_jkcommodity/assets';
		$document->addScript( $component_assets_uri . '/js/jquery.formatCurrency.js' );
		$document->addScript( $component_assets_uri . '/js/jquery.flot.js' );
		$document->addScript( $component_assets_uri . '/js/jquery.flot.time.js' );
		$document->addScript( $component_assets_uri . '/js/jquery.flot.navigate.js' );
		$document->addScript( $component_assets_uri . '/js/html2canvas.min.js' );
	}

	static function setData($data, $commodity, $options)
	{
		foreach ($data as $commodity_id => $item)
		{
			$row = array();
			foreach ($item as $date => $price)
			{
				if(isset($options->not_money) && $options->not_money) {
					$row[] = array($date * 1000, $price);
				} else {
					$row[] = array($date * 1000, round($price));
				}

			}
			$data[$commodity_id] = array('label' => isset($commodity[$commodity_id]->name) ? $commodity[$commodity_id]->name : $commodity[$commodity_id], 'data' => $row);

		}
		$data = json_encode(array_values($data));
		return $data;
	}

	static function setOptions($options)
	{
		$min_range = 3 * 24 * 60 * 60 * 1000;
		$max_range = $options->end_date - $options->start_date;

		if(isset($options->show_tooltip) && $options->show_tooltip == 1)
		{
			$opt['series']['lines']['show'] = TRUE;
			$opt['series']['points']['show'] = TRUE;
			$opt['grid']['hoverable'] = TRUE;
		}

		$opt['legend']['show'] = isset($options->show_legend) && in_array($options->show_legend, array(1,2));

		$opt['xaxis']['mode'] = 'time';
		$opt['xaxis']['timeformat'] = isset($options->date_format) ? $options->date_format : '%d %b %y';
		$opt['xaxis']['min'] = $options->start_date;
		$opt['xaxis']['max'] = $options->end_date;
		$opt['xaxis']['panRange'] = isset($options->enable_pan) && $options->enable_pan == 1 ? array($options->start_date, $options->end_date) : FALSE;
		$opt['xaxis']['zoomRange'] = isset($options->enable_zoom) && $options->enable_zoom == 1 ? array($min_range, $max_range) : FALSE;
		if(!(isset($options->show_xaxis) && $options->show_xaxis == 1)) {
			$opt['xaxis']['tickFormatter'] = "*function () { return '' }*";
		}


		$opt['yaxis']['panRange'] = FALSE;
		$opt['yaxis']['zoomRange'] = FALSE;
		$not_money = isset($options->not_money) && $options->not_money;
		if(!$not_money) {
			$opt['yaxis']['mode'] = 'money';
			$opt['yaxis']['tickFormatter'] = isset($options->show_yaxis) && $options->show_yaxis == 1 ? "*function (v, axis) { return toCurrency(v.toFixed(axis)) + ' ' }*" : "*function () { return '' }*";
		} else {
			$opt['yaxis']['mode'] = 'number';
		}

		$opt['pan']['interactive'] = isset($options->enable_pan) && $options->enable_pan == 1;
		$opt['zoom']['interactive'] = isset($options->enable_zoom) && $options->enable_zoom == 1;

		$opt = json_encode($opt);
		$opt = str_replace(array('"*', '*"'), '', $opt);
		return $opt;
	}

}
