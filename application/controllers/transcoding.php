<?php

class Transcoding extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Collections() {
        parent::__construct();
        //$this->load->model('collections_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Manage system / Transcoding';
        $data['lang'] = $this->langs;
        $content = $this->load->view('transcoding/view', $data, true);
        $this->out($content);
    }

    function out($content = null, $pagination = null, $type = 1) {
        $this->builder->output(array('content' => $content, 'path' => $this->path, 'pagination' => $pagination,
                                     'error' => $this->error, 'message' => $this->message), $type);
    }
}
