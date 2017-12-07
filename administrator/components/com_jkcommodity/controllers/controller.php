<?php
/**
 * @package		JK Commodity
 * @author		JoomlaKu Team
 * @link		http://www.joomlaku.net
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
defined('_JEXEC') or die;

class JKCommodityController extends JKController
{
	public function __construct($config = array())
	{
		$config['default_view'] = 'ref_categories';
		parent::__construct($config);
	}
}
