<?php


namespace BE\Clarifai\Sdk\Endpoints;
use BE\Clarifai\Sdk\Contracts\Endpoint;
use BE\Clarifai\Sdk\EndpointsLinks;
use BE\Clarifai\Sdk\Exceptions\EndpointException;
use BE\Clarifai\Sdk\Responses\Feedback\FeedbackResponse;
use BE\Clarifai\Sdk\Responses\ResponseFactory;

/**
 * Class Feedback
 * @package BE\Clarifai\Sdk\Endpoints
 */
class Feedback implements \BE\Clarifai\Sdk\Contracts\Endpoints\Feedback
{
    /**
     * @var Endpoint
     */
    private $endpoint;

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var string
     */
    private $url;

    /**
     * doc id of image r video to specify feedback,
     * perhaps can be list of ids separated by coma
     *
     * @var string
     */
    private $docIds;

    /**
     * @var string
     */
    private $addTags;

    /**
     * @var string
     */
    private $removeTags;

    /**
     * @var string
     */
    private $similarDocIds;

    /**
     * @var string
     */
    private $dissimilarDocIds;

    /**
     * @var string
     */
    private $searchClick;

    /**
     * Feedback constructor.
     * @param Endpoint $endpoint
     * @param ResponseFactory $responseFactory
     */
    public function __construct(Endpoint $endpoint, ResponseFactory $responseFactory)
    {
        $this->endpoint = $endpoint;
        $this->responseFactory = $responseFactory;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Feedback
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getDocIds()
    {
        return $this->docIds;
    }

    /**
     * @param string $docIds
     * @return Feedback
     */
    public function setDocIds($docIds)
    {
        $this->docIds = $docIds;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddTags()
    {
        return $this->addTags;
    }

    /**
     * @param string $addTags
     * @return Feedback
     */
    public function setAddTags($addTags)
    {
        $this->addTags = $addTags;
        return $this;
    }

    /**
     * @return string
     */
    public function getRemoveTags()
    {
        return $this->removeTags;
    }

    /**
     * @param string $removeTags
     * @return Feedback
     */
    public function setRemoveTags($removeTags)
    {
        $this->removeTags = $removeTags;
        return $this;
    }

    /**
     * @return string
     */
    public function getSimilarDocIds()
    {
        return $this->similarDocIds;
    }

    /**
     * @param string $similarDocIds
     * @return Feedback
     */
    public function setSimilarDocIds($similarDocIds)
    {
        $this->similarDocIds = $similarDocIds;
        return $this;
    }

    /**
     * @return string
     */
    public function getDissimilarDocIds()
    {
        return $this->dissimilarDocIds;
    }

    /**
     * @param string $dissimilarDocIds
     * @return Feedback
     */
    public function setDissimilarDocIds($dissimilarDocIds)
    {
        $this->dissimilarDocIds = $dissimilarDocIds;
        return $this;
    }

    /**
     * @return string
     */
    public function getSearchClick()
    {
        return $this->searchClick;
    }

    /**
     * @param string $searchClick
     * @return Feedback
     */
    public function setSearchClick($searchClick)
    {
        $this->searchClick = $searchClick;
        return $this;
    }

    /**
     * @return FeedbackResponse
     */
    public function feedback()
    {
        $params = [];

        if ($this->getDocIds()) {
            $params['docids'] = $this->getDocIds();
        } elseif ($this->getUrl()) {
            $params['url'] = $this->getUrl();
        }

        if ($this->getAddTags()) {
            $params['add_tags'] = $this->getAddTags();
        }

        if ($this->getRemoveTags()) {
            $params['remove_tags'] = $this->getRemoveTags();
        }

        if ($this->getSimilarDocIds()) {
            $params['similar_docids'] = $this->getSimilarDocIds();
        } elseif (false) {
            // @TODO check how to implement similar by urls
        }

        if ($this->getDissimilarDocIds()) {
            $params['dissimilar_docids'] = $this->getDissimilarDocIds();
        } elseif (false) {
            // @TODO check how to implement dessimilar by urls
        }

        if ($this->getSearchClick()) {
            $params['search_click'] = $this->getSearchClick();
        }

        $this->endpoint->request(EndpointsLinks::FEEDBACK, $params);

        if (!$this->endpoint->isSuccess()) {
            throw new EndpointException (
                $this->responseFactory->createErrorResponse()
                    ->setStatusCode($this->endpoint->response()->status_code)
                    ->setStatusMessage($this->endpoint->response()->status_msg)
            );
        }

        $this->clearFields();

        return $this->responseFactory
            ->createFeedbackResponse()
            ->setStatusCode($this->endpoint->response()->status_code)
            ->setStatusMessage($this->endpoint->response()->status_msg);
    }

    /**
     * clear preset fields for next request
     */
    private function clearFields()
    {
        $this->url = null;
        $this->docIds = null;
        $this->addTags = null;
        $this->removeTags = null;
        $this->searchClick = null;
        $this->similarDocIds = null;
        $this->dissimilarDocIds = null;
    }
}