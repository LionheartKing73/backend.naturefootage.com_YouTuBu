<?php

class Camerachipsize extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Camerachipsize() {
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
        $this->path = 'Media Formats / Camera Sensor';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'sort';
        $all = $this->formats_model->get_camera_chip_sizes_count();
        $data['camera_chip_sizes'] = $this->formats_model->get_camera_chip_sizes_list($limit, $order_by);
        $data['paging'] = $this->api->get_pagination('camerachipsize/view', $all, $this->settings['perpage']);
        $content = $this->load->view('formats/camera_chip_size_view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Media Formats / Edit Camera Sensor';
        else
            $this->path = 'Media Formats / Add Camera Sensor';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->formats_model->save_camera_chip_size($this->id);
            if ($this->id) {
                redirect($this->langs . '/camerachipsize/view');
            } else {
                redirect($this->langs . '/camerachipsize/edit/' . $id);
            }
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->formats_model->get_camera_chip_size($this->id);
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('formats/camera_chip_size_edit', $data, true);
        $this->out($content);
    }

    function ord() {
        $ids = $this->input->post('ord');

        if(is_array($ids) && count($ids)) {
            foreach($ids as $id=>$ord) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_camera_chip_size', array('sort' => intval($ord)));
            }
        }
        redirect($this->langs . '/camerachipsize/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->formats_model->delete_camera_chip_sizes($ids);
        redirect($this->langs . '/camerachipsize/view');
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