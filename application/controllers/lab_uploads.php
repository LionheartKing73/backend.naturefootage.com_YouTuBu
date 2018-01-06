<?php

/**
 * Class Labs
 * @property Invoices_model $invoices_model
 * @property Labs_model $labs_model
 * @property Groups_model $groups_model
 * @property Upload_tokens_model $upload_tokens_model
 */
class Lab_uploads extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function __construct() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('invoices_model');
        $this->load->model('labs_model');
        $this->load->model('groups_model');
        $this->load->model('upload_tokens_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        if($this->session->userdata('uid') && $this->groups_model->is_user_in_lab_group($this->session->userdata('uid'))){
            $this->path = 'Media Formats / Lab';
            $data['lang'] = $this->langs;
            //$data['tokens'] = $this->upload_tokens_model->get_tokens_by_uid($this->session->userdata('uid'));
            $limit = $this->get_limit();
            $order_by = 'id';
            $lab_ids = $this->labs_model->get_lab_ids_by_user_id($this->session->userdata('uid'));
            $all = $this->invoices_model->get_lab_invoices_count($lab_ids);
            $data['orders'] = $this->invoices_model->get_lab_invoices_list($lab_ids, $limit, $order_by);
            $data['paging'] = $this->api->get_pagination('labs/view', $all, $this->settings['perpage']);
            $this->load->model('users_model');
            $user = $this->users_model->get_user( $this->session->userdata('uid') );
            $data['provider_password'] = $user['password'];
            $content = $this->load->view('lab_uploads/index', $data, true);
            $this->out($content);
        }else{
            $this->load->model('users_model');
            $user = $this->users_model->get_user($this->session->userdata('uid'));

            if($user['group_id'] == 1){
                $this->path = 'Media Formats / Lab';
                $data['lang'] = $this->langs;
                $limit = $this->get_limit();
                $order_by = 'id';
                $lab_ids = $this->labs_model->get_labs_id_list();
                $all = $this->invoices_model->get_lab_invoices_count($lab_ids);
                $data['orders'] = $this->invoices_model->get_lab_invoices_list($lab_ids, $limit, $order_by);
                $data['paging'] = $this->api->get_pagination('labs/view', $all, $this->settings['perpage']);
                $this->load->model('users_model');
                $user = $this->users_model->get_user( $this->session->userdata('uid') );
                $data['provider_password'] = $user['password'];
                $content = $this->load->view('lab_uploads/index', $data, true);
                $this->out($content);
            }else{
                redirect('/' . $this->langs . '/login');
            }

        }
    }

    function get_limit () {
        return array ( 'start' => intval( $_REQUEST['start'] ), 'perpage' => $this->settings[ 'perpage' ] );
    }

    function out ( $content = NULL, $pagination = NULL, $type = 1 ) {
        $this->builder->output( array ( 'content' => $content, 'path' => $this->path, 'pagination' => $pagination,
            'error'   => $this->error, 'message' => $this->message ), $type );
    }
}