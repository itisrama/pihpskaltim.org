<?php

defined('_JEXEC') or die('Restricted access');

function GTPIHPSBCastBuildRoute(&$query) {
	$segments = array();

	// get a menu item based on Itemid or currently active
	$app	= JFactory::getApplication();
	$menu	= $app->getMenu();
	
	if (empty($query['Itemid'])) {
		$menuItem = $menu->getActive();
	} else {
		$menuItem = $menu->getItem($query['Itemid']);
	}
	
	$mView		= empty($menuItem->query['view']) ? null : $menuItem->query['view'];
	$mLayout	= empty($menuItem->query['layout']) ? null : $menuItem->query['layout'];
	$qView		= !isset($query['view']) || empty($query['view']) ? null : $query['view']; 
	$qLayout	= !isset($query['layout']) || empty($query['layout']) ? null : $query['layout']; 
	$qTask		= !isset($query['task']) || empty($query['task']) ? null : $query['task']; 
	$qId		= !isset($query['id']) || empty($query['id']) ? null : $query['id']; 
		
	/*
	if($qView && $mView != $qView) {
		$segments[] = $qView;
	}*/
	if($qLayout && $mLayout != $qLayout) {
		$segments[] = $qLayout;
	}
	if($qTask) {
		$segments[] = 'task';
		$segments[] = str_replace('.', '-', $qTask);
	}
	$segments[] = $qId;

	if(isset($query['limit']) && !isset($query['start'])) {
		$query['limitstart'] = (int) @$query['start'];
		unset($query['limit']);
	}
	
	unset($query['view']);
	unset($query['layout']);
	unset($query['task']);
	unset($query['id']);
	return $segments;
}

function GTPIHPSBCastParseRoute($segments) {
	$vars	= array();
	$app	= JFactory::getApplication();
    $menu	= $app->getMenu();
    $active	= $menu->getActive();

	$mView = empty($active->query['view']) ? null : $active->query['view'];
	
	$firstSegment		= !isset($segments[0]) || empty($segments[0]) ? null : $segments[0];
	if(in_array($firstSegment, array('edit', 'view'))) {
		$vars['view']	= GTPIHPSBCastSingularize($mView);
		$vars['layout']	= $firstSegment;
		$vars['id']		= !isset($segments[1]) || empty($segments[1]) ? null : $segments[1]; 
	} elseif($firstSegment == 'task') {
		$task			= !isset($segments[1]) || empty($segments[1]) ? null : $segments[1];
		$vars['task']	= str_replace(':', '.', $task);
		$vars['id']		= !isset($segments[2]) || empty($segments[2]) ? null : $segments[2]; 
	} else {
		$vars['view']	= $mView;
		$vars['layout']	= $firstSegment;
	}

	return $vars;
}

function GTPIHPSBCastSingularize($word) {
	$rules = array( 
		'ss' => false, 
		'os' => 'o', 
		'ies' => 'y', 
		'xes' => 'x', 
		'oes' => 'oe', 
		'ies' => 'y', 
		'ves' => 'fe', 
		's' => '',
		'eet' => 'oot'
		// if you know more add them here
	);

	foreach( $rules as $key => $v ) {
		// does the word end in a rule?
		if( preg_match( "/".$key."$/" , $word ) ) {
			// we met that ss rule
			if($key === false) {
				return $word;
			}
			// return the word depluraled
			return preg_replace( "/".$key."$/" , $v , $word ); 
		}
	}
	
	// ok we didn't find any rules so return the original word, sorry.... :(
	return $word;
}

?>
