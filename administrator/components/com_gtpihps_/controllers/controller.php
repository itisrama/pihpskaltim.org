<?php
/**
 * @package		GT PIHPS
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSController extends GTController
{
	public function __construct($config = array())
	{
		$config['default_view'] = 'ref_national_commodities';
		parent::__construct($config);
	}
}
