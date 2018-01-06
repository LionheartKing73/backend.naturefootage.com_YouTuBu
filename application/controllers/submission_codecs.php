<?php

class Submission_codecs extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('submission_codecs_model');
        $this->load->model('deliveryoptions_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Media Formats / Submission Format';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'sort';
        $all = $this->submission_codecs_model->get_submission_codecs_count();
        $data['submission_codecs'] = $this->submission_codecs_model->get_submission_codecs_list($limit, $order_by);
        $data['paging'] = $this->api->get_pagination('submission_codecs/view', $all, $this->settings['perpage']);
        $content = $this->load->view('submission_codecs/view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Media Formats / Edit Submission Format';
        else
            $this->path = 'Media Formats / Add Submission Format';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->submission_codecs_model->save_submission_codec($this->id);
            if ($this->id) {
                redirect($this->langs . '/submission_codecs/view');
            } else {
                redirect($this->langs . '/submission_codecs/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->submission_codecs_model->get_submission_codec($this->id);
        }
        $data['delivery_categories'] = $this->deliveryoptions_model->get_delivery_categories_list('', 'description');
        $data['lang'] = $this->langs;
        $content = $this->load->view('submission_codecs/edit', $data, true);
        $this->out($content);
    }

    function ord() {
        $ids = $this->input->post('ord');

        if(is_array($ids) && count($ids)) {
            foreach($ids as $id=>$ord) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_submission_codecs', array('sort' => intval($ord)));
            }
        }
        redirect($this->langs . '/submission_codecs/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->submission_codecs_model->delete_submission_codecs($ids);
        redirect($this->langs . '/submission_codecs/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('name')) {
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