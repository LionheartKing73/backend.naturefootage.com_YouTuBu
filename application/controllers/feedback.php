<?php
class Feedback extends CI_Controller {

  var $langs;
  var $method;

  #------------------------------------------------------------------------------------------------

  function Feedback() {
    parent::__construct();

    $this->load->model('feedback_model', 'fm');

    #$this->settings = $this->api->settings();

    $this->langs = $this->uri->segment(1);
    $this->method = $this->uri->segment(3);
    $this->page = $this->uri->segment(5);
  }

  #------------------------------------------------------------------------------------------------

  function index() {
    $data = array();
    
    if (count($_POST)) {
      $errors = $this->fm->get_errors();
      if (!$errors) {
        $this->fm->submit();
        $data['submited'] = true;
      }
      else {
        $data = $_POST;
        $data['errors'] = $errors;
      }
    }
  
    $content['title'] = 'Feedback - stock footage, video library';
    $content['meta_desc'] = 'Feedback - stock footage, ' . $this->api->get_seo_keys(3, 2);
    $content['meta_keys'] = 'Feedback, ' . $this->api->get_seo_keys(3, 2);
    
    $content['add_css'] = 'data/css/feedback.css';
    $content['body'] = $this->load->view('feedback/index', $data, true);
    $this->out($content, 0, 0);
  }

  #------------------------------------------------------------------------------------------------

  function view() {
    $this->path = 'Manage system / Feedback';

    $data = array();
    $data['lang'] = $this->langs;
    $data['feedback'] = $this->fm->get_list();

    $content = $this->load->view('feedback/view', $data, true);

    $this->out($content, '', 1);
  }

  #------------------------------------------------------------------------------------------------
  
  function delete() {
    $ids = $this->input->post('id');
    if (!$ids) {
      $id = intval($this->uri->segment(4));
      $ids = array($id);
    }
    if ($ids) {
      $this->fm->delete($ids);
    }
    redirect($this->langs.'/feedback/view');
  }
  
  #------------------------------------------------------------------------------------------------

  function out($content=null, $pagination=null, $type=1) {
    $this->builder->output(array('content'=>$content, 'path'=>$this->path,
      'pagination'=>$pagination, 'error'=>$this->error), $type);
  }
}
