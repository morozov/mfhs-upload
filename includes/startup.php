<?php

$basedir = dirname(__FILE__) . '/..';

set_include_path(get_include_path()
	. PATH_SEPARATOR . getenv('HOME') . '/pear/php'
	. PATH_SEPARATOR . $basedir . '/lib'
	. PATH_SEPARATOR . $basedir . '/classes');

