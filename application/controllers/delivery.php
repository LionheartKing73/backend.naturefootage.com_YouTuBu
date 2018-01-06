<?php

class Delivery extends CI_Controller {

  var $id;
  var $langs;
  var $error;

  function Delivery() {
    parent::__construct();
      $this->db_master = $this->load->database('master', TRUE);

    $this->load->model('delivery_model','dm');

    $this->id = intval($this->uri->segment(4));
    $this->langs = $this->uri->segment(1);
  }
  
  #------------------------------------------------------------------------------------------------

  function index() {
    show_404();
  }

  #------------------------------------------------------------------------------------------------

  function view() {
    $data['lang'] = $this->langs;
    $data['delivery'] = $this->dm->get_list();
    
    $this->path = 'Library settings / Delivery methods';

    $content = $this->load->view('delivery/view', $data, true);
    $this->out($content);
  }
  
  #------------------------------------------------------------------------------------------------

  function _save() {
    $data = $this->input->post();

    if($this->id) {
      $this->dm->update($this->id);
      redirect($this->langs.'/delivery/view');
    } else {
      $this->id = $this->dm->add();
      if ($this->id) {
        redirect($this->langs.'/delivery/view');
      } else {
        $this->error = 'Database error occured. Please try again.';
      }
    }
  }

  #------------------------------------------------------------------------------------------------

  function check_details() {
    /*if(false) {
      $this->error = $this->lang->line('empty_fields');
      return false;
    }*/

    return true;
  }

  #------------------------------------------------------------------------------------------------

  function edit() {

    if($this->input->post('save') && $this->check_details()) {
      $this->_save();
    }

    if($this->id) {
      $method = $this->dm->get($this->id);
      $data['method'] = $method;
      $data['action'] = 'EDIT DELIVERY METHOD';
    } else {
      $data['method'] = $this->input->post();
      $data['action'] = 'ADD DELIVERY METHOD';
    }
    $data['lang'] = $this->langs;
    
    $action = $this->id ? 'Edit' : 'Add';
    $this->path = 'Library settings / Delivery methods / ' . $action;

    $content = $this->load->view('delivery/edit', $data, true);
    $this->out($content);
  }

  #------------------------------------------------------------------------------------------------

  function ord() {
    $ids = $this->input->post('ord');

    if(is_array($ids) && count($ids)) {
      foreach($ids as $id=>$ord) {
        $this->db_master->where('id', $id);
        $this->db_master->update('lib_delivery', array('ord'=>intval($ord)));
      }
    }
    redirect($this->langs . '/delivery/view');
  }

  #------------------------------------------------------------------------------------------------

  function delete() {
    $ids = $this->input->post('id');
    $this->dm->delete($ids);
    redirect($this->langs.'/delivery/view');
  }

  #------------------------------------------------------------------------------------------------

  function out($content=null, $pagination=null) {
    $this->builder->output(array('content'=>$content, 'path'=>$this->path, 'error'=>$this->error), 1);
  }
}