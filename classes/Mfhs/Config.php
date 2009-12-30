<?php

/**
 * Configuration object class.
 */
class Mfhs_Config {

	/**
	 * Configuration data.
	 *
	 * @var array
	 */
	protected $data = array();

	/**
	 * Constuctor.
	 *
	 * @param array $data
	 */
	public function __construct(array $data) {
		$this->data = array_map(array($this, 'toSelf'), $data);
	}

	/**
	 * Implements magic __get() method.
	 *
	 * @see http://www.php.net/language.oop5.magic
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name) {
		return isset($this->data[$name]) ? $this->data[$name] : null;
	}

	/**
	 * Implements magic __isset() method.
	 *
	 * @see http://www.php.net/language.oop5.magic
	 * @param string $name
	 * @return boolean
	 */
	public function __isset($name) {
		return isset($this->data[$name]);
	}

	/**
	 * Returns configuration data as array.
	 *
	 * @return array
	 */
	public function toArray() {
		$array = array();
		foreach ($this->data as $key => $value) {
			$array[$key] = $value instanceof self ? $value->toArray() : $value;
		}
		return $array;
	}

	/**
	 * Converts initial data to configuration node.
	 *
	 * @param mixed $value
	 * @return mixed
	 */
	protected function toSelf($value) {
		return is_array($value) ? new self($value) : $value;
	}
}
