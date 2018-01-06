<?php

/**
 * Header Redirect
 *
 * Header redirect in two flavors
 * For very fine grained control over headers, you could use the Output
 * Library's set_header() function.
 *
 * @access    public
 * @param string $uri
 * @param string $method
 * @param int $http_response_code
 * @param bool $skip_url_suffix
 * @return string
 * @internal param the $string URL
 * @internal param the $string method: location or redirect
 */
function redirect($uri = '', $method = 'location', $http_response_code = 302, $skip_url_suffix = false)
{
    if ( ! preg_match('#^https?://#i', $uri))
    {
        $uri = site_url($uri, $skip_url_suffix);
    }

    switch($method)
    {
        case 'refresh'	: header("Refresh:0;url=".$uri);
            break;
        default			: header("Location: ".$uri, TRUE, $http_response_code);
            break;
    }
    exit;
}

/**
 * @param string $uri
 * @return string
 */
function site_url($uri = '', $skip_url_suffix = false)
{
    $CI =& get_instance();
    return $CI->config->site_url($uri, $skip_url_suffix);
}