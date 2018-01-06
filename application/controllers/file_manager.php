<?php

class File_manager extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;
    var $user;
    var $group;

    function __construct() {
        parent::__construct();
        //$this->load->model('hints_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);
        $this->settings = $this->api->settings();
        $this->load->model('users_model');
        $this->set_user();
        $this->set_group();
    }

    function index() {
        show_404();
    }

    function view() {

        $this->path = 'Manage system / File manager';
        if($this->user && ($this->user['storage_account'] || $this->group['is_admin'])) {

            $data['lang'] = $this->langs;
            $this->config->load('aspera', TRUE);
            $data['aspera_config'] = $this->config->item('aspera');
            $data['home_path'] = '/' . $this->user['login'];

            if($this->group['is_admin']) {
                $data['home_path'] = '/';
                $data['users'] = $this->users_model->get_users_with_storage_account();
                if(isset($_REQUEST['user']) && $_REQUEST['user']) {
                    $selected_user = $this->users_model->get_user((int)$_REQUEST['user']);
                    if($selected_user) {
                        $data['selected_user'] = $selected_user['id'];
                        $data['home_path'] .= $selected_user['login'];
                    }
                }
            }

            $content = $this->load->view('file_manager/view', $data, true);
        }
        else {
            $this->error = 'Permission denied';
        }
        $this->out($content);
    }

    function upload_notification() {
        if($this->input->post('paths')) {
            $paths = array();
            foreach($this->input->post('paths') as $path) {
                $paths[] = $path['source'];
            }
            $this->load->model('file_manager_model');
            $this->file_manager_model->send_upload_notification($this->user['login'], $paths, $this->input->post('destination_root'));
        }
        exit();
    }

    function node_api () {
        if ($this->input->post('path')) {

            if ($this->user) {
                $this->config->load('aspera', TRUE);
                $aspera_config = $this->config->item('aspera');
                $node_api_host = $aspera_config['node_api_host'];
                $node_api_port = $aspera_config['node_api_port'];
                $node_api_login = $aspera_config['node_api_usersstorage_user'];
                $node_api_password = $aspera_config['node_api_usersstorage_password'];

                $params = json_decode($this->input->post('params'), true);
                if(isset($params['path']) && !$this->group['is_admin'] && !preg_match('/^\/' . $this->user['login'] . '.*/', $params['path'])){
                    $params['path'] = '/' . $this->user['login'] . $params['path'];
                }
                $params = json_encode($params);
            }

            if (isset($node_api_login) && isset($node_api_password) && isset($node_api_host)) {
                $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, 'https://' . $node_api_host . ':'
                    . (isset($node_api_port) ? $node_api_port : '9092') . $this->input->post('path'));
                curl_setopt($ch, CURLOPT_USERPWD, $node_api_login . ":" . $node_api_password);
                curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_USERAGENT, $agent);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
                curl_setopt($ch, CURLOPT_POST, TRUE);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                $result = curl_exec($ch);
                $info = curl_getinfo($ch);
                curl_close($ch);
                if ($info['http_code'] == 200)
                    echo $result;
                else
                    $this->output->set_status_header($info['http_code']);
            }
        } else
            $this->output->set_status_header(400);

        exit();
    }

    function set_user() {
        $uid = $this->session->userdata('uid') ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $this->user = $this->users_model->get_user($uid);
    }

    function set_group() {
        $uid = ($this->session->userdata('uid')) ? $this->session->userdata('uid') : $this->session->userdata('client_uid');
        $this->load->model('groups_model');
        $this->group = $this->groups_model->get_group_by_user($uid);
    }


    function out($content = null, $pagination = null, $type = 1) {
        $this->builder->output(array('content' => $content, 'path' => $this->path, 'pagination' => $pagination,
            'error' => $this->error, 'message' => $this->message), $type);
    }
}