<?php

/**
 * @see Zend_Http_Client
 */
require_once 'Zend/Http/Client.php';

/**
 * Адаптер авторизации на сервисе.
 */
class MfhsUpload_AuthAdapter {

	/**
	 * Имя пользователя для авторизации.
	 *
	 * @var string
	 */
	protected $username;

	/**
	 * Пароль для авторизации.
	 *
	 * @var string
	 */
	protected $password;

	/**
	 * Флаг, хранящий статус авторизации адаптера на сайте.
	 *
	 * @var boolean
	 */
	protected $is_authorized = false;

	/**
	 * Экземпляр HTTP-клиента.
	 *
	 * @var Zend_Http_Client_Abstract
	 */
	protected $client;

	/**
	 * Конструктор.
	 *
	 * @param string $base_url
	 * @param string $username
	 * @param string $password
	 */
	public function __construct($base_url, $username, $password) {
		$this->base_url = $base_url;
		$this->username = $username;
		$this->password = $password;
	}

	protected function authorize() {
		$client = $this->getClient();

		$client->resetParameters()
			->setConfig(array(
				'maxredirects' => 0,
			))->setUri($this->base_url . 'login.php')
			->setParameterPost(array(
				'act'  => 'login',
				'user' => $this->username,
				'pass' => $this->password,
			));

		try {
			$response = $client->request(Zend_Http_Client::POST);
		} catch (Zend_Http_Client_Exception $e) {
			throw new Adapter_Exception($e->getMessage());
		}

		if ($response->isRedirect()
			&& $this->base_url . 'members.php' == $response->getHeader('location')) {

			$this->is_authorized = true;
			//
			return;
		}

		if ($response->isError()) {
			throw new Adapter_Exception($response->getMessage());
		} else {
			$body = $response->getBody();
			if (false !== strpos($body, '<div class=error')) {
				throw new Adapter_Exception('Some other error');
			}
		}
	}

	/**
	 * Gets the HTTP client object. If none is set, a new Zend_Http_Client will be used.
	 *
	 * @return Zend_Http_Client_Abstract
	 */
	public function getClient() {
		if (!$this->client instanceof Zend_Http_Client_Abstract) {
			$this->client = new Zend_Http_Client(null, array(
				'useragent' => 'Opera/9.80 (Windows NT 6.1; U; ru) Presto/2.2.15 Version/10.00',
			));
			$this->client->setCookieJar();
		}
		return $this->client;
	}

	/**
	 * Set the HTTP client instance
	 *
	 * @param  Zend_Http_Client $client
	 * @return void
	 */
	public function setClient(Zend_Http_Client $client) {
		$this->client = $client;
	}
}

class Adapter_Exception extends Exception { }