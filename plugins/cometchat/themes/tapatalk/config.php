<?php

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'config.php');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$tapatalk_settings = setConfigValue('tapatalk_settings',array('barPadding' => '20', 'showOnlineTab' => '1', 'showSettingsTab' => '1', 'showModules' => '1', 'chatboxHeight' => '261', 'chatboxWidth' => '235'));

/* SETTINGS START */

foreach ($tapatalk_settings as $key => $value) {
	$$key = $value;
}

/* SETTINGS END */

$iPhoneView = '0';				// iPhone style messages in chatboxes?

?>