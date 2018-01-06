<?php

class Pricingrfdiscount extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Pricingrfdiscount() {
        parent::__construct();
        $this->load->model('pricing_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Pricing / RF discounts';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'count, discount';
        $all = $this->pricing_model->get_rf_discounts_count();
        $data['discounts'] = $this->pricing_model->get_rf_discounts_list($limit, $order_by);
        $data['paging'] = $this->api->get_pagination('pricingrfdiscount/view', $all, $this->settings['perpage']);
        $content = $this->load->view('pricing/rf_discount_view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Pricing / Edit RF discount';
        else
            $this->path = 'Pricing / Add RF discount';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->pricing_model->save_rf_discount($this->id);
            if ($this->id) {
                redirect($this->langs . '/pricingrfdiscount/view');
            } else {
                redirect($this->langs . '/pricingrfdiscount/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->pricing_model->get_rf_discount($this->id);
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('pricing/rf_discount_edit', $data, true);
        $this->out($content);
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->pricing_model->delete_rf_discounts($ids);
        redirect($this->langs . '/pricingrfdiscount/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('count') || !$this->input->post('discount')) {
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