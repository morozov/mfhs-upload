<?php

/**
 * @see Mfhs_Adapter_Upload_Interface
 */
require_once 'Mfhs/Adapter/Upload/Interface.php';

/**
 * HTTP-file upload adapter.
 */
class Mfhs_Adapter_Upload_Http implements Mfhs_Adapter_Upload_Interface {

	/**
	 * Download adapter instance.
	 *
	 * @var Mfhs_Adapter_Download
	 */
	protected $downloadAdapter;

	/**
	 * Upload adapter instance.
	 *
	 * @var Mfhs_Adapter_Upload_Interface
	 */
	protected $uploadAdapter;

	/**
	 * Constructor.
	 *
	 * @param Mfhs_Config $config
	 * @throws Mfhs_Adapter_Upload_Exception
	 */
	public function __construct($config = null) {
		if (isset($config->downloadAdapter)) {
			$this->setDownloadAdapter($config->downloadAdapter);
		}
		if (isset($config->uploadAdapter)) {
			$this->setUploadAdapter($config->uploadAdapter);
		}
	}

	/**
	 * Returns download adapter instance.
	 *
	 * @return Mfhs_Adapter_Download
	 */
	public function getDownloadAdapter() {
		if (!$this->downloadAdapter instanceof Mfhs_Adapter_Download) {
			throw new Mfhs_Adapter_Upload_Exception('Download adapter is not set');
		}
		return $this->downloadAdapter;
	}

	/**
	 * Sets download adapter instance.
	 *
	 * @param Mfhs_Adapter_Download $downloadAdapter
	 * @return Mfhs_Adapter_Upload_Http
	 */
	public function setDownloadAdapter(Mfhs_Adapter_Download $downloadAdapter) {
		$this->downloadAdapter = $downloadAdapter;
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
	 * @return Mfhs_Adapter_Upload_Http
	 */
	public function setUploadAdapter(Mfhs_Adapter_Upload_Interface $uploadAdapter) {
		$this->uploadAdapter = $uploadAdapter;
		return $this;
	}

	/**
	 * Uploads a file from specified path.
	 *
	 * @param string $path
	 * @throws Mfhs_Adapter_Upload_Exception
	 */
	public function upload($path) {

		try {
			$tmp = $this->getDownloadAdapter()->download($path);
		} catch (Mfhs_Adapter_Download_Exception $e) {
			throw new Mfhs_Adapter_Upload_Exception($e->getMessage());
		}

		$this->getUploadAdapter()->upload($tmp);

		ini_set('error_reporting', '0');
		ini_set('track_errors', '1');

		$php_errormsg = null;
		unlink($tmp);
		$error = $php_errormsg;

		ini_restore('track_errors');
		ini_restore('error_reporting');

		if ($error) {
			throw new Mfhs_Adapter_Upload_Exception($error);
		}
	}
}
