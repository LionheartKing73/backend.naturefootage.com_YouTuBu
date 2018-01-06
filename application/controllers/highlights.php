<?php

class Highlights extends AppController {

	var $id;
	var $error;

	function __construct() {
        parent::__construct();
        $this->load->model('highlights_model');
        $this->path = 'Library settings / Highlights';
        $this->id = $this->uri->segment(4);
        //$this->settings = $this->api->settings();
    }

    function index() {
        show_404();
    }

    #------------------------------------------------------------------------------------------------

    function view() {
		$data['highlights'] = $this->highlights_model->get_highlights_list($this->langs);
		$data['uri'] = $this->api->prepare_uri();
		$data['lang'] = $this->langs;

		$content = $this->load->view('highlights/view', $data, true);
		$this->out($content);
	}

	function get_limit() {
		return ' limit ' . intval($this->uri->segment(4)) . ',' . $this->settings['perpage'];
	}

	function ord() {
		$this->highlights_model->change_ord($this->input->post('ord'));
		$this->api->log('log_highlights_ord');
		redirect($this->langs . '/highlights/view');
	}

	function visible() {
		$this->highlights_model->change_visible($this->input->post('id'));
		$this->api->log('log_highlights_visible', $this->input->post('id'));
		redirect($this->langs . '/highlights/view');
	}

	function delete() {
		if ($this->id) {
			$ids[] = $this->id;
		} else {
			$ids = $this->input->post('id');
		}

		$this->api->log('log_highlights_delete', $ids);
		$this->highlights_model->delete_highlights($ids);
		redirect($this->langs . '/highlights/view');
	}

	function edit() {

        if($this->input->post('delete')) {
            $this->highlights_model->delete_resource($this->input->post('sid'));
        }

		if ($this->input->post('save') && $this->check_details()) {
			$sub_id = $this->highlights_model->save_highlight($this->id, $this->langs);

			if ($this->id) {
				$this->api->log('log_highlights_edit', $this->id);
			} else {
				$this->api->log('log_highlights_new');
			}

            $this->highlights_model->upload_resource($sub_id);

			if (!$this->error) {
				redirect($this->langs . '/highlights/view');
			}
		}

		$data = $_POST;

		if (!$this->error) {
			$data = $this->highlights_model->get_highlight($this->id, $this->langs);
		}

        $temp = $this->highlights_model->get_links($this->langs);
        $data['pages'] = $temp;

		$data['id'] = ($this->id) ? $this->id : '';
		$data['lang'] = $this->langs;

		$action = $this->id ? 'Edit' : 'Add';
		$this->path .= ' / ' . $action;

		$content = $this->load->view('highlights/edit', $data, true);
		$this->out($content);
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
