<?php

namespace rootpress\exception\Media;

use rootpress\exception\MediaException;

class PersistenseMediaException extends MediaException {
	public function __construct() {
		$code = 6000;
		$message = 'The attemp to persist the media failed.';
		parent::__construct($message, $code);
	}
}