<?php


namespace BE\Clarifai\Sdk\Endpoints;

require_once (APPPATH . '/libraries/clarifai/src/Contracts/Endpoints/Tag.php');

use BE\Clarifai\Sdk\Contracts\Endpoint;
use BE\Clarifai\Sdk\EndpointsLinks;
use BE\Clarifai\Sdk\Exceptions\EndpointException;
use BE\Clarifai\Sdk\Responses\ResponseContainer;
use BE\Clarifai\Sdk\Responses\ResponseFactory;

/**
 * Class Tag
 * @package BE\Clarifai\Sdk\Endpoints
 */
class Tag implements \BE\Clarifai\Sdk\Contracts\Endpoints\Tag
{

    /**
     * @var ResponseFactory
     */
    private $responseFactory;

    /**
     * @var Endpoint
     */
    private $endpoint;

    /**
     * @var string
     */
    private $model;

    /**
     * @var string
     */
    private $language;

    /**
     * @var string
     */
    private $selectClasses;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $localId;

    /**
     * Tag constructor.
     * @param Endpoint $endpoint
     * @param ResponseFactory $responseFactory
     */
    public function __construct(Endpoint $endpoint, ResponseFactory $responseFactory)
    {
        $this->responseFactory = $responseFactory;
        $this->endpoint = $endpoint;
    }

    /**
     * @return string
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param $model
     * @return $this
     */
    public function setModel($model)
    {
        $this->model = $model;

        return $this;
    }

    /**
     * @return string
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * @param $language
     * @return $this
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * @param $selectClasses
     * @return $this
     */
    public function setClasses($selectClasses)
    {
        $this->selectClasses = $selectClasses;

        return $this;
    }

    /**
     * @return string
     */
    public function getClasses()
    {
        return $this->selectClasses;
    }

    /**
     * url to file or local path to predict
     * local is path is not supported yet
     *
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return string
     */
    public function getLocalId()
    {
        return $this->localId;
    }

    /**
     * @param string $localId
     * @return $this
     */
    public function setLocalId($localId)
    {
        $this->localId = $localId;

        return $this;
    }

    /**
     * @param string|null $path url or local path
     * local is path is not supported yet
     *
     * @return ResponseContainer
     */
    public function predict($path = null, $localId = null)
    {
        if (!empty($path)) {
            $this->setPath($path);
        }

        if (!empty($localId)) {
            $this->setLocalId($localId);
        }

        $params = [];

        if ($this->getPath()) {
            $params['url'] = $this->getPath();
        }

        if ($this->getLocalId()) {
            $params['local_id'] = $this->getLocalId();
        }

        if ($this->getModel()) {
            // add model if specified
            $params['model'] = $this->getModel();
        }

        if ($this->getLanguage()) {
            // add language, if specified
            $params['language'] = $this->getLanguage();
        }

        if ($this->getClasses()) {
            // add select classes if specified
            $params['select_classes'] = $this->getClasses();
        }

        $this->endpoint->request(EndpointsLinks::TAG, $params);

        if (!$this->endpoint->isSuccess()) {
            throw new EndpointException (
                $this->responseFactory->createErrorResponse()
                    ->setStatusCode($this->endpoint->response()->status_code)
                    ->setStatusMessage($this->endpoint->response()->status_msg)
            );
        }

        $this->clearFields();
        
        $results = [];

        foreach ($this->endpoint->response()->results as $result) {
            $results[] = $this->responseFactory
                ->createTagResponseContainer()
                ->setDocId($result->docid)
                ->setDocIdStr($result->docid_str)
                ->setUrl($result->url)
                ->setStatusCode($result->status_code)
                ->setStatusMessage($result->status_msg)
                ->setLocalId($result->local_id)
                ->setTag($this->responseFactory
                    ->createTagResponse()
                    ->setClasses($result->result->tag->classes)
                    ->setConceptIds($result->result->tag->concept_ids)
                    ->setProbs($result->result->tag->probs)
                );
        }

        return $this->responseFactory
            ->createContainerResponse()
            ->setMeta($this->endpoint->response()->meta)
            ->setStatusMessage($this->endpoint->response()->status_msg)
            ->setStatusCode($this->endpoint->response()->status_code)
            ->setResults($results);
    }

    /**
     * clear preset fields for next request
     */
    private function clearFields()
    {
        $this->path = null;
        $this->model = null;
        $this->language = null;
        $this->selectClasses = null;
        $this->localId = null;
    }
}