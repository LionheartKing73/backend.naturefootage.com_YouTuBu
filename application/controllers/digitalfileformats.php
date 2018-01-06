<?php

class Digitalfileformats extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Digitalfileformats() {
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
        $this->path = 'Formats / Digital file format';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'sort';
        $all = $this->formats_model->get_digital_file_formats_count();
        $data['digital_file_formats'] = $this->formats_model->get_digital_file_formats_list($limit, $order_by);
        $data['paging'] = $this->api->get_pagination('digitalfileformats/view', $all, $this->settings['perpage']);
        $content = $this->load->view('formats/digital_file_formats_view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Formats / Edit digital file format';
        else
            $this->path = 'Formats / Add digital file format';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->formats_model->save_digital_file_format($this->id);
            if ($this->id) {
                redirect($this->langs . '/digitalfileformats/view');
            } else {
                redirect($this->langs . '/digitalfileformats/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->formats_model->get_digital_file_format($this->id);
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('formats/digital_file_formats_edit', $data, true);
        $this->out($content);
    }

    function ord() {
        $ids = $this->input->post('ord');

        if(is_array($ids) && count($ids)) {
            foreach($ids as $id=>$ord) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_digital_file_formats', array('sort' => intval($ord)));
            }
        }
        redirect($this->langs . '/digitalfileformats/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->formats_model->delete_digital_file_formats($ids);
        redirect($this->langs . '/digitalfileformats/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('format')) {
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