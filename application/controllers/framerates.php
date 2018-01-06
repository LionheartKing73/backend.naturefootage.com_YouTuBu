<?php

class Framerates extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Framerates() {
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
        $this->path = 'Media Formats / Frame Rate';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'sort';
        $all = $this->formats_model->get_frame_rates_count();
        $data['frame_rates'] = $this->formats_model->get_frame_rates_list($limit, $order_by);
        $data['paging'] = $this->api->get_pagination('framerates/view', $all, $this->settings['perpage']);
        $content = $this->load->view('formats/frame_rates_view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Media Formats / Edit Frame Rate';
        else
            $this->path = 'Media Formats / Add Frame Rate';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->formats_model->save_frame_rate($this->id);
            if ($this->id) {
                redirect($this->langs . '/framerates/view');
            } else {
                redirect($this->langs . '/framerates/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->formats_model->get_frame_rate($this->id);
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('formats/frame_rates_edit', $data, true);
        $this->out($content);
    }

    function ord() {
        $ids = $this->input->post('ord');

        if(is_array($ids) && count($ids)) {
            foreach($ids as $id=>$ord) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_frame_rates', array('sort' => intval($ord)));
            }
        }
        redirect($this->langs . '/framerates/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->formats_model->delete_frame_rates($ids);
        redirect($this->langs . '/framerates/view');
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