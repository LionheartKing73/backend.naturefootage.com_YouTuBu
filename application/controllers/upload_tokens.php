<?php

/**
 * Class Upload_tokens
 * @property Upload_tokens_model upload_tokens_model
 * @property Groups_model groups_model
 * @property Users_model users_model
 * @property Labs_model labs_model
 * @property \Invoices_model $invoices_model
 * @property array group - where does it come from?
 */
class Upload_tokens extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Upload_tokens() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('upload_tokens_model');
        $this->load->model('groups_model');
        $this->load->model('users_model');
        $this->load->model('labs_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
        $this->set_group();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Commerce / Upload tokens';
        $data['lang'] = $this->langs;
        $filter = array();
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        if($this->group['is_lab'] && $uid){
            //$filter['provider_id'] = (int)$uid;
        }
        $limit = $this->get_limit();
        $order_by = 'id desc';
        $all = $this->upload_tokens_model->get_tokens_count($filter);
        $data['tokens'] = $this->upload_tokens_model->get_tokens($filter, $limit, $order_by);
        $data['paging'] = $this->api->get_pagination('upload_tokens/view', $all, $this->settings['perpage']);
        $content = $this->load->view('upload_tokens/view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Upload tokens / Edit Token';
        else
            $this->path = 'Upload tokens / Add Token';

        $check = $this->upload_tokens_model->get_token($this->id);

        if ($check['provider_id'] === $this->session->userdata('client_uid')
            || $check['provider_id'] === $this->session->userdata('uid') || $this->group['is_admin'] || $this->group['is_beditor']
            || !$this->id) {

            if ($this->input->post('save') && $this->check_details()) {
                $this->upload_tokens_model->save_token($this->id);
                redirect($this->langs . '/upload_tokens/view');
            }

            $data = $this->input->post();
            if (!$this->error) {
                $data = $this->upload_tokens_model->get_token($this->id);
            }

            if ($this->group['is_admin']) {
                $data['providers'] = $this->users_model->get_providers_list();
                $data['is_admin'] = true;
            }
            $data['labs'] = $this->labs_model->get_labs_list();
            $data['lang'] = $this->langs;
            $content = $this->load->view('upload_tokens/edit', $data, true);
            $this->out($content);

        }
        else{
            redirect($this->langs . '/upload_tokens/view');
        }
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->upload_tokens_model->delete_tokens($ids);
        redirect($this->langs . '/upload_tokens/view');
    }

    function visible() {
        $this->upload_tokens_model->change_visible($this->input->post('id'));
        redirect($this->langs . '/upload_tokens/view');
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('token') || !$this->input->post('path') || !$this->input->post('lab_id') ) {
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

    function test_token_email(){
        $this->load->model('invoices_model');
        $token_id = $this->uri->segment(4);
        echo $token_id;
        $this->invoices_model->send_upload_token($token_id);
        echo 'must be sent..';
        exit();
    }
}