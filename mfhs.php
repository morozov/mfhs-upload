#!/usr/bin/env php
<?php

chdir(dirname(__FILE__));

set_include_path(get_include_path()
	. PATH_SEPARATOR . getenv('HOME') . '/pear/php'
	. PATH_SEPARATOR . 'lib'
	. PATH_SEPARATOR . 'classes');

require_once 'Mfhs/Config.php';
require_once 'Mfhs/Controller.php';

try {
	$config = new Mfhs_Config(require 'config.php');
	$controller = new Mfhs_Controller($config);
	$controller->process();
} catch (Mfhs_Exception $e) {
	echo $e->getMessage() . PHP_EOL;
	exit(1);
}
