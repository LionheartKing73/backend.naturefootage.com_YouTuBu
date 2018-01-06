<?php

class MY_Config extends CI_Config {

    function __construct()
    {
        parent::__construct();
    }

    /**
     * Site URL
     * Returns base_url . index_page [. uri_string]
     *
     * @access    public
     * @param string $uri the URI string
     * @param bool $skip_url_suffix
     * @return string
     */
    function site_url($uri = '', $skip_url_suffix = false)
    {
        if ($uri == '')
        {
            return $this->slash_item('base_url').$this->item('index_page');
        }

        if ($this->item('enable_query_strings') == FALSE)
        {
            $url = '';
            $url .= $this->slash_item('base_url').$this->slash_item('index_page').$this->_uri_string($uri);
            if(!$skip_url_suffix && $suffix = $this->item('url_suffix')){
                $url .= $suffix;
            }
            return $url;
        }
        else
        {
            return $this->slash_item('base_url').$this->item('index_page').'?'.$this->_uri_string($uri);
        }
    }
}