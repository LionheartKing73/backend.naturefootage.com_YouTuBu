<?php

namespace BE\Clarifai\Sdk\Endpoints;

require_once (APPPATH . '/libraries/clarifai/src/Contracts/Endpoints/Authenticate.php');
require_once (APPPATH . '/libraries/clarifai/src/EndpointsLinks.php');

use BE\Clarifai\Sdk\Contracts\Endpoint;
use BE\Clarifai\Sdk\EndpointsLinks;
use BE\Clarifai\Sdk\Exceptions\EndpointException;
use BE\Clarifai\Sdk\Exceptions\InvalidCredentialsException;
use BE\Clarifai\Sdk\Exceptions\InvalidParamsExceptions;
use BE\Clarifai\Sdk\Responses\ResponseFactory;

class Authenticate implements \BE\Clarifai\Sdk\Contracts\Endpoints\Authenticate
{
    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var Endpoint
     */
    private $endpoint;

    public function __construct(Endpoint $endpoint, ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->endpoint = $endpoint;
    }

    /**
     * @param $clientId
     * @param $clientSecret
     * @param $grantType
     *
     * @throws InvalidCredentialsException
     * @throws InvalidParamsExceptions
     *
     * @return \BE\Clarifai\Sdk\Contracts\AuthenticationToken
     */
    public function token($clientId, $clientSecret, $grantType = 'client_credentials')
    {
        $this->endpoint->request(
            EndpointsLinks::AUTHENTICATION,
            [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'grant_type' => $grantType,
            ]
        );

        if (!$this->endpoint->isSuccess()) {
            throw new EndpointException (
                $this->responseFactory->createErrorResponse()
                    ->setStatusCode($this->endpoint->response()->status_code)
                    ->setStatusMessage($this->endpoint->response()->status_msg)
            );
        }


        $token = $this->endpoint->response()->access_token;
        $expire = new \DateTime();
        $expire->setTimestamp($expire->getTimestamp() + $this->endpoint->response()->expires_in);

        return $this->responseFactory->createToken($token, $expire);
    }
}