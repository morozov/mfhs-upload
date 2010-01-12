<?php

/**
 * @see Mfhs_Builder_Exception
 */
require_once 'Mfhs/Builder/Exception.php';

/**
 * Builder class.
 */
class Mfhs_Builder {

	/**
	 * Builder configuration.
	 *
	 * @var array
	 */
	protected $config = array(
		'quiet'    => false,
		'download' => array(),
		'upload'   => array(),
		'feed'     => array(),
	);

	/**
	 * Constuctor.
	 *
	 * @param array $config
	 */
	public function __construct($config = null) {
		if (null !== $config) {
			$this->setConfig($config);
		}
	}

	/**
	 * Sets builder configuration.
	 *
	 * @param array|string $nameOrConfig
	 * @param mixed $value
	 * @return Mfhs_Builder
	 */
	public function setConfig($nameOrConfig, $value = null) {
		if (is_array($nameOrConfig)) {
			foreach ($nameOrConfig as $name => $value) {
				$this->setConfigRaw($name, $value);
			}
		} else {
			$this->setConfigRaw($nameOrConfig, $value);
		}
		return $this;
	}

	/**
	 * Sets builder configuration parameter.
	 *
	 * @param string $name
	 * @param mixed $value
	 * @return Mfhs_Builder
	 */
	protected function setConfigRaw($name, $value) {
		if (!array_key_exists($name, $this->config)) {
			throw new Mfhs_Builder_Exception(
				'Unknown configuration parameter "' . $name . '"'
			);
		}
		$this->config[$name] = $value;
		return $this;
	}

	/**
	 * Builds download adapter instance.
	 *
	 * @return Mfhs_Adapter_Download
	 */
	public function getDownloadAdapter() {
		require_once 'Mfhs/Adapter/Download.php';
		return new Mfhs_Adapter_Download($this->config['download']);
	}

	/**
	 * Builds local upload adapter instance.
	 *
	 * @return Mfhs_Adapter_Upload_Local
	 */
	public function getLocalUploadAdapter() {
		require_once 'Mfhs/Adapter/Upload/Local.php';
		require_once 'Mfhs/Observer.php';
		$adapter = new Mfhs_Adapter_Upload_Local($this->config['upload']);
		$adapter->getHttpRequest()
			->setConfig('connect_timeout', 300);
		if (!$this->config['quiet']) {
			$adapter->attach(new Mfhs_Observer());
		}
		return $adapter;
	}

	/**
	 * Builds HTTP upload adapter instance.
	 *
	 * @return Mfhs_Adapter_Upload_Http
	 */
	public function getHttpUploadAdapter() {
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
	public function getFeedUploadAdapter() {
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
	public function getRegistry() {
		require_once 'Mfhs/Registry.php';
		return new Mfhs_Registry($this->config['feed']['log']);
	}
}