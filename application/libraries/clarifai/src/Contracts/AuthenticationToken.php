<?php


namespace BE\Clarifai\Sdk\Contracts;

/**
 * Interface AuthenticationToken
 * @package BE\Clarifai\Sdk\Contracts
 */
interface AuthenticationToken
{
    /**
     * @return mixed
     */
    public function getToken();

    /**
     * @return \DateTime
     */
    public function getExpire();

    /**
     * @return boolean
     */
    public function isExpired();

}