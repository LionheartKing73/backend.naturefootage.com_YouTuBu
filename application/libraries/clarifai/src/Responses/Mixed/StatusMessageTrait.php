<?php


namespace BE\Clarifai\Sdk\Responses\Mixed;


trait StatusMessageTrait
{
    /**
     * @var string
     */
    private $statusMessage;

    /**
     * @return string
     */
    public function getStatusMessage()
    {
        return $this->statusMessage;
    }

    /**
     * @param string $statusMessage
     * @return $this
     */
    public function setStatusMessage($statusMessage)
    {
        $this->statusMessage = $statusMessage;
        return $this;
    }
}