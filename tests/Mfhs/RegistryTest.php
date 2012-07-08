<?php

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Mfhs/Registry.php';

/**
 * Mfhs_Registry test.
 */
class Mfhs_RegistryTest extends PHPUnit_Framework_TestCase {

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
	protected function rmdir($dir) {
		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		/** @var SplFileInfo $path */
		foreach ($iterator as $path) {
			$baseName = $path->getBasename();
			if ($baseName == '.' || $baseName == '..') {
				continue;
			}
			if ($path->isDir()) {
				$this->rmdir($path);
			} else {
				unlink($path);
			}
		}
		rmdir($dir);
	}

	/**
	 * Test constructor with non-existing file and writable directory.
	 */
	public function testConstructNoFileDirWritable() {
		$registry = new Mfhs_Registry($this->file);
		$this->assertFileExists($this->file);
	}

	/**
	 * Test constructor with non-existing file and non-writable directory.
	 */
	public function testConstructNoFileDirNotWritable() {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return;
		}
		chmod($this->dir, 0400);
		$this->setExpectedException('Mfhs_Registry_Exception');
		$registry = new Mfhs_Registry($this->file);
	}

	/**
	 * Test constructor with non-readable file.
	 */
	public function testConstructFileNotReadable() {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return;
		}
		$this->setExpectedException('Mfhs_Registry_Exception');
		touch($this->file);
		chmod($this->file, 0200);
		$registry = new Mfhs_Registry($this->file);
	}

	/**
	 * Test constructor with non-writable file.
	 */
	public function testConstructFileNotWritable() {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return;
		}
		$this->setExpectedException('Mfhs_Registry_Exception');
		touch($this->file);
		chmod($this->file, 0400);
		$registry = new Mfhs_Registry($this->file);
	}

	/**
	 * Test constructor with non-existing file and writable directory.
	 */
	public function testConstructDirectory() {
		$this->setExpectedException('Mfhs_Registry_Exception');
		mkdir($this->file);
		$registry = new Mfhs_Registry($this->file);
	}

	/**
	 * Test constructor with correct file.
	 */
	public function testConstructExistingFile() {
		touch($this->file);
		$registry = new Mfhs_Registry($this->file);
	}

	/**
	 * Test constructor with correct file.
	 */
	public function testConstructAutoCreatePath() {
		$file = $this->dir . '/'. md5(time()) . '/'. md5(time()) . '/file.log';
		$registry = new Mfhs_Registry($file);
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
		$this->setExpectedException('Mfhs_Registry_Exception');
		$registry = $this->createRegistry();
		$registry->register('baz' . PHP_EOL);
	}

	public function testPersistence() {
		$registry1 = $this->createRegistry();
		$registry1->register('baz');
		unset($registry1);
		$registry2 = new Mfhs_Registry($this->file);
		$this->assertTrue($registry2->isRegistered('baz'));
	}

	public function testLock() {
		$this->setExpectedException('Mfhs_Registry_Exception');
		$registry1 = new Mfhs_Registry($this->file);
		$registry2 = new Mfhs_Registry($this->file);
	}

	protected function createRegistry() {
		file_put_contents($this->file, 'foo' . PHP_EOL . 'bar');
		return new Mfhs_Registry($this->file);
	}
}
