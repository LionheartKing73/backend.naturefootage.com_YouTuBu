<?php

/**
 * Class Deliveryoptions
 * @property Deliveryoptions_model $deliveryoptions_model
 * @property Labs_model $labs_model
 */
class Deliveryoptions extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Deliveryoptions() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('deliveryoptions_model');
        $this->load->model('labs_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Delivery options / Options';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'delivery, display_order';
        $all = $this->deliveryoptions_model->get_deliveryoptions_count();
        $data['deliveryoptions'] = $this->deliveryoptions_model->get_deliveryoptions_list($limit, $order_by);
        $data['paging'] = $this->api->get_pagination('deliveryoptions/view', $all, $this->settings['perpage']);
        $content = $this->load->view('deliveryoptions/deliveryoption_view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Delivery options / Edit option';
        else
            $this->path = 'Delivery options / Add option';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->deliveryoptions_model->save_deliveryoption($this->id);
            if ($this->id) {
                redirect($this->langs . '/deliveryoptions/view');
            } else {
                redirect($this->langs . '/deliveryoptions/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->deliveryoptions_model->get_deliveryoption($this->id);
        }
        $data['lang'] = $this->langs;
        $data['delivery_methods'] = $this->deliveryoptions_model->get_methods_list();
        //$data['labs'] = $this->labs_model->get_labs_list();
        $data['delivery_price_factors'] = $this->deliveryoptions_model->get_delivery_price_factors_list();
        $content = $this->load->view('deliveryoptions/deliveryoption_edit', $data, true);
        $this->out($content);
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->deliveryoptions_model->delete_deliveryoptions($ids);
        redirect($this->langs . '/deliveryoptions/view');
    }

    function ord() {
        $ids = $this->input->post('ord');

        if(is_array($ids) && count($ids)) {
            foreach($ids as $id=>$ord) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_delivery_options', array('display_order' => intval($ord)));
            }
        }
        redirect($this->langs . '/deliveryoptions/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('code') || !$this->input->post('source') || !$this->input->post('destination')
            || !$this->input->post('description') || !strlen($this->input->post('price')) || !$this->input->post('delivery')) {
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