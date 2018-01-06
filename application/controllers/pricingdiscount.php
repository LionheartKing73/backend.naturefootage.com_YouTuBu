<?php

class Pricingdiscount extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Pricingdiscount() {
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
        $this->path = 'Pricing / RM Discounts';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'duration, discount';
        $all = $this->pricing_model->get_discounts_count();
        $data['discounts'] = $this->pricing_model->get_discounts_list($limit, $order_by);
        $data['paging'] = $this->api->get_pagination('pricingdiscount/view', $all, $this->settings['perpage']);
        $content = $this->load->view('pricing/discount_view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Pricing / Edit discount';
        else
            $this->path = 'Pricing / Add discount';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->pricing_model->save_discount($this->id);
            if ($this->id) {
                redirect($this->langs . '/pricingdiscount/view');
            } else {
                redirect($this->langs . '/pricingdiscount/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->pricing_model->get_discount($this->id);
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('pricing/discount_edit', $data, true);
        $this->out($content);
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->pricing_model->delete_discounts($ids);
        redirect($this->langs . '/pricingdiscount/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('duration') || !$this->input->post('discount')) {
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