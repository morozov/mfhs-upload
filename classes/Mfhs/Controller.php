<?php

class Mfhs_Controller {

	public function __construct($config) {
		$this->config = $config;
	}

	public function process() {

		$is_feed = false;
		$paths = array();

		foreach (array_slice($_SERVER['argv'], 1) as $arg) {
			if (0 === strpos($arg, '--')) {
				switch ($arg) {
					case '--feed':
						$is_feed = true;
						break;
					default:
						die('Unknown option ' . $arg);
						break;
				}
			} else {
				$paths[] = $arg;
			}
		}

		if (0 == count($paths)) {
			die("Usage: {$_SERVER['argv'][0]} FILE");
		}

		foreach ($paths as $path) {
			if ($is_feed) {
				$this->uploadFeed($path);
			} elseif (0 === strpos($arg, 'http://') || 0 === strpos($arg, 'https://')) {
				$this->uploadHttp($arg);
			} else {
				$this->uploadLocal($arg);
			}
		}
	}

	public function uploadLocal($path) {
		$this->getLocalUploadAdapter()->upload($path);
	}

	public function uploadHttp($url) {
		$this->getHttpUploadAdapter()->upload($url);
	}

	public function uploadFeed($url) {
		$this->getFeedUploadAdapter()->upload($url);
	}

	/**
	 * Builds download adapter instance.
	 *
 	 * @return Mfhs_Adapter_Download
	 */
	protected function getDownloadAdapter() {
		require_once 'Mfhs/Adapter/Download.php';
		return new Mfhs_Adapter_Download($this->config->download);
	}

	/**
	 * Builds local upload adapter instance.
	 *
 	 * @return Mfhs_Adapter_Upload_Local
	 */
	protected function getLocalUploadAdapter() {
		require_once 'Mfhs/Adapter/Upload/Local.php';
		require_once 'Mfhs/Observer.php';
		$adapter = new Mfhs_Adapter_Upload_Local($this->config->upload);
		$adapter->setObserver(new Mfhs_Observer());
		return $adapter;
	}

	/**
	 * Builds HTTP upload adapter instance.
	 *
 	 * @return Mfhs_Adapter_Upload_Http
	 */
	protected function getHttpUploadAdapter() {
		require_once 'Mfhs/Adapter/Upload/Http.php';
		$adapter = new Mfhs_Adapter_Upload_Http();
		$adapter->setDownloadAdapter($this->getDownloadAdapter())
			->setUploadAdapter($this->getLocalUploadAdapter());
		return $adapter;
	}

	/**
	 * Builds feed upload adapter instance.
	 *
 	 * @return Mfhs_Adapter_Upload_Feed
	 */
	protected function getFeedUploadAdapter() {
		require_once 'Mfhs/Adapter/Upload/Feed.php';
		$adapter = new Mfhs_Adapter_Upload_Feed();
		$adapter->setUploadAdapter($this->getHttpUploadAdapter())
			->setRegistry($this->getRegistry());
		return $adapter;
	}

	/**
	 * Builds registry instance.
	 *
 	 * @return Mfhs_Registry
	 */
	protected function getRegistry() {
		require_once 'Mfhs/Registry.php';
		return new Mfhs_Registry($this->config->feed->log);
	}
}
