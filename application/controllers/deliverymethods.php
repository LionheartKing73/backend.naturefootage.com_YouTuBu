<?php

class Deliverymethods extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Deliverymethods() {
        parent::__construct();
        $this->load->model('deliveryoptions_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Delivery options / Delivery Methods';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $all = $this->deliveryoptions_model->get_methods_count();
        $data['methods'] = $this->deliveryoptions_model->get_methods_list($limit);
        $data['paging'] = $this->api->get_pagination('deliverymethods/view', $all, $this->settings['perpage']);
        $content = $this->load->view('deliveryoptions/method_view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Delivery options / Edit delivery method';
        else
            $this->path = 'Delivery options / Add delivery method';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->deliveryoptions_model->save_method($this->id);
            if ($this->id) {
                redirect($this->langs . '/deliverymethods/view');
            } else {
                redirect($this->langs . '/deliverymethods/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->deliveryoptions_model->get_method($this->id);
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('deliveryoptions/method_edit', $data, true);
        $this->out($content);
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->deliveryoptions_model->delete_method($ids);
        redirect($this->langs . '/deliverymethods/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('code') || !$this->input->post('title')) {
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