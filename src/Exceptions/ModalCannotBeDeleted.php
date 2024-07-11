<?php

namespace kha333n\crudmodule\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class ModalCannotBeDeleted extends HttpException
{
    public function __construct($message = "Model cannot be deleted.", $code = 422, \Throwable $previous = null, array $headers = [])
    {
        parent::__construct($code, $message, $previous, $headers);
    }
}
