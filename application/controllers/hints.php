<?php

class Hints extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Hints() {
        parent::__construct();
        $this->load->model('hints_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Library settings / Hints';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $all = $this->hints_model->get_hints_count();
        $data['hints'] = $this->hints_model->get_hints_list(array(), $limit);
        $fields = $this->hints_model->get_fields_list();
        foreach($fields as $field){
            $data['fields'][$field['name']] = $field['title'];
        }
        $data['paging'] = $this->api->get_pagination('hints/view', $all, $this->settings['perpage']);
        $content = $this->load->view('hints/view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Hints / Edit hint';
        else
            $this->path = 'Hints / Add hint';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->hints_model->save_hint($this->id);
            redirect($this->langs . '/hints/view');
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->hints_model->get_hint($this->id);
        }
        $data['fields'] = $this->hints_model->get_fields_list();
        $data['lang'] = $this->langs;
        $content = $this->load->view('hints/edit', $data, true);
        $this->out($content);
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->hints_model->delete_hints($ids);
        redirect($this->langs . '/hints/view');
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