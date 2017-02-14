<?php

namespace rootpress\exception\Media;

use rootpress\exception\MediaException;

class PersistenseUploadAttachmentFailedException extends MediaException {
	public function __construct() {
		$code = 6001;
		$message = 'The attachment upload failed.';
		parent::__construct($message, $code);
	}
}