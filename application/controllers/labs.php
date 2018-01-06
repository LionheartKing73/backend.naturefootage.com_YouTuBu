<?php

/**
 * Class Labs
 * @property Labs_model $labs_model
 * @property Groups_model $groups_model
 * @property Users_model $users_model
 * @property Builder builder
 */
class Labs extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;
    var $lab_group_id;

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('labs_model');
        $this->load->model('groups_model');
        $this->load->model('users_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);
        $this->lab_group_id = $this->groups_model->get_lab_group_id();
        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Media Formats / Lab';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'id';
        $all = $this->labs_model->get_labs_count();
        $data['labs'] = $this->labs_model->get_labs_list(array(), $limit, $order_by);
        $data['paging'] = $this->api->get_pagination('labs/view', $all, $this->settings['perpage']);
        $content = $this->load->view('labs/view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Media Formats / Edit Lab';
        else
            $this->path = 'Media Formats / Add Lab';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->labs_model->save_lab($this->id);
            redirect($this->langs . '/labs/view');
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->labs_model->get_lab($this->id);
            $data['users'] = $this->users_model->get_users_list(' AND lu.group_id='.$this->lab_group_id.' ', '', '');
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('labs/edit', $data, true);
        $this->out($content);
    }

    function edit_users(){
        if($this->id){
            $ids = $this->input->post('ids');
            $selected_ids = $this->input->post('selected_ids');
            if ($this->input->post('save') && is_array($ids)) {
                $this->labs_model->save_lab_users($this->id, $ids, $selected_ids);
                redirect($this->langs . '/labs/edit_users/'.$this->id);
            }

            $this->path = 'Media Formats / Lab / Users';
            $data['lang'] = $this->langs;
            $limit = $this->get_users_limit();
            $order_by = 'lib_users.id';
            //$all = $this->labs_model->get_lab_users_count($this->id);

            $data['users'] = $this->labs_model->get_available_users($this->lab_group_id, $this->id, array(), $limit, $order_by);
            $data['selected_user_ids'] = $this->labs_model->get_lab_user_ids($this->id);
            $data['id'] = $this->id;
            $users_count = count($data['users']);
            $data['paging'] = $this->api->get_pagination('labs/view', $users_count, $this->settings['perpage']);
            $content = $this->load->view('labs/edit_users', $data, true);
            $this->out($content);
        }else{
            show_404();
        }
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->labs_model->delete_labs($ids);
        redirect($this->langs . '/labs/view');
    }

    function get_users_limit(){
        return array('start' => intval($_REQUEST['start']), 'perpage' => $this->settings['perpage']);
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