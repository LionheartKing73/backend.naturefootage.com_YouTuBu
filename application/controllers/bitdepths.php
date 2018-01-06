<?php

class Bitdepths extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Bitdepths() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('formats_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Media Formats / Source Bit Depth';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'sort';
        $all = $this->formats_model->get_bit_depths_count();
        $data['bit_depths'] = $this->formats_model->get_bit_depths_list($limit, $order_by);
        $data['paging'] = $this->api->get_pagination('bitdepths/view', $all, $this->settings['perpage']);
        $content = $this->load->view('formats/bit_depths_view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Media Formats / Edit Source Bit Depth';
        else
            $this->path = 'Media Formats / Add Source Bit Depth';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->formats_model->save_bit_depth($this->id);
            if ($this->id) {
                redirect($this->langs . '/bitdepths/view');
            } else {
                redirect($this->langs . '/bitdepths/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->formats_model->get_bit_depth($this->id);
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('formats/bit_depths_edit', $data, true);
        $this->out($content);
    }

    function ord() {
        $ids = $this->input->post('ord');

        if(is_array($ids) && count($ids)) {
            foreach($ids as $id=>$ord) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_bit_depth', array('sort' => intval($ord)));
            }
        }
        redirect($this->langs . '/bitdepths/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->formats_model->delete_bit_depths($ids);
        redirect($this->langs . '/bitdepths/view');
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