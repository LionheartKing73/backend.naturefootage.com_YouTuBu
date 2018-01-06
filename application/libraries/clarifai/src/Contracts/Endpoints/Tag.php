<?php

namespace BE\Clarifai\Sdk\Contracts\Endpoints;

use BE\Clarifai\Sdk\Responses\ResponseContainer;


/**
 *
 * Interface Tag
 * @package BE\Clarifai\Sdk\Contracts\Endpoints
 */
interface Tag
{
    /**
     * @return mixed
     */
    public function getModel();

    /**
     * @param $model
     * @return $this
     */
    public function setModel($model);

    /**
     * @return mixed
     */
    public function getLanguage();

    /**
     * @param $language
     * @return $this
     */
    public function setLanguage($language);

    /**
     * @return string
     */
    public function getClasses();

    /**
     * @param $selectClasses
     * @return $this
     */
    public function setClasses($selectClasses);

    /**
     * @return mixed
     */
    public function getPath();

    /**
     * @param $path
     * @return $this
     */
    public function setPath($path);

    /**
     * @return string
     */
    public function getLocalId();

    /**
     * @param string $localId
     * @return $this
     */
    public function setLocalId($localId);

    /**
     * @param null $path
     * @return ResponseContainer
     */
    public function predict($path = null);
}