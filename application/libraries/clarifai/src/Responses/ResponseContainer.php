<?php


namespace BE\Clarifai\Sdk\Responses;

require_once (APPPATH . '/libraries/clarifai/src/Responses/Mixed/StatusCodeTrait.php');
require_once (APPPATH . '/libraries/clarifai/src/Responses/Mixed/StatusMessageTrait.php');

use BE\Clarifai\Sdk\Responses\Mixed\StatusCodeTrait;
use BE\Clarifai\Sdk\Responses\Mixed\StatusMessageTrait;

/**
 * Class ResponseContainer
 * @package BE\Clarifai\Sdk\Responses
 */
class ResponseContainer
{
    use StatusCodeTrait, StatusMessageTrait;

    /**
     * @var mixed, null for some request
     */
    private $meta;

    /**
     * @var array
     */
    private $results;

    /**
     * @return mixed
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * @param mixed $meta
     * @return ResponseContainer
     */
    public function setMeta($meta)
    {
        $this->meta = $meta;
        return $this;
    }

    /**
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @param array $results
     * @return ResponseContainer
     */
    public function setResults($results)
    {
        $this->results = $results;
        return $this;
    }

}