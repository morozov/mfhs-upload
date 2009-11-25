#!/usr/bin/env php
<?php

if (2 != $_SERVER['argc']) {
	die("Usage: {$_SERVER['argv'][0]} FILE");
}

$dirname = dirname(__FILE__);

set_include_path(get_include_path()
	. PATH_SEPARATOR . getenv('HOME') . '/pear/php'
	. PATH_SEPARATOR . $dirname . '/lib'
	. PATH_SEPARATOR . $dirname . '/classes');

$config = require dirname(__FILE__) . '/config.php';

require_once 'MfhsUpload/UploadAdapter.php';

$adapter = new MfhsUpload_UploadAdapter('http://93.84.113.212:8082/cgi-bin/upload.cgi', $config['username']);
$id = $adapter->upload($_SERVER['argv'][1]);

echo $config['base_url'] . 'download.php?id=' . $id . PHP_EOL;
