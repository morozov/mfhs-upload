#!/usr/bin/env php
<?php

require dirname(__FILE__) . '/includes/startup.php';

require_once 'Download/Controller.php';

if (2 != $_SERVER['argc']) {
	die("Usage: {$_SERVER['argv'][0]} URL");
}

try {
	$ctlr = new Download_Controller();
	$ctlr->process($_SERVER['argv'][1], 'var/completed.log');
} catch (Download_Controller_Exception $e) {
	echo $e->getMessage();
}

echo PHP_EOL;
