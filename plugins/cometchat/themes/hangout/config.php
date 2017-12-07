<?php

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'config.php');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$hangout_settings = setConfigValue('hangout_settings',array('barPadding' => '20','showSettingsTab' => '1','showOnlineTab' => '1','showModules' => '1','chatboxHeight' => '200','chatboxWidth' => '230'));

/* SETTINGS START */

foreach ($hangout_settings as $key => $value) {
	$$key = $value;
}

/* SETTINGS END */

$iPhoneView = '0';				// iPhone style messages in chatboxes?

?>