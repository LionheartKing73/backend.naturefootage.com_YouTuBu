<?php


namespace BE\Clarifai\Sdk\Token;

require_once (APPPATH . '/libraries/clarifai/src/Contracts/AuthenticationToken.php');

/**
 * Class AuthenticationToken
 * @package BE\Clarifai\Sdk
 */
    class AuthenticationToken implements \BE\Clarifai\Sdk\Contracts\AuthenticationToken
{

    /**
     * @var string
     */
    private $token;

    /**
     * @var \DateTime
     */
    private $expire;

    public function __construct($token, \DateTime $expire)
    {
        $this->token = $token;
        $this->expire = $expire;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return \DateTime
     */
    public function getExpire($format = 'Y-m-d H:i:s')
    {
        return $this->expire->format($format);
    }

    /**
     * return with little buffer
     *
     * @return boolean
     */
    public function isExpired()
    {
        return ($this->expire->getTimestamp() + 10) < time();
    }
}