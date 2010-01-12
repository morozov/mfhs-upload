<?php

/**
 * @see Mfhs_Adapter_Upload_Interface
 */
require_once 'Mfhs/Adapter/Upload/Interface.php';

/**
 * @see Common_ProgressMeter
 */
require_once 'HTTP/Request2.php';

/**
 * @see HtmlParser
 */
require_once 'htmlparser.inc.php';

/**
 * Local file upload adapter.
 */
class Mfhs_Adapter_Upload_Local implements Mfhs_Adapter_Upload_Interface {

	/**
	 * HTTP_Request2 instance.
	 *
	 * @var HTTP_Request2 $httpRequest
	 */
	protected $httpRequest;

	/**
	 * Upload script URL.
	 *
	 * @var string
	 */
	protected $uploadUrl;

	/**
	 * Username.
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * Count of tries to upload to a busy server.
	 *
	 * @var integer
	 */
	protected $triesCount = 3;

	/**
	 * Constructor.
	 *
	 * @param array $config
	 * @throws Mfhs_Adapter_Upload_Exception
	 */
	public function __construct($config = null) {
		if (isset($config['uploadUrl'])) {
			$this->setUploadUrl($config['uploadUrl']);
		}
		if (isset($config['username'])) {
			$this->setUsername($config['username']);
		}
		if (isset($config['triesCount'])) {
			$this->setTriesCount($config['triesCount']);
		}
	}

	/**
	 * Returns upload script URL.
	 *
	 * @return string
	 */
	protected function getUploadUrl() {
		if (!$this->uploadUrl) {
			throw new Mfhs_Adapter_Upload_Exception('Script URL is not set');
		}
		return $this->uploadUrl;
	}

	/**
	 * Sets upload script URL.
	 *
	 * @param string $uploadUrl
	 * @return Mfhs_Adapter_Upload_Local
	 */
	public function setUploadUrl($uploadUrl) {
		$this->uploadUrl = $uploadUrl;
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
		// clear post body for further re-use
		return $this->httpRequest->setBody('');
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
	 * Sets count of tries to upload to a busy server.
	 *
	 * @param integer $triesCount
	 * @return Mfhs_Adapter_Upload_Local
	 */
	public function setTriesCount($triesCount) {
		$this->triesCount = $triesCount;
		return $this;
	}

	/**
	 * Returns upload username.
	 *
	 * @return string
	 */
	protected function getUsername() {
		if (!$this->username) {
			throw new Mfhs_Adapter_Upload_Exception('Username is not set');
		}
		return $this->username;
	}

	/**
	 * Sets upload username.
	 *
	 * @param string $username
	 * @return Mfhs_Adapter_Upload_Local
	 */
	public function setUsername($username) {
		$this->username = $username;
		return $this;
	}

	public function upload($path) {

		$response1 = $this->sendUploadRequest($path);

		list($action, $params) = $this->parseUploadResponse($response1);

		$response2 = $this->sendCompleteRequest($action, $params);

		echo $this->parseCompleteResponse($response2) . PHP_EOL;
	}

	protected function sendUploadRequest($path) {

		$url = new Net_URL2($this->getUploadUrl());
		$url->setQueryVariables(array(
			'upload_id' => $this->generateUID(),
			'js_on'     => '1',
			'xpass'     => '1B2M2Y8AsgTpgAmY7PhCfg',
			'xmode'     => '2',
		));

		$request = $this->getHttpRequest()
			->setUrl($url)
			->setMethod(HTTP_Request2::METHOD_POST)
			->addPostParameter(array(
				'xmode'     => '2',
				'pbmode'    => 'inline2',
				'css_name'  => '',
				'tmpl_name' => '',
				'user_name' => $this->getUsername(),
				'file_key'  => "312e0",
				'terms'     => 'on',
			))
			->addUpload('file_1', $path);
		return $this->send($request)->getBody();
	}

	protected function sendCompleteRequest($url, $params) {
		$request = $this->getHttpRequest()
			->setUrl($url)
			->setMethod(HTTP_Request2::METHOD_POST)
			->setHeader('content-type', 'application/x-www-form-urlencoded')
			->addPostParameter($params);
		return $this->send($request)->getBody();
	}

	/**
	 * Разбирает содержимое ответа сервера после загрузки файла. Вычленяет адрес
	 * и параметры формы.
	 *
	 * @param string $contents
	 * @return array($action, $params)
	 */
	protected function parseUploadResponse($contents) {

		$parser = new HtmlParser($contents);

		$action = null;
		$params = array();

		while ($parser->parse()) {
			if (NODE_TYPE_ELEMENT == $parser->iNodeType) {
				switch (strtolower($parser->iNodeName)) {
					case 'form':
						$action = $parser->iNodeAttributes['action'];
						break;
					case 'textarea':
						$param = $parser->iNodeAttributes['name'];
						$parser->parse();
						$value = $parser->iNodeValue;
						$params[$param] = $value;
						break;
					// element <b> contains error message
					case 'b':
						$parser->parse();
						throw new Mfhs_Adapter_Upload_Exception('Server-side error: ' . stripslashes($parser->iNodeValue));
						break;
				}
			}
		}

		if (null === $action) {
			throw new Mfhs_Adapter_Upload_Exception('Invalid server response: ' . PHP_EOL . $contents);
		}

		return array($action, $params);
	}

	/**
	 * Sends specified http-request.
	 *
	 * @param HTTP_Request2 $request
	 */
	protected function send(HTTP_Request2 $request) {
		$tries = 0;
		do {
			$tries++;
			try {
				return $request->send();
			} catch (HTTP_Request2_Exception $e) {
				$isMalformed = 0 === strpos($e->getMessage(), 'Malformed response');
				if ($isMalformed && $tries < 3) {
					sleep(10);
				} else {
					throw $e;
				}
			}
		}
		while (true);
	}

	/**
	 * Разбирает содержимое ответа сервера при завершении загрузки. Возвращает
	 * ссылку на страницу загрузки файла.
	 *
	 * @param string $contents
	 * @return string
	 */
	protected function parseCompleteResponse($contents) {
		$matches = null;
		return preg_match('/(https?):\/\/([^\/]+)\/index.php\?code=([^&]+)/', $contents, $matches)
			? $matches[1] . '://'. $matches[2] . '/download.php?id=' . $matches[3]
			: null;
	}

	/**
	 * Генерирует уникальный идентификатор загружаемого файла. На клиенте!
	 * После загрузки файл хранится с именем, равным идентификатору загрузки,
	 * плюс расширение исходного файла.
	 *
	 * @return string
	 */
	protected function generateUID() {
		$uid = '';
		for ($i = 0; $i < 12; $i++) {
			$uid .= rand(0, 9);
		}
		return $uid;
	}
}
