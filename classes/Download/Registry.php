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
 *
 */
class Download_Registry implements Download_Registry_Interface {

	protected $file;

	public function __construct($file) {
	}

	/**
	 * Checks whether a key is registered.
	 *
	 * @param string $key
	 * @return boolean
	 */
	public function isRegistered($key) {}

	/**
	 * Registers a key.
	 *
	 * @param string $key
	 * @throws Download_Registry_Exception
	 */
	public function register($key) {}
}
