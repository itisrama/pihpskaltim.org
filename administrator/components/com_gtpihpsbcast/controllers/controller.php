<?php
/**
 * @package		GT PIHPS BCast
 * @author		Yudhistira Ramadhan
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class GTPIHPSBCastController extends GTController
{
	public function __construct($config = array())
	{
		$config['default_view'] = 'commodity';
		parent::__construct($config);
	}
}