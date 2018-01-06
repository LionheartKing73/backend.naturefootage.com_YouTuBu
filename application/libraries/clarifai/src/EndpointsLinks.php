<?php

namespace BE\Clarifai\Sdk;

/**
 * List of endpoints urls
 *
 * Class EndpointsLinks
 * @package BE\Clarifai\Sdk
 */
final class EndpointsLinks
{
    /**
     * Retrieve access token
     */
    const AUTHENTICATION = 'https://api.clarifai.com/v1/token';

    /**
     * Prediction endpoint
     */
    const TAG = 'https://api.clarifai.com/v1/tag/';

    /**
     * Send feedback about results
     */
    const FEEDBACK = 'https://api.clarifai.com/v1/feedback/';

    /**
     * Receive info about domnant colors
     */
    const COLOR = 'https://api.clarifai.com/v1/color/';

    /**
     * API details
     */
    const INFO = 'https://api.clarifai.com/v1/info/';

    /**
     * list of languages api support
     */
    const LANGUAGE = 'https://api.clarifai.com/v1/info/languages/';

    /**
     * current api usage for month and hour
     */
    const USAGE = 'https://api.clarifai.com/v1/usage/';

    /**
     * avoid instance creation
     *
     * EndpointsList constructor.
     */
    private function __construct()
    {
    }
}