<?php

/**
 * @see Mfhs_Adapter_Upload_Exception
 */
require_once 'Mfhs/Adapter/Upload/Exception.php';

/**
 * An interface of upload adapters.
 */
interface Mfhs_Adapter_Upload_Interface {

	/**
	 * Uploads a file from specified path.
	 *
	 * @param string $path
	 * @throws Mfhs_Adapter_Upload_Exception
	 */
	public function upload($path);
}
