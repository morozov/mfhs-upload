<?php

/**
 * @see Common_ProgressMeter
 */
require_once 'HTTP/Request2.php';

/**
 * @see Download_Adapter_Exception
 */
require_once 'Download/Adapter/Exception.php';

/**
 * HTTP download adapter.
 */
class Download_Adapter implements SplObserver {

	/**
	 * Directory to save file into.
	 *
	 * @var string $dir
	 */
	protected $dir;

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
	 * @param string $dir
	 * @throws Download_Adapter_Exception
	 */
	public function __construct($dir) {
		if (!is_dir($dir)) {
			throw new Download_Adapter_Exception('"' . $dir . '" is not a directory');
		}
		$this->dir = $dir;
	}

	/**
	 * Downloads file from specified URL.
	 *
	 * @param string $url
	 * @throws Download_Adapter_Exception
	 */
	public function download($url) {
		$this->target = null;
		$request = new HTTP_Request2($url, HTTP_Request2::METHOD_GET, array('store_body' => false));
		$request->attach($this);
		$request->send();
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
					throw new Download_Adapter_Exception($response->getStatus() . ' - ' . $response->getReasonPhrase());
				}
				if (!$filename = $this->getFilename($response)) {
					$filename = basename($subject->getUrl()->getPath());
				}
				$this->target = $this->dir . DIRECTORY_SEPARATOR . $filename;
				if (!($this->fp = @fopen($this->target, 'wb'))) {
					throw new Download_Adapter_Exception("Cannot open target file '{$target}'");
				}
				break;

			case 'receivedBodyPart':
			case 'receivedEncodedBodyPart':
				$written = fwrite($this->fp, $event['data']);
				if ($written != strlen($event['data'])) {
					throw new Download_Adapter_Exception("Cannot write to target file");
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
