<?php

namespace Rootpress\exception\CRUD;

use Rootpress\exception\CRUDException;

/**
 * Class PersistenseCreationFailedException
 * @package rootpress\exception\CRUD
 */
class PersistenseCreationFailedException extends CRUDException {

	/**
	 * PersistenseCreationFailedException constructor.
	 */
	public function __construct( ) {
		$code = 5001;
		$message = 'The attempt to persist the object failed.';
		parent::__construct( $message, $code );
	}
}