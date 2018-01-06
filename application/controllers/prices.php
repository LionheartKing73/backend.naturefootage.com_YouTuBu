<?php

class Prices extends CI_Controller {

  function Prices() {
    parent::__construct();

    $this->load->model('prices_model','pm');
    $this->langs = $this->uri->segment(1);
    $this->id = $this->uri->segment(4);
  }
  
  #------------------------------------------------------------------------------------------------

  function index() {
      show_404();
  }

  #------------------------------------------------------------------------------------------------

  function view($data=null) {

    $data['prices'] = $this->pm->get_prices_list();
    $data['lang'] = $this->langs;
    
    $this->path = 'Library settings / Prices';

    $content = $this->load->view('prices/view', $data, true);
    $this->out($content);
  }
  
  #------------------------------------------------------------------------------------------------
  
    function edit() {
       if($this->input->post('save') && $this->check_details()) {
         $this->pm->save_price($this->id);
         redirect($this->langs.'/prices/view');
       }

       $data['id'] = $this->id;
       $data['action'] = $this->id ? 'EDIT PRICE' : 'ADD PRICE';
       $data['price'] = ($this->error) ? $_POST : $this->pm->get_price($this->id);
       
       $data['lang'] = $this->langs;
       
       $action = $this->id ? 'Edit' : 'Add';
       $this->path = 'Library settings / Prices / ' . $action;

       $content = $this->load->view('prices/edit', $data, true);
       $this->out($content);
    }

    #------------------------------------------------------------------------------------------------

    function delete() {
        if($this->id) $ids[] = $this->id;
        else $ids = $this->input->post('id');

        $this->pm->delete_prices($ids);
        redirect($this->langs.'/prices/view');
    }

    #------------------------------------------------------------------------------------------------

    function check_details() {
       if(!$this->input->post('code') || !$this->input->post('price')) {
          $this->error = $this->lang->line('empty_fields');
          return false;
       }
       return true;
    }

    #------------------------------------------------------------------------------------------------

    function out($content=null, $pagination=null, $type=1) {
      $this->builder->output(array('content'=>$content,'path'=>$this->path,
        'pagination'=>$pagination,'error'=>$this->error),$type);
    }
}