<?php

namespace Biig\Optimus\Exception;

class JsonOptimusException extends OptimusException
{
    public function __construct(array $message = [], $code = 0, \Throwable $previous = null)
    {
        parent::__construct(json_encode($message), $code, $previous);
    }
}
