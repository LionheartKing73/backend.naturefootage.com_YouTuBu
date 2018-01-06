<?php

namespace BE\Clarifai\Sdk\Exceptions;

use BE\Clarifai\Sdk\Responses\ErrorResponse;

/**
 * Class EndpointException
 * @package BE\Clarifai\Sdk\Exceptions
 */
class EndpointException extends ErrorRequestException
{
    public function __construct(ErrorResponse $errorResponse, $message = "", $code = 0, \Throwable $previous = null)
    {
        $message = "{$errorResponse->getStatusCode()} : {$errorResponse->getStatusMessage()} \n" . $message;
        parent::__construct($message, $code, $previous);
    }
}