<?php

class Sequences extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Sequences() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('sequences_model');
        $this->load->model('groups_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
        $this->path = 'Library settings / Sequences';
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

        $all = $this->sequences_model->get_sequences_count($filter);
        $data['sequences'] = $this->sequences_model->get_sequences_list($filter, $limit);
        $data['paging'] = $this->api->get_pagination('sequences/view', $all, $this->settings['perpage']);
        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $res['sequences_list'] = $this->load->view('cliplog/sequences_list', array('sequences' => $data['sequences'], 'lang' => $data['lang']), true);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else{
            $content = $this->load->view('sequences/view', $data, true);
            $this->out($content);
        }
    }

    function edit() {

        $action = $this->id ? 'Edit' : 'Add';
        $this->path .= ' / ' . $action;

        $check = $this->sequences_model->get_sequence($this->id);
        if ($check['provider_id'] === $this->session->userdata('client_uid')
            || $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']
            || !$this->id) {

            if ($this->input->post('save') && $this->check_details()) {
                $id = $this->sequences_model->save_sequence($this->id);
                if($this->input->is_ajax_request()){
                    $res = array('success' => 1);
                    $this->output->set_content_type('application/json');;
                    echo json_encode($res);
                    exit();
                }
                else{
                    if ($this->id) {
                        redirect($this->langs . '/sequences/view');
                    } else {
                        redirect($this->langs . '/sequences/edit/' . $id);
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
                $data = $this->sequences_model->get_sequence($this->id);
            }
            $data['lang'] = $this->langs;
            $content = $this->load->view('sequences/edit', $data, true);
            $this->out($content);
        }
        else{
            redirect($this->langs . '/sequences/view');
        }
    }

    function items(){
        if($this->input->post('items_ids') && $this->id){
            $this->sequences_model->add_items($this->id, $this->input->post('items_ids'));
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
                $this->db_master->update('lib_sequences', array('sort' => intval($ord)));
            }
        }
        redirect($this->langs . '/sequences/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->sequences_model->delete_sequences($ids);
        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else{
            redirect($this->langs . '/sequences/view');
        }
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
    function get_sequence(){
        $res = array();
        if($this->input->post('sequence_id')){
            $sequence = $this->sequences_model->get_sequence($this->input->post('sequence_id'));
            if($sequence){
                $res['success'] = 1;
                $res['sequence'] = $sequence;
                $this->output->set_content_type('application/json');;
                echo json_encode($res);
                exit();
            }
        }
    }
}