<?php

namespace BE\Clarifai\Sdk\Contracts\Endpoints;

use BE\Clarifai\Sdk\Responses\Languages\LanguagesResponse;

/**
 * Interface Language
 * @package BE\Clarifai\Sdk\Contracts\Endpoints
 */
interface Language
{
    /**
     * @return LanguagesResponse
     */
    public function getList();

}