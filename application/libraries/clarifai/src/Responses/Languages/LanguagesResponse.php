<?php


namespace BE\Clarifai\Sdk\Responses\Languages;

use BE\Clarifai\Sdk\Responses\Mixed\StatusCodeTrait;
use BE\Clarifai\Sdk\Responses\Mixed\StatusMessageTrait;

class LanguagesResponse
{
    use StatusCodeTrait, StatusMessageTrait;

    /**
     * @var array
     */
    private $languages;

    /**
     * @return array
     */
    public function getLanguages()
    {
        return $this->languages;
    }

    /**
     * @param array $languages
     * @return LanguagesResponse
     */
    public function setLanguages($languages)
    {
        $this->languages = $languages;
        return $this;
    }
}