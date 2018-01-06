<?php

class BrowseListItems extends CI_Controller {

    var $id;
    var $langs;
    var $message;
    var $error;
    var $path;

    function BrowseListItems() {
        parent::__construct();
        $this->db_master = $this->load->database('master', TRUE);
        $this->load->model('browselistitems_model');
        $this->load->model('browselists_model');
        $this->id = intval($this->uri->segment(5));
        $this->list_id = intval($this->uri->segment(4));
        $this->langs = $this->uri->segment(1);

        $this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    function edit() {
        if($this->id)
            $this->path = 'List items / Edit Item';
        else
            $this->path = 'List items / Add Item';

        $list = $this->browselists_model->get_browse_list($this->list_id);

        if ($this->input->post('save') && $this->check_details()) {
            $id = $this->browselistitems_model->save_browse_list_item($this->id);
            redirect($this->langs . '/browselists/items/' . $this->list_id);
        }

        $data = $this->input->post();
        if (!$this->error && $this->id) {
            $data = $this->browselistitems_model->get_browse_list_item($this->id);
        }

        $data['lang'] = $this->langs;
        $data['list_id'] = $this->list_id;
        $content = $this->load->view('browse_list_items/edit_' . $list['type'], $data, true);
        $this->out($content);
    }

    function ord() {
        $ids = $this->input->post('ord');

        if(is_array($ids) && count($ids)) {
            foreach($ids as $id=>$ord) {
                $this->db_master->where('id', $id);
                $this->db_master->update('lib_browse_list_items', array('sort' => intval($ord)));
            }
        }
        redirect($this->langs . '/browselists/items/' . $this->list_id);
    }

    function visible() {
        $this->browselistitems_model->change_visible($this->input->post('id'));
        redirect($this->langs . '/browselists/items/' . $this->list_id);
    }

    function delete() {
        if ($this->id)
            $ids[] = $this->id;
        else
            $ids = $this->input->post('id');

        $this->browselistitems_model->delete_browse_list_items($ids);
        redirect($this->langs . '/browselists/items/' . $this->list_id);
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