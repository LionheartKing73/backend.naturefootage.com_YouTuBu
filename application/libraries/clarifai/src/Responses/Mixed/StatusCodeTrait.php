<?php


namespace BE\Clarifai\Sdk\Responses\Mixed;

/**
 * Class StatusCodeTrait
 * @package BE\Clarifai\Sdk\Responses\Mixed
 */
trait StatusCodeTrait
{
    /**
     * @var string
     */
    private $statusCode;

    /**
     * @return string
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param string $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        return $this;
    }
}