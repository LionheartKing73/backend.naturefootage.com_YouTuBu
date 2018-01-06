<?php

class Blocks extends CI_Controller {

  var $id;
  var $langs;
  var $settings;
  var $error;
  
  function blocks() {
    parent::__construct();

    $this->load->model('blocks_model', 'bm');

    $this->id = $this->uri->segment(4);
    $this->langs = $this->uri->segment(1);
    $this->settings = $this->api->settings();
  }

  #------------------------------------------------------------------------------------------------

  function edit() {
    $this->path = 'Manage system / Edit Block';

    if($this->input->post('save')) {
      $this->bm->save($this->id, $this->langs);
    }

    $data = $this->bm->get($this->id,$this->langs);
    
    $content = $this->load->view('blocks/edit', $data, true);
    $this->out($content, '', 3);
  }

  #------------------------------------------------------------------------------------------------

  function out($content=null, $pagination=null, $type=1) {
    $this->builder->output(array('content'=>$content, 'path'=>$this->path,
      'pagination'=>$pagination, 'error'=>$this->error), $type);
  }

  #------------------------------------------------------------------------------------------------

  function out_home($content, $parts) {
    $parts['content'] = $content;
    $this->builder->output($parts, 0);
  }
}
