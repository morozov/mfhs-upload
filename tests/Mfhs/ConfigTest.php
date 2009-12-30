<?php

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Mfhs/Config.php';

/**
 * Mfhs_Config test.
 */
class Mfhs_ConfigTest extends PHPUnit_Framework_TestCase {

	/**
	 * Test construstor with non-array argument.
	 */
	public function testConstructInvalidArgument() {
		$this->setExpectedException('PHPUnit_Framework_Error');
		$config = new Mfhs_Config('dummy');
	}

	/**
	 * Test construstor with non-array argument.
	 */
	public function testGettingParams() {
		$config = new Mfhs_Config(array('foo' => 'bar'));
		$this->assertEquals('bar', $config->foo);
		$this->assertEquals(null, $config->bar);
		$this->assertEquals(true, isset($config->foo));
		$this->assertEquals(false, isset($config->bar));
	}
}
