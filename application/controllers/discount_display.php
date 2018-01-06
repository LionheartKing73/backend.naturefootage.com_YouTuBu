<?php

class Discount_display extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function __construct() {
        parent::__construct();
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);
        $this->settings = $this->api->settings();
        $this->load->model('discount_display_model');
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Pricing / RM Discounts Display';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $all = $this->discount_display_model->get_discount_displays_count();
        $data['discount_displays'] = $this->discount_display_model->get_discount_displays_list(array(), $limit);
        $data['paging'] = $this->api->get_pagination('discount_display/view', $all, $this->settings['perpage']);
        $content = $this->load->view('discount_display/view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Pricing / Edit Discount Display';
        else
            $this->path = 'Pricing / Add Discount Display';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->discount_display_model->save_discount_display($this->id);
            if ($this->id) {
                redirect($this->langs . '/discount_display/view');
            } else {
                redirect($this->langs . '/discount_display/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->discount_display_model->get_discount_display($this->id);
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('discount_display/edit', $data, true);
        $this->out($content);
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->discount_display_model->delete_discount_displays($ids);
        redirect($this->langs . '/discount_display/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('type')) {
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