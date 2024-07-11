<?php

namespace Exceptions;

use Exception;

class ModalCannotBeDeleted extends Exception
{
    //TODO: Implement this exception
    public function __construct($message = "Model cannot be deleted.", $code = 0, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
