<?php

namespace rootpress\exception;

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
	public function __construct( $message = '', $code = '' ) {
		$code = 0000;
		$message = 'Something went wrong with the CRUD.';
		parent::__construct( $message, $code );
	}
}