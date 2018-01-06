<?php

class Pricingterm extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Pricingterm() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('pricing_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Pricing / Terms';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'term_cat, territory, sort';
        $all = $this->pricing_model->get_terms_count();
        $data['terms'] = $this->pricing_model->get_terms_list($limit, $order_by);
        $data['paging'] = $this->api->get_pagination('pricingterm/view', $all, $this->settings['perpage']);
        $content = $this->load->view('pricing/term_view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Pricing / Edit term';
        else
            $this->path = 'Pricing / Add term';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->pricing_model->save_term($this->id);
            if ($this->id) {
                redirect($this->langs . '/pricingterm/view');
            } else {
                redirect($this->langs . '/pricingterm/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->pricing_model->get_term($this->id);
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('pricing/term_edit', $data, true);
        $this->out($content);
    }

    function ord() {
        $ids = $this->input->post('ord');

        if(is_array($ids) && count($ids)) {
            foreach($ids as $id=>$ord) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_pricing_terms', array('sort' => intval($ord)));
            }
        }
        redirect($this->langs . '/pricingterm/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->pricing_model->delete_terms($ids);
        redirect($this->langs . '/pricingterm/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('term_cat') || !$this->input->post('territory') || !$this->input->post('term')
            || !$this->input->post('factor')) {
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