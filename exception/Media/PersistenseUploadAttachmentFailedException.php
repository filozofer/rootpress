<?php

namespace Rootpress\exception\Media;

use Rootpress\exception\MediaException;

class PersistenseUploadAttachmentFailedException extends MediaException {
	public function __construct() {
		$code = 6001;
		$message = 'The attachment upload failed.';
		parent::__construct($message, $code);
	}
}