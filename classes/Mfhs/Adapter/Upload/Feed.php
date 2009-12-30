<?php

/**
 * @see Mfhs_Adapter_Upload_Interface
 */
require_once 'Mfhs/Adapter/Upload/Interface.php';

/**
 * @see Xml_Feed
 */
require_once 'XML/Feed.php';

/**
 * Downloaded items registry.
 */
class Mfhs_Adapter_Upload_Feed implements Mfhs_Adapter_Upload_Interface {

	/**
	 * Registry instance.
	 *
	 * @var Mfhs_Registry_Interface
	 */
	protected $registry;

	/**
	 * Upload adapter instance.
	 *
	 * @var Mfhs_Adapter_Upload_Interface
	 */
	protected $uploadAdapter;

	/**
	 * Returns registry instance.
	 *
	 * @return Mfhs_Registry_Interface
	 */
	public function getRegistry() {
		if (!$this->registry instanceof Mfhs_Registry_Interface) {
			throw new Mfhs_Adapter_Upload_Exception('Registry is not set');
		}
		return $this->registry;
	}

	/**
	 * Sets registry instance.
	 *
	 * @param Mfhs_Registry_Interface $registry
	 * @return Download_Controller
	 */
	public function setRegistry(Mfhs_Registry_Interface $registry) {
		$this->registry = $registry;
		return $this;
	}

	/**
	 * Returns upload adapter instance.
	 *
	 * @return Mfhs_Adapter_Upload_Interface
	 */
	public function getUploadAdapter() {
		if (!$this->uploadAdapter instanceof Mfhs_Adapter_Upload_Interface) {
			throw new Mfhs_Adapter_Upload_Exception('Upload adapter is not set');
		}
		return $this->uploadAdapter;
	}

	/**
	 * Sets upload adapter instance.
	 *
	 * @param Mfhs_Adapter_Upload_Interface $uploadAdapter
	 * @return Download_Controller
	 */
	public function setUploadAdapter(Mfhs_Adapter_Upload_Interface $uploadAdapter) {
		$this->uploadAdapter = $uploadAdapter;
		return $this;
	}

	/**
	 * Processes feed at provided URL.
	 *
	 * @param string url
	 * @throws Mfhs_Registry_Exception
	 */
	public function upload($path) {

		try {
			$feed = Xml_Feed::import($path);
		} catch (XML_Feed_Exception $e) {
			throw new Mfhs_Adapter_Upload_Exception($e->getMessage());
		}

		$adapter  = $this->getUploadAdapter();
		$registry = $this->getRegistry();

		foreach ($feed as $entry) {
			if (false !== ($enclosure = $entry->enclosure())
				&& preg_match('/^(audio|video)\//', $enclosure['type'])) {
				$url = $enclosure['url'];
				// отрезаем левую QUERY_STRING из адресов на rpod.ru
				if (0 === strpos($url, 'http://rpod.ru/')) {
					$url = substr($url, 0, strpos($url, '?'));
				}
				if (!$registry->isRegistered($url)) {
					$adapter->upload($url);
					$registry->register($url);
				}
			}
		}
	}
}
