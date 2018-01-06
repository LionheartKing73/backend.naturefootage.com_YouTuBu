<?php

class Rss extends CI_Controller {

    var $id;
    var $langs;
    var $settings;
    var $error;
    
	function Rss()
	{
		parent::__construct();
        
        $this->load->library('rss_reader'); 	       
        $this->load->model('rss_model','nm'); 

        $this->id = $this->uri->segment(4);  
        $this->langs = $this->uri->segment(1); 
        $this->settings = $this->api->settings(); 
	}
	
    #------------------------------------------------------------------------------------------------
    
    function index()
    {
        show_404();
    }
    
    #------------------------------------------------------------------------------------------------
    
    function view()
    { 
        $data['channels'] = $this->nm->get_channels_list(); 
        $data['uri'] = $this->api->prepare_uri(); 
        $data['lang'] = $this->langs;
        
        $this->path = 'Manage system / RSS channels';
        
        $content = $this->load->view('rss/view', $data, true);
        $this->out($content);    
    }
    
    #------------------------------------------------------------------------------------------------
        
    function delete()
    {   
        if($this->id) $ids[] = $this->id;
        else $ids = $this->input->post('id'); 
        
        $this->nm->delete_channels($ids);
        $this->api->log('log_rss_edit', $ids);
        redirect($this->langs.'/rss/view');
    } 

    #------------------------------------------------------------------------------------------------
    
    function edit()
    {
        if($this->input->post('save') && $this->check_details()){
          $this->nm->save_channel($this->id);
          
          if($this->id) $this->api->log('log_rss_edit', $this->id);   
          else $this->api->log('log_rss_new');
          
          redirect($this->langs.'/rss/view');
        }
        
        $data = ($this->error) ? $_POST : $this->nm->get_channel($this->id);
          
        $data['id'] = ($this->id) ? $this->id : '';
        $data['lang'] = $this->langs;
        $data['lgs'] = $this->config->item('support_languages');
        
        $action = $this->id ? 'Edit' : 'Add';
        $this->path = 'Manage system / RSS channels / ' . $action;
         
        $content = $this->load->view('rss/edit', $data, true);  
        $this->out($content); 
    }
    
    #------------------------------------------------------------------------------------------------
    
    function items()
    { 
        $channel = $this->nm->get_channel($this->id);

        $data['items'] = $this->rss_reader->Retrieve($channel['url']); 
        $data['uri'] = $this->api->prepare_uri(); 
        $data['lang'] = $this->langs;
        $data['channel'] = $this->id;

        $content = $this->load->view('rss/items', $data, true);
        $this->out($content);    
    }

    #------------------------------------------------------------------------------------------------
    
    function publish()
    {
        $channel = $this->nm->get_channel($this->id);
        $item = $this->uri->segment(5); 
        $data = $this->rss_reader->Retrieve($channel['url'],100,$item);
        $data['body'] = $data['description'];
        $data['lang'] = $this->langs;
         
        $content = $this->load->view('news/edit', $data, true);  
        $this->out($content); 
    }
            
    #------------------------------------------------------------------------------------------------
            
    function check_details()
    {  
       if(!$this->input->post('title') || !$this->input->post('url') || !$this->input->post('lang')){
          $this->error = $this->lang->line('empty_fields');
          return false;
       }
       return true;
    }

    #------------------------------------------------------------------------------------------------
        
    function out($content=null, $pagination=null)
    {        
        $this->builder->output(array('content'=>$content, 'path'=>$this->path,
          'pagination'=>$pagination, 'error'=>$this->error), 1);
    }       
}
