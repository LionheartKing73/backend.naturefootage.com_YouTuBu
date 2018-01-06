<?php

class Tokens extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Tokens() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('tokens_model');
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
        $this->path = 'Commerce / Download tokens';
        $data['lang'] = $this->langs;
        $filter = array();
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        if($this->group['is_editor'] && $uid){
            $filter['provider_id'] = (int)$uid;
        }
        $limit = $this->get_limit();
        $order_by = 'id desc';
        $all = $this->tokens_model->get_tokens_count($filter);
        $data['tokens'] = $this->tokens_model->get_tokens($filter, $limit, $order_by);
        $data['paging'] = $this->api->get_pagination('tokens/view', $all, $this->settings['perpage']);
        $content = $this->load->view('tokens/view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Download tokens / Edit Token';
        else
            $this->path = 'Download tokens / Add Token';

        $check = $this->tokens_model->get_token($this->id);

        if ($check['provider_id'] === $this->session->userdata('client_uid')
            || $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']
            || !$this->id) {

            if ($this->input->post('save') && $this->check_details()) {
                $this->tokens_model->save_token($this->id);
                redirect($this->langs . '/tokens/view');
            }

            $data = $this->input->post();
            if (!$this->error) {
                $data = $this->tokens_model->get_token($this->id);
            }

            if ($this->group['is_admin']) {
                $data['providers'] = $this->users_model->get_providers_list();
                $data['is_admin'] = true;
            }

            $data['lang'] = $this->langs;
            $content = $this->load->view('tokens/edit', $data, true);
            $this->out($content);

        }
        else
            redirect($this->langs . '/tokens/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->tokens_model->delete_tokens($ids);
        redirect($this->langs . '/tokens/view');
    }

    function visible() {
        $this->tokens_model->change_visible($this->input->post('id'));
        redirect($this->langs . '/tokens/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('token') || !$this->input->post('path')) {
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