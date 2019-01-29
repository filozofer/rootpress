<?php

namespace Rootpress\exception;

use Exception;

/**
 * Class CRUDException
 * @package rootpress\exception
 */
abstract class CRUDException extends Exception {
	/**
	 * CRUDException constructor.
	 *
	 * @param string $message
	 * @param string $code
	 */
	public function __construct( $message = null, $code = null ) {
		$code = is_null($code) ? 0000 : $code;
		$message = is_null($message) ? 'Something went wrong with the CRUD.' : $message;
		parent::__construct( $message, $code );
	}
}