<?php

class FootageTypes extends CI_Controller {
  var $langs;
  var $error;
  var $message;

  function __construct() {
    parent::__construct();
    $this->load->model('footage_types_model');
    $this->langs = $this->uri->segment(1);
  }

  function view() {
    $data['lang'] = $this->langs;
    $data['footage_types'] = $this->footage_types_model->get_list($this->langs);
    $content = $this->load->view('footage_types/view', $data, true);
    $this->path = 'Library settings / Footage types';
    $this->out($content);
  }

  function save() {
    $ids = $this->input->post('id');
    if ($ids) {
      $names = $this->input->post('name');
      foreach ($ids as $key=>$id) {
        if ((intval($id) == 0) && $names[$key]) {
          $this->footage_types_model->insert($names[$key], $this->langs);
        } else {
          $this->footage_types_model->update($ids[$key], $names[$key], $this->langs);
        }
      }
    }
    redirect('/' . $this->langs . '/footagetypes/view');
  }

  function delete() {
    $id = intval($this->uri->segment(4));
    $this->footage_types_model->delete($id);
    redirect('/' . $this->langs . '/footagetypes/view');
  }

  function out($content) {
    $this->builder->output(array('content'=>$content, 'path'=>$this->path,
      'error'=>$this->error, 'message'=>$this->message), 1);
  }
}