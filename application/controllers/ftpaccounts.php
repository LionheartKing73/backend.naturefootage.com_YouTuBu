<?php

class FtpAccounts extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function FtpAccounts() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('ftpaccounts_model');
        $this->load->model('groups_model');
        $this->load->model('users_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
        $this->set_group();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Commerce / Ftp accounts';
        $data['lang'] = $this->langs;
        $filter = array();
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        if($this->group['is_editor'] && $uid){
            $filter['provider_id'] = (int)$uid;
        }
        $limit = $this->get_limit();
        $order_by = 'id desc';
        $all = $this->ftpaccounts_model->get_ftpaccounts_count($filter);
        $data['ftpaccounts'] = $this->ftpaccounts_model->get_ftpaccounts($filter, $limit, $order_by);
        $store = array();
        require(__DIR__ . '/../config/store.php');
        $data['ftp_host'] = $store['user_delivery']['host'];
        $data['ftp_port'] = $store['user_delivery']['port'];
        $data['paging'] = $this->api->get_pagination('ftpaccounts/view', $all, $this->settings['perpage']);
        $content = $this->load->view('ftpaccounts/view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Ftp accounts / Edit Account';
        else
            $this->path = 'Ftp accounts / Add Account';

        $check = $this->ftpaccounts_model->get_ftpaccount($this->id);

        if ($check['provider_id'] === $this->session->userdata('client_uid')
            || $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']
            || !$this->id) {

            if ($this->input->post('save') && $this->check_details()) {
                $this->ftpaccounts_model->save_ftpaccount($this->id);
                redirect($this->langs . '/ftpaccounts/view');
            }

            $data = $this->input->post();
            if (!$this->error) {
                $data = $this->ftpaccounts_model->get_ftpaccount($this->id);
            }

            if ($this->group['is_admin']) {
                $data['providers'] = $this->users_model->get_providers_list();
                $data['is_admin'] = true;
            }

            $data['lang'] = $this->langs;
            $content = $this->load->view('ftpaccounts/edit', $data, true);
            $this->out($content);

        }
        else
            redirect($this->langs . '/ftpaccounts/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->ftpaccounts_model->delete_ftpaccounts($ids);
        redirect($this->langs . '/ftpaccounts/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('userid') || !$this->input->post('passwd')) {
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
}