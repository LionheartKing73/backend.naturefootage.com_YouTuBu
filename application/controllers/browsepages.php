<?php

class BrowsePages extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function BrowsePages() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('browsepages_model');
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
        $this->path = 'Manage system / Browse Pages';
        $data['lang'] = $this->langs;
        $filter = array();
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        if($this->group['is_editor'] && $uid){
            $filter['provider_id'] = (int)$uid;
        }
        $limit = $this->get_limit();
        $order_by = 'sort, id desc';
        $all = $this->browsepages_model->get_browse_pages_count($filter);
        $data['browse_pages'] = $this->browsepages_model->get_browse_pages_list($filter, $limit, $order_by);
        $data['paging'] = $this->api->get_pagination('browsepages/view', $all, $this->settings['perpage']);
        $content = $this->load->view('browse_pages/view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Browse Pages / Edit Page';
        else
            $this->path = 'Browse Pages / Add Page';

        $check = $this->browsepages_model->get_browse_page($this->id);

        if ($check['provider_id'] === $this->session->userdata('client_uid')
            || $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']
            || !$this->id) {

            if ($this->input->post('save') && $this->check_details()) {
                $id = $this->browsepages_model->save_browse_page($this->id);
                redirect($this->langs . '/browsepages/view');
            }

            $data = $this->input->post();
            if (!$this->error) {
                $data = $this->browsepages_model->get_browse_page($this->id);
            }

            if ($this->group['is_admin']) {
                $data['providers'] = $this->users_model->get_providers_list();
                $data['is_admin'] = true;
            }

            $data['lang'] = $this->langs;
            $content = $this->load->view('browse_pages/edit', $data, true);
            $this->out($content);

        }
        else
            redirect($this->langs . '/browsepages/view');
    }

    function lists(){

        $this->path = 'Browse Pages / Lists';

        $check = $this->browsepages_model->get_browse_page($this->id);

        if ($check['provider_id'] === $this->session->userdata('client_uid')
            || $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']
            || !$this->id) {

            if ($this->input->post('save')) {
                $this->browsepages_model->save_lists($this->id, $this->input->post('id'));
                redirect($this->langs . '/browsepages/view');
            }

            $data['lang'] = $this->langs;
            $data['id'] = $this->id;
            $filter = array();
            $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
            if($this->group['is_editor'] && $uid){
                $filter['provider_id'] = (int)$uid;
            }
            $this->load->model('browselists_model');
            $data['lists'] = $this->browselists_model->get_browse_page_lists($this->id);
            $content = $this->load->view('browse_pages/lists', $data, true);
            $this->out($content);
        }
        else
            redirect($this->langs . '/browsepages/view');
    }

    function ord() {
        $ids = $this->input->post('ord');

        if(is_array($ids) && count($ids)) {
            foreach($ids as $id=>$ord) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_browse_pages', array('sort' => intval($ord)));
            }
        }
        redirect($this->langs . '/browsepages/view');
    }

    function visible() {
        $this->browsepages_model->change_visible($this->input->post('id'));
        redirect($this->langs . '/browsepages/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->browsepages_model->delete_browse_pages($ids);
        redirect($this->langs . '/browsepages/view');
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
}