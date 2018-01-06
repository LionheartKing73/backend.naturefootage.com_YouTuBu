<?php


namespace BE\Clarifai\Sdk\Contracts;

/**
 * Interface TokenStorage
 * @package BE\Clarifai\Sdk\Contracts
 */
interface TokenStorage
{
    /**
     * @param $key
     * @return AuthenticationToken
     */
    public function get($key);

    /**
     * @param $key
     * @param AuthenticationToken $token
     * @return mixed
     */
    public function set($key, AuthenticationToken $token);

    /**
     * @param $key
     * @return boolean
     */
    public function exists($key);

}