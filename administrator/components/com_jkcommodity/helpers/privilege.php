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

class JKHelperPrivilege{
	
	public static function getPrivileges(){
	    $userID = JFactory::getUser()->id;
	    $groups = JAccess::getGroupsByUser($userID);
	    $group_list = implode(',', $groups);
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('city_id');
		$query->from('#__jkcommodity_group_city');
		$query->where('group_id IN ('.$group_list.')');
		$query->where('published = 1');
        $db->setQuery($query);
        
        $cities = $db->loadObjectList();

        $city_list = array(0);
        foreach ($cities as $city) {
        	$city_list[] = $city->city_id;
        }

        $city_list = implode(',', $city_list);
        
        return $city_list;
	}
}
