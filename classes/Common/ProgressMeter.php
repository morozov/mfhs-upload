<?php

class Common_ProgressMeter {

	protected $total;

	protected $previous = 0;

	public function __construct($label, $total) {
		$this->total = $total;
		echo $label . ': 0%';
	}

	public function __destruct() {
		echo PHP_EOL;
	}

	public function update($current) {
		$percent2 = $this->percent($current);
		$percent1 = $this->percent($this->previous);
		if ($percent2 != $percent1) {
			echo str_repeat("\010", strlen($percent1) + 1) . $percent2 . "%";
		}
		$this->previous = $current;
	}

	public function increase($delta) {
		$this->update($this->previous + $delta);
	}

	protected function percent($value) {
		return floor($value / $this->total * 100);
	}
}
