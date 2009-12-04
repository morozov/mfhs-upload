<?php

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Download/Registry.php';

/**
 *
 */
class Download_RegistryTest extends PHPUnit_Framework_TestCase {

	/**
	 * Test construstor with non-existing file.
	 */
	public function testConstructFileDoesntExist() {
		$this->setExpectedException('Download_Registry_Exception');
	}

	/**
	 * Test construstor with non-readable file.
	 */
	public function testConstructFileNotReadable() {
		$this->setExpectedException('Download_Registry_Exception');
	}

	/**
	 * Test construstor with non-writable file.
	 */
	public function testConstructFileNotWritable() {
		$this->setExpectedException('Download_Registry_Exception');
	}

	/**
	 * Test construstor with non-writable dir.
	 */
	public function testConstructDirNotWritable() {
		$this->setExpectedException('Download_Registry_Exception');
	}

	/**
	 * Test construstor with correct file.
	 */
	public function testConstructSuccess() {
		try {
			$registry = new Download_Registry(null);
		} catch (Download_Registry_Exception $e) {
			$this->fail($e->getMessage());
		}
	}
}
