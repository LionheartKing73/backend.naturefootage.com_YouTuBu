<?php

class SharedPages extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function SharedPages() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('sharedpages_model');
        $this->id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function view() {
        $this->path = 'Manage system / Shared Pages';
        $data['lang'] = $this->langs;
        $limit = $this->get_limit();
        $order_by = 'sort, id desc';
        $all = $this->sharedpages_model->get_shared_pages_count();
        $data['shared_pages'] = $this->sharedpages_model->get_shared_pages_list(array(), $limit, $order_by);
        $data['paging'] = $this->api->get_pagination('sharedpages/view', $all, $this->settings['perpage']);
        $content = $this->load->view('shared_pages/view', $data, true);
        $this->out($content);
    }

    function edit() {
        if($this->id)
            $this->path = 'Shared Pages / Edit Page';
        else
            $this->path = 'Shared Pages / Add Page';

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->sharedpages_model->save_shared_page($this->id);
            redirect($this->langs . '/sharedpages/view');
        }

        $data = $this->input->post();
        if (!$this->error) {
            $data = $this->sharedpages_model->get_shared_page($this->id);
        }
        $data['lang'] = $this->langs;
        $content = $this->load->view('shared_pages/edit', $data, true);
        $this->out($content);
    }

    function ord() {
        $ids = $this->input->post('ord');

        if(is_array($ids) && count($ids)) {
            foreach($ids as $id=>$ord) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_shared_pages', array('sort' => intval($ord)));
            }
        }
        redirect($this->langs . '/sharedpages/view');
    }

    function visible() {
        $this->sharedpages_model->change_visible($this->input->post('id'));
        redirect($this->langs . '/sharedpages/view');
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->sharedpages_model->delete_shared_pages($ids);
        redirect($this->langs . '/sharedpages/view');
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
}