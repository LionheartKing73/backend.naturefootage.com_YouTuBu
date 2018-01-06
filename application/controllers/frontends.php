<?php

class Frontends extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Frontends() {
        parent::__construct();
        $this->load->model('frontends_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Manage system / Manage sites';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'id desc';
        $all = $this->frontends_model->get_frontends_count();
        $data['frontends'] = $this->frontends_model->get_frontends_list_with_providers(array(), $limit, $order_by);
        $data['paging'] = $this->api->get_pagination('frontends/view', $all, $this->settings['perpage']);
        $content = $this->load->view('frontends/view', $data, true);
        $this->out($content);
    }

    function manage() {
        $this->path = 'Manage sites / Manage';
        $frontends = $this->frontends_model->get_frontends_list_with_providers(array('f.id' => $this->id));
        if($frontends)
            $data = $frontends[0];
        $data['lang'] = $this->langs;
        $content = $this->load->view('frontends/manage', $data, true);
        $this->out($content);
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function out($content = null, $pagination = null, $type = 1) {
        $this->builder->output(array('content' => $content, 'path' => $this->path, 'pagination' => $pagination,
            'error' => $this->error, 'message' => $this->message), $type);
    }

}