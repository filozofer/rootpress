<?php

namespace rootpress\exception\Media;

use rootpress\exception\MediaException;

class PersistenseChmodAttachmentFailedException extends MediaException {
	public function __construct() {
		$code = 6002;
		$message = 'The attemp to change attachment\' mod failed.';
		parent::__construct($message, $code);
	}
}