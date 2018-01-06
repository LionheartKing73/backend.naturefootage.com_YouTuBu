<?php
class Sitemap extends CI_Controller {

  var $langs;

  function Sitemap() {
    parent::__construct();
    $this->langs = $this->uri->segment(1);
    $this->load->model('sitemap_model','smm');
  }
  
  #------------------------------------------------------------------------------------------------
  
  function update() {
    $this->data['lang'] = $this->langs;    
    $this->path = 'Manage system / Site map';
    
    if ($this->input->post('update')) {
      $this->smm->update($this->langs);
      redirect($this->langs . '/sitemap/update/success');
    }
    
    if ($this->uri->segment(4) == 'success') {
      $this->message = 'Sitemap updated.';
    }

    $content = $this->load->view('sitemap/update', $data, true);
    $this->out($content);
  }
  
  #------------------------------------------------------------------------------------------------
  
  function out($content=null) {
    $this->builder->output(array('content'=>$content, 'path'=>$this->path, 'error'=>$this->im->error,
      'message'=>$this->message), 1);
  }
}