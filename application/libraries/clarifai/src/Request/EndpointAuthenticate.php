<?php

namespace BE\Clarifai\Sdk\Request;

require_once (APPPATH . '/libraries/clarifai/src/Request/Endpoint.php');

use BE\Clarifai\Sdk\Contracts\TokenStorage;
use BE\Clarifai\Sdk\Exceptions\TokenExpireException;
use BE\Clarifai\Sdk\Exceptions\TokenNotExistException;

/**
 * Class EndpointAuthenticate
 * @package BE\Clarifai\Sdk
 */
class EndpointAuthenticate extends Endpoint
{

    /**
     * @var TokenStorage
     */
    private $tokenStorage;

    /**
     * key to determine token in a storage
     *
     * @var string
     */
    private $tokenKey;


    public function __construct(TokenStorage $tokenStorage, $tokenKey)
    {
        $this->tokenStorage = $tokenStorage;
        $this->tokenKey = $tokenKey;
    }

    /**
     * send request to endpoint
     *
     * @param string $url
     * @param array $params
     *
     * @throws TokenNotExistException
     * @throws TokenExpireException
     *
     * @return mixed
     */
    public function request($url, array $params = [], array $headers = [], $method = 'POST')
    {
        $token = $this->tokenStorage->get($this->tokenKey);

        if (!$token) {
            throw new TokenNotExistException("Token does not exist for specified token key");
        }

        if ($token->isExpired()) {
            throw new TokenExpireException("Token {$token->getToken()} is expired");
        }

        $headers['Authorization'] = "Bearer {$token->getToken()}";

        parent::request($url, $params, $headers, $method);
    }
}