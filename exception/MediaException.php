<?php

namespace Rootpress\exception;

use Exception;

/**
 * Class MediaException
 * @package rootpress\exception
 */
abstract class MediaException extends Exception {

	/**
	 * MediaException constructor.
	 *
	 * @param string $message
	 * @param int $code
	 */
	public function __construct( $message = '', $code = 0000 ) {
		$message = empty($message) ? 'Something went wrong with a media.' : $message;
		parent::__construct( $message, $code );
	}
}