<?php

class Bins extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Bins() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('bins_model');
        $this->load->model('groups_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
        $this->path = 'Library settings / Bins';
        $this->set_group();
    }

    function index() {
        show_404();
    }

    function view() {
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $filter = array();

        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $group = $this->groups_model->get_group_by_user($uid);
        if($group['is_editor'] && $uid){
            $filter['provider_id'] = (int)$uid;
        }
        $all = $this->bins_model->get_bins_count($filter);
        $data['bins'] = $this->bins_model->get_bins_list($filter, $limit);
        $data['paging'] = $this->api->get_pagination('bins/view', $all, $this->settings['perpage']);
        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $res['bins_list'] = $this->load->view('cliplog/bins_list', array('bins' => $data['bins'], 'lang' => $data['lang']), true);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else{
            $content = $this->load->view('bins/view', $data, true);
            $this->out($content);
        }
    }

    function edit() {

        $action = $this->id ? 'Edit' : 'Add';
        $this->path .= ' / ' . $action;

        $check = $this->bins_model->get_bin($this->id);
        if ($check['provider_id'] === $this->session->userdata('client_uid')
            || $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']
            || !$this->id) {

            if ($this->input->post('save') && $this->check_details()) {
                $id = $this->bins_model->save_bin($this->id);
                if($this->input->is_ajax_request()){
                    $res = array('success' => 1);
                    $this->output->set_content_type('application/json');;
                    echo json_encode($res);
                    exit();
                }
                else{
                    if ($this->id) {
                        redirect($this->langs . '/bins/view');
                    } else {
                        redirect($this->langs . '/bins/edit/' . $id);
                    }
                }
            }
            elseif($this->input->is_ajax_request() && $this->error){
                $res = array('error' => $this->error);
                $this->output->set_content_type('application/json');;
                echo json_encode($res);
                exit();
            }

            $data = $this->input->post();
            if (!$this->error) {
                $data = $this->bins_model->get_bin($this->id);
            }
            $data['lang'] = $this->langs;
            $content = $this->load->view('bins/edit', $data, true);
            $this->out($content);
        }
        else{
            redirect($this->langs . '/bins/view');
        }
    }

    function items(){
        if($this->input->post('items_ids') && $this->id){
            $this->bins_model->add_items($this->id, $this->input->post('items_ids'));
        }

        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
    }

    function ord() {
        $ids = $this->input->post('ord');

        if(is_array($ids) && count($ids)) {
            foreach($ids as $id=>$ord) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_bins', array('sort' => intval($ord)));
            }
        }
        redirect($this->langs . '/bins/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->bins_model->delete_bins($ids);
        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else
            redirect($this->langs . '/bins/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('title')) {
            $this->error = $this->lang->line('empty_fields');
            return false;
        }
        return true;
    }

    function out($content = null, $pagination = null, $type = 1) {
        $this->builder->output(array('content' => $content, 'path' => $this->path, 'pagination' => $pagination,
            'error' => $this->error, 'message' => $this->message), $type);
    }

    function set_group() {
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $this->group = $this->groups_model->get_group_by_user($uid);
    }

    //For ajax
    function get_bin(){
        $res = array();
        if($this->input->post('bin_id')){
            $bin = $this->bins_model->get_bin($this->input->post('bin_id'));
            if($bin){
                $res['success'] = 1;
                $res['bin'] = $bin;
                $this->output->set_content_type('application/json');;
                echo json_encode($res);
                exit();
            }
        }
    }
}