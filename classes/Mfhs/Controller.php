<?php

class Mfhs_Controller {

	public function __construct($config) {
		$this->config = $config;
	}

	public function process() {

		$is_feed  = false;
		$paths = array();
		$options = array(
			'quiet' => false,
		);

		foreach (array_slice($_SERVER['argv'], 1) as $arg) {
			if (0 === strpos($arg, '--')) {
				switch ($arg) {
					case '--feed':
						$is_feed = true;
						break;
					case '--quiet':
						$options['quiet'] = true;
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
			die("Usage: {$_SERVER['argv'][0]} FILE" . PHP_EOL);
		}

		foreach ($paths as $path) {
			if ($is_feed) {
				$this->uploadFeed($path, $options);
			} elseif (0 === strpos($arg, 'http://') || 0 === strpos($arg, 'https://')) {
				$this->uploadHttp($arg, $options);
			} else {
				$this->uploadLocal($arg, $options);
			}
		}
	}

	public function uploadLocal($path, $options) {
		$this->getLocalUploadAdapter($options)->upload($path);
	}

	public function uploadHttp($url, $options) {
		$this->getHttpUploadAdapter($options)->upload($url);
	}

	public function uploadFeed($url, $options) {
		$this->getFeedUploadAdapter($options)->upload($url);
	}

	/**
	 * Builds download adapter instance.
	 *
	 * @return Mfhs_Adapter_Download
	 */
	protected function getDownloadAdapter(array $options) {
		require_once 'Mfhs/Adapter/Download.php';
		return new Mfhs_Adapter_Download($this->config->download);
	}

	/**
	 * Builds local upload adapter instance.
	 *
	 * @return Mfhs_Adapter_Upload_Local
	 */
	protected function getLocalUploadAdapter(array $options) {
		require_once 'Mfhs/Adapter/Upload/Local.php';
		require_once 'Mfhs/Observer.php';
		$adapter = new Mfhs_Adapter_Upload_Local($this->config->upload);
		$adapter->getHttpRequest()
			->setConfig('connect_timeout', 300);
		if (!$options['quiet']) {
			$adapter->attach(new Mfhs_Observer());
		}
		return $adapter;
	}

	/**
	 * Builds HTTP upload adapter instance.
	 *
	 * @return Mfhs_Adapter_Upload_Http
	 */
	protected function getHttpUploadAdapter(array $options) {
		require_once 'Mfhs/Adapter/Upload/Http.php';
		$adapter = new Mfhs_Adapter_Upload_Http();
		$adapter->setDownloadAdapter($this->getDownloadAdapter($options))
			->setUploadAdapter($this->getLocalUploadAdapter($options));
		return $adapter;
	}

	/**
	 * Builds feed upload adapter instance.
	 *
	 * @return Mfhs_Adapter_Upload_Feed
	 */
	protected function getFeedUploadAdapter(array $options) {
		require_once 'Mfhs/Adapter/Upload/Feed.php';
		$adapter = new Mfhs_Adapter_Upload_Feed();
		$adapter->setUploadAdapter($this->getHttpUploadAdapter($options))
			->setRegistry($this->getRegistry($options));
		return $adapter;
	}

	/**
	 * Builds registry instance.
	 *
	 * @return Mfhs_Registry
	 */
	protected function getRegistry(array $options) {
		require_once 'Mfhs/Registry.php';
		return new Mfhs_Registry($this->config->feed->log);
	}
}
