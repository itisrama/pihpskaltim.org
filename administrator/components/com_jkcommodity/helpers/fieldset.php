<?php

/**
 * @package		GT Component
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class JKHelperFieldset {

	public static function tplEditField($label, $input, $class = null) {
		$str = '
			<div class="form-group %class">
				<div class="control-label col-sm-3">%label</div>
				<div class="col-sm-9">%input</div>
			</div>
		';

		return str_replace(array('%label', '%input', '%class'), array($label, $input, $class), $str);
	}

	public function tplEditField2($label, $input, $class = null) {
		$str = '
			<div class="form-group %class">
				%label
				%input
			</div>
		';

		return str_replace(array('%label', '%input', '%class'), array($label, $input, $class), $str);
	}

	public static function tplEditFieldBo($label, $input, $class = null) {
		$str = '
			<div class="control-group %class">
				<div class="control-label col-sm-3">%label</div>
				<div class="controls col-sm-9">%input</div>
			</div>
		';

		return str_replace(array('%label', '%input', '%class'), array($label, $input, $class), $str);
	}

	public static function tplEditFieldset($fieldset) {
		$str = '
			<fieldset>%fieldset</fieldset>
		';

		return str_replace(array('%fieldset'), array($fieldset), $str);
	}

	public static function tplViewField($num, $label, $input, $showNum = true) {
		$label = str_replace('*', '', $label);
		if($showNum) {
			$str = '
				<tr>
					<td width="40px" class="text-center">%num</td>
					<td width="25%"><strong>%label</strong></td>
					%input
				</tr>
			';
		} else {
			$str = '
				<tr>
					<td width="25%"><strong>%label</strong></td>
					%input
				</tr>
			';
		}

		return str_replace(array('%label', '%input', '%num'), array($label, $input, $num), $str);
	}

	public static function tplViewFieldset($tbody, $class = '') {
		$str = '
			<table class="table table-striped table-hover table-bordered '.$class.'">
				<tbody>%tbody</tbody>
			</table>
		';

		return str_replace(array('%tbody'), array($tbody), $str);
	}

	public function tplViewTable($thead, $tbody, $condensed = false) {
		$condensed = $condensed ? ' table-condensed' : '';
		$str = '
			<table class="table table-striped table-bordered table-hover'.$condensed.'">
				<thead><tr>%thead</tr></thead>
				<tbody>%tbody</tbody>
			</table>
		';

		return str_replace(array('%thead', '%tbody'), array($thead, $tbody), $str);
	}

	public function renderEdit($fieldset, $type = 2) {
		$editField = "";
		$editFieldset = "";

		$html	= array();
		foreach($fieldset as $field) {
			if(in_array($field->type, array('Hidden', 'AltHidden'))) {
				$html[]	= $field->input;
			} else {
				$html[]	= $type == 2 ? self::tplEditField2($field->label, $field->input, $field->fieldname) : self::tplEditField($field->label, $field->input);
			}
		}
		$html	= implode(PHP_EOL, $html);
		$html	= self::tplEditFieldset($html);

		return $html;
	}

	public function renderView($fieldset, $showNum = true, $class = '') {
		$html	= array();

		$k = 0;
		foreach($fieldset as $field) {
			if(!in_array($field->type, array('Hidden', 'AltHidden'))) {
				$k++;
				$html[]	= self::tplViewField($k, strip_tags($field->label), '<td>'.nl2br($field->value).'</td>', $showNum);
			}
		}
		$html	= implode(PHP_EOL, $html);
		$html	= self::tplViewFieldset($html, $class);
		return $html;
	}

	public function renderSubView($field, $showNum = true, $condensed = false) {
		$data = JArrayHelper::fromObject(json_decode($field->value));
		if(!$data) {
			$html = '<p>'.JText::_('COM_JKCOMMODITY_NO_DATA').'</p>';
			return $html;
		}
		$keys = array_keys($data);
		$subForm = @$field->subForm;
		$subFields = @self::getSubField($field->fieldname, $keys, $subForm);

		$thead = array();
		if($showNum) {
			$thead[] = '<th class="text-center" width="40px">'.JText::_('COM_JKCOMMODITY_NUM').'</th>';
		}
		foreach($subFields as $subField) {
			if(@$subField->fieldname == 'id') continue;
			$width = isset($subField->width) ? $subField->width : 'auto';
			$thead[] = '<th class="text-center" width="'.$width.'">'.$subField->label.'</th>';
		}

		$tbody = array();
		$k = 0;
		foreach(reset($data) as $row => $value) {
			$k++;
			$tbody[] = '<tr>';
			if($showNum) {
				$tbody[] = '<td style="text-align:center">'.$k.'</k>';
			}
			foreach($subFields as $subField) {
				if(@$subField->fieldname == 'id') continue;
				$align = isset($subField->align) ? $subField->align : 'left';
				$tbody[] = '<td style="text-align:'.$align.'">'.@$data[$subField->fieldname][$row].'</td>';
			}
			$tbody[] = '</tr>';
		}
		$thead = implode('', $thead);
		$tbody = implode('', $tbody);
		$table = self::tplViewTable($thead, $tbody, $condensed);
		return $table;
	}

	public function renderSubVerticalView($field) {
		$data = JArrayHelper::fromObject(json_decode($field->value));
		if(!$data) {
			$html = '<p>'.JText::_('COM_JKCOMMODITY_NO_DATA').'</p>';
			return $html;
		}

		$keys = array_keys($data);
		$subForm = $field->subForm;
		$subFields = self::getSubField($field->fieldname, $keys, $subForm);

		$table = array();
		$k = 0;
		foreach($subFields as $subField) {
			$k++;
			$label	= $subField->label;
			$columns = array();
			$width = count($data[$keys[0]]); 
			$width = 73 / $width .'%';
			foreach($data[$keys[0]] as $row => $value) {
				if(@$subField->fieldname == 'id') continue;
				if(!in_array($subField->fieldname, $keys)) continue;
				if(end($data[$keys[0]]) == $value) $width = 'auto';
				$value		= $data[$subField->fieldname][$row];
				$columns[]	= '<td width="'.$width.'">'.$value.'</td>';
			}
			$columns = implode('', $columns);
			$table[] = self::tplViewField($k, $label, $columns);

		}
		$table	= implode(PHP_EOL, $table);
		$table	= self::tplViewFieldset($table);

		return $table;
	}

	public function getSubField($name, $keys, $form) {
		$subFields = array();
		
		if(is_object($form)) {
			foreach ($keys as $k => $key) {
				$field = $form->getField($key, $name);
				if(is_object($field)) {
					$subFields[$k]['fieldname']	= $field->fieldname;
					$subFields[$k]['label']		= strip_tags($field->label);
					$subFields[$k]['type']		= $field->type;
					$subFields[$k]['align']		= $form->getFieldAttribute($field->fieldname, 'align', 'left', $field->group);
					$subFields[$k]['width']		= $form->getFieldAttribute($field->fieldname, 'width', 'auto', $field->group);
				} else {
					$subFields[$k]['fieldname']	= $key;
					$subFields[$k]['label']		= JText::_('COM_JKCOMMODITY_FIELD_'.strtoupper($key));
				}
				
			}
		} else {
			foreach ($keys as $k => $fl) {
				$field			= explode('__', $fl);
				$name			= @$field[0];
				$align			= @$field[1];
				$width			= @$field[2];
				$notranslate	= @$field[3];

				$subFields[$k]['fieldname']	= $fl;
				$subFields[$k]['label']		= $notranslate == 1 ? str_replace('_', ' ', $name) : JText::_('COM_JKCOMMODITY_FIELD_'.strtoupper($name));
				$subFields[$k]['align']		= $align ? $align : 'left';
				$subFields[$k]['width']		= $field == reset($keys) ? '25%' : 'auto';
				$subFields[$k]['width']		= $width ? $width : $subFields[$k]['width'];
			}
		}
		foreach ($subFields as $k => $subField) {
			$subFields[$k] = JArrayHelper::toObject($subField);
		}
		return $subFields;
	}
}
