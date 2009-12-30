<?php

class Mfhs_Controller {

	public function __construct($config) {
		$this->config = $config;
	}

	public function process() {
		if (2 != $_SERVER['argc']) {
			die("Usage: {$_SERVER['argv'][0]} FILE");
		}

		$arg = $_SERVER['argv'][1];

		if (0 === strpos($arg, 'http://') || 0 === strpos($arg, 'https://')) {
			$this->uploadHttp($arg);
		} else {
			$this->uploadLocal($arg);
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

	protected function getDownloadAdapter() {
		require_once 'Mfhs/Adapter/Download.php';
		return new Mfhs_Adapter_Download($this->config->download);
	}

	protected function getLocalUploadAdapter() {
		require_once 'Mfhs/Adapter/Upload/Local.php';
		require_once 'Mfhs/Observer.php';
		$adapter = new Mfhs_Adapter_Upload_Local($this->config->upload);
		$adapter->setObserver(new Mfhs_Observer());
		return $adapter;
	}

	protected function getHttpUploadAdapter() {
		require_once 'Mfhs/Adapter/Upload/Http.php';
		$adapter = new Mfhs_Adapter_Upload_Http();
		$adapter->setDownloadAdapter($this->getDownloadAdapter())
			->setUploadAdapter($this->getLocalUploadAdapter());
		return $adapter;
	}

	protected function getFeedUploadAdapter() {
		require_once 'Mfhs/Adapter/Upload/Feed.php';
		$adapter = new Mfhs_Adapter_Upload_Feed();
		$adapter->setUploadAdapter($this->getHttpUploadAdapter())
			->setRegistry($this->getRegistry());
		return $adapter;
	}

	protected function getRegistry() {
		require_once 'Mfhs/Registry.php';
		return new Mfhs_Registry($this->config->feed->log);
	}
}
