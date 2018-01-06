<?php

namespace BE\Clarifai\Sdk\Responses;

require_once (APPPATH . '/libraries/clarifai/src/Token/AuthenticationToken.php');
require_once (APPPATH . '/libraries/clarifai/src/Responses/Tag/TagResponse.php');
require_once (APPPATH . '/libraries/clarifai/src/Responses/Tag/TagResponseContainer.php');
require_once (APPPATH . '/libraries/clarifai/src/Responses/ResponseContainer.php');
require_once (APPPATH . '/libraries/clarifai/src/Responses/Languages/LanguagesResponse.php');
require_once (APPPATH . '/libraries/clarifai/src/Responses/Feedback/FeedbackResponse.php');
require_once (APPPATH . '/libraries/clarifai/src/Exceptions/NotImplementedMethodException.php');

use BE\Clarifai\Sdk\Exceptions\NotImplementedMethodException;
use BE\Clarifai\Sdk\Responses\Feedback\FeedbackResponse;
use BE\Clarifai\Sdk\Responses\Languages\LanguagesResponse;
use BE\Clarifai\Sdk\Responses\Tag\TagResponse;
use BE\Clarifai\Sdk\Responses\Tag\TagResponseContainer;
use BE\Clarifai\Sdk\Token\AuthenticationToken;

class ResponseFactory
{
    /**
     * @param $tokenString
     * @param \DateTime $expire
     * @return \BE\Clarifai\Sdk\Contracts\AuthenticationToken
     */
    public function createToken($tokenString, \DateTime $expire)
    {
        return new AuthenticationToken($tokenString, $expire);
    }

    /**
     * @return ErrorResponse
     */
    public function createErrorResponse()
    {
        return new ErrorResponse();
    }

    /**
     *
     * @return ResponseContainer
     */
    public function createContainerResponse()
    {
        return new ResponseContainer();
    }

    /**
     * @return TagResponseContainer
     */
    public function createTagResponseContainer()
    {
        return new TagResponseContainer();
    }

    /**
     * @param $initData
     * @return TagResponse
     */
    public function createTagResponse()
    {
        return new TagResponse();
    }

    /**
     * @return LanguagesResponse
     */
    public function createLanguagesResponse()
    {
        return new LanguagesResponse();
    }

    /**
     * @TODO implement :)
     */
    public function createInfoResponse()
    {
        throw new NotImplementedMethodException();
    }

    /**
     * @return FeedbackResponse
     */
    public function createFeedbackResponse()
    {
       return new FeedbackResponse();
    }
}