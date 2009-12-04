#!/usr/bin/env php
<?php

require dirname(__FILE__) . '/includes/startup.php';

if (2 != $_SERVER['argc']) {
	die("Usage: {$_SERVER['argv'][0]} FILE");
}

$config = require dirname(__FILE__) . '/config.php';

require_once 'MfhsUpload/UploadAdapter.php';

try {
	$adapter = new MfhsUpload_UploadAdapter($config['base_url'], $config['username']);
	$id = $adapter->upload($_SERVER['argv'][1]);
	echo $config['base_url'] . 'download.php?id=' . $id;
} catch (MfhsUpload_Exception $e) {
	echo $e->getMessage();
}

echo PHP_EOL;