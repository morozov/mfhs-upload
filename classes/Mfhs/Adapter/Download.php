<?php

/**
 * @see Common_ProgressMeter
 */
require_once 'HTTP/Request2.php';

/**
 * @see Mfhs_Adapter_Download
 */
require_once 'Mfhs/Adapter/Download/Exception.php';

/**
 * HTTP download adapter.
 */
class Mfhs_Adapter_Download implements SplObserver {

	/**
	 * HTTP_Request2 instance.
	 *
	 * @var HTTP_Request2 $httpRequest
	 */
	protected $httpRequest;

	/**
	 * Directory to save file into.
	 *
	 * @var string $dir
	 */
	protected $dir = '.';

	/**
	 * Target file path.
	 *
	 * @var string $target
	 */
	protected $target;

	/**
	 * Target file resource.
	 *
	 * @var resource $fp
	 */
	protected $fp;

	/**
	 * Constructor.
	 *
	 * @param Mfhs_Config $config
	 * @throws Mfhs_Adapter_Download_Exception
	 */
	public function __construct($config = null) {
		if (isset($config->dir)) {
			$this->setDir($config->dir);
		}
		if (isset($config->httpRequest)) {
			$this->setHttpRequest($config->httpRequest);
		}
	}

	/**
	 * Sets download directory.
	 *
	 * @param  string $dir
	 * @throws Mfhs_Adapter_Download_Exception
	 * @return Mfhs_Adapter_Download
	 */
	public function setDir($dir) {
		if (file_exists($dir)) {
			if (!is_dir($dir)) {
				throw new Mfhs_Adapter_Download_Exception('"' . $dir . '" is not a directory');
			}
			if (!is_writable($dir)) {
				throw new Mfhs_Adapter_Download_Exception('"' . $dir . '" is not writable');
			}
		}
		$this->dir = $dir;
		return $this;
	}

	/**
	 * Returns HTTP_Request2 instance.
	 *
	 * @return HTTP_Request2
	 */
	public function getHttpRequest() {
		if (!$this->httpRequest instanceof HTTP_Request2) {
			$this->httpRequest = new HTTP_Request2();
		}
		return $this->httpRequest;
	}

	/**
	 * Sets HTTP_Request2 instance.
	 *
	 * @param HTTP_Request2 $httpRequest
	 * @return Mfhs_Adapter_Download
	 */
	public function setHttpRequest(HTTP_Request2 $httpRequest) {
		$this->httpRequest = $httpRequest;
		return $this;
	}

	/**
	 * Downloads file from specified URL.
	 *
	 * @param string $url
	 * @throws Download_Adapter_Exception
	 */
	public function download($url) {
		$this->target = null;
		$this->fp = null;
		$httpRequest = $this->getHttpRequest();
		$httpRequest->setUrl($url)
			->setMethod(HTTP_Request2::METHOD_GET)
			->setConfig('store_body', false);
		$httpRequest->attach($this);
		$httpRequest->send();
		$httpRequest->detach($this);
		return $this->target;
	}

	/**
	 * Implements SplObserver#update
	 *
	 * @param SplSubject $subject
	 */
	public function update(SplSubject $subject) {
		$event = $subject->getLastEvent();

		switch ($event['name']) {
			case 'receivedHeaders':
				$response = $event['data'];
				if (!in_array(substr($response->getStatus(), 0, 1), array('2', '3'))) {
					throw new Mfhs_Adapter_Download_Exception($response->getStatus() . ' - ' . $response->getReasonPhrase());
				}
				if (!$filename = $this->getFilename($response)) {
					$filename = basename($subject->getUrl()->getPath());
				}
				if (!is_dir($this->dir) && !mkdir($this->dir, 0777, true)) {
					throw new Mfhs_Adapter_Download_Exception('Couldn\'t create directory "' . $this->dir . '"');
				}
				$this->target = $this->dir . DIRECTORY_SEPARATOR . $filename;
				break;

			case 'receivedBodyPart':
			case 'receivedEncodedBodyPart':
				if (!$this->fp && !($this->fp = @fopen($this->target, 'wb'))) {
					throw new Mfhs_Adapter_Download_Exception("Cannot open target file '{$this->target}'");
				}
				$written = fwrite($this->fp, $event['data']);
				if ($written != strlen($event['data'])) {
					throw new Mfhs_Adapter_Download_Exception("Cannot write to target file");
				}
				break;

			case 'receivedBody':
				fclose($this->fp);
				break;
		}
	}

	/**
	 * Detects filename from response.
	 *
	 * @param HTTP_Request2_Response $response
	 */
	protected function getFilename(HTTP_Request2_Response $response) {

		$matches = null;

		foreach (array(
			'content-disposition' => 'filename',
			'content-type'        => 'name',
		) as $headerName => $directiveName) {
			if (preg_match(
				sprintf(
					'/%s\s*=\s*(?(?=")"([^"]*)|([^;]*))/i',
					preg_quote($directiveName)
				),
				// try to find filename in header
				$response->getHeader($headerName), $matches)) {
				// get the last of matches (because expr. has a condition)
				return end($matches);
			}
		}

		return null;
	}
}
