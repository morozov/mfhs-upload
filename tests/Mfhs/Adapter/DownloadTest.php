<?php

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Mfhs/Adapter/Download.php';

/**
 * Mfhs_Adapter_Download test.
 */
class Mfhs_Adapter_DownloadTest extends PHPUnit_Framework_TestCase {

	private
		$dir = '/tmp/mfhs-test';

	public function testSetDir() {
		$this->setExpectedException('Mfhs_Adapter_Download_Exception');
		$adapter = new Mfhs_Adapter_Download();
		$adapter->setDir('/path/to/non-existing/directory');
	}
}
