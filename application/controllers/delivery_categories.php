<?php

class Delivery_categories extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('deliveryoptions_model');
        $this->id = $this->uri->segment(4);
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Media Formats / Delivery Category';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'display_order';
        $all = $this->deliveryoptions_model->get_delivery_categories_count();
        $data['delivery_categories'] = $this->deliveryoptions_model->get_delivery_categories_list($limit, $order_by);
        $data['paging'] = $this->api->get_pagination('delivery_categories/view', $all, $this->settings['perpage']);
        $content = $this->load->view('delivery_categories/view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Media Formats / Edit Delivery category';
        else
            $this->path = 'Media Formats / Add Delivery category';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->deliveryoptions_model->save_delivery_category($this->id);
            redirect($this->langs . '/delivery_categories/view');
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->deliveryoptions_model->get_delivery_category($this->id);
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('delivery_categories/edit', $data, true);
        $this->out($content);
    }

    function ord() {
        $ids = $this->input->post('ord');

        if(is_array($ids) && count($ids)) {
            foreach($ids as $id=>$ord) {
                $this->db_master->where('pk', $id);
                $this->db_master->update('lib_pricing_category_type', array('display_order' => intval($ord)));
            }
        }
        redirect($this->langs . '/delivery_categories/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->deliveryoptions_model->delete_delivery_categories($ids);
        redirect($this->langs . '/delivery_categories/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('id') || !$this->input->post('description')) {
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