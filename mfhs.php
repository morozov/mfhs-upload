#!/usr/bin/env php
<?php

require dirname(__FILE__) . '/includes/startup.php';

require_once 'Mfhs/Config.php';
require_once 'Mfhs/Controller.php';

try {
	$config = new Mfhs_Config(require dirname(__FILE__) . '/config.php');
	$controller = new Mfhs_Controller($config);
	$controller->process();
} catch (Mfhs_Exception $e) {
	echo $e->getMessage() . PHP_EOL;
	exit(1);
}
