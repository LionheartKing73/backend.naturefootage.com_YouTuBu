<?php

namespace BE\Clarifai\Sdk\Endpoints;

require_once (APPPATH . '/libraries/clarifai/src/Contracts/Endpoints/Language.php');

use BE\Clarifai\Sdk\EndpointsLinks;
use BE\Clarifai\Sdk\Exceptions\EndpointException;
use BE\Clarifai\Sdk\Exceptions\ErrorRequestException;
use BE\Clarifai\Sdk\Request\Endpoint;
use BE\Clarifai\Sdk\Responses\ResponseFactory;

/**
 * Class Language
 * @package BE\Clarifai\Sdk\Endpoints
 */
class Language implements \BE\Clarifai\Sdk\Contracts\Endpoints\Language
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
     * @return \BE\Clarifai\Sdk\Responses\Languages\LanguagesResponse
     */
    public function getList()
    {
        $this->endpoint->request(EndpointsLinks::LANGUAGE, [], [], 'GET');

        if (!$this->endpoint->isSuccess()) {
            throw new EndpointException (
                $this->responseFactory->createErrorResponse()
                    ->setStatusCode($this->endpoint->response()->status_code)
                    ->setStatusMessage($this->endpoint->response()->status_msg)
            );
        }

        return $this->responseFactory
            ->createLanguagesResponse()
            ->setStatusMessage($this->endpoint->response()->status_msg)
            ->setStatusCode($this->endpoint->response()->status_code)
            ->setLanguages($this->endpoint->response()->languages);
    }
}