<?php

return array(
	'upload' => array(
		'username'  => 'username',
		'uploadUrl' => 'http://example.com/cgi-bin/upload.cgi',
	),
	'download' => array(
		'dir' => dirname(__FILE__) . '/tmp',
	),
	'feed' => array(
		'log' => dirname(__FILE__) . '/var/completed.log',
	),
);
