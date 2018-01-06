<?php


namespace BE\Clarifai\Sdk\Endpoints;

require_once (APPPATH . '/libraries/clarifai/src/Contracts/Endpoints/Info.php');

use BE\Clarifai\Sdk\EndpointsLinks;
use BE\Clarifai\Sdk\Exceptions\EndpointException;
use BE\Clarifai\Sdk\Request\Endpoint;
use BE\Clarifai\Sdk\Responses\ResponseFactory;

/**
 * Class Info
 * @package BE\Clarifai\Sdk\Endpoints
 */
class Info implements \BE\Clarifai\Sdk\Contracts\Endpoints\Info
{

    /**
     * @var Endpoint
     */
    private $endpoint;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * Tag constructor.
     * @param Endpoint $endpoint
     * @param ResponseFactory $responseFactory
     */
    public function __construct(Endpoint $endpoint, ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->endpoint = $endpoint;
    }

    /**
     * @return \stdClass
     */
    function info()
    {
        $this->endpoint->request(EndpointsLinks::INFO, [], [], 'GET');

        if (!$this->endpoint->isSuccess()) {
            throw new EndpointException (
                $this->responseFactory->createErrorResponse()
                    ->setStatusCode($this->endpoint->response()->status_code)
                    ->setStatusMessage($this->endpoint->response()->status_msg)

            );
        }

        return $this->endpoint->response();
    }
}