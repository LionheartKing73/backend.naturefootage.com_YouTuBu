<?php

class Collections extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Collections() {
        parent::__construct();
        $this->load->model('collections_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Library settings / Collections';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $all = $this->collections_model->get_collections_count();
        $data['collections'] = $this->collections_model->get_collections_list(array(), $limit);
        $data['paging'] = $this->api->get_pagination('collections/view', $all, $this->settings['perpage']);
        $content = $this->load->view('collections/view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Collections / Edit collection';
        else
            $this->path = 'Collections / Add collection';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->collections_model->save_collection($this->id);
            if ($this->id) {
                redirect($this->langs . '/collections/view');
            } else {
                redirect($this->langs . '/collections/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->collections_model->get_collection($this->id);
        }
        $this->load->model('frontends_model');
        $data['frontends'] = $this->frontends_model->get_frontends_list(array('status' => 1));
        $data['lang'] = $this->langs;
        $content = $this->load->view('collections/edit', $data, true);
        $this->out($content);
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->collections_model->delete_collections($ids);
        redirect($this->langs . '/collections/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('name')) {
            $this->error = $this->lang->line('empty_fields');
            return false;
        }
        return true;
    }

    function out($content = null, $pagination = null, $type = 1) {
        $this->builder->output(array('content' => $content, 'path' => $this->path, 'pagination' => $pagination,
            'error' => $this->error, 'message' => $this->message), $type);
    }
}