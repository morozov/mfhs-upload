<?php

/**
 * @see Common_ProgressMeter
 */
require_once 'Common/ProgressMeter.php';

class MfhsUpload_UploadObserver implements SplObserver {

	protected $indicator;

	public function update(SplSubject $subject) {

		$event = $subject->getLastEvent();

		switch ($event['name']) {
			case 'sentHeaders':
				$matches = null;
				if (preg_match('/content-length:\s*(\d+)/i', $event['data'], $matches)) {
					$this->indicator = new Common_ProgressMeter('Uploading', $matches[1]);
				}
				break;
			case 'sentBodyPart':
				if ($this->indicator) {
					$this->indicator->increase($event['data']);
				}
				break;
		}
	}
}
