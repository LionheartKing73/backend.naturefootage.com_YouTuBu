<?php

namespace BE\Clarifai\Sdk\Contracts\Endpoints;

/**
 * process authentication by credentials
 *
 * Interface Authenticate
 * @package BE\Clarifai\Sdk\Contracts\Endpoints
 */
interface Authenticate
{
    /**
     * @param $clientId
     * @param $clientSecret
     * @param $grantType
     *
     * @return \BE\Clarifai\Sdk\Contracts\AuthenticationToken
     */
    public function token($clientId, $clientSecret, $grantType);
}