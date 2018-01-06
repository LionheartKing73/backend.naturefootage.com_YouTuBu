<?php

class Presets extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function __construct() {
        parent::__construct();
        $this->load->model('presets_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Library settings / Presets';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $all = $this->presets_model->get_presets_count();
        $data['presets'] = $this->presets_model->get_presets_list(array(), $limit);
        $data['paging'] = $this->api->get_pagination('presets/view', $all, $this->settings['perpage']);
        $content = $this->load->view('presets/view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Presets / Edit preset';
        else
            $this->path = 'Presets / Add preset';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->presets_model->save_preset($this->id);
            redirect($this->langs . '/presets/view');
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->presets_model->get_preset($this->id);
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('presets/edit', $data, true);
        $this->out($content);
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->presets_model->delete_presets($ids);
        redirect($this->langs . '/presets/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('name') || !$this->input->post('resolution')) {
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