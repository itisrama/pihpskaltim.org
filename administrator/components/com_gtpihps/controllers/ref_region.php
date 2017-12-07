<?php

/**
 * @package		JK Docuno
 * @author		Yudhistira Ramadhan
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSControllerRef_Region extends GTControllerForm{

	public function getModel($name = 'Ref_Region', $prefix = '', $config = array('ignore_request' => true)) {
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}
