<?php

namespace Louis1021\Presento\Exceptions;

class BadPropertyTransformerMethodException extends \Exception {
    public function __construct($method, $code = 400, \Throwable $previous = null) {
        $message = sprintf("Your given method %s is not exists!", $method);
        parent::__construct($message, $code, $previous);
    }
}