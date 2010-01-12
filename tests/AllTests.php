<?php

if (!defined('PHPUnit_MAIN_METHOD')) {
	define('PHPUnit_MAIN_METHOD', 'AllTests::main');
}

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

chdir(dirname(__FILE__) . '/../classes');

require_once dirname(__FILE__) . '/Mfhs/RegistryTest.php';
require_once dirname(__FILE__) . '/Mfhs/Adapter/DownloadTest.php';

class AllTests {

	public static function main() {
		PHPUnit_TextUI_TestRunner::run(self::suite());
	}

	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('Splitter');
		$suite->addTestSuite('Mfhs_RegistryTest');
		$suite->addTestSuite('Mfhs_Adapter_DownloadTest');
		return $suite;
	}
}

if (PHPUnit_MAIN_METHOD == 'AllTests::main') {
	AllTests::main();
}
