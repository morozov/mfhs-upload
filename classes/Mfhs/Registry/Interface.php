<?php

/**
 * @see Mfhs_Registry_Exception
 */
require_once 'Mfhs/Registry/Exception.php';

/**
 * An interface of registries that allow to store downloaded urls to prevent
 * multiple downloads of the same file
 */
interface Mfhs_Registry_Interface {

	/**
	 * Checks whether a key is registered.
	 *
	 * @param string $key
	 * @return boolean
	 */
	function isRegistered($key);

	/**
	 * Registers a key.
	 *
	 * @param string $key
	 * @throws Mfhs_Registry_Exception
	 */
	function register($key);
}
