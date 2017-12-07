<?php

/**
 * @package		JKCommodity
 * @author		Herwin Pradana
 * @link		http://gt.web.id
 * @license		GNU/GPL
 * @copyright	Copyright (C) 2012 GtWeb Gamatechno. All Rights Reserved.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');

class GTHelperPrivilege{
	
	public static function getPrivileges(){
	    $groups = JFactory::getUser()->get('groups');
	    $group_list = implode(',', $groups);
	    
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('region_id');
		$query->from('#__gtpihps_permissions');
		$query->where('group_id IN ('.$group_list.')');
		$query->where('published = 1');

        $db->setQuery($query);
        
        $regions = $db->loadAssocList();
        
        $regions_count = count($regions);
        $region_list = '';

        for($i = 0;$i < $regions_count;$i++){
            $region_list .= $regions[$i]['region_id'];
            if($i < $regions_count-1) $region_list .= ',';
        }
        
        return $region_list;
	}
}
