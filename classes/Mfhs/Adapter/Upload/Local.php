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
	 * Upload script URL.
	 *
	 * @var SplObserver
	 */
	protected $observer;

	/**
	 * Constructor.
	 *
	 * @param Mfhs_Config $config
	 * @throws Mfhs_Adapter_Upload_Exception
	 */
	public function __construct($config = null) {
		if (isset($config->uploadUrl)) {
			$this->setUploadUrl($config->uploadUrl);
		}
		if (isset($config->username)) {
			$this->setUsername($config->username);
		}
		if (isset($config->observer)) {
			$this->setObserver($config->observer);
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
	 * Sets upload observer.
	 *
	 * @param SplObserver $observer
	 * @return Mfhs_Adapter_Upload_Local
	 */
	public function setObserver(SplObserver $observer) {
		$this->observer = $observer;
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

		$backup = ini_set('default_socket_timeout', '300');

		$response1 = $this->sendUploadRequest($path);

		list($action, $params) = $this->parseUploadResponse($response1);

		$response2 = $this->sendCompleteRequest($action, $params);

		ini_set('default_socket_timeout', $backup);
		return $this->parseCompleteResponse($response2);
	}

	protected function sendUploadRequest($path) {

		$request = new HTTP_Request2($this->getUploadUrl());

		$url = $request->getUrl();
		$url->setQueryVariables(array(
			'upload_id' => $this->generateUID(),
			'js_on'     => '1',
			'xpass'     => '1B2M2Y8AsgTpgAmY7PhCfg',
			'xmode'     => '2',
		));

		if ($this->observer instanceof SplObserver) {
			$request->attach($this->observer);
		}

		return $request
			->setMethod(HTTP_Request2::METHOD_POST)
			->setConfig(array(
				'connect_timeout' => 300,
			))
			->addPostParameter(array(
				'xmode'     => '2',
				'pbmode'    => 'inline2',
				'css_name'  => '',
				'tmpl_name' => '',
				'user_name' => $this->getUsername(),
				'file_key'  => "312e0",
				'terms'     => 'on',
			))
			->addUpload('file_1', $path)
			->send()
			->getBody();
	}

	protected function sendCompleteRequest($url, $params) {

		$request = new HTTP_Request2($url);

		return $request->setMethod(HTTP_Request2::METHOD_POST)
			->setConfig(array(
				'connect_timeout' => 300,
			))
			->addPostParameter($params)
			->send()
			->getBody();
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
	 * Разбирает содержимое ответd сервера при завершении загрузки. Возвращает
	 * код загруженного файла.
	 *
	 * @param string $contents
	 * @return string
	 */
	protected function parseCompleteResponse($contents) {
		$matches = null;
		return preg_match('/code=([^&]+)/', $contents, $matches)
			? $matches[1] : null;
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
