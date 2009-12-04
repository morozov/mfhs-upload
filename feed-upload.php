#!/usr/bin/env php
<?php

require dirname(__FILE__) . '/includes/startup.php';

require_once 'XML/Feed.php';

if (2 != $_SERVER['argc']) {
	die("Usage: {$_SERVER['argv'][0]} URL");
}

function get_enclosures($url, $log) {

	if (file_exists($log)) {
		if (false === ($completed = @file($log))) {
			throw new Exception('Couldn\'t import log file');
		}
		$completed = array_map('trim', $completed);
	} else {
		if (!touch($log)) {
			throw new Exception('Couldn\'t create log file');
		}
		$completed = array();
	}

	if (!is_writable($log)) {
		throw new Exception('Couldn\'t write to log file');
	}

	try {
		$feed = Xml_Feed::import($url);
	} catch (XML_Feed_Exception $e) {
		throw new Exception($e->getMessage());
	}

	$backup = error_reporting();
	error_reporting($backup ^ ~E_STRICT);

	$enclosures = array();

	foreach ($feed as $entry) {
		if (false !== ($enclosure = $entry->enclosure())
			&& preg_match('/^(audio|video)\//', $enclosure['type'])) {
			$url = $enclosure['url'];
			// отрезаем левую QUERY_STRING из адресов на rpod.ru
			if (0 === strpos($url, 'http://rpod.ru/')) {
				$url = substr($url, 0, strpos($url, '?'));
			}
			$enclosures[] = $url;
		}
	}

	error_reporting($backup);

	return $enclosures;
}

var_dump(get_enclosures($_SERVER['argv'][1], 'var/completed.log'));