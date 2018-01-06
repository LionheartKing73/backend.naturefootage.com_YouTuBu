<?php

namespace BE\Clarifai\Sdk\Responses;

use BE\Clarifai\Sdk\Responses\Mixed\StatusCodeTrait;
use BE\Clarifai\Sdk\Responses\Mixed\StatusMessageTrait;

/**
 * Class ErrorResponse
 * @package BE\Clarifai\Sdk\Responses
 */
class ErrorResponse
{
    use StatusCodeTrait, StatusMessageTrait;
}