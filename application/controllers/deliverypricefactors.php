<?php

class Deliverypricefactors extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Deliverypricefactors() {
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
        $this->path = 'Delivery options / Delivery price factor';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = '';
        $all = $this->deliveryoptions_model->get_delivery_price_factors_count();
        $data['delivery_price_factors'] = $this->deliveryoptions_model->get_delivery_price_factors_list($limit, $order_by);
        $data['paging'] = $this->api->get_pagination('deliverypricefactors/view', $all, $this->settings['perpage']);
        $content = $this->load->view('deliveryoptions/deliverypricefactors_view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Delivery options / Edit delivery price factor';
        else
            $this->path = 'Delivery options / Add delivery price factor';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->deliveryoptions_model->save_delivery_price_factor($this->id);
            if ($this->id) {
                redirect($this->langs . '/deliverypricefactors/view');
            } else {
                redirect($this->langs . '/deliverypricefactors/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->deliveryoptions_model->get_delivery_price_factor($this->id);
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('deliveryoptions/deliverypricefactors_edit', $data, true);
        $this->out($content);
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->deliveryoptions_model->delete_delivery_price_factors($ids);
        redirect($this->langs . '/deliverypricefactors/view');
    }

//    function ord() {
//        $ids = $this->input->post('ord');
//
//        if(is_array($ids) && count($ids)) {
//            foreach($ids as $id=>$ord) {
//                $this->db_master->where('id', $id);
//                $this->db_master->update('lib_delivery_options', array('display_order' => intval($ord)));
//            }
//        }
//        redirect($this->langs . '/deliverypricefactors/view');
//    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('format') || !$this->input->post('factor')) {
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