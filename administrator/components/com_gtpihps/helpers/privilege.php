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

		$query->select('province_id');
		$query->from('#__gtpihps_permissions');
		$query->where('group_id IN ('.$group_list.')');
		$query->where('published = 1');

        $db->setQuery($query);
        
        $provinces = $db->loadAssocList();
        
        $provinces_count = count($provinces);
        $province_list = '';

        for($i = 0;$i < $provinces_count;$i++){
            $province_list .= $provinces[$i]['province_id'];
            if($i < $provinces_count-1) $province_list .= ',';
        }
        
        return $province_list;
	}
}
