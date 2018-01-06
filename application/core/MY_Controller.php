<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AppController extends CI_Controller {
    var $langs;

    function __construct() {
        parent::__construct();
        $this->langs = $this->uri->segment(1);
        if (!in_array($this->langs, array_keys($this->config->item('support_languages')))) {
            $this->langs = $this->config->item('default_language');
            array_unshift($this->uri->segments, null, $this->langs);
            unset($this->uri->segments[0]);
        }
    }

    function out($content=null, $pagination=null, $type=0) {
        $this->builder->output(array('content'=>$content, 'path'=>$this->path, 'pagination'=>$pagination,
            'error'=>$this->error, 'message'=>$this->message), $type);
    }
}