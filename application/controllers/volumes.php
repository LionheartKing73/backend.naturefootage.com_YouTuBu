<?php

class Volumes extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function __construct() {
        parent::__construct();
        $this->load->model('volumes_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Library settings / Volumes';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $all = $this->volumes_model->get_volumes_count();
        $volumes = $this->volumes_model->get_volumes_list(array(), $limit, 'active DESC, name');
        foreach($volumes as $key => $volume) {
            if($volume['size'] > 0) {
                $volumes[$key]['used'] = round($volume['used'] * 100 / $volume['size']) . '%';
                $volumes[$key]['size'] = $this->volumes_model->humanize_size($volume['size']);
            }
        }
        $data['volumes'] = $volumes;
        $data['paging'] = $this->api->get_pagination('volumes/view', $all, $this->settings['perpage']);
        $content = $this->load->view('volumes/view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Volumes / Edit volume';
        else
            $this->path = 'Volumes / Add volume';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->volumes_model->save_volume($this->id);
            redirect($this->langs . '/volumes/view');
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->volumes_model->get_volume($this->id);
        }
        $data['fields'] = $this->volumes_model->get_fields_list();
        $data['lang'] = $this->langs;
        $content = $this->load->view('volumes/edit', $data, true);
        $this->out($content);
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->volumes_model->delete_volumes($ids);
        redirect($this->langs . '/volumes/view');
    }

    function full() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');
        $this->volumes_model->change_status($ids);
        redirect($this->langs . '/volumes/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('field') || !$this->input->post('text')) {
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