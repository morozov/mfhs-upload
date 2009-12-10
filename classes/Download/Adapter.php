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

	protected $fp;

	protected $target;

	public function __construct($dir) {
		if (!is_dir($dir)) {
			throw new Download_Adapter_Exception('"' . $dir . '" is not a directory');
		}
		$this->dir = $dir;
	}

	public function download($url) {
		$this->target = null;
		$request = new HTTP_Request2($url, HTTP_Request2::METHOD_GET, array('store_body' => false));
		$request->attach($this);
		$request->send();
		return $this->target;
	}

	public function update(SplSubject $subject) {
		$event = $subject->getLastEvent();

		switch ($event['name']) {
			case 'receivedHeaders':
				$response = $event['data'];
				if ('2' != substr($response->getStatus(), 0, 1)) {
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
				fwrite($this->fp, $event['data']);
				break;

			case 'receivedBody':
				fclose($this->fp);
				break;
		}
	}

	protected function getFilename($response) {

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
				// проверяем совпадение в искомом заголовке ответа
				$response->getHeader($headerName), $matches)) {
				// достаем последнее из совпадений (т.к. RegExp - с условием)
				return end($matches);
			}
		}

		return null;
	}
}
