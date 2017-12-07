<?php
/**
 * @package		JK Commodity
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

require_once( JK_ADMIN_HELPERS . DS . 'chart.php' );
require_once( JK_ADMIN_HELPERS . DS . 'map.php' );
require_once( JK_ADMIN_HELPERS . DS . 'date.php' );

class JKCommodityViewReport extends JKView
{
	function display($tpl = null)
	{
		$document = $this->document;
		// Load JS & CSS
		JText::script('COM_JKCOMMODITY_CONFIRM_CITY_CHANGE');
		$document->addScript( JK_JS . '/script_report.js' );

		// Set variables
		$this->return_url	= base64_encode(JURI::getInstance()->toString());
		$layout = JRequest::getCmd('layout', 'default');
		// Set Dates
		$times = array();
		$period = array();
		// Set Time
		$latest_date = $this->get('LatestDate');
		$start_date_unix = strtotime(JRequest::getVar('start_date', date('d-m-Y', strtotime('-8 day', strtotime($latest_date)))));
		$end_date_unix = strtotime(JRequest::getVar('end_date', date('d-m-Y', strtotime($latest_date))));
		if($start_date_unix > $end_date_unix) {
			list($start_date_unix, $end_date_unix) = array($end_date_unix, $start_date_unix);
		}
		if($start_date_unix > time()) {
			$start_date_unix = strtotime('-10 day', time());
		}
		if($end_date_unix > time()) {
			$end_date_unix = time();
		}
		switch($layout) {
			case 'map':
				$count_days = 2;
				$start_date_unix = $end_date_unix - ($count_days*24*60*60);
			default:
				$period = JKHelperDate::getDayPeriod($start_date_unix, $end_date_unix);
				break;
			case 'weekly':
				$period = JKHelperDate::getWeekPeriod($start_date_unix, $end_date_unix);
				break;
			case 'monthly':
				$period = JKHelperDate::getMonthPeriod($start_date_unix, $end_date_unix);
				break;
			case 'yearly':
				$period = JKHelperDate::getYearPeriod($start_date_unix, $end_date_unix);
				break;
		}
		$start_date = date('d-m-Y', $start_date_unix);
		$end_date = date('d-m-Y', $end_date_unix);
		$menu_id = JRequest::getVar('Itemid');
		JRequest::setVar('start_date', $start_date);
		JRequest::setVar('end_date', $end_date);
		
		// Load Header
		$this->header = JKHelperDocument::getReportHeader($period, $layout);
		
		// Set Default Commodity
		$all_commodity = JRequest::getInt('all_commodity');
		$max_commodity = 15;
		$commodity_ids = JRequest::getVar('commodity_id');
		$commodity_ids = in_array($layout, array('map', 'chart')) && count($commodity_ids) > 15 ? array_slice($commodity_ids, 0, $max_commodity) : $commodity_ids;
		if(!count($commodity_ids) && !$all_commodity) {
			JRequest::setVar('commodity_limit', $max_commodity);
			$commodity_ids = array_keys($this->get('Commodity'));
			JRequest::setVar('commodity_limit', NULL);
		}
		JRequest::setVar('commodity_id', $commodity_ids);
		
		// PREPARE COMMODITY SELECTBOX
		$commodities = $this->get('AllCommodity');
		$commodities_el = array();
		foreach($commodities as $item) {
			$commodities_el[intval($item->category_id)][$item->id] = $item;
		}
		$categories = $this->get('Category');
		$categories_el = array();
		foreach($categories as $item) {
			$categories_el[intval($item->parent_id)][$item->id] = $item;
		}
		$this->commodity_ids = $commodity_ids;
		$this->commodity_select = JKHelperDocument::prepareCommodity($categories_el[0], $categories_el, $commodities_el);
		
		// Get model data
		$this->data			= $this->get('Data');
		$this->state		= $this->get('State');
		$this->params		= $this->state->params;
		$this->commodity	= $this->get('Commodity');
		$this->market		= $this->get('Market');
		$this->form			= $this->get('Form');
		
		
		// Set array for different layouts
		$data = array();

		$available_comms = array();
		foreach($this->data as $item) {
			$price = round($item->price/50)*50;
			switch($layout) {
				case 'market':
					$data[$item->commodity_id][$item->market_id] = JKHelperDocument::toCurrency($price);
					break;
				case 'map':
					$data[$item->market_id][$item->commodity_id][strtotime($item->date)] = $price;
					break;
				case 'chart':
					$data[$item->commodity_id][strtotime($item->date)] = $price;
					break;
				default:
					$data[$item->commodity_id][strtotime($item->date)] = JKHelperDocument::toCurrency($price);
					break;
			}
			$available_comms[$item->commodity_id] = $item->commodity_id;
		}
		
		$this->commodity = array_intersect_key($this->commodity, $available_comms);

		// Set Commodity List
		$commodities_el2 = array();
		$category_ids = array();
		foreach($this->commodity as $item) {
			$commodities_el2[intval($item->category_id)][$item->id] = $item;
			$category_ids[] = $item->category_id;
		}
		$selected_category = array();

		foreach(array_unique($category_ids) as $category_id) {
			$category = @$categories[$category_id];
			if(!is_object($category)) continue;
			$selected_category[] = $category_id;
			$selected_category[] = $category->parent_id;
			while(isset($categories[$category->parent_id])) {
				$category = $categories[$category->parent_id];
				$selected_category[] = $category->parent_id;
			}
		}
		$selected_category = array_filter(array_unique($selected_category));
		$selected_category = $selected_category ? $selected_category : array(0);
		$this->commodity_list = JKHelperDocument::prepareCommodity($categories_el[0], $categories_el, $commodities_el2, 0, 0, $selected_category);
		
		switch($layout) {
			case 'chart':
				// Generate chart
				$options = new stdClass();
				$options->start_date = $start_date_unix * 1000;
				$options->end_date = $end_date_unix * 1000;
				$options->show_legend = 2;
				$options->show_tooltip = 1;
				$options->show_yaxis = 1;
				$options->show_xaxis = 1;
				$options->enable_zoom = 1;
				$options->zoom_buttons = 1;
				$options->enable_pan = 1;
				$options->pan_buttons = 1;
				$options->date_format = '%d %b %y';
				$data = JKHelperChart::loadChart('jkchart'.$menu_id, $data, $this->commodity, $options);
				break;
			case 'map':
				$markets = array();
				foreach($this->market as $market) {
					$market_data = isset($data[$market->id]) ? $data[$market->id] : array();
					$description = '<h4>'.$market->name.'</h4>';
					$description .= '<table class="table table-bordered table-condensed"><thead><tr>';
					$description .= '<th width="125px">'.JText::_('COM_JKCOMMODITY_LABEL_COMMODITY').'</th>';
					foreach($period as $time) {
						$description .= '<th width="85px" style="text-align:center">'.$time->sdate.'</th>';
					}
					$description .= '</tr></thead><tbody>';
					foreach($this->commodity as $id => $commodity) {
						$description .= '<tr><td>'.$commodity->name.'</td>';
						foreach($period as $time) {
							$price = isset($market_data[$id][$time->unix]) ? JKHelperDocument::toCurrency(round($market_data[$id][$time->unix])) : '<div style="text-align:center">N/A</div>';
							$description .= '<td style="text-align:right">'.$price.'</td>';
						}
						$description .= '</tr>';
					}
					$description .= '</tbody></table>';
					$market->description = htmlentities($description);
					$markets[] = $market;
				}
				JKHelperMap::initialize('map_canvas', $markets);
				break;
		}
		// Set variables
		$this->document		= $document;
		$this->data			= $data;

		$this->period		= $period;
		$this->return_url	= base64_encode(JURI::getInstance()->toString());
		$this->start_date	= $start_date;
		$this->end_date		= $end_date;
		
		// Set Form Fields
		$this->form->setValue('start_date', null, $this->start_date);
		$this->form->setValue('end_date', null, $this->end_date);
		$this->form->setValue('all_commodity', null, JRequest::getInt('all_commodity'));
		$this->form->setValue('city_id', null, JRequest::getVar('city_id'));
		$this->form->setValue('market_id', null, JRequest::getVar('market_id'));
		$this->form->setValue('layout', null, JRequest::getCmd('layout'));

		
		// Display the results
		parent::display($tpl);
	}




}
