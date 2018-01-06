<?php
class Locations extends CI_Controller {

  var $langs;
  var $id;
  
  #------------------------------------------------------------------------------------------------

  function Locations() {
    parent::__construct();

    $this->load->model('locations_model','lm');
    $this->langs = $this->uri->segment(1);
    $this->id = $this->uri->segment(4);
  }

  #------------------------------------------------------------------------------------------------

  function index() {
    show_404();
  }

  #------------------------------------------------------------------------------------------------

  function view() {
    $id = $this->id;
    
    if (!$id) {
      $stored_location = $this->session->userdata('selected_location');
      if ($stored_location) {
        $id = $stored_location;
      }
    } else {    
      $this->session->set_userdata('selected_location', $id);
    }

    $selected = array();

    if ($id) {
      $selected[] = $id;
      $id = $this->lm->get_parent_id($id);
      if ($id) {
        $selected[] = $id;
      }
      $selected = array_reverse($selected);
    }

    $data['lang'] = $this->langs;
    $data['countries'] = $this->lm->get_list(0, $this->langs);
    if ($selected[0]) {
      $data['selected'] = $selected;
      $data['provinces'] = $this->lm->get_list($selected[0], $this->langs);
    }
    if ($selected[1]) {
      $data['cities'] = $this->lm->get_list($selected[1], $this->langs);
    }
    
    $this->path = 'Library settings / Locations';

    $content = $this->load->view('locations/view', $data, true);
    $this->out($content);
  }

  #------------------------------------------------------------------------------------------------

  function edit() {
    $data['lang'] = $this->langs;

    if (isset($_POST['save'])) {
      if ($this->id) {
        $this->lm->update($this->id, $_POST['name'], $this->langs, $_POST['parent_id']);
      } else {
        $id = $this->lm->add($_POST['name'], $this->langs, $_POST['parent_id']);
        if (!$id) {
          $this->error = 'Database error occured.';
        }
      }
      if (!$this->error) {
        redirect($this->langs . '/locations/view');
      }
    }

    if ($this->id) {
      $data['location'] = $this->lm->get($this->id, $this->langs);
      $data['parent_id'] = $data['location']['parent_id'];

      if (!$data['parent_id']) {
        $data['location_type'] = 'Country';
      } else {
        $data['location_type'] = 'Province';
        $data['parent_type'] = 'Country';
        $grandparent_id = $this->lm->get_parent_id($data['parent_id']);
        if ($grandparent_id) {
          $data['location_type'] = 'Location';
          $data['parent_type'] = 'Province';
        }
      }
    } else {        
      if (isset($_POST['add_province'])) {
        $data['location_type'] = 'Province';
        $data['parent_type'] = 'Country';
        $data['parent_id'] = $_POST['country'];
      } elseif (isset($_POST['add_location'])) {
        $data['location_type'] = 'Location';
        $data['parent_type'] = 'Province';
        $data['parent_id'] = $_POST['province'];
      } else {
        $data['location_type'] = 'Country';
      }
    }

    if ($data['parent_id']) {
      $data['parents'] = $this->lm->get_list($this->lm->get_parent_id($data['parent_id']), $this->langs);
    }
    
    $action = $this->id ? 'Edit' : 'Add';
    $this->path = 'Library settings / Locations / ' . $action;

    $content = $this->load->view('locations/edit', $data, true);
    $this->out($content);
  }

  #------------------------------------------------------------------------------------------------

  function delete() {
    $parent_id = $this->lm->get_parent_id($this->id);
    $this->lm->delete($this->id);
    $url = '/' . $this->langs . '/locations/view';
    if ($parent_id) {
      $url .= '/' . $parent_id;
    }
    redirect($url);
  }

  #------------------------------------------------------------------------------------------------

  function out($content=null, $type=1) {
    $this->builder->output(array('content'=>$content, 'path'=>$this->path, 'error'=>$this->error,
      'message'=>$this->message), $type);
  }
}