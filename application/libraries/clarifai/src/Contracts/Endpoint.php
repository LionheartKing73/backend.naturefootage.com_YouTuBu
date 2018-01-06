<?php

namespace BE\Clarifai\Sdk\Contracts;

/**
 * Endpoint interface
 *
 * Interface Endpoint
 * @package BE\Clarifai\Sdk\Contracts
 */
interface Endpoint
{
    /**
     * send request to endpoint
     *
     * @param string $url
     * @param array $params
     * @return mixed
     */
    public function request($url, array $params = [], array $headers = []);

    /**
     * get result response
     *
     * @return mixed
     */
    public function response();

    /**
     * @return boolean true if succes, false if error
     */
    public function isSuccess();

    /**
     * request status code
     *
     * @return mixed
     */
    public function statusCode();
}