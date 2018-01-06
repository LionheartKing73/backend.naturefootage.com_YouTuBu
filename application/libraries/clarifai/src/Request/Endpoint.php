<?php


namespace BE\Clarifai\Sdk\Request;

require_once (APPPATH . '/libraries/clarifai/src/Contracts/Endpoint.php');
require_once (APPPATH . '/libraries/clarifai/vendor/php-curl-class/php-curl-class/src/Curl/Curl.php');

use BE\Clarifai\Sdk\Exceptions\ErrorRequestException;
use Curl\Curl;

/**
 * Class Endpoint
 * @package BE\Clarifai\Sdk
 */
class Endpoint implements \BE\Clarifai\Sdk\Contracts\Endpoint
{
    /**
     * @var object
     */
    private $response;

    /**
     * @var boolean
     */
    private $successRequest;

    /**
     * http request status code
     *
     * @var int
     */
    private $statusCode;

    /**
     * send request to endpoint
     *
     * @param string $url
     * @param array $params
     * @param string $method
     *
     * @return mixed
     */
    public function request($url, array $params = [], array $headers = [], $method = 'POST')
    {
        $curl = new Curl();

        if (!empty($headers)) {
            $curl->setHeaders($headers);
        }

//        $curl->post($url, $params);
        if ($method == "GET") {
            $curl->get($url, $params);
        } else {
            $curl->post($url, $params);
        }

        $this->successRequest = !$curl->error;
        $this->statusCode = $curl->httpStatusCode;
        $this->response = $curl->response;

        // some unexpected api error
        if (!$this->isSuccess() && isset($this->response()->error)) {
            throw new ErrorRequestException(
                "Request ended with error, status code {$this->statusCode()} Error message: {$this->response()->error}"
            );
        }
    }

    /**
     * get result response
     *
     * @return object
     */
    public function response()
    {
        return $this->response;
    }

    /**
     * @return boolean true if succes, false if error
     */
    public function isSuccess()
    {
        return $this->successRequest;
    }

    /**
     * request status code
     *
     * @return mixed
     */
    public function statusCode()
    {
        return $this->statusCode;
    }
}