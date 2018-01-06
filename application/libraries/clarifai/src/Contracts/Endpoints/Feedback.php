<?php

namespace BE\Clarifai\Sdk\Contracts\Endpoints;

use BE\Clarifai\Sdk\Responses\Feedback\FeedbackResponse;

/**
 * Interface Feedback
 * @package BE\Clarifai\Sdk\Contracts\Endpoints
 */
interface Feedback
{
    /**
     * @return FeedbackResponse
     */
    public function feedback();

    /**
     * @return string
     */
    public function getUrl();

    /**
     * @param string $url
     * @return Feedback
     */
    public function setUrl($url);

    /**
     * @return string
     */
    public function getDocIds();

    /**
     * @param string $docIds
     * @return Feedback
     */
    public function setDocIds($docIds);

    /**
     * @return string
     */
    public function getAddTags();

    /**
     * @param string $addTags
     * @return Feedback
     */
    public function setAddTags($addTags);

    /**
     * @return string
     */
    public function getRemoveTags();

    /**
     * @param string $removeTags
     * @return Feedback
     */
    public function setRemoveTags($removeTags);

    /**
     * @return string
     */
    public function getSimilarDocIds();

    /**
     * @param string $similarDocIds
     * @return Feedback
     */
    public function setSimilarDocIds($similarDocIds);

    /**
     * @return string
     */
    public function getDissimilarDocIds();

    /**
     * @param string $dissimilarDocIds
     * @return Feedback
     */
    public function setDissimilarDocIds($dissimilarDocIds);

    /**
     * @return string
     */
    public function getSearchClick();

    /**
     * @param string $searchClick
     * @return Feedback
     */
    public function setSearchClick($searchClick);
}