<?php

class Rm extends CI_Controller {

    var $error;
    var $langs; 
    
	function Rm()
	{
		parent::__construct();
        
        $this->load->model('rm_model','rm');
        $this->langs = $this->uri->segment(1);	
	}
    
    #------------------------------------------------------------------------------------------------
    
    function index()
    {
        show_404();
    }
    
    #------------------------------------------------------------------------------------------------
    
    function view()
    {
        $sets = $this->input->post('sets'); 
   
        if($this->input->post('save') && $this->check_details($sets)){
          $this->rm->save_rm($sets);
          $this->api->log('log_settings_save');
        }
        
        $data['sets'] = $this->rm->get_rm(); 
        $data['lang'] = $this->langs;
        
        $this->path = 'Library settings / RM manager';
                      
        $content = $this->load->view('rm/view', $data, true);
        $this->out($content); 
    }
    
    #------------------------------------------------------------------------------------------------
            
    function check_details($sets)
    {      
        foreach($sets as $set){
          if(empty($set)){
            $this->error = $this->lang->line('empty_fields'); 
            return false;
          }
        }
       return true;
    }
        
    #------------------------------------------------------------------------------------------------
        
    function out($content=null, $pagination=null)
    {        
        $this->builder->output(array('content'=>$content,'path'=>$this->path,
          'pagination'=>$pagination,'error'=>$this->error),1);
    }       
}
