<?php

/**
 * @see Download_Registry
 */
require_once 'Download/Registry.php';

/**
 * @see Xml_Feed
 */
require_once 'XML/Feed.php';

/**
 * @see Download_Controller_Exception
 */
require_once 'Download/Controller/Exception.php';

/**
 * Downloaded items registry.
 */
class Download_Controller {

	/**
	 * Constructor.
	 *
	 * @param string file
	 * @throws Download_Registry_Exception
	 */
	public function process($url, $log) {

		try {
			$registry = new Download_Registry($log);
		} catch (Download_Registry_Exception $e) {
			throw new Download_Controller_Exception($e->getMessage());
		}

		try {
			$feed = Xml_Feed::import($url);
		} catch (XML_Feed_Exception $e) {
			throw new Download_Controller_Exception($e->getMessage());
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
}
