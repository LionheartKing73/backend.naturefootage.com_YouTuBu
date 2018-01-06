<?php

class Deliveryoptionsrf extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Deliveryoptionsrf() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('deliveryoptions_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Delivery options / RF delivery options';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'display_order';
        $all = $this->deliveryoptions_model->get_rf_deliveryoptions_count();
        $data['deliveryoptions'] = $this->deliveryoptions_model->get_rf_deliveryoptions_list($limit, $order_by);
        $data['paging'] = $this->api->get_pagination('deliveryoptionsrf/view', $all, $this->settings['perpage']);
        $content = $this->load->view('deliveryoptions/deliveryoptionrf_view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Delivery options / Edit option';
        else
            $this->path = 'Delivery options / Add option';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->deliveryoptions_model->save_rf_deliveryoption($this->id);
            if ($this->id) {
                redirect($this->langs . '/deliveryoptionsrf/view');
            } else {
                redirect($this->langs . '/deliveryoptionsrf/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->deliveryoptions_model->get_rf_deliveryoption($this->id);
        }
        $data['lang'] = $this->langs;
        $data['delivery_price_factors'] = $this->deliveryoptions_model->get_delivery_price_factors_list();
        $content = $this->load->view('deliveryoptions/deliveryoptionrf_edit', $data, true);
        $this->out($content);
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->deliveryoptions_model->delete_rf_deliveryoptions($ids);
        redirect($this->langs . '/deliveryoptionsrf/view');
    }

    function ord() {
        $ids = $this->input->post('ord');

        if(is_array($ids) && count($ids)) {
            foreach($ids as $id=>$ord) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_rf_delivery_options', array('display_order' => intval($ord)));
            }
        }
        redirect($this->langs . '/deliveryoptionsrf/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('description')) {
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