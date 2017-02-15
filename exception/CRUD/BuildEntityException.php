<?php

namespace rootpress\exception\CRUD;

use rootpress\exception\CRUDException;

/**
 * Class BuildEntityException
 * @package rootpress\exception\CRUD
 */
class BuildEntityException extends CRUDException {

    /**
     * BuildEntityException constructor.
     */
    public function __construct($message = null, $code = null) {
        $code = (is_null($code)) ? null : $code;
        $message = (is_null($message)) ? 'The attempt to build an entity failed.' : $message;
        parent::__construct($message, $code);
    }

}