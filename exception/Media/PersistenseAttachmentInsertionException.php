<?php

namespace rootpress\exception\Media;

use rootpress\exception\MediaException;

class PersistenseAttachmentInsertionException extends MediaException {
	public function __construct() {
		$code = 6003;
		$message = 'The attemp to insert the attachment in WP failed.';
		parent::__construct($message, $code);
	}
}