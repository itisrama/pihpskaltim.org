<?php

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'config.php');

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$standard_settings = setConfigValue('standard_settings',array('barType' => 'fluid', 'barWidth' => '960', 'barAlign' => 'center','barPadding' => '20','autoLoadModules' => '1','chatboxHeight' => '200', 'iPhoneView' => '0', 'chatboxWidth' => '230'));

/* SETTINGS START */

foreach ($standard_settings as $key => $value) {
	$$key = $value;
}

/* SETTINGS END */

?>