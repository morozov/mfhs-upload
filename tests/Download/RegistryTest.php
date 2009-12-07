<?php

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Download/Registry.php';

/**
 * Download_Registry test.
 */
class Download_RegistryTest extends PHPUnit_Framework_TestCase {

	private
		$dir = '/tmp/mfhs-test',
		$file = null;

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->cleanup();
		mkdir($this->dir);
		$this->file = $this->dir . '/' . md5(time()) . '.log';
	}

	/**
	 * Tears down the fixture, for example, close a network connection.
	 * This method is called after a test is executed.
	 */
	protected function tearDown() {
		$this->cleanup();
	}

	/**
	 * Cleans up test directory.
	 */
	protected function cleanup() {
		if (file_exists($this->dir)) {
			if (is_file($this->dir)) {
				unlink($this->dir);
			} elseif (is_dir($this->dir)) {
				$this->rmdir($this->dir);
			}
		}
	}

	/**
	 * Removes non-empty directory.
	 */
	protected function rmdir($path) {
		$dir = new RecursiveDirectoryIterator($path);
		foreach (new RecursiveIteratorIterator($dir) as $file) {
			unlink($file);
		}
		foreach ($dir as $subdir) {
			if (!@rmdir($subdir)) {
				$this->rmdir($subdir);
			}
		}
		rmdir($path);
	}

	/**
	 * Test construstor with non-existing file and writable directory.
	 */
	public function testConstructNoFileDirWritable() {
		$registry = new Download_Registry($this->file);
		$this->assertFileExists($this->file);
	}

	/**
	 * Test construstor with non-existing file and non-writable directory.
	 */
	public function testConstructNoFileDirNotWritable() {
		$this->setExpectedException('Download_Registry_Exception');
		$registry = new Download_Registry('/path/to/non/existing/dir');
	}

	/**
	 * Test construstor with non-readable file.
	 */
	/*public function testConstructFileNotReadable() {
		$this->setExpectedException('Download_Registry_Exception');
	}*/

	/**
	 * Test construstor with non-writable file.
	 */
	/*public function testConstructFileNotWritable() {
		$this->setExpectedException('Download_Registry_Exception');
	}*/

	/**
	 * Test construstor with non-existing file and writable directory.
	 */
	public function testConstructDirectory() {
		$this->setExpectedException('Download_Registry_Exception');
		mkdir($this->file);
		$registry = new Download_Registry($this->file);
	}

	/**
	 * Test construstor with correct file.
	 */
	public function testConstructSuccess() {
		touch($this->file);
		$registry = new Download_Registry($this->file);
	}

	public function testExistingKey() {
		$registry = $this->createRegistry();
		$this->assertTrue($registry->isRegistered('foo'));
		$this->assertTrue($registry->isRegistered('bar'));
	}

	public function testNonExistingKey() {
		$registry = $this->createRegistry();
		$this->assertFalse($registry->isRegistered('baz'));
	}

	public function testRegisterValidKey() {
		$registry = $this->createRegistry();
		$registry->register('baz');
		$this->assertTrue($registry->isRegistered('baz'));
	}

	public function testRegisterInvalidKey() {
		$this->setExpectedException('Download_Registry_Exception');
		$registry = $this->createRegistry();
		$registry->register('baz' . PHP_EOL);
	}

	public function testPersistence() {
		$registry1 = $this->createRegistry();
		$registry1->register('baz');
		unset($registry1);
		$registry2 = new Download_Registry($this->file);
		$this->assertTrue($registry2->isRegistered('baz'));
	}

	public function testLock() {
		$this->setExpectedException('Download_Registry_Exception');
		$registry1 = new Download_Registry($this->file);
		$registry2 = new Download_Registry($this->file);
	}

	protected function createRegistry() {
		file_put_contents($this->file, 'foo' . PHP_EOL . 'bar');
		return new Download_Registry($this->file);
	}
}
