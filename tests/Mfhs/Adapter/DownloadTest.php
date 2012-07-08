<?php

require_once 'PHPUnit/Framework/TestCase.php';

require_once 'Mfhs/Adapter/Download.php';

require_once 'HTTP/Request2/Adapter/Mock.php';

/**
 * Mfhs_Adapter_Download test.
 */
class Mfhs_Adapter_DownloadTest extends PHPUnit_Framework_TestCase {

	private
		$dir = '/tmp/mfhs-test';

	/**
	 * Sets up the fixture, for example, open a network connection.
	 * This method is called before a test is executed.
	 */
	protected function setUp() {
		$this->cleanup();
		mkdir($this->dir);
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

	public function testSetDirFile() {
		$file = $this->dir . '/' . md5(time());
		touch($file);
		$this->setExpectedException('Mfhs_Adapter_Download_Exception');
		$adapter = new Mfhs_Adapter_Download();
		$adapter->setDir($file);
	}

	public function testSetDirNotWritable() {
		if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
			return;
		}
		chmod($this->dir, 0400);
		$this->setExpectedException('Mfhs_Adapter_Download_Exception');
		$adapter = new Mfhs_Adapter_Download();
		$adapter->setDir($this->dir);
	}

	public function testAutoCreatePath() {
		$dir = $this->dir . '/' . md5(time());
		$adapter = new Mfhs_Adapter_Download();
		$adapter->setDir($dir);

		$mock = new HTTP_Request2_Adapter_Mock();
		$mock->addResponse(
			"HTTP/1.1 200 OK\r\n" .
			"Content-Type: text/plain; charset=iso-8859-1\r\n" .
			"\r\n" .
			"This is a string"
		);

		$adapter->getHttpRequest()->setAdapter($mock);
		$adapter->download('http://www.example.com/');
	}
}
