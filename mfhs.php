#!/usr/bin/env php
<?php

$basedir = dirname(__FILE__);

set_include_path(get_include_path()
	. PATH_SEPARATOR . $basedir . '/lib'
	. PATH_SEPARATOR . $basedir . '/classes');

require_once 'Mfhs/Config.php';
require_once 'Mfhs/Controller.php';

ini_set('default_socket_timeout', '300');

try {
	$controller = new Mfhs_Controller();
	$config = require $basedir . '/config.php';
	$controller->getBuilder()->setConfig($config);
	$controller->process();
} catch (Mfhs_Exception $e) {
	echo $e->getMessage() . PHP_EOL;
	exit(1);
}
