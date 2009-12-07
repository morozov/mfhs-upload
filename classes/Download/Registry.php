<?php

/**
 * @see Download_Registry_Interface
 */
require_once 'Download/Registry/Interface.php';

/**
 * @see Download_Registry_Exception
 */
require_once 'Download/Registry/Exception.php';

/**
 * Downloaded items registry.
 */
class Download_Registry implements Download_Registry_Interface {

	/**
	 * Line separator.
	 */
	const SEPARATOR = PHP_EOL;

	/**
	 * Registry file pointer.
	 *
	 * @var resource
	 */
	protected $fp;

	/**
	 * Registry data.
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * Constructor.
	 *
	 * @param string file
	 * @throws Download_Registry_Exception
	 */
	public function __construct($file) {

		if (is_dir($file)) {
			throw new Download_Registry_Exception('Directory provided');
		}

		if (!$fp = @fopen($file, 'a+')) {
			throw new Download_Registry_Exception('Couldn\'t open registry file');
		}

		if (!@flock($fp, LOCK_EX | LOCK_NB)) {
			throw new Download_Registry_Exception('Registry file is already in use');
		}

		$tmp = '';

		while (!feof($fp)) {
			$tmp .= fread($fp, 1024);
		}

		$this->fp = $fp;
		$this->data = array_flip(array_filter(array_map('trim', explode(self::SEPARATOR, $tmp))));
	}

	/**
	 * Destructor.
	 */
	public function __destruct() {
		ftruncate($this->fp, 0);
		fwrite($this->fp, implode(self::SEPARATOR, array_keys($this->data)));
		flock($this->fp, LOCK_UN);
		fclose($this->fp);
	}

	/**
	 * Checks whether a key is registered.
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function isRegistered($key) {
		return isset($this->data[$key]);
	}

	/**
	 * Registers a key.
	 *
	 * @param string $key
	 * @throws Download_Registry_Exception
	 */
	public function register($key) {
		if (false !== strpos($key, self::SEPARATOR)) {
			throw new Download_Registry_Exception('A key containing SEPARATOR is specified');
		}
		$this->data[$key] = true;
	}
}
