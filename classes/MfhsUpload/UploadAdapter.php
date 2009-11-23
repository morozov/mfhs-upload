<?php

/**
 * @see Common_ProgressMeter
 */
require_once 'HTTP/Request2.php';

/**
 * @see MfhsUpload_UploadObserver
 */
require_once 'MfhsUpload/UploadObserver.php';

/**
 * @see HtmlParser
 */
require_once 'htmlparser.inc.php';

class MfhsUpload_UploadAdapter {

	protected $script_url;
	protected $username;

	public function __construct($script_url, $username) {
		$this->script_url = $script_url;
		$this->username = $username;
	}

	public function upload($path) {

		$response1 = $this->sendUploadRequest($path);

		list($action, $params) = $this->parseUploadResponse($response1);

		$response2 = $this->sendCompleteRequest($action, $params);

		return $this->parseCompleteResponse($response2);
	}

	protected function sendUploadRequest($path) {

		$request = new HTTP_Request2($this->script_url);

		$url = $request->getUrl();
		$url->setQueryVariables(array(
			'upload_id' => $this->generateUID(),
			'js_on'     => '1',
			'xpass'     => '1B2M2Y8AsgTpgAmY7PhCfg',
			'xmode'     => '2',
		));

		$request->attach(new MfhsUpload_UploadObserver());

		return $request
			->setMethod(HTTP_Request2::METHOD_POST)
			->setConfig(array(
				'timeout' => 86400,
			))
			->addPostParameter(array(
				'xmode'     => '2',
				'pbmode'    => 'inline2',
				'css_name'  => '',
				'tmpl_name' => '',
				'user_name' => $this->username,
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
				}
			}
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
