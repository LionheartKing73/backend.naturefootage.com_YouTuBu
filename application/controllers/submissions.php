<?php

class Submissions extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function Submissions() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('submissions_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
        $this->path = 'Library settings / Submissions';
    }

    function index() {
        show_404();
    }

    function view() {
        $this->load->model( 'users_model' );

        $user_data = $this->users_model->get_user($this->session->userdata('uid'));
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $filter = array();
        $filter['provider_id'] = ($user_data['id'] && $user_data['group_id'] != 1)?$user_data['id']:(int)$_REQUEST['provider_id'];
        if(isset($_REQUEST['words']) && $_REQUEST['words'])
            $filter['words'] = $_REQUEST['words'];
        $all = $this->submissions_model->get_submissions_count($filter);
        $order_by = 'date DESC';
        $data['submissions'] = $this->submissions_model->get_submissions_list($filter, $limit, $order_by);
        $data['paging'] = $this->api->get_pagination('submissions/view', $all, $this->settings['perpage']);
        if($this->input->is_ajax_request()){
            $data['submissions'] = $this->submissions_model->get_submissions_tree($filter, array(), $order_by);
            $res = array('success' => 1);
            $res['submissions_list'] = $this->load->view('cliplog/submissions_tree', array('submissions' => $data['submissions'], 'lang' => $data['lang']), true);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        $content = $this->load->view('submissions/view', $data, true);
        $this->out($content);
    }

    function edit() {

        $action = $this->id ? 'Edit' : 'Add';
        $this->path .= ' / ' . $action;

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->submissions_model->save_submission($this->id);
            if($this->input->is_ajax_request()){
                $res = array('success' => 1);
                $this->output->set_content_type('application/json');;
                echo json_encode($res);
                exit();
            }
            else{
                if ($this->id) {
                    redirect($this->langs . '/submissions/view');
                } else {
                    redirect($this->langs . '/submissions/edit/' . $id);
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
            $data = $this->submissions_model->get_submission($this->id);
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('submissions/edit', $data, true);
        $this->out($content);
    }

    function ord() {
        $ids = $this->input->post('ord');

        if(is_array($ids) && count($ids)) {
            foreach($ids as $id=>$ord) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_submissions', array('sort' => intval($ord)));
            }
        }
        redirect($this->langs . '/submissions/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->submissions_model->delete_submissions($ids);
        if($this->input->is_ajax_request()){
            $res = array('success' => 1);
            $this->output->set_content_type('application/json');;
            echo json_encode($res);
            exit();
        }
        else{
            redirect($this->langs . '/submissions/view');
        }
    }

    function get_limit() {
        return array('start' => intval($this->uri->segment(4)), 'perpage' => $this->settings['perpage']);
    }

    function check_details() {
        if (!$this->input->post('code')) {
            $this->error = $this->lang->line('empty_fields');
            return false;
        }
        return true;
    }

    function out($content = null, $pagination = null, $type = 1) {
        $this->builder->output(array('content' => $content, 'path' => $this->path, 'pagination' => $pagination,
            'error' => $this->error, 'message' => $this->message), $type);
    }

    //For ajax
    function get_submission(){
        $res = array();
        if($this->input->post('submission_id')){
            $submission = $this->submissions_model->get_submission($this->input->post('submission_id'));
            if($submission){
                $res['success'] = 1;
                $res['submission'] = $submission;
                $this->output->set_content_type('application/json');;
                echo json_encode($res);
                exit();
            }
        }
    }
}