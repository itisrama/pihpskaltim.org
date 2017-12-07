<?php

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'config.php');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$lite_settings = setConfigValue('lite_settings',array('barPadding' => '20', 'iPhoneView' => '0','showSettingsTab' => '1','showOnlineTab' => '1','showModules' => '1','chatboxHeight' => '200','chatboxWidth' => '230'));

/* SETTINGS START */

foreach ($lite_settings as $key => $value) {
	$$key = $value;
}

/* SETTINGS END */

?>