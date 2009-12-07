#!/usr/bin/env php
<?php

require dirname(__FILE__) . '/includes/startup.php';

require_once 'XML/Feed.php';
require_once 'Download/Registry.php';

if (2 != $_SERVER['argc']) {
	die("Usage: {$_SERVER['argv'][0]} URL");
}

function get_enclosures($url, Download_Registry $registry) {

	try {
		$feed = Xml_Feed::import($url);
	} catch (XML_Feed_Exception $e) {
		throw new Exception($e->getMessage());
	}

	foreach ($feed as $entry) {
		if (false !== ($enclosure = $entry->enclosure())
			&& preg_match('/^(audio|video)\//', $enclosure['type'])) {
			$url = $enclosure['url'];
			// отрезаем левую QUERY_STRING из адресов на rpod.ru
			if (0 === strpos($url, 'http://rpod.ru/')) {
				$url = substr($url, 0, strpos($url, '?'));
			}
			if (!$registry->isRegistered($url)) {
				echo $url . PHP_EOL;
				$registry->register($url);
			}
		}
	}
}

$registry = new Download_Registry('var/completed.log');

get_enclosures($_SERVER['argv'][1], $registry);