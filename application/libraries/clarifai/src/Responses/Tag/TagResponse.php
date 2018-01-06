<?php


namespace BE\Clarifai\Sdk\Responses\Tag;

/**
 *
 * Class TagResponse
 * @package BE\Clarifai\Sdk\Responses
 */
class TagResponse
{
    /**
     * @var array
     */
    private $classes;

    /**
     * @var array
     */
    private $conceptIds;

    /**
     * @var array
     */
    private $probs;

    /**
     * @return array
     */
    public function getClasses()
    {
        return $this->classes;
    }

    /**
     * @param array $classes
     * @return TagResponse
     */
    public function setClasses($classes)
    {
        $this->classes = $classes;
        return $this;
    }

    /**
     * @return array
     */
    public function getConceptIds()
    {
        return $this->conceptIds;
    }

    /**
     * @param array $conceptIds
     * @return TagResponse
     */
    public function setConceptIds($conceptIds)
    {
        $this->conceptIds = $conceptIds;
        return $this;
    }

    /**
     * @return array
     */
    public function getProbs()
    {
        return $this->probs;
    }

    /**
     * @param array $probs
     * @return TagResponse
     */
    public function setProbs($probs)
    {
        $this->probs = $probs;
        return $this;
    }


}