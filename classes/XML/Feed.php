<?php

/**
 * @see HTTP_Request2
 */
require_once 'HTTP/Request2.php';

/**
 * @see XML_Feed_Parser
 */
require_once 'XML/Feed/Parser.php';

/**
 * @see XML_Feed_Exception
 */
require_once 'XML/Feed/Exception.php';

/**
 * Imports feed from URL.
 */
abstract class Xml_Feed {

	/**
	 * Imports feed from specified URL.
	 *
	 * @return XML_Feed_Parser
	 * @throws XML_Feed_Exception
	 */
	public static function import($url) {

		try {
			$request = new HTTP_Request2($url);
			$xml = $request->send()->getBody();
		} catch (HTTP_Request2_Exception $e) {
			throw new XML_Feed_Exception($e->getMessage());
		}

		// XML_Feed_Parser-1.0.3 is not E_STRICT-compatible, so we have to
		// suppress it's errors
		$backup = error_reporting();
		error_reporting($backup ^ ~E_STRICT);

		try {
			$feed = new XML_Feed_Parser($xml);
		} catch (XML_Feed_Parser_Exception $e) {
			throw new XML_Feed_Exception($e->getMessage());
		}

		// restoring error reporting level
		error_reporting($backup);

		return $feed;
	}
}
