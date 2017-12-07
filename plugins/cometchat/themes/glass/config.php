<?php

include_once(dirname(dirname(dirname(__FILE__))).DIRECTORY_SEPARATOR.'config.php');

$glass_settings = setConfigValue('glass_settings',array('barType' => 'fluid', 'barWidth' => '960', 'barAlign' => 'center','barPadding' => '20','autoLoadModules' => '1','chatboxHeight' => '245', 'chatboxWidth' => '250', 'backgroundOpacity' => '0.93'));

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/* SETTINGS START */

foreach ($glass_settings as $key => $value) {
	$$key = $value;
}

/* SETTINGS END */

$iPhoneView = '0';				// iPhone style messages in chatboxes?

?>