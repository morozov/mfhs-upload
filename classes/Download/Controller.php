<?php

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

	protected $downloadAdapter;

	protected $uploadAdapter;

	protected $registry;

	public function setDownloadAdapter($adapter) {
		$this->downloadAdapter = $adapter;
		return $this;
	}

	public function setUploadAdapter($adapter) {
		$this->uploadAdapter = $adapter;
		return $this;
	}

	public function setRegistry($registry) {
		$this->registry = $registry;
		return $this;
	}

	/**
	 * Constructor.
	 *
	 * @param string file
	 * @throws Download_Registry_Exception
	 */
	public function process($url) {

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
				if (!$this->registry->isRegistered($url)) {
					try {
						$tmp = $this->downloadAdapter->download($url);
					} catch (XML_Feed_Exception $e) {
						// currently just skipping the current itaration
						continue;
					}
					echo $this->uploadAdapter->upload($tmp);
					unlink($tmp);
					$this->registry->register($url);
				}
			}
		}
	}
}
