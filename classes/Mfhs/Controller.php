<?php

/**
 * @see Mfhs_Builder
 */
require_once 'Mfhs/Builder.php';

class Mfhs_Controller {

	/**
	 * Builder instance.
	 *
	 * @var Mfhs_Builder $builder
	 */
	protected $builder;

	/**
	 * Returns builder instance.
	 *
	 * @return Mfhs_Builder
	 */
	public function getBuilder() {
		if (!$this->builder instanceof Mfhs_Builder) {
			$this->builder = new Mfhs_Builder();
		}
		return $this->builder;
	}

	/**
	 * Sets builder instance.
	 *
	 * @param Mfhs_Builder $builder
	 * @return Mfhs_Controller
	 */
	public function setBuilder(Mfhs_Builder $builder) {
		$this->builder = $builder;
		return $this;
	}

	public function process() {

		$is_feed  = false;
		$paths = array();
		$builder = $this->getBuilder();

		foreach (array_slice($_SERVER['argv'], 1) as $arg) {
			if (0 === strpos($arg, '--')) {
				switch ($arg) {
					case '--feed':
						$is_feed = true;
						break;
					case '--quiet':
						$this->builder->setConfig('quiet', true);
						break;
					default:
						die('Unknown option ' . $arg);
						break;
				}
			} else {
				$paths[] = $arg;
			}
		}

		if (0 == count($paths)) {
			die("Usage: {$_SERVER['argv'][0]} [--feed] [--quiet] FILE1 [FILE2 [FILE3... [FILEN]]]" . PHP_EOL);
		}

		foreach ($paths as $path) {
			if ($is_feed) {
				$adapter = $this->builder->getFeedUploadAdapter();
			} elseif (0 === strpos($arg, 'http://') || 0 === strpos($arg, 'https://')) {
				$adapter = $this->builder->getHttpUploadAdapter();
			} else {
				$adapter = $this->builder->getLocalUploadAdapter();
			}
			$adapter->upload($path);
		}
	}
}
