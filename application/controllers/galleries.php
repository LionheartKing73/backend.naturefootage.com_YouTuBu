<?php

class Galleries extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function galleries() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('galleries_model');
        $this->load->model('groups_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
        $this->path = 'Library settings / Galleries';
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
        $all = $this->galleries_model->get_galleries_count($filter);
        $data['galleries'] = $this->galleries_model->get_galleries_list($filter, $limit);
        $data['paging'] = $this->api->get_pagination('galleries/view', $all, $this->settings['perpage']);
        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $res['galleries_list'] = $this->load->view('cliplog/galleries_list', array('galleries' => $data['galleries'], 'lang' => $data['lang']), true);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else{
            $content = $this->load->view('galleries/view', $data, true);
            $this->out($content);
        }
    }

    function edit() {

        $action = $this->id ? 'Edit' : 'Add';
        $this->path .= ' / ' . $action;

        $check = $this->galleries_model->get_gallery($this->id);
        if ($check['provider_id'] === $this->session->userdata('client_uid')
            || $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']
            || !$this->id) {

            if ($this->input->post('save') && $this->check_details()) {
                $id = $this->galleries_model->save_gallery($this->id);
                if($this->input->is_ajax_request()){
                    $res = array('success' => 1);
                    $this->output->set_content_type('application/json');;
                    echo json_encode($res);
                    exit();
                }
                else{
                    if ($this->id) {
                        redirect($this->langs . '/galleries/view');
                    } else {
                        redirect($this->langs . '/galleries/edit/' . $id);
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
                $data = $this->galleries_model->get_gallery($this->id);
            }
            if($this->id){
                $data['clips'] = $this->galleries_model->get_gallery_clips($this->id);
            }
            $data['lang'] = $this->langs;
            $content = $this->load->view('galleries/edit', $data, true);
            $this->out($content);
        }
        else{
            redirect($this->langs . '/galleries/view');
        }
    }

    function items(){
        if($this->input->post('items_ids') && $this->id){
            $this->galleries_model->add_items($this->id, $this->input->post('items_ids'));
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
                $this->db_master->update('lib_galleries', array('sort' => intval($ord)));
            }
        }
        redirect($this->langs . '/galleries/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->galleries_model->delete_galleries($ids);
        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else{
            redirect($this->langs . '/galleries/view');
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
    function get_gallery(){
        $res = array();
        if($this->input->post('gallery_id')){
            $gallery = $this->galleries_model->get_gallery($this->input->post('gallery_id'));
            if($gallery){
                $gallery['clips'] = $this->galleries_model->get_gallery_clips($gallery['id']);
                $res['success'] = 1;
                $res['gallery'] = $gallery;
                $this->output->set_content_type('application/json');;
                echo json_encode($res);
                exit();
            }
        }
    }
}