<?php


namespace BE\Clarifai\Sdk\Responses\Tag;

require_once (APPPATH . '/libraries/clarifai/src/Responses/Mixed/StatusCodeTrait.php');
require_once (APPPATH . '/libraries/clarifai/src/Responses/Mixed/StatusMessageTrait.php');

use BE\Clarifai\Sdk\Responses\Mixed\StatusCodeTrait;
use BE\Clarifai\Sdk\Responses\Mixed\StatusMessageTrait;

class TagResponseContainer
{
    use StatusCodeTrait, StatusMessageTrait;

    /**
     * @var float
     */
    private $docId;

    /**
     * @var string
     */
    private $docIdStr;

    /**
     * @var string
     */
    private $url;

    /**
     * @var mixed
     */
    private $localId;

    /**
     * @var TagResponse
     */
    private $tag;

    /**
     * @return float
     */
    public function getDocId()
    {
        return $this->docId;
    }

    /**
     * @param float $docId
     * @return TagResponseContainer
     */
    public function setDocId($docId)
    {
        $this->docId = $docId;
        return $this;
    }

    /**
     * @return string
     */
    public function getDocIdStr()
    {
        return $this->docIdStr;
    }

    /**
     * @param string $docIdStr
     * @return TagResponseContainer
     */
    public function setDocIdStr($docIdStr)
    {
        $this->docIdStr = $docIdStr;
        return $this;
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
     * @return TagResponseContainer
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getLocalId()
    {
        return $this->localId;
    }

    /**
     * @param mixed $localId
     * @return TagResponseContainer
     */
    public function setLocalId($localId)
    {
        $this->localId = $localId;
        return $this;
    }

    /**
     * @return TagResponse
     */
    public function getTag()
    {
        return $this->tag;
    }

    /**
     * @param TagResponse $tag
     * @return TagResponseContainer
     */
    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }

}