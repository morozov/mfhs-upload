#!/usr/bin/env php
<?php

require dirname(__FILE__) . '/includes/startup.php';

require_once 'Download/Controller.php';

if (2 != $_SERVER['argc']) {
	die("Usage: {$_SERVER['argv'][0]} URL");
}

$config = require dirname(__FILE__) . '/config.php';

/**
 * @see Mfhs_Registry
 */
require_once 'Mfhs/Registry.php';

/**
 * @see Download_Adapter
 */
require_once 'Download/Adapter.php';

require_once 'MfhsUpload/UploadAdapter.php';

try {
	$ctlr = new Download_Controller();
	$ctlr->setDownloadAdapter(new Download_Adapter('.'))
		 ->setUploadAdapter(new MfhsUpload_UploadAdapter($config['upload_url'], $config['username']))
		 ->setRegistry(new Mfhs_Registry('var/completed.log'))
		 ->process($_SERVER['argv'][1]);
} catch (Download_Controller_Exception $e) {
	echo $e->getMessage() . PHP_EOL;
	exit(1);
}
