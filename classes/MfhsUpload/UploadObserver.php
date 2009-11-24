<?php

/**
 * @see Console_ProgressBar
 */
require_once 'Console/ProgressBar.php';

/**
 * Обозреватель процесса загрузки. Отображает ход процесса.
 */
class MfhsUpload_UploadObserver implements SplObserver {

	/**
	 * Единицы измерения объема загружаемых данных.
	 *
	 * @var array
	 */
	protected static $units = array('B', 'KiB', 'MiB', 'GiB');

	/**
	 * Количество цифр, отображаемых в индикаторе загрузки.
	 *
	 * @var array
	 */
	protected static $digits = 3;

	/**
	 * Объект-индикатор прогресса.
	 *
	 * @var Console_ProgressBar
	 */
	protected $bar;

	/**
	 * Текущее значение объема загруженных данных.
	 *
	 * @var integer
	 */
	protected $current = 0;

	/**
	 * Делитель для перевода в текущую единицу измерения.
	 *
	 * @var integer
	 */
	protected $divisor = 1;

	/**
	 * Отображает изменение состояния субъекта.
	 *
	 * @param SplSubject $subject
	 */
	public function update(SplSubject $subject) {
		$event = $subject->getLastEvent();
		$method = 'on' . ucfirst($event['name']);
		if (method_exists($this, $method)) {
			$this->$method($event['data']);
		}
	}

	/**
	 * Отображает отправку заголовков.
	 *
	 * @param string $headers
	 */
	protected function onSentHeaders($headers) {
		$this->current = 0;
		$this->divisor = 1;
		$matches = null;
		if (preg_match('/content-length:\s*(\d+)/i', $headers, $matches)) {

			$base = 1024;

			// определяем порядок величины
			// вводим поправочный коэффициент, чтобы получить гарантированно не больше 3-х
			// знаков для запятой (например, для случая 1022 байт => 1К)
			$order1 = floor(log($matches[1] * 1.024, $base));

			$this->divisor = pow($base, $order1);

			// значение в полученных единицах
			$total = $matches[1] / $this->divisor;

			// порядок полученного значения
			$order2 = floor(log($total, 10)) + 1;

			// точность отображения (чтобы получить нужное количество знаков)
			$precision = self::$digits - $order2;

			$this->bar = $this->getBar($total, self::$units[$order1], $precision);
		}
	}

	/**
	 * Отображает отправку части тела запроса.
	 *
	 * @param integer $length
	 */
	protected function onSentBodyPart($length) {
		$this->current += $length;
		$this->bar->update($this->current / $this->divisor);
	}

	/**
	 * Отображает получение заголовков. На самом деле нужно обработать событие
	 * "onSentBody", которое на настоящий момент, в версии 0.5.1, не реализовано.
	 *
	 */
	protected function onReceivedHeaders() {
		// выводим перевод строки после того, как отработает индикатор загрузки
		echo PHP_EOL;
	}

	/**
	 * Возвращает объект-индикатор.
	 *
	 * @param integer $total
	 * @param string $unit
	 * @param integer $precision
	 * @return Common_ProgressMeter
	 */
	protected function getBar($total, $unit, $precision) {
		return new Console_ProgressBar(
			'%fraction%' . $unit . ' [%bar%] %percent%',
			'=>',
			'-',
			76,
			$total,
			array(
				'percent_precision' => 0,
				'fraction_precision' => $precision,
				'min_draw_interval' => 0.25,
			));
	}
}
