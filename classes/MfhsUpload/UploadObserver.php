<?php

/**
 * @see Common_ProgressMeter
 */
require_once 'Common/ProgressMeter.php';

/**
 * Обозреватель процесса загрузки. Отображает ход процесса.
 */
class MfhsUpload_UploadObserver implements SplObserver {

	/**
	 * Объект-индикатор прогресса.
	 *
	 * @var Common_ProgressMeter
	 */
	protected $indicator;

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
		$matches = null;
		if (preg_match('/content-length:\s*(\d+)/i', $headers, $matches)) {
			$this->indicator = $this->getIndicator($matches[1]);
		}
	}

	/**
	 * Отображает отправку части тела запроса.
	 *
	 * @param integer $length
	 */
	protected function onSentBodyPart($length) {
		if ($this->indicator) {
			$this->indicator->increase($length);
		}
	}

	/**
	 * Возвращает объект-индикатор.
	 *
	 * @param integer $total
	 * @return Common_ProgressMeter
	 */
	protected function getIndicator($total) {
		return new Common_ProgressMeter('Uploading', $total);
	}
}
